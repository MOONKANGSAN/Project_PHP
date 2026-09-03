<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PickupLocationModel;
use App\Models\GoodsModel;
use App\Models\GoodsOptionValueModel;
use App\Models\UserInfoModel;
use App\Libraries\PortOnePayment;

/**
 * 주문·결제·마이페이지 컨트롤러
 * - form()        : GET  /order             주문서 폼
 * - store()       : POST /order/store       주문 레코드 생성 (pending)
 * - verify()      : POST /order/verify      PortOne 결제 검증 후 주문 확정
 * - complete()    : GET  /order/complete/N  주문 완료 페이지
 * - myOrders()    : GET  /mypage/orders     내 주문 목록
 * - myOrderDetail(): GET /mypage/orders/N   내 주문 상세
 */
class Order extends BaseController
{
    /**
     * 로그인 체크 — 미로그인 시 로그인 페이지로 리다이렉트
     * @return int 로그인한 사용자 idx
     */
    private function userIdx(): int
    {
        $idx = session()->get('user.idx');
        if (!$idx) {
            redirect()->to('/auth/login')->send();
            exit;
        }
        return (int) $idx;
    }

    /**
     * GET /order — 주문서 폼
     * 장바구니 항목을 가져와 주문서 화면에 표시
     */
    public function form(): string
    {
        $userIdx = $this->userIdx();
        $cart    = new CartModel();
        $items   = $cart->getCartItems($userIdx);

        if (empty($items)) {
            return redirect()->to('/cart')->with('error', '장바구니가 비어있습니다.');
        }

        /* 장바구니 항목의 총액 계산 (단가 + 옵션 추가금액) × 수량 */
        $total   = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $pickups = (new PickupLocationModel())->getActive();

        // PortOne V2 customer.email 필수값 — user_info 에서 조회
        $userInfo  = (new UserInfoModel())->find($userIdx);
        $userEmail = $userInfo['email'] ?? '';

        return view('service/order/form', [
            'cartItems'        => $items,
            'total'            => $total,
            'pickups'          => $pickups,
            'v2StoreId'        => env('PORTONE_V2_STORE_ID', ''),
            'inicisChannelKey' => env('PORTONE_INICIS_CHANNEL_KEY', ''),
            'kakaoChannelKey'  => env('PORTONE_KAKAO_CHANNEL_KEY', ''),
            'userEmail'        => $userEmail,
        ]);
    }

    /**
     * POST /order/store — 주문 레코드 생성 (결제 전 pending 상태)
     * 폼 POST 데이터: delivery_type, recipient_name, recipient_phone,
     *                  delivery_address (택배), pickup_location_idx (픽업)
     * 응답: JSON { success, order_idx, order_no, total_price }
     */
    public function store(): void
    {
        $userIdx = $this->userIdx();
        $post    = $this->request->getPost();

        $cart  = new CartModel();
        $items = $cart->getCartItems($userIdx);

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => '장바구니가 비어있습니다.']);
            return;
        }

        /* 총 결제금액 계산 */
        $total        = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $deliveryType = (int) ($post['delivery_type'] ?? 1);

        $orderModel = new OrderModel();
        $db         = \Config\Database::connect();
        $db->transStart();

        /* 주문 기본 데이터 */
        $orderData = [
            'status'        => 'pending',
            'order_no'      => $orderModel->generateOrderNo(),
            'user_idx'      => $userIdx,
            'total_price'   => $total,
            'delivery_type' => $deliveryType,
        ];

        /* 배송 유형에 따라 추가 필드 분기 */
        if ($deliveryType === 1) {
            /* 택배: 수령인 정보 + 배송지 */
            $orderData['recipient_name']   = trim($post['recipient_name']   ?? '');
            $orderData['recipient_phone']  = trim($post['recipient_phone']  ?? '');
            $orderData['delivery_address'] = trim($post['delivery_address'] ?? '');
        } else {
            /* 픽업: 픽업 장소 idx */
            $orderData['pickup_location_idx'] = (int) ($post['pickup_location_idx'] ?? 0);
        }

        $orderModel->insert($orderData);
        $orderIdx = (int) $orderModel->getInsertID();

        /* 주문 항목 레코드 생성 */
        $itemModel = new OrderItemModel();
        foreach ($items as $item) {
            $unitPrice = $item['price'] + ($item['additional_price'] ?? 0);
            $itemModel->insert([
                'order_idx'        => $orderIdx,
                'goods_idx'        => $item['goods_idx'],
                'vendor_idx'       => null,
                'option_value_idx' => $item['option_value_idx'],
                'goods_name'       => $item['goods_name'],
                'option_label'     => $item['option_name']
                                      ? $item['option_name'] . ': ' . $item['option_value']
                                      : null,
                'quantity'         => $item['quantity'],
                'unit_price'       => $unitPrice,
            ]);
        }

        $db->transComplete();

        echo json_encode([
            'success'     => $db->transStatus(),
            'order_idx'   => $orderIdx,
            'order_no'    => $orderData['order_no'],
            'total_price' => $total,
        ]);
    }

    /**
     * POST /order/verify — PortOne V2 결제 검증 후 주문 확정
     * body JSON: { payment_id, order_idx }
     * payment_id = 결제 시 전달한 orderNo (PortOne paymentId)
     */
    public function verify(): void
    {
        $body      = $this->request->getJSON(true) ?? [];
        $paymentId = trim($body['payment_id'] ?? '');
        $orderIdx  = (int)($body['order_idx'] ?? 0);
        $userIdx   = $this->userIdx();

        if ($paymentId === '' || $orderIdx === 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        $orderModel = new OrderModel();
        $order      = $orderModel->where('idx', $orderIdx)
                                 ->where('user_idx', $userIdx)
                                 ->first();

        if (!$order || $order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => '유효하지 않은 주문입니다.']);
            return;
        }

        // PortOne V2 결제 검증
        $result = (new PortOnePayment())->verify($paymentId, (int)$order['total_price']);

        if (!$result['valid']) {
            log_message('error', '[Order::verify] 검증 실패: ' . $result['error']);
            echo json_encode(['success' => false, 'message' => $result['error']]);
            return;
        }

        // V2 응답 method.type 으로 결제 수단 추출 (Card | EasyPay)
        $methodType = $result['data']['method']['type'] ?? 'card';
        $payMethod  = match ($methodType) {
            'Card'    => 'card',
            'EasyPay' => strtolower($result['data']['method']['easyPay']['provider'] ?? 'easypay'),
            default   => strtolower($methodType),
        };

        // 주문 상태 paid 처리 — pgTxId(PG 트랜잭션 ID) 우선, 없으면 paymentId 저장
        $pgTxId = $result['data']['pgTxId'] ?? $paymentId;
        $orderModel->markPaid($orderIdx, $pgTxId, $payMethod);

        // 재고 차감
        $goodsModel       = new GoodsModel();
        $optionValueModel = new GoodsOptionValueModel();
        $itemModel        = new OrderItemModel();

        foreach ($itemModel->getByOrder($orderIdx) as $item) {
            $goodsModel->decreaseStock((int)$item['goods_idx'], (int)$item['quantity']);
            if ($item['option_value_idx']) {
                $optionValueModel->decreaseStock((int)$item['option_value_idx'], (int)$item['quantity']);
            }
        }

        (new CartModel())->clearByUser($userIdx);

        echo json_encode(['success' => true, 'order_idx' => $orderIdx]);
    }

    /**
     * GET /order/complete/{idx} — 주문 완료 페이지
     * pending 상태 주문은 404 처리
     */
    public function complete(int $idx): string
    {
        $userIdx    = $this->userIdx();
        $orderModel = new OrderModel();
        $order      = $orderModel->getDetail($idx, $userIdx);

        if (!$order || $order['status'] === 'pending') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $items = (new OrderItemModel())->getByOrder($idx);

        return view('service/order/complete', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    /**
     * GET /mypage/orders — 내 주문 목록 (페이지네이션)
     */
    public function myOrders(): string
    {
        $userIdx    = $this->userIdx();
        $orderModel = new OrderModel();
        $orders     = $orderModel->getMyOrders($userIdx);

        return view('service/mypage/orders', [
            'orders' => $orders,
            'pager'  => $orderModel->pager,
            'labels' => OrderModel::STATUS_LABELS,
        ]);
    }

    /**
     * GET /mypage/orders/{idx} — 내 주문 상세
     * 본인 주문이 아니면 404 처리
     */
    public function myOrderDetail(int $idx): string
    {
        $userIdx = $this->userIdx();
        $order   = (new OrderModel())->getDetail($idx, $userIdx);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $items = (new OrderItemModel())->getByOrder($idx);

        return view('service/mypage/order_detail', [
            'order'  => $order,
            'items'  => $items,
            'labels' => OrderModel::STATUS_LABELS,
        ]);
    }
}
