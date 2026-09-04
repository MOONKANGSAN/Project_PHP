<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 상품 모델
 */
class GoodsModel extends Model
{
    protected $table      = 'goods';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state', 'vendor_idx', 'name', 'description',
        'price', 'stock', 'delivery_type', 'thumbnail',
        'reg_date', 'edit_date',
    ];

    /**
     * 판매중 상품 목록 조회 (필터·정렬·페이징)
     */
    public function getList(string $q = '', string $deliveryType = '', string $sort = 'latest'): array
    {
        $this->where('state', 1);
        if ($q !== '')            $this->like('name', $q);
        if ($deliveryType !== '') $this->where('delivery_type', (int) $deliveryType);

        match ($sort) {
            'price_asc'  => $this->orderBy('price', 'ASC'),
            'price_desc' => $this->orderBy('price', 'DESC'),
            default      => $this->orderBy('idx', 'DESC'),
        };

        return $this->paginate(12) ?? [];
    }

    /**
     * 상품 상세 조회 (판매중인 것만)
     */
    public function getDetail(int $idx): ?array
    {
        return $this->where('idx', $idx)->where('state', 1)->first();
    }

    /**
     * 현재 상품 제외 다른 판매중 상품 최신순 조회 (상세 페이지 추천 영역용)
     */
    public function getOtherGoods(int $excludeIdx, int $limit = 6): array
    {
        return $this->where('state', 1)
                    ->where('idx !=', $excludeIdx)
                    ->orderBy('idx', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * 재고 차감 — 트랜잭션 내에서 호출, stock 부족 시 false 반환
     */
    public function decreaseStock(int $idx, int $qty): bool
    {
        return $this->db->table('goods')
            ->where('idx', $idx)
            ->where('stock >=', $qty)
            ->set('stock', "stock - {$qty}", false)
            ->update();
    }
}
