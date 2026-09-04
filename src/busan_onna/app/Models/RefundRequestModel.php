<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 환불 요청 헤더 모델 — CRUD, 목록(order/user JOIN), 승인/반려 처리
 */
class RefundRequestModel extends Model
{
    protected $table      = 'refund_requests';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_idx', 'user_idx', 'reason', 'reason_text',
        'status', 'admin_memo', 'processed_at',
    ];

    public const STATUS_LABELS = [
        'pending'  => '대기중',
        'approved' => '승인',
        'rejected' => '반려',
    ];

    public const REASON_LABELS = [
        'change_of_mind'   => '단순 변심',
        'defective'        => '상품 불량 / 파손',
        'wrong_item'       => '상품 오배송 (다른 상품 도착)',
        'delay'            => '배송 지연',
        'not_as_described' => '상품 설명과 다름',
        'duplicate'        => '중복 주문',
        'direct'           => '직접 입력',
    ];

    /**
     * 백오피스 목록 — orders·user_info JOIN, status·주문번호·주문자·날짜 필터, 페이지네이션
     */
    public function getAdminList(
        string $status   = '',
        string $orderNo  = '',
        string $userName = '',
        string $dateFrom = '',
        string $dateTo   = ''
    ): array {
        $this->select('refund_requests.*, orders.order_no, orders.status AS order_status, ui.name AS user_name, ui.id AS user_id')
             ->join('orders', 'orders.idx = refund_requests.order_idx', 'left')
             ->join('user_info ui', 'ui.idx = refund_requests.user_idx', 'left');

        if ($status !== '') {
            $this->where('refund_requests.status', $status);
        }
        if ($orderNo !== '') {
            $this->like('orders.order_no', $orderNo);
        }
        if ($userName !== '') {
            $this->groupStart()
                 ->like('ui.name', $userName)
                 ->orLike('ui.id', $userName)
                 ->groupEnd();
        }
        if ($dateFrom !== '') {
            $this->where('DATE(refund_requests.created_at) >=', $dateFrom);
        }
        if ($dateTo !== '') {
            $this->where('DATE(refund_requests.created_at) <=', $dateTo);
        }

        return $this->orderBy('refund_requests.idx', 'DESC')->paginate(20) ?? [];
    }

    /**
     * 단건 상세 — orders·user_info JOIN
     */
    public function getDetail(int $idx): ?array
    {
        return $this->select('refund_requests.*, orders.order_no, orders.status AS order_status, ui.name AS user_name, ui.id AS user_id')
                    ->join('orders', 'orders.idx = refund_requests.order_idx', 'left')
                    ->join('user_info ui', 'ui.idx = refund_requests.user_idx', 'left')
                    ->where('refund_requests.idx', $idx)
                    ->first();
    }

    /**
     * 주문 목록의 환불 상태 맵 반환 — [order_idx => 'pending'|'approved']
     * pending 우선, 없으면 approved, 둘 다 없으면 맵에 미포함
     */
    public function getRefundStatusMap(array $orderIdxList): array
    {
        if (empty($orderIdxList)) return [];
        $rows = \Config\Database::connect()
            ->table('refund_requests')
            ->select("order_idx,
                CASE WHEN SUM(status='pending')  > 0 THEN 'pending'
                     WHEN SUM(status='approved') > 0 THEN 'approved'
                ELSE NULL END AS refund_status")
            ->whereIn('order_idx', $orderIdxList)
            ->groupBy('order_idx')
            ->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            if ($row['refund_status'] !== null) {
                $map[(int) $row['order_idx']] = $row['refund_status'];
            }
        }
        return $map;
    }

    /**
     * 승인 처리 — status=approved, processed_at 기록
     */
    public function approve(int $idx, string $adminMemo = ''): bool
    {
        return $this->update($idx, [
            'status'       => 'approved',
            // 공백 문자 제거 후 빈 값 확인 — 공백만 입력된 경우 null로 저장
            'admin_memo'   => trim($adminMemo) !== '' ? trim($adminMemo) : null,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 반려 처리 — status=rejected, admin_memo 필수
     */
    public function reject(int $idx, string $adminMemo): bool
    {
        return $this->update($idx, [
            'status'       => 'rejected',
            'admin_memo'   => $adminMemo,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
