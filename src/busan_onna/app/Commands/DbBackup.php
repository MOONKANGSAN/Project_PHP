<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * [스케줄러 2] DB 백업 배치
 *
 * 현재 접속 설정(app/Config/Database.php + .env)에 저장된 DB 정보를 그대로 사용해
 * `mysqldump`로 하루 1개(day 단위 파일명)의 SQL 덤프 파일을 writable/backup/ 에 생성한다.
 *
 * - 파일명이 `db_YYYY-MM-DD.sql` 이므로 같은 날 여러 번 실행되면 마지막 실행 결과로 덮어써진다
 *   (요청사항 "하루에 하나씩"을 파일명 단위로 보장).
 * - 비밀번호를 프로세스 인자로 직접 넘기면 `ps` 등으로 노출될 수 있어,
 *   임시 --defaults-extra-file(ini)에 접속정보를 담아 mysqldump에 전달한 뒤 즉시 삭제한다.
 * - 배포서버에서는 매일 새벽 1시(0 1 * * *) cron으로 `php spark db:backup` 을 호출해서 사용한다.
 * - 오래된 백업이 무한히 쌓이지 않도록 보관 기간(기본 30일)이 지난 파일은 자동 삭제한다.
 *   (.env 에 `DB_BACKUP_RETENTION_DAYS=0` 을 설정하면 자동 삭제를 끈다)
 * - 실행 기록은 CodeIgniter 통합 로그(writable/logs/log-*.log)뿐 아니라, 이 커맨드 전용으로
 *   app/Commands/logs/db_backup-YYYY-MM-DD.log 에도 하루 단위로 따로 남긴다.
 *
 * 실행: php spark db:backup
 */
class DbBackup extends BaseCommand
{
    protected $group       = 'busan-onna';
    protected $name        = 'db:backup';
    protected $description = '현재 DB 접속 설정을 사용해 mysqldump로 writable/backup/ 에 SQL 덤프를 1일 1개씩 생성합니다.';
    protected $usage       = 'db:backup';

    public function run(array $params)
    {
        CLI::write('=== DB 백업 시작 (' . date('Y-m-d H:i:s') . ') ===', 'yellow');
        $this->writeCommandLog('=== 백업 시작 ===');

        // .env(database.default.*)에서 실제 접속정보를 읽어온다 (하드코딩 금지)
        $dbConfig = config('Config\Database')->default;

        if (empty($dbConfig['database'])) {
            CLI::error('DB 설정을 찾을 수 없습니다 (.env의 database.default.* 값을 확인하세요).');
            log_message('error', '[db:backup] DB 설정을 찾을 수 없어 백업을 중단함');
            $this->writeCommandLog('[ERROR] DB 설정을 찾을 수 없어 백업 중단');

            return;
        }

        // mysqldump 실행 파일 경로: 배포서버(Linux)는 PATH에 있는 mysqldump를 그대로 사용,
        // 로컬(Windows) 테스트 시 PATH에 없다면 .env에 DB_BACKUP_MYSQLDUMP_PATH로 전체 경로 지정 가능
        // 예) DB_BACKUP_MYSQLDUMP_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
        $mysqldumpBin = env('DB_BACKUP_MYSQLDUMP_PATH', 'mysqldump');

        $backupDir = WRITEPATH . 'backup' . DIRECTORY_SEPARATOR;
        if (! is_dir($backupDir) && ! mkdir($backupDir, 0775, true) && ! is_dir($backupDir)) {
            CLI::error('백업 디렉터리를 생성할 수 없습니다: ' . $backupDir);
            log_message('error', '[db:backup] 백업 디렉터리 생성 실패: {dir}', ['dir' => $backupDir]);

            return;
        }

        $outputFile = $backupDir . 'db_' . date('Y-m-d') . '.sql';

        $defaultsFile = null;

        try {
            $defaultsFile = $this->writeDefaultsExtraFile($dbConfig);

            $this->runMysqldump($mysqldumpBin, $defaultsFile, $dbConfig['database'], $outputFile);

            $size = filesize($outputFile);
            CLI::write(sprintf('  - 백업 완료: %s (%s)', $outputFile, $this->humanSize($size)), 'green');
            log_message('info', '[db:backup] 백업 완료: {file} ({size} bytes)', [
                'file' => $outputFile,
                'size' => $size,
            ]);
            $this->writeCommandLog(sprintf('백업 완료: %s (%s)', $outputFile, $this->humanSize($size)));
        } catch (\Throwable $e) {
            // 실패한 백업 파일(빈 파일/부분 저장본)은 남겨두지 않는다
            if (is_file($outputFile)) {
                unlink($outputFile);
            }

            CLI::error('  - 백업 실패: ' . $e->getMessage());
            log_message('error', '[db:backup] 백업 실패: {msg}', ['msg' => $e->getMessage()]);
            $this->writeCommandLog('[ERROR] 백업 실패: ' . $e->getMessage());

            return;
        } finally {
            // 비밀번호가 담긴 임시 파일은 성공/실패와 무관하게 반드시 삭제
            if ($defaultsFile !== null && is_file($defaultsFile)) {
                unlink($defaultsFile);
            }
        }

        $this->cleanupOldBackups($backupDir);

        CLI::write('=== DB 백업 종료 ===', 'yellow');
        $this->writeCommandLog('=== 백업 종료 ===');
    }

    /**
     * 이 커맨드 전용 로그 파일에 한 줄 남긴다.
     * CodeIgniter 통합 로그(writable/logs/log-*.log)와는 별개로, 이 커맨드의 실행 이력만
     * app/Commands/logs/db_backup-YYYY-MM-DD.log 에서 바로 확인할 수 있도록 하기 위함.
     *
     * 어디까지나 부가 기능이라, 이 로그 기록이 실패하더라도(예: 배포 직후 이 디렉터리의
     * 소유권/쓰기 권한이 cron 실행 계정과 안 맞는 경우) 절대 본 작업(DB 백업)을 막으면
     * 안 된다 — CI4는 file_put_contents의 PHP 경고까지 예외로 승격시키므로 try/catch로
     * 통째로 감싸서 무슨 일이 있어도 여기서 조용히 넘어가도록 한다.
     */
    private function writeCommandLog(string $message): void
    {
        try {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

            if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
                return;
            }

            $file = $dir . 'db_backup-' . date('Y-m-d') . '.log';
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

            @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // 부가 로그 기록 실패는 무시 — 실패 사실만 통합 로그에 한 번 남겨둔다.
            log_message('warning', '[db:backup] 커맨드 전용 로그 기록 실패: {msg}', ['msg' => $e->getMessage()]);
        }
    }

    /**
     * mysqldump 접속정보를 담은 임시 --defaults-extra-file(ini)을 생성한다.
     * 비밀번호를 커맨드라인 인자(-p비번)로 넘기지 않기 위한 용도.
     */
    private function writeDefaultsExtraFile(array $dbConfig): string
    {
        $lines = ['[client]'];
        $lines[] = 'host=' . $this->iniEscape($dbConfig['hostname'] ?? 'localhost');
        $lines[] = 'port=' . (int) ($dbConfig['port'] ?? 3306);
        $lines[] = 'user=' . $this->iniEscape($dbConfig['username'] ?? '');
        $lines[] = 'password=' . $this->iniEscape($dbConfig['password'] ?? '');

        $path = tempnam(sys_get_temp_dir(), 'busan_onna_dbbackup_');
        if ($path === false) {
            throw new \RuntimeException('임시 접속정보 파일을 생성할 수 없습니다.');
        }

        file_put_contents($path, implode("\n", $lines) . "\n");
        // Linux/서버 환경에서는 소유자만 읽도록 제한 (Windows에서는 별도 효과 없음)
        @chmod($path, 0600);

        return $path;
    }

    /**
     * mysql 옵션 파일(ini) 값 이스케이프: 값을 큰따옴표로 감싸고 backslash/큰따옴표만 이스케이프.
     */
    private function iniEscape(string $value): string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }

    /**
     * proc_open으로 mysqldump를 실행하고, 표준출력을 바로 파일로 흘려보낸다.
     * (셸 리다이렉션 `>` 대신 파일 디스크립터를 직접 지정해 Windows/Linux 양쪽에서 동일하게 동작)
     */
    private function runMysqldump(string $mysqldumpBin, string $defaultsFile, string $database, string $outputFile): void
    {
        $command = [
            $mysqldumpBin,
            '--defaults-extra-file=' . $defaultsFile,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            $database,
        ];

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $outputFile, 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (! is_resource($process)) {
            throw new \RuntimeException("mysqldump 프로세스를 시작할 수 없습니다 (실행파일 경로 확인: {$mysqldumpBin}).");
        }

        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \RuntimeException('mysqldump 종료 코드 ' . $exitCode . ': ' . trim($stderr));
        }

        if (! is_file($outputFile) || filesize($outputFile) === 0) {
            throw new \RuntimeException('mysqldump 결과 파일이 비어 있습니다.' . ($stderr !== '' ? ' (' . trim($stderr) . ')' : ''));
        }
    }

    /**
     * 보관 기간(기본 30일)이 지난 백업 파일을 정리한다.
     * .env의 DB_BACKUP_RETENTION_DAYS=0 으로 설정하면 자동 삭제를 비활성화한다.
     */
    private function cleanupOldBackups(string $backupDir): void
    {
        $retentionDays = (int) env('DB_BACKUP_RETENTION_DAYS', 30);

        if ($retentionDays <= 0) {
            return;
        }

        $threshold = time() - ($retentionDays * 86400);
        $deleted   = 0;

        foreach (glob($backupDir . 'db_*.sql') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            CLI::write(sprintf('  - 보관기간(%d일) 초과 백업 %d건 삭제', $retentionDays, $deleted), 'green');
            log_message('info', '[db:backup] 보관기간 초과로 {n}건 삭제', ['n' => $deleted]);
            $this->writeCommandLog(sprintf('보관기간(%d일) 초과 백업 %d건 삭제', $retentionDays, $deleted));
        }
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
