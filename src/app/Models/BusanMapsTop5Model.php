<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 지역별 탐색 TOP5 항목 모델 (busan_maps_top5)
 */
class BusanMapsTop5Model extends Model
{
    protected $table         = 'busan_maps_top5';
    protected $primaryKey    = 'idx';
    protected $allowedFields = [
        'state', 'main_idx', 'title', 'link_url',
        'sort_order', 'content_type', 'content_idx',
        'description', 'thumb_url', 'reg_id', 'reg_date',
    ];
    protected $useTimestamps = false;

    /**
     * 특정 지역의 활성 TOP5 목록을 순서대로 반환
     */
    public function getTop5ByRegion(int $mainIdx): array
    {
        return $this->where('main_idx', $mainIdx)
                    ->where('state', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('idx', 'ASC')
                    ->findAll();
    }

    /**
     * 특정 지역의 TOP5를 전부 삭제 후 새로 저장 (일괄 교체 방식)
     */
    public function replaceTop5(int $mainIdx, array $items, string $regId): void
    {
        // 기존 항목 전체 삭제
        $this->where('main_idx', $mainIdx)->delete();

        foreach ($items as $i => $item) {
            $this->insert([
                'state'        => 1,
                'main_idx'     => $mainIdx,
                'title'        => $item['title'],
                'link_url'     => $item['link_url']     ?? null,
                'sort_order'   => $i + 1,
                'content_type' => $item['content_type'] ?? null,
                'content_idx'  => isset($item['content_idx']) ? (int) $item['content_idx'] : null,
                'description'  => $item['description']  ?? null,
                'thumb_url'    => $item['thumb_url']    ?? null,
                'reg_id'       => $regId,
                'reg_date'     => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * 메인 페이지용: 모든 지역의 활성 TOP5를 지역 idx 기준으로 반환
     * 반환 형태: [ main_idx => [ top5_item, ... ], ... ]
     */
    public function getActiveGroupedByRegion(): array
    {
        $rows = $this->where('state', 1)
                     ->orderBy('main_idx', 'ASC')
                     ->orderBy('sort_order', 'ASC')
                     ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['main_idx']][] = $row;
        }
        return $grouped;
    }

    /**
     * 특정 지역의 활성 TOP5만 반환 (메인 페이지 AJAX용)
     */
    public function getActiveByRegion(int $mainIdx): array
    {
        return $this->where('main_idx', $mainIdx)
                    ->where('state', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
}
