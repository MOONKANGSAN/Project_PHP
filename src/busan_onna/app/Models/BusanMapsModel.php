<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 지역별 탐색 마스터 모델 (busan_maps)
 */
class BusanMapsModel extends Model
{
    protected $table         = 'busan_maps';
    protected $primaryKey    = 'idx';
    protected $allowedFields = ['state', 'name', 'sort_order', 'reg_id', 'reg_date'];
    protected $useTimestamps = false;

    /**
     * 활성 지역 목록을 순서대로 반환
     */
    public function getActiveList(): array
    {
        return $this->where('state', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('idx', 'ASC')
                    ->findAll();
    }

    /**
     * 전체 지역 목록 (관리용)
     */
    public function getAllList(): array
    {
        return $this->orderBy('sort_order', 'ASC')
                    ->orderBy('idx', 'ASC')
                    ->findAll();
    }
}
