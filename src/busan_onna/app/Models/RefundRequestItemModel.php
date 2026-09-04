<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 환불 요청 대상 상품 모델 — 다건 INSERT, 요청별 조회(order_items JOIN)
 */
class RefundRequestItemModel extends Model
{
    protected $table      = 'refund_request_items';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['refund_request_idx', 'order_item_idx'];

    /**
     * 체크박스로 선택된 상품 idx 배열을 일괄 INSERT
     */
    public function insertItems(int $refundRequestIdx, array $orderItemIdxList): bool
    {
        // 빈 배열 체크 — 선택된 상품이 없으면 false 반환
        if (empty($orderItemIdxList)) {
            return false;
        }
        $rows = array_map(
            fn($itemIdx) => [
                'refund_request_idx' => $refundRequestIdx,
                'order_item_idx'     => (int) $itemIdx,
            ],
            $orderItemIdxList
        );
        return (bool) $this->insertBatch($rows);
    }

    /**
     * 환불 요청별 대상 상품 목록 — order_items JOIN으로 상품명·옵션·금액 포함
     */
    public function getByRefundRequest(int $refundRequestIdx): array
    {
        return $this->select('refund_request_items.order_item_idx, oi.goods_name, oi.option_label, oi.quantity, oi.unit_price')
                    ->join('order_items oi', 'oi.idx = refund_request_items.order_item_idx', 'left')
                    ->where('refund_request_idx', $refundRequestIdx)
                    ->findAll();
    }
}
