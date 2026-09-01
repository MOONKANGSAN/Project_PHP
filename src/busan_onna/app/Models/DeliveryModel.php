<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 배송 정보 모델 — 택배 주문에만 사용
 */
class DeliveryModel extends Model
{
    protected $table      = 'deliveries';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['order_idx', 'courier', 'tracking_no', 'status', 'updated_at'];

    /** 주요 택배사 목록 */
    public const COURIERS = ['CJ대한통운', '한진택배', '롯데택배', '우체국택배', '로젠택배'];

    /**
     * 특정 주문의 배송 정보 조회
     */
    public function getByOrder(int $orderIdx): ?array
    {
        return $this->where('order_idx', $orderIdx)->first();
    }

    /**
     * 배송 정보 저장 또는 수정 (upsert)
     */
    public function upsert(int $orderIdx, string $courier, string $trackingNo): void
    {
        $existing = $this->getByOrder($orderIdx);
        $data = [
            'courier'     => $courier,
            'tracking_no' => $trackingNo,
            'status'      => 'shipped',
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        if ($existing) {
            $this->update($existing['idx'], $data);
        } else {
            $this->insert(array_merge($data, ['order_idx' => $orderIdx]));
        }
    }
}
