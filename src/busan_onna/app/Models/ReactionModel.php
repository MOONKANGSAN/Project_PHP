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
 */
class ReactionModel extends Model
{
    protected $table      = 'reactions';
    protected $primaryKey = 'idx';

    protected $allowedFields = ['user_idx', 'target_type', 'target_idx', 'type', 'state'];
    protected $useTimestamps = false;

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
            return ['action' => 'added', 'user_reaction' => $type];
        }

        // 같은 타입 클릭
        if ($existing['type'] === $type) {
            if ((int) $existing['state'] !== 9) {
                // 활성 → 취소 (state=9)
                $this->update($existing['idx'], ['state' => 9]);
                return ['action' => 'cancelled', 'user_reaction' => null];
            } else {
                // 취소 → 재활성화 (state=0)
                $this->update($existing['idx'], ['state' => 0]);
                return ['action' => 'reactivated', 'user_reaction' => $type];
            }
        }

        // 다른 타입 클릭 → 타입 변경 + 강제 활성화 (state=1)
        $this->update($existing['idx'], ['type' => $type, 'state' => 1]);
        return ['action' => 'changed', 'user_reaction' => $type];
    }
}
