<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 편의시설(busan_facility) 모델
 * restaurant_idx / place_idx / event_idx 중 하나와 연결
 */
class FacilityModel extends Model
{
    protected $table      = 'busan_facility';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state',
        'restaurant_idx', 'place_idx', 'event_idx',
        'takeout', 'wifi', 'toilet', 'group_seat',
        'reservation', 'waiting_area', 'baby_chair',
        'reg_date', 'edit_date',
    ];

    // 편의시설 항목 정의 (필드명 => [라벨, 옵션 배열])
    public const FIELDS = [
        'takeout'      => ['label' => '포장',     'options' => [0 => '불가', 1 => '가능']],
        'wifi'         => ['label' => '무선인터넷', 'options' => [0 => '불가', 1 => '가능']],
        'toilet'       => ['label' => '화장실',    'options' => [0 => '없음', 1 => '있음', 2 => '남녀 구분', 3 => '건물 내 이용']],
        'group_seat'   => ['label' => '단체석',    'options' => [0 => '불가', 1 => '가능']],
        'reservation'  => ['label' => '예약',      'options' => [0 => '불가', 1 => '가능']],
        'waiting_area' => ['label' => '대기공간',  'options' => [0 => '없음', 1 => '있음']],
        'baby_chair'   => ['label' => '유아의자',  'options' => [0 => '없음', 1 => '있음']],
    ];

    /**
     * 콘텐츠 타입별 조회/저장을 처리하는 내부 공통 메서드
     */
    private function getByKey(string $column, int $idx): ?array
    {
        return $this->where($column, $idx)->first();
    }

    private function saveForKey(string $column, int $idx, array $data): void
    {
        $existing = $this->getByKey($column, $idx);

        $data[$column]     = $idx;
        $data['edit_date'] = date('Y-m-d H:i:s');

        if ($existing) {
            $this->update($existing['idx'], $data);
        } else {
            $data['state']    = 1;
            $data['reg_date'] = date('Y-m-d H:i:s');
            $this->insert($data);
        }
    }

    // ── 맛집 ────────────────────────────────────────────────────────
    public function getByRestaurant(int $idx): ?array  { return $this->getByKey('restaurant_idx', $idx); }
    public function saveForRestaurant(int $idx, array $data): void { $this->saveForKey('restaurant_idx', $idx, $data); }

    // ── 관광지 ──────────────────────────────────────────────────────
    public function getByPlace(int $idx): ?array  { return $this->getByKey('place_idx', $idx); }
    public function saveForPlace(int $idx, array $data): void { $this->saveForKey('place_idx', $idx, $data); }

    // ── 행사·축제 ────────────────────────────────────────────────────
    public function getByEvent(int $idx): ?array  { return $this->getByKey('event_idx', $idx); }
    public function saveForEvent(int $idx, array $data): void { $this->saveForKey('event_idx', $idx, $data); }
}
