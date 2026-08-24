<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * [스케줄러 1] 좋아요 카운트 동기화 배치
 *
 * 맛집/관광지/축제(메인 3대 서비스) 각 테이블의 `like_cnt` 컬럼을
 * `reactions` 테이블(실제 좋아요 원장)에서 "활성화된" 좋아요만 집계해서 반영한다.
 *
 * - "활성화된 좋아요" 정의: reactions.type = 'like' AND reactions.state != 9
 *   (state=9는 사용자가 취소한 반응이라 카운트에서 제외해야 함 — ReactionModel::getCounts()와 동일 기준)
 * - 배포서버에서는 매 정각(0 * * * *) cron으로 `php spark like:sync` 를 호출해서 사용한다.
 * - 실행 기록은 CodeIgniter 통합 로그(writable/logs/log-*.log)뿐 아니라, 이 커맨드 전용으로
 *   app/Commands/logs/like_sync-YYYY-MM-DD.log 에도 하루 단위로 따로 남긴다.
 *
 * 실행: php spark like:sync
 */
class LikeCountSync extends BaseCommand
{
    protected $group       = 'busan-onna';
    protected $name        = 'like:sync';
    protected $description = '맛집/관광지/축제 3개 테이블의 like_cnt를 reactions 테이블 기준으로 재집계해서 반영합니다.';
    protected $usage       = 'like:sync';

    /**
     * 동기화 대상 테이블 매핑
     * table       : like_cnt 를 갱신할 실제 테이블명
     * target_type : reactions.target_type 값 (기획 문서 기준: restaurant / spot / festival)
     * label       : 콘솔 출력용 한글 라벨
     */
    private array $targets = [
        ['table' => 'busan_restaurant', 'target_type' => 'restaurant', 'label' => '맛집'],
        ['table' => 'busan_place',      'target_type' => 'spot',       'label' => '관광지'],
        ['table' => 'busan_event',      'target_type' => 'festival',   'label' => '축제'],
    ];

    public function run(array $params)
    {
        $db = Database::connect();

        CLI::write('=== 좋아요 카운트 동기화 시작 (' . date('Y-m-d H:i:s') . ') ===', 'yellow');
        $this->writeCommandLog('=== 동기화 시작 ===');

        $totalAffected = 0;
        $hasError      = false;

        // 3개 테이블을 하나의 트랜잭션으로 묶어서, 중간에 실패하면 전체 롤백되도록 처리
        $db->transStart();

        foreach ($this->targets as $target) {
            try {
                $affected = $this->syncOne($db, $target['table'], $target['target_type']);
                $totalAffected += $affected;
                CLI::write(sprintf('  - %s(%s): %d건 갱신', $target['label'], $target['table'], $affected), 'green');
                $this->writeCommandLog(sprintf('%s(%s): %d건 갱신', $target['label'], $target['table'], $affected));
            } catch (\Throwable $e) {
                $hasError = true;
                CLI::error(sprintf('  - %s(%s) 동기화 실패: %s', $target['label'], $target['table'], $e->getMessage()));
                log_message('error', '[like:sync] {table} 동기화 실패: {msg}', [
                    'table' => $target['table'],
                    'msg'   => $e->getMessage(),
                ]);
                $this->writeCommandLog(sprintf('[ERROR] %s(%s) 동기화 실패: %s', $target['label'], $target['table'], $e->getMessage()));
            }
        }

        $db->transComplete();

        if (! $db->transStatus() || $hasError) {
            CLI::error('=== 좋아요 카운트 동기화 실패 — 트랜잭션 롤백됨 ===');
            log_message('error', '[like:sync] 트랜잭션 실패로 전체 롤백됨');
            $this->writeCommandLog('=== 동기화 실패 — 트랜잭션 롤백됨 ===');

            return;
        }

        CLI::write(sprintf('=== 좋아요 카운트 동기화 완료 (총 %d건 갱신) ===', $totalAffected), 'yellow');
        log_message('info', '[like:sync] 동기화 완료. 총 {n}건 갱신', ['n' => $totalAffected]);
        $this->writeCommandLog(sprintf('=== 동기화 완료 (총 %d건 갱신) ===', $totalAffected));
    }

    /**
     * 이 커맨드 전용 로그 파일에 한 줄 남긴다.
     * CodeIgniter 통합 로그(writable/logs/log-*.log)와는 별개로, 이 커맨드의 실행 이력만
     * app/Commands/logs/like_sync-YYYY-MM-DD.log 에서 바로 확인할 수 있도록 하기 위함.
     */
    private function writeCommandLog(string $message): void
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $file = $dir . 'like_sync-' . date('Y-m-d') . '.log';
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * 테이블 1개에 대해 reactions 집계 결과를 UPDATE ... LEFT JOIN 한 번으로 반영한다.
     *
     * LEFT JOIN을 쓰는 이유: 좋아요가 하나도(혹은 더 이상) 없는 항목도 반드시 0으로
     * 리셋되어야 하기 때문 (좋아요를 눌렀다가 전부 취소된 경우 등).
     * 값이 실제로 바뀌는 행만 쓰기 위해 WHERE 절로 불필요한 UPDATE(바이너리 로그 부담)를 줄인다.
     */
    private function syncOne($db, string $table, string $targetType): int
    {
        $sql = "
            UPDATE `{$table}` AS t
            LEFT JOIN (
                SELECT target_idx, COUNT(*) AS cnt
                FROM reactions
                WHERE target_type = ?
                  AND type = 'like'
                  AND state != 9
                GROUP BY target_idx
            ) AS c ON c.target_idx = t.idx
            SET t.like_cnt = COALESCE(c.cnt, 0)
            WHERE t.like_cnt <> COALESCE(c.cnt, 0)
        ";

        $query = $db->query($sql, [$targetType]);

        if ($query === false) {
            throw new \RuntimeException($db->error()['message'] ?? 'UPDATE 쿼리 실행 실패');
        }

        return $db->affectedRows();
    }
}
