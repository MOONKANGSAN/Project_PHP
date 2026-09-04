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
     * 백오피스 목록 — orders·user_info JOIN, status 필터, 페이지네이션
     */
    public function getAdminList(string $status = ''): array
    {
        $this->select('refund_requests.*, orders.order_no, orders.status AS order_status, ui.name AS user_name, ui.id AS user_id')
             ->join('orders', 'orders.idx = refund_requests.order_idx', 'left')
             ->join('user_info ui', 'ui.idx = refund_requests.user_idx', 'left');
        if ($status !== '') {
            $this->where('refund_requests.status', $status);
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
