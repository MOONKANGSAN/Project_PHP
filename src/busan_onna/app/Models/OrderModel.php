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
        'delivery_address', 'delivery_address2', 'pickup_location_idx',
        'payment_key', 'payment_method', 'pay_kind', 'paid_at',
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

    /** 결제 분류 레이블 */
    public const PAY_KIND_LABELS = [
        'kakao'  => '카카오페이',
        'inicis' => '이니시스',
    ];

    /** 결제내역 페이지 상태 필터 드롭다운용 레이블 */
    public const PAYMENT_STATUS_FILTER = [
        'pending'   => '결제실패',
        'preparing' => '상품준비중',
        'shipped'   => '배송중',
        'delivered' => '배송완료',
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
     * 결제내역 관리자 목록 — user_info JOIN, 상태·주문번호·날짜 범위 필터, 페이지네이션
     */
    public function getPaymentList(
        string $status  = '',
        string $q       = '',
        string $dateFrom = '',
        string $dateTo   = ''
    ): array {
        $this->select("orders.idx, orders.order_no, orders.status, orders.total_price,
                        orders.delivery_type, orders.recipient_name, orders.recipient_phone,
                        orders.delivery_address, orders.delivery_address2, orders.reg_date,
                        orders.paid_at, orders.pay_kind,
                        ui.name AS orderer_name, ui.id AS orderer_id");
        $this->join('user_info ui', 'orders.user_idx = ui.idx', 'left');

        if ($status   !== '') $this->where('orders.status', $status);
        if ($q        !== '') $this->like('orders.order_no', $q);
        /* paid_at 기준 날짜 범위 필터 — 시작일 00:00:00 ~ 종료일 23:59:59 */
        if ($dateFrom !== '') $this->where('orders.paid_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo   !== '') $this->where('orders.paid_at <=', $dateTo   . ' 23:59:59');

        return $this->orderBy('orders.idx', 'DESC')->paginate(20) ?? [];
    }

    /**
     * 결제 완료 처리 — status를 paid로 변경하고 PG 정보 저장
     * pay_kind: kakao(카카오페이), inicis(이니시스 카드)
     */
    public function markPaid(int $idx, string $paymentKey, string $method, string $payKind = ''): bool
    {
        return $this->update($idx, [
            'status'         => 'paid',
            'payment_key'    => $paymentKey,
            'payment_method' => $method,
            'pay_kind'       => $payKind,
            'paid_at'        => date('Y-m-d H:i:s'),
        ]);
    }
}
