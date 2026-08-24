<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 해시태그 연결(join) 테이블 모델
 * hashtag ↔ 콘텐츠(restaurant / place / event) 간 N:M 연결을 관리
 */
class HashtagNumberModel extends Model
{
    protected $table      = 'hashtag_number';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'reg_date', 'state',
        'hashtag_idx',
        'restaurant_idx', 'place_idx', 'event_idx',
    ];

    // ----------------------------------------------------------------
    // 맛집
    // ----------------------------------------------------------------

    /**
     * 맛집에 연결된 태그 목록 반환 (hashtag 테이블 join → name 포함)
     */
    public function getTagsByRestaurant(int $restaurantIdx): array
    {
        return $this->db->table('hashtag_number hn')
                        ->select('hn.idx, hn.hashtag_idx, h.name')
                        ->join('hashtag h', 'h.idx = hn.hashtag_idx')
                        ->where('hn.restaurant_idx', $restaurantIdx)
                        ->where('hn.state', 1)
                        ->get()->getResultArray();
    }

    /**
     * 맛집에 연결된 모든 태그 연결 삭제 (수정 시 재삽입 전 초기화)
     * 삭제된 hashtag_idx 목록을 반환해 use_count 갱신에 사용
     */
    public function deleteByRestaurant(int $restaurantIdx): array
    {
        $rows = $this->where('restaurant_idx', $restaurantIdx)->findAll();
        $this->where('restaurant_idx', $restaurantIdx)->delete();
        return array_column($rows, 'hashtag_idx');
    }

    // ----------------------------------------------------------------
    // 관광지
    // ----------------------------------------------------------------

    public function getTagsByPlace(int $placeIdx): array
    {
        return $this->db->table('hashtag_number hn')
                        ->select('hn.idx, hn.hashtag_idx, h.name')
                        ->join('hashtag h', 'h.idx = hn.hashtag_idx')
                        ->where('hn.place_idx', $placeIdx)
                        ->where('hn.state', 1)
                        ->get()->getResultArray();
    }

    public function deleteByPlace(int $placeIdx): array
    {
        $rows = $this->where('place_idx', $placeIdx)->findAll();
        $this->where('place_idx', $placeIdx)->delete();
        return array_column($rows, 'hashtag_idx');
    }

    // ----------------------------------------------------------------
    // 행사·축제
    // ----------------------------------------------------------------

    public function getTagsByEvent(int $eventIdx): array
    {
        return $this->db->table('hashtag_number hn')
                        ->select('hn.idx, hn.hashtag_idx, h.name')
                        ->join('hashtag h', 'h.idx = hn.hashtag_idx')
                        ->where('hn.event_idx', $eventIdx)
                        ->where('hn.state', 1)
                        ->get()->getResultArray();
    }

    public function deleteByEvent(int $eventIdx): array
    {
        $rows = $this->where('event_idx', $eventIdx)->findAll();
        $this->where('event_idx', $eventIdx)->delete();
        return array_column($rows, 'hashtag_idx');
    }

    // ----------------------------------------------------------------
    // 숨은 명소 (백오피스 이벤트 관리용)
    // ----------------------------------------------------------------

    /**
     * '숨은 명소' 태그가 연결된 맛집·관광지 전체 목록
     * (프로트와 달리 state 값과 무관하게 모두 반환 — 백오피스 관리 화면에서 활성/비활성 토글용)
     */
    public function getHiddenSpotList(): array
    {
        $restaurantRows = $this->db->table('hashtag_number hn')
                        ->select('hn.idx AS hn_idx, hn.state, hn.hashtag_idx, hn.reg_date, hn.restaurant_idx AS content_idx, r.name, r.category_num')
                        ->join('hashtag h', 'h.idx = hn.hashtag_idx')
                        ->join('busan_restaurant r', 'r.idx = hn.restaurant_idx')
                        ->where('h.name', '숨은 명소')
                        ->get()->getResultArray();

        $placeRows = $this->db->table('hashtag_number hn')
                        ->select('hn.idx AS hn_idx, hn.state, hn.hashtag_idx, hn.reg_date, hn.place_idx AS content_idx, p.name, p.category_num')
                        ->join('hashtag h', 'h.idx = hn.hashtag_idx')
                        ->join('busan_place p', 'p.idx = hn.place_idx')
                        ->where('h.name', '숨은 명소')
                        ->get()->getResultArray();

        foreach ($restaurantRows as &$r) { $r['type'] = 'restaurant'; $r['type_label'] = '맛집'; }
        unset($r);
        foreach ($placeRows as &$p) { $p['type'] = 'place'; $p['type_label'] = '관광지'; }
        unset($p);

        $rows = array_merge($restaurantRows, $placeRows);
        usort($rows, static fn ($a, $b) => (int) $b['hn_idx'] <=> (int) $a['hn_idx']);

        return $rows;
    }
}
