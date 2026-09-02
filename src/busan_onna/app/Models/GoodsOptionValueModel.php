<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 옵션 값 모델 (빨강·L 등, 개별 재고·추가금액 포함)
 */
class GoodsOptionValueModel extends Model
{
    protected $table      = 'goods_option_values';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['option_idx', 'value', 'additional_price', 'stock'];

    /**
     * 특정 옵션 그룹의 값 목록
     */
    public function getByOption(int $optionIdx): array
    {
        return $this->where('option_idx', $optionIdx)->findAll();
    }

    /**
     * 옵션 값의 재고 차감 — stock 부족 시 false 반환
     */
    public function decreaseStock(int $idx, int $qty): bool
    {
        return $this->db->table('goods_option_values')
            ->where('idx', $idx)
            ->where('stock >=', $qty)
            ->set('stock', "stock - {$qty}", false)
            ->update();
    }
}
