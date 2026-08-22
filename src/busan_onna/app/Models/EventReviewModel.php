<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이벤트 방문 후기(event_review) 모델
 * '부산 골목 탐험단' 등 방문인증형 이벤트 상세 페이지의 후기 목록/등록을 담당
 */
class EventReviewModel extends Model
{
    protected $table      = 'event_review';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'event_idx', 'user_idx', 'user_id',
        'spot_name', 'content', 'photo_url',
        'like_cnt', 'state', 'reg_date',
    ];

    /**
     * 특정 이벤트의 노출 상태 후기 목록 (최신순)
     */
    public function getByEvent(int $eventIdx, int $limit = 30): array
    {
        return $this->where('event_idx', $eventIdx)
                    ->where('state', 1)
                    ->orderBy('idx', 'DESC')
                    ->findAll($limit);
    }

    /**
     * 특정 이벤트의 노출 후기 총 개수
     */
    public function countByEvent(int $eventIdx): int
    {
        return $this->where('event_idx', $eventIdx)
                    ->where('state', 1)
                    ->countAllResults();
    }
}
