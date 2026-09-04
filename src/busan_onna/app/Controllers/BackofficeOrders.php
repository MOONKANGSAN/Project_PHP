<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\DeliveryModel;
use App\Models\RefundRequestModel;
use App\Models\RefundRequestItemModel;
use App\Models\RefundRequestImageModel;

/**
 * 백오피스 — 주문 관리 컨트롤러
 * /backoffice/orders/* 요청을 처리한다.
 */
class BackofficeOrders extends BaseController
{
    // 주문 모델 인스턴스
    private OrderModel $model;

    /**
     * 컨트롤러 초기화 — 부모 initController 호출 후 모델 바인딩
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $req,
        \CodeIgniter\HTTP\ResponseInterface $res,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($req, $res, $logger);
        $this->model = new OrderModel();
    }

    /**
     * 공통 뷰 데이터 구성
     * 페이지 제목·관리자 세션 정보·현재 URI를 배열로 반환
     */
    private function base(string $title, array $extra = []): array
    {
        return array_merge([
            'page_title'  => $title,
            'admin'       => [
                'idx'   => session()->get('backoffice.idx'),
                'id'    => session()->get('backoffice.id'),
                'level' => session()->get('backoffice.level'),
            ],
            'current_uri' => '/' . uri_string(),
        ], $extra);
    }

    /**
     * GET /backoffice/orders
     * 주문 목록 — status·q(주문번호) 필터 지원
     */
    public function list(): string
    {
        // 상태 필터 및 검색어 수신
        $status = trim($this->request->getGet('status') ?? '');
        $q      = trim($this->request->getGet('q')      ?? '');

        // 모델에서 페이지네이션 적용 목록 조회
        $orders = $this->model->getAdminList($status, $q);

        return view('backoffice/orders/list', $this->base('주문 관리', [
            'orders'  => $orders,
            'pager'   => $this->model->pager,
            'status'  => $status,
            'q'       => $q,
            'labels'  => OrderModel::STATUS_LABELS,
        ]));
    }

    /**
     * GET /backoffice/orders/{idx}
     * 주문 상세 — 주문·상품·배송 정보 일괄 조회
     */
    public function detail(int $idx): string
    {
        // 주문 기본 정보
        $order    = $this->model->find($idx);
        // 주문 상품 목록
        $items    = (new OrderItemModel())->getByOrder($idx);
        // 배송(송장) 정보
        $delivery = (new DeliveryModel())->getByOrder($idx);

        return view('backoffice/orders/detail', $this->base('주문 상세', [
            'order'    => $order,
            'items'    => $items,
            'delivery' => $delivery,
            'labels'   => OrderModel::STATUS_LABELS,
            'couriers' => DeliveryModel::COURIERS,
        ]));
    }

    /**
     * POST /backoffice/orders/{idx}/status
     * 주문 상태 변경 — STATUS_LABELS에 없는 값은 무시
     */
    public function updateStatus(int $idx)
    {
        // 전송된 상태값이 유효한 경우에만 업데이트
        $status = trim($this->request->getPost('status') ?? '');
        if (array_key_exists($status, OrderModel::STATUS_LABELS)) {
            $this->model->update($idx, ['status' => $status]);
        }
        return redirect()->to("/backoffice/orders/{$idx}")->with('success', '상태가 변경되었습니다.');
    }

    /**
     * GET /backoffice/payments
     * 결제내역 목록 — 상태·주문번호·날짜 범위 필터 지원
     */
    public function payments(): string
    {
        $status   = trim($this->request->getGet('status')    ?? '');
        $q        = trim($this->request->getGet('q')         ?? '');
        $dateFrom = trim($this->request->getGet('date_from') ?? '');
        $dateTo   = trim($this->request->getGet('date_to')   ?? '');

        $orders = $this->model->getPaymentList($status, $q, $dateFrom, $dateTo);

        return view('backoffice/payments/list', $this->base('결제내역', [
            'orders'        => $orders,
            'pager'         => $this->model->pager,
            'status'        => $status,
            'q'             => $q,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'statusLabels'  => OrderModel::STATUS_LABELS,
            'filterLabels'  => OrderModel::PAYMENT_STATUS_FILTER,
            'payKindLabels' => OrderModel::PAY_KIND_LABELS,
        ]));
    }

    /**
     * POST /backoffice/payments/{idx}/cancel
     * 주문 취소 — orders.status를 cancelled로 업데이트
     */
    public function cancelOrder(int $idx)
    {
        $this->model->update($idx, ['status' => 'cancelled']);
        return redirect()->to('/backoffice/payments')->with('success', '주문이 취소되었습니다.');
    }

    /**
     * POST /backoffice/payments/{idx}/status  (AJAX JSON)
     * 결제내역 상태 순환 변경 — paid→preparing→shipped→delivered→paid
     */
    public function updatePaymentStatus(int $idx): void
    {
        /* 순환 순서 정의 */
        $cycle = ['paid' => 'preparing', 'preparing' => 'shipped', 'shipped' => 'delivered', 'delivered' => 'paid'];

        $order = $this->model->find($idx);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => '주문을 찾을 수 없습니다.']);
            return;
        }

        $current = $order['status'];
        if (!array_key_exists($current, $cycle)) {
            echo json_encode(['success' => false, 'message' => '변경할 수 없는 상태입니다.']);
            return;
        }

        $next = $cycle[$current];
        $updateData = ['status' => $next];
        if ($next === 'delivered') {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }
        $this->model->update($idx, $updateData);

        echo json_encode([
            'success'    => true,
            'nextStatus' => $next,
            'nextLabel'  => OrderModel::STATUS_LABELS[$next] ?? $next,
        ]);
    }

    /**
     * POST /backoffice/orders/{idx}/delivery
     * 송장번호 저장 — 저장 후 주문 상태를 shipped로 자동 변경
     */
    public function saveDelivery(int $idx)
    {
        // 택배사 코드 및 송장번호 수신
        $courier    = trim($this->request->getPost('courier')     ?? '');
        $trackingNo = trim($this->request->getPost('tracking_no') ?? '');

        // 배송 정보 upsert (없으면 insert, 있으면 update)
        (new DeliveryModel())->upsert($idx, $courier, $trackingNo);
        // 주문 상태를 배송중으로 자동 전환
        $this->model->update($idx, ['status' => 'shipped']);

        return redirect()->to("/backoffice/orders/{$idx}")->with('success', '송장번호가 저장되었습니다.');
    }

    /**
     * GET /backoffice/refunds — 환불 요청 목록 (status·주문번호·주문자·날짜 필터, 페이지네이션)
     */
    public function refundList(): string
    {
        $status   = trim($this->request->getGet('status')    ?? '');
        $orderNo  = trim($this->request->getGet('order_no')  ?? '');
        $userName = trim($this->request->getGet('user_name') ?? '');
        $dateFrom = trim($this->request->getGet('date_from') ?? '');
        $dateTo   = trim($this->request->getGet('date_to')   ?? '');

        $refundModel = new RefundRequestModel();
        $refunds     = $refundModel->getAdminList($status, $orderNo, $userName, $dateFrom, $dateTo);

        return view('backoffice/refunds/list', $this->base('환불 요청 관리', [
            'refunds'      => $refunds,
            'pager'        => $refundModel->pager,
            'status'       => $status,
            'orderNo'      => $orderNo,
            'userName'     => $userName,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'statusLabels' => RefundRequestModel::STATUS_LABELS,
        ]));
    }

    /**
     * GET /backoffice/refunds/{idx}/detail — 모달용 환불 요청 상세 JSON
     */
    public function refundDetail(int $idx): void
    {
        $this->response->setContentType('application/json');
        $refund = (new RefundRequestModel())->getDetail($idx);
        if (!$refund) {
            echo json_encode(['success' => false, 'message' => '환불 요청을 찾을 수 없습니다.']);
            return;
        }

        $items  = (new RefundRequestItemModel())->getByRefundRequest($idx);
        $images = (new RefundRequestImageModel())->getByRefundRequest($idx);

        echo json_encode([
            'success' => true,
            'refund'  => $refund,
            'items'   => $items,
            'images'  => $images,
        ]);
    }

    /**
     * POST /backoffice/refunds/{idx}/approve — 환불 승인 (AJAX)
     * pending 상태가 아닌 요청은 거부, approve() 반환값 확인
     */
    public function approveRefund(int $idx): void
    {
        $this->response->setContentType('application/json');
        $adminMemo   = trim($this->request->getPost('admin_memo') ?? '');
        $refundModel = new RefundRequestModel();
        $refund      = $refundModel->getDetail($idx);
        if (!$refund || $refund['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => '처리할 수 없는 요청입니다.']);
            return;
        }
        if (!$refundModel->approve($idx, $adminMemo)) {
            echo json_encode(['success' => false, 'message' => '승인 처리 중 오류가 발생했습니다.']);
            return;
        }
        echo json_encode(['success' => true, 'message' => '환불 요청이 승인되었습니다.']);
    }

    /**
     * POST /backoffice/refunds/{idx}/reject — 환불 반려 (AJAX)
     * pending 상태가 아닌 요청은 거부, admin_memo 필수, reject() 반환값 확인
     */
    public function rejectRefund(int $idx): void
    {
        $this->response->setContentType('application/json');
        $adminMemo = trim($this->request->getPost('admin_memo') ?? '');
        if ($adminMemo === '') {
            echo json_encode(['success' => false, 'message' => '반려 사유를 입력해주세요.']);
            return;
        }
        $refundModel = new RefundRequestModel();
        $refund      = $refundModel->getDetail($idx);
        if (!$refund || $refund['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => '처리할 수 없는 요청입니다.']);
            return;
        }
        if (!$refundModel->reject($idx, $adminMemo)) {
            echo json_encode(['success' => false, 'message' => '반려 처리 중 오류가 발생했습니다.']);
            return;
        }
        echo json_encode(['success' => true, 'message' => '환불 요청이 반려되었습니다.']);
    }
}
