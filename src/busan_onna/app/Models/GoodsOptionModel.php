<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 옵션 그룹 모델 (색상·사이즈 등 옵션 종류)
 */
class GoodsOptionModel extends Model
{
    protected $table      = 'goods_options';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['goods_idx', 'option_name'];

    /**
     * 특정 상품의 옵션 그룹 목록
     */
    public function getByGoods(int $goodsIdx): array
    {
        return $this->where('goods_idx', $goodsIdx)->findAll();
    }
}
