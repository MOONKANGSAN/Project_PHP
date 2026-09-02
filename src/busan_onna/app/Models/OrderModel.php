<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 주문 헤더 모델
 */
class OrderModel extends Model
{
    protected $table      = 'orders';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'status', 'order_no', 'user_idx', 'total_price',
        'delivery_type', 'recipient_name', 'recipient_phone',
        'delivery_address', 'pickup_location_idx',
        'payment_key', 'payment_method', 'paid_at',
    ];

    /** 주문 상태 한국어 레이블 */
    public const STATUS_LABELS = [
        'pending'   => '결제대기',
        'paid'      => '결제완료',
        'preparing' => '상품준비중',
        'shipped'   => '배송중',
        'delivered' => '배송완료',
        'cancelled' => '취소됨',
    ];

    /**
     * 주문번호 생성: BO-YYYYMMDD-NNNN (당일 누적 순번)
     */
    public function generateOrderNo(): string
    {
        $date  = date('Ymd');
        $count = $this->where("order_no LIKE 'BO-{$date}-%'")->countAllResults() + 1;
        return sprintf('BO-%s-%04d', $date, $count);
    }

    /**
     * 내 주문 목록 (최신순·페이징)
     */
    public function getMyOrders(int $userIdx): array
    {
        return $this->where('user_idx', $userIdx)
                    ->orderBy('idx', 'DESC')
                    ->paginate(10) ?? [];
    }

    /**
     * 주문 상세 — 본인 주문만 조회
     */
    public function getDetail(int $idx, int $userIdx): ?array
    {
        return $this->where('idx', $idx)->where('user_idx', $userIdx)->first();
    }

    /**
     * 관리자용 주문 목록 (상태·주문번호 필터·페이징)
     */
    public function getAdminList(string $status = '', string $q = ''): array
    {
        if ($status !== '') $this->where('status', $status);
        if ($q !== '')      $this->like('order_no', $q);
        return $this->orderBy('idx', 'DESC')->paginate(20) ?? [];
    }

    /**
     * 결제 완료 처리 — status를 paid로 변경하고 PG 정보 저장
     */
    public function markPaid(int $idx, string $paymentKey, string $method): bool
    {
        return $this->update($idx, [
            'status'         => 'paid',
            'payment_key'    => $paymentKey,
            'payment_method' => $method,
            'paid_at'        => date('Y-m-d H:i:s'),
        ]);
    }
}
