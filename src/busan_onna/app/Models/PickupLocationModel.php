<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 픽업 고정 장소 모델
 */
class PickupLocationModel extends Model
{
    protected $table      = 'pickup_locations';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['state', 'name', 'address'];

    /**
     * 활성 픽업 장소 목록
     */
    public function getActive(): array
    {
        return $this->where('state', 1)->orderBy('idx', 'ASC')->findAll();
    }
}
