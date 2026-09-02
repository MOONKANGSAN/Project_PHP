<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 입점 판매자 모델 — state: 0=대기, 1=승인, 2=거절
 */
class VendorModel extends Model
{
    protected $table      = 'vendors';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['state', 'user_idx', 'shop_name', 'contact', 'note'];

    /**
     * 판매자 목록 조회 (state 필터·페이징)
     */
    public function getList(string $state = ''): array
    {
        if ($state !== '') $this->where('state', (int) $state);
        return $this->orderBy('idx', 'DESC')->paginate(20) ?? [];
    }

    /**
     * 특정 사용자의 승인된 판매자 정보 조회
     */
    public function getApprovedByUser(int $userIdx): ?array
    {
        return $this->where('user_idx', $userIdx)->where('state', 1)->first();
    }
}
