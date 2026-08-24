<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 추천/비추천 반응 모델
 * reactions 테이블과 매핑되며 토글·집계·유저 반응 조회를 담당한다.
 *
 * state 값 정의
 *  1 : 신규 활성 (카운트 포함)
 *  9 : 취소 (카운트 제외)
 *  0 : 재활성화 (카운트 포함)
 *
 * [좋아요 카운트 방식 변경 - 2026-08]
 * 기존에는 배치 스케줄러(php spark like:sync, 매 정각)가 reactions 테이블을 집계해서
 * 각 서비스 테이블의 like_cnt를 주기적으로 재계산했지만, 이제는 실시간 방식으로 전환한다.
 * toggle() 호출 시점에 실제로 "좋아요(like)" 활성 상태가 바뀌는 경우에만
 * adjustLikeCount()로 like_cnt 컬럼을 그 자리에서 +1 / -1 즉시 반영한다.
 * (비추천/dislike는 like_cnt에 영향을 주지 않는다)
 */
class ReactionModel extends Model
{
    protected $table      = 'reactions';
    protected $primaryKey = 'idx';

    protected $allowedFields = ['user_idx', 'target_type', 'target_idx', 'type', 'state'];
    protected $useTimestamps = false;

    /**
     * target_type → like_cnt를 실시간으로 갱신할 실제 서비스 테이블 매핑
     * (app/Commands/LikeCountSync.php의 $targets와 동일한 대상)
     */
    private const LIKE_COUNT_TABLES = [
        'restaurant' => 'busan_restaurant',
        'spot'       => 'busan_place',
        'festival'   => 'busan_event',
    ];

    /**
     * 특정 유저의 특정 대상 반응 단건 조회 (state 무관)
     */
    public function getUserReaction(int $userIdx, string $targetType, int $targetIdx): ?array
    {
        return $this->where('user_idx', $userIdx)
                    ->where('target_type', $targetType)
                    ->where('target_idx', $targetIdx)
                    ->first();
    }

    /**
     * 대상별 like / dislike 집계 카운트 반환
     * state=9(취소)는 제외하고, state=0·1(활성)만 집계한다.
     */
    public function getCounts(string $targetType, int $targetIdx): array
    {
        $rows = $this->select('type, COUNT(*) as cnt')
                     ->where('target_type', $targetType)
                     ->where('target_idx', $targetIdx)
                     ->where('state !=', 9)
                     ->groupBy('type')
                     ->findAll();

        $counts = ['like' => 0, 'dislike' => 0];
        foreach ($rows as $row) {
            $counts[$row['type']] = (int) $row['cnt'];
        }

        return $counts;
    }

    /**
     * 반응 토글 처리
     *
     * 기록 없음               → INSERT state=1           → ['action'=>'added',       'user_reaction'=>$type]
     * 활성(state≠9) + 같은타입 → UPDATE state=9           → ['action'=>'cancelled',   'user_reaction'=>null]
     * 취소(state=9) + 같은타입 → UPDATE state=0           → ['action'=>'reactivated', 'user_reaction'=>$type]
     * 어떤 state + 다른 타입   → UPDATE type·state=1     → ['action'=>'changed',     'user_reaction'=>$type]
     *
     * 4가지 분기 각각에서 "좋아요(like) 활성 상태"가 실제로 바뀌었는지 판단해서
     * like_cnt 실시간 증감(adjustLikeCount)을 함께 처리한다.
     */
    public function toggle(int $userIdx, string $targetType, int $targetIdx, string $type): array
    {
        $existing = $this->getUserReaction($userIdx, $targetType, $targetIdx);

        // 처음 반응
        if (!$existing) {
            $this->insert([
                'user_idx'    => $userIdx,
                'target_type' => $targetType,
                'target_idx'  => $targetIdx,
                'type'        => $type,
                'state'       => 1,
            ]);

            // 최초 반응이 '좋아요'인 경우에만 +1 (비추천은 like_cnt에 영향 없음)
            if ($type === 'like') {
                $this->adjustLikeCount($targetType, $targetIdx, 1);
            }

            return ['action' => 'added', 'user_reaction' => $type];
        }

        // 같은 타입 클릭
        if ($existing['type'] === $type) {
            if ((int) $existing['state'] !== 9) {
                // 활성 → 취소 (state=9)
                $this->update($existing['idx'], ['state' => 9]);

                // 취소된 반응이 '좋아요'였다면 -1
                if ($type === 'like') {
                    $this->adjustLikeCount($targetType, $targetIdx, -1);
                }

                return ['action' => 'cancelled', 'user_reaction' => null];
            } else {
                // 취소 → 재활성화 (state=0)
                $this->update($existing['idx'], ['state' => 0]);

                // 재활성화된 반응이 '좋아요'라면 +1
                if ($type === 'like') {
                    $this->adjustLikeCount($targetType, $targetIdx, 1);
                }

                return ['action' => 'reactivated', 'user_reaction' => $type];
            }
        }

        // 다른 타입 클릭 → 타입 변경 + 강제 활성화 (state=1)
        // UPDATE로 덮어쓰기 전에, 변경 전 '좋아요'가 활성 상태였는지 미리 판단해둔다.
        $wasLikeActive = $existing['type'] === 'like' && (int) $existing['state'] !== 9;
        $isLikeAfter   = $type === 'like';

        $this->update($existing['idx'], ['type' => $type, 'state' => 1]);

        if ($wasLikeActive && !$isLikeAfter) {
            // 좋아요(활성) → 비추천으로 전환: -1
            $this->adjustLikeCount($targetType, $targetIdx, -1);
        } elseif (!$wasLikeActive && $isLikeAfter) {
            // 비추천(혹은 취소된 좋아요) → 좋아요로 전환: +1
            $this->adjustLikeCount($targetType, $targetIdx, 1);
        }
        // 그 외(전후 모두 좋아요가 아니었던 경우)는 like_cnt에 영향 없음

        return ['action' => 'changed', 'user_reaction' => $type];
    }

    /**
     * target_type에 매핑된 서비스 테이블의 like_cnt를 delta만큼 즉시 증감시킨다.
     * GREATEST(0, ...)로 감싸서 어떤 경우에도 음수로 내려가지 않도록 방어한다.
     */
    private function adjustLikeCount(string $targetType, int $targetIdx, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        $table = self::LIKE_COUNT_TABLES[$targetType] ?? null;
        if ($table === null) {
            // 알 수 없는 target_type이면 조용히 무시 (컨트롤러 단에서 이미 검증됨)
            return;
        }

        $this->db->query(
            "UPDATE `{$table}` SET like_cnt = GREATEST(0, like_cnt + ?) WHERE idx = ?",
            [$delta, $targetIdx]
        );
    }
}
