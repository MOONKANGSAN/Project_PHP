<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PickupLocationModel;
use App\Models\GoodsModel;
use App\Models\GoodsOptionValueModel;
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

        return view('service/order/form', [
            'cartItems'        => $items,
            'total'            => $total,
            'pickups'          => $pickups,
            'impCode'          => env('PORTONE_IMP_CODE', ''),
            'inicisChannelKey' => env('PORTONE_INICIS_CHANNEL_KEY', ''),
            'kakaoChannelKey'  => env('PORTONE_KAKAO_CHANNEL_KEY', ''),
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
     * POST /order/verify — PortOne 결제 검증 후 주문 확정
     * body JSON: { imp_uid, order_idx }
     * 검증 성공 시 재고 차감 + 장바구니 비우기
     */
    public function verify(): void
    {
        $body     = $this->request->getJSON(true) ?? [];
        $impUid   = trim($body['imp_uid']   ?? '');
        $orderIdx = (int) ($body['order_idx'] ?? 0);
        $userIdx  = $this->userIdx();

        /* [DEBUG] 런타임에서 실제 읽히는 PortOne 키 앞자리 확인 — 문제 해결 후 제거 */
        $debugKey    = env('PORTONE_IMP_KEY', '');
        $debugSecret = env('PORTONE_IMP_SECRET', '');
        log_message('debug', '[Order::verify] impKey=' . $debugKey . ' | impSecret(앞20)=' . substr($debugSecret, 0, 20));

        /* 브라우저에서 바로 확인할 수 있도록 임시 조기 반환 — 키 확인 후 제거 */
        if ($this->request->getGet('debug_key') === '1') {
            echo json_encode([
                'imp_key_read'    => $debugKey,
                'imp_secret_head' => substr($debugSecret, 0, 20) . '...',
                'imp_code_read'   => env('PORTONE_IMP_CODE', 'NOT_FOUND'),
            ]);
            return;
        }

        $orderModel = new OrderModel();
        $order      = $orderModel->where('idx', $orderIdx)
                                 ->where('user_idx', $userIdx)
                                 ->first();

        /* 주문이 존재하지 않거나 이미 처리된 경우 */
        if (!$order || $order['status'] !== 'pending') {
            /* [DEBUG] 주문 조회 실패 원인 출력 */
            echo json_encode([
                'success'    => false,
                'message'    => '유효하지 않은 주문입니다.',
                '_debug'     => ['order_found' => !empty($order), 'status' => $order['status'] ?? 'N/A'],
            ]);
            return;
        }

        /* PortOne 결제 금액 검증 */
        $portone = new PortOnePayment();
        $result  = $portone->verify($impUid, (int) $order['total_price']);

        if (!$result['valid']) {
            /* [DEBUG] PortOne API 오류 원인 상세 출력 */
            echo json_encode([
                'success' => false,
                'message' => $result['error'],
                '_debug'  => [
                    'imp_uid'          => $impUid,
                    'expected_amount'  => (int) $order['total_price'],
                    'portone_response' => $result['data'],
                    'portone_raw'      => $result['_raw'] ?? null,
                ],
            ]);
            return;
        }

        /* 주문 상태를 paid로 변경 */
        $orderModel->markPaid($orderIdx, $impUid, $result['data']['pay_method'] ?? 'card');

        /* 재고 차감: 상품 및 옵션값 재고 감소 */
        $goodsModel       = new GoodsModel();
        $optionValueModel = new GoodsOptionValueModel();
        $itemModel        = new OrderItemModel();

        foreach ($itemModel->getByOrder($orderIdx) as $item) {
            $goodsModel->decreaseStock((int) $item['goods_idx'], (int) $item['quantity']);
            if ($item['option_value_idx']) {
                $optionValueModel->decreaseStock((int) $item['option_value_idx'], (int) $item['quantity']);
            }
        }

        /* 결제 완료 후 해당 사용자의 장바구니 전체 삭제 */
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
