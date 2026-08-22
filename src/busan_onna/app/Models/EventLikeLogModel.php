<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이벤트 전용 좋아요 로그 모델
 * '마! 이게 진짜 국밥이다!' 등 투표형 이벤트의 1인 1일 1회 좋아요 기록 및 집계를 담당
 */
class EventLikeLogModel extends Model
{
    protected $table      = 'event_like_log';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'event_idx', 'user_idx', 'restaurant_idx', 'like_date', 'reg_date',
    ];

    /**
     * 오늘 이미 참여했는지 여부 (이벤트 단위 — 맛집 무관 1일 1회)
     */
    public function hasLikedToday(int $eventIdx, int $userIdx): bool
    {
        return (bool) $this->getTodayVote($eventIdx, $userIdx);
    }

    /**
     * 오늘 자신이 투표한 기록 (있으면 배열, 없으면 null)
     */
    public function getTodayVote(int $eventIdx, int $userIdx): ?array
    {
        return $this->where('event_idx', $eventIdx)
                    ->where('user_idx', $userIdx)
                    ->where('like_date', date('Y-m-d'))
                    ->first();
    }

    /**
     * 좋아요 기록 시도 (1인 1일 1회 — 이미 기록이 있으면 false)
     */
    public function tryLike(int $eventIdx, int $userIdx, int $restaurantIdx): bool
    {
        if ($this->hasLikedToday($eventIdx, $userIdx)) {
            return false;
        }

        $this->insert([
            'event_idx'      => $eventIdx,
            'user_idx'       => $userIdx,
            'restaurant_idx' => $restaurantIdx,
            'like_date'      => date('Y-m-d'),
            'reg_date'       => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * 맛집별 누적 득표수 (event_idx 기준)
     * 반환: [restaurant_idx => count, ...]
     */
    public function getVoteCountsByEvent(int $eventIdx): array
    {
        $rows = $this->select('restaurant_idx, COUNT(*) as cnt')
                     ->where('event_idx', $eventIdx)
                     ->groupBy('restaurant_idx')
                     ->findAll();

        return array_column($rows, 'cnt', 'restaurant_idx');
    }

    /**
     * 특정 유저의 참여 일수
     * event_idx + user_idx + like_date 유니크 제약으로 유저당 하루 최대 1건이므로
     * 전체 row 수가 곧 서로 다른 날짜 수(참여 일수)와 같다.
     */
    public function getParticipationDays(int $eventIdx, int $userIdx): int
    {
        return $this->where('event_idx', $eventIdx)
                    ->where('user_idx', $userIdx)
                    ->countAllResults();
    }

    /**
     * 추첨 대상자 목록: 서로 다른 날짜로 $minDays일 이상 참여한 유저
     * 반환: [['user_idx' => ..., 'days' => ...], ...]
     */
    public function getEligibleUsers(int $eventIdx, int $minDays = 3): array
    {
        return $this->select('user_idx, COUNT(*) as days')
                    ->where('event_idx', $eventIdx)
                    ->groupBy('user_idx')
                    ->having('days >=', $minDays)
                    ->findAll();
    }
}
