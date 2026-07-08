<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이미지 join 테이블(busan_thumbnail) 모델
 * 콘텐츠(맛집/관광지/행사)당 최대 8장 이미지를 img_order 순으로 관리
 */
class ThumbnailModel extends Model
{
    protected $table      = 'busan_thumbnail';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'img_order', 'img_url', 'reg_date', 'state',
        'restaurant_idx', 'place_idx', 'event_idx',
    ];

    // ----------------------------------------------------------------
    // 맛집
    // ----------------------------------------------------------------

    public function getByRestaurant(int $restaurantIdx): array
    {
        return $this->where('restaurant_idx', $restaurantIdx)
                    ->where('state', 1)
                    ->orderBy('img_order', 'ASC')
                    ->findAll();
    }

    /**
     * img_order 순으로 정렬 후 1부터 재번호 부여
     */
    public function reorderByRestaurant(int $restaurantIdx): void
    {
        $rows = $this->getByRestaurant($restaurantIdx);
        foreach ($rows as $i => $row) {
            $this->update($row['idx'], ['img_order' => $i + 1]);
        }
    }

    // ----------------------------------------------------------------
    // 관광지
    // ----------------------------------------------------------------

    public function getByPlace(int $placeIdx): array
    {
        return $this->where('place_idx', $placeIdx)
                    ->where('state', 1)
                    ->orderBy('img_order', 'ASC')
                    ->findAll();
    }

    public function reorderByPlace(int $placeIdx): void
    {
        $rows = $this->getByPlace($placeIdx);
        foreach ($rows as $i => $row) {
            $this->update($row['idx'], ['img_order' => $i + 1]);
        }
    }

    // ----------------------------------------------------------------
    // 행사·축제
    // ----------------------------------------------------------------

    public function getByEvent(int $eventIdx): array
    {
        return $this->where('event_idx', $eventIdx)
                    ->where('state', 1)
                    ->orderBy('img_order', 'ASC')
                    ->findAll();
    }

    public function reorderByEvent(int $eventIdx): void
    {
        $rows = $this->getByEvent($eventIdx);
        foreach ($rows as $i => $row) {
            $this->update($row['idx'], ['img_order' => $i + 1]);
        }
    }

    // ----------------------------------------------------------------
    // 공통
    // ----------------------------------------------------------------

    /**
     * DB 레코드 + 서버 파일을 함께 삭제
     */
    public function deleteWithFile(int $idx): void
    {
        $row = $this->find($idx);
        if (!$row) return;

        $filePath = FCPATH . ltrim($row['img_url'], '/');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $this->delete($idx);
    }
}
