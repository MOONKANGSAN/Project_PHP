<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 여행코스 항목 모델 (travel_course_item)
 */
class TravelCourseItemModel extends Model
{
    protected $table      = 'travel_course_item';
    protected $primaryKey = 'idx';
    protected $returnType = 'array';

    public const MAX_ITEMS = 8;

    protected $allowedFields = [
        'course_idx', 'item_order', 'content_type', 'content_idx',
        'name', 'description', 'stay_time', 'address',
        'latitude', 'longitude', 'reg_date',
    ];

    /**
     * 특정 코스의 항목 전체를 순서대로 반환
     */
    public function getByCourse(int $courseIdx): array
    {
        return $this->where('course_idx', $courseIdx)
                    ->orderBy('item_order', 'ASC')
                    ->findAll();
    }

    /**
     * 특정 코스의 항목 전체 삭제
     */
    public function deleteByCourse(int $courseIdx): void
    {
        $this->where('course_idx', $courseIdx)->delete();
    }

    /**
     * 코스 항목 배열을 일괄 저장 (기존 항목 삭제 후 재삽입)
     *
     * @param int   $courseIdx
     * @param array $items  [['name'=>..., 'content_type'=>..., ...], ...]
     */
    public function replaceByCourse(int $courseIdx, array $items): void
    {
        $this->deleteByCourse($courseIdx);

        $now   = date('Y-m-d H:i:s');
        $order = 1;

        foreach (array_slice($items, 0, self::MAX_ITEMS) as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') continue;

            $this->insert([
                'course_idx'   => $courseIdx,
                'item_order'   => $order,
                'content_type' => $item['content_type'] ?? 'custom',
                'content_idx'  => ($item['content_idx'] ?? '') !== '' ? (int) $item['content_idx'] : null,
                'name'         => mb_substr($name, 0, 100),
                'description'  => $item['description'] ?? null,
                'stay_time'    => $item['stay_time']    ?? null,
                'address'      => $item['address']      ?? null,
                'latitude'     => ($item['latitude']    ?? '') !== '' ? (float) $item['latitude']  : null,
                'longitude'    => ($item['longitude']   ?? '') !== '' ? (float) $item['longitude'] : null,
                'reg_date'     => $now,
            ]);

            $order++;
        }
    }
}
