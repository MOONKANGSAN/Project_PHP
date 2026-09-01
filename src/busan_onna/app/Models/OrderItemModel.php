<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 주문 라인 아이템 모델 — 가격은 주문 시점 스냅샷으로 저장
 */
class OrderItemModel extends Model
{
    protected $table      = 'order_items';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_idx', 'goods_idx', 'vendor_idx',
        'option_value_idx', 'goods_name', 'option_label',
        'quantity', 'unit_price',
    ];

    /**
     * 특정 주문의 아이템 목록
     */
    public function getByOrder(int $orderIdx): array
    {
        return $this->where('order_idx', $orderIdx)->findAll();
    }
}
