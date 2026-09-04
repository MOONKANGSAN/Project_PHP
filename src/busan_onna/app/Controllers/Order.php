<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CartModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PickupLocationModel;
use App\Models\GoodsModel;
use App\Models\GoodsOptionModel;
use App\Models\GoodsOptionValueModel;
use App\Models\UserInfoModel;
use App\Libraries\PortOnePayment;
use App\Models\RefundRequestModel;
use App\Models\RefundRequestItemModel;
use App\Models\RefundRequestImageModel;

/**
 * 주문·결제·마이페이지 컨트롤러
 * - form()        : GET  /order             주문서 폼 (장바구니 또는 즉시구매)
 * - store()       : POST /order/store       주문 레코드 생성 (pending)
 * - verify()      : POST /order/verify      PortOne 결제 검증 후 주문 확정
 * - complete()    : GET  /order/complete/N  주문 완료 페이지
 * - myOrders()    : GET  /mypage/orders     내 주문 목록
 * - myOrderDetail(): GET /mypage/orders/N   내 주문 상세
 */
class Order extends BaseController
{
    /**
     * 로그인 체크 — 미로그인 시 로그인 페이지로 리다이렉트 (일반 요청용)
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
     * 로그인 체크 — AJAX 엔드포인트 전용, 미로그인 시 JSON 401 반환
     * @return int|false 로그인 idx 또는 false(응답 완료)
     */
    private function userIdxForAjax(): int|false
    {
        $idx = (int) session()->get('user.idx');
        if (!$idx) {
            $this->response->setStatusCode(401);
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return false;
        }
        return $idx;
    }

    /**
     * GET /order — 주문서 폼
     * buy_now=1 이면 장바구니 없이 상품 정보로 즉시구매 폼 구성
     * cart_ids 쿼리스트링이 있으면 해당 항목만, 없으면 전체 장바구니
     */
    public function form(): ResponseInterface|string
    {
        $userIdx  = $this->userIdx();
        $userInfo = (new UserInfoModel())->find($userIdx);
        $pickups  = (new PickupLocationModel())->getActive();
        $viewBase = [
            'pickups'          => $pickups,
            'userName'         => $userInfo['name']  ?? $userInfo['id'] ?? '',
            'userPhone'        => $userInfo['phone'] ?? '',
            'v2StoreId'        => env('PORTONE_V2_STORE_ID', ''),
            'inicisChannelKey' => env('PORTONE_INICIS_CHANNEL_KEY', ''),
            'kakaoChannelKey'  => env('PORTONE_KAKAO_CHANNEL_KEY', ''),
            'userEmail'        => $userInfo['email'] ?? '',
        ];

        /* ── 즉시구매 모드: 장바구니를 전혀 건드리지 않음 ── */
        if ($this->request->getGet('buy_now') === '1') {
            $goodsIdx  = (int) ($this->request->getGet('goods_idx') ?? 0);
            $qty       = max(1, (int) ($this->request->getGet('qty') ?? 1));
            $optValIdx = $this->request->getGet('option_value_idx')
                         ? (int) $this->request->getGet('option_value_idx') : null;

            $goods = (new GoodsModel())->getDetail($goodsIdx);
            if (!$goods) {
                return redirect()->to('/goods')->with('error', '상품 정보를 찾을 수 없습니다.');
            }

            /* 옵션 값·이름 조회 */
            $additionalPrice = 0;
            $optionValue     = null;
            $optionName      = null;
            if ($optValIdx) {
                $optVal = (new GoodsOptionValueModel())->find($optValIdx);
                if ($optVal) {
                    $additionalPrice = (int) ($optVal['additional_price'] ?? 0);
                    $optionValue     = $optVal['value'] ?? null;
                    $optGroup        = (new GoodsOptionModel())->find($optVal['option_idx']);
                    $optionName      = $optGroup['option_name'] ?? null;
                }
            }

            /* 장바구니 항목과 동일한 구조의 임시 배열 생성 */
            $items = [[
                'idx'              => 0,
                'goods_idx'        => $goodsIdx,
                'option_value_idx' => $optValIdx,
                'quantity'         => $qty,
                'goods_name'       => $goods['name'],
                'price'            => (int) $goods['price'],
                'thumbnail'        => $goods['thumbnail'] ?? '',
                'stock'            => $goods['stock'],
                'delivery_type'    => $goods['delivery_type'],
                'option_value'     => $optionValue,
                'additional_price' => $additionalPrice,
                'option_name'      => $optionName,
            ]];

            $total = ((int)$goods['price'] + $additionalPrice) * $qty;

            return view('service/order/form', array_merge($viewBase, [
                'cartItems'       => $items,
                'total'           => $total,
                'isBuyNow'        => true,
                'buyNowGoodsIdx'  => $goodsIdx,
                'buyNowQty'       => $qty,
                'buyNowOptValIdx' => $optValIdx ?? 0,
                'cartIds'         => '',
            ]));
        }

        /* ── 장바구니 모드 ── */
        $cart  = new CartModel();
        $items = $cart->getCartItems($userIdx);

        if (empty($items)) {
            return redirect()->to('/cart')->with('error', '장바구니가 비어있습니다.');
        }

        /* cart_ids 쿼리스트링으로 선택 항목 필터링 */
        $cartIdsParam = trim($this->request->getGet('cart_ids') ?? '');
        if ($cartIdsParam !== '') {
            $allowedIds = array_map('intval', explode(',', $cartIdsParam));
            $items = array_values(array_filter(
                $items,
                fn($i) => in_array((int)$i['idx'], $allowedIds, true)
            ));
        }

        if (empty($items)) {
            return redirect()->to('/cart')->with('error', '선택된 상품이 없습니다.');
        }

        $total      = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $cartIdsStr = implode(',', array_column($items, 'idx'));

        return view('service/order/form', array_merge($viewBase, [
            'cartItems' => $items,
            'total'     => $total,
            'isBuyNow'  => false,
            'cartIds'   => $cartIdsStr,
        ]));
    }

    /**
     * POST /order/store — 주문 레코드 생성 (결제 전 pending 상태)
     * buy_now=1 이면 장바구니 없이 상품 정보로 직접 주문 생성
     */
    public function store(): void
    {
        $userIdx = $this->userIdxForAjax();
        if ($userIdx === false) return;

        $post         = $this->request->getPost();
        $orderModel   = new OrderModel();
        $db           = \Config\Database::connect();
        $deliveryType = (int) ($post['delivery_type'] ?? 1);

        /* 공통 배송 데이터 */
        $orderData = [
            'status'        => 'pending',
            'order_no'      => $orderModel->generateOrderNo(),
            'user_idx'      => $userIdx,
            'delivery_type' => $deliveryType,
        ];
        if ($deliveryType === 1) {
            $orderData['recipient_name']    = trim($post['recipient_name']    ?? '');
            $orderData['recipient_phone']   = trim($post['recipient_phone']   ?? '');
            $orderData['delivery_address']  = trim($post['delivery_address']  ?? '');
            $orderData['delivery_address2'] = trim($post['delivery_address2'] ?? '');
        } else {
            $orderData['pickup_location_idx'] = (int) ($post['pickup_location_idx'] ?? 0);
        }

        /* ── 즉시구매 모드: 장바구니 조회·변경 없이 상품 정보로 주문 생성 ── */
        if (($post['buy_now'] ?? '') === '1') {
            $goodsIdx  = (int) ($post['goods_idx'] ?? 0);
            $qty       = max(1, (int) ($post['qty'] ?? 1));
            $optValIdx = !empty($post['option_value_idx']) ? (int) $post['option_value_idx'] : null;

            $goods = (new GoodsModel())->getDetail($goodsIdx);
            if (!$goods) {
                echo json_encode(['success' => false, 'message' => '상품 정보를 찾을 수 없습니다.']);
                return;
            }

            /* #2 재고 상한 검증 */
            if ($qty > (int)$goods['stock']) {
                echo json_encode(['success' => false, 'message' => '재고가 부족합니다.']);
                return;
            }

            $additionalPrice = 0;
            $optionLabel     = null;
            if ($optValIdx) {
                $optVal = (new GoodsOptionValueModel())->find($optValIdx);
                if (!$optVal) {
                    echo json_encode(['success' => false, 'message' => '잘못된 옵션입니다.']);
                    return;
                }
                /* #1 option_value_idx가 해당 상품 소속인지 검증 */
                $optGroup = (new GoodsOptionModel())->find($optVal['option_idx']);
                if (!$optGroup || (int)$optGroup['goods_idx'] !== $goodsIdx) {
                    echo json_encode(['success' => false, 'message' => '잘못된 옵션입니다.']);
                    return;
                }
                $additionalPrice = (int) ($optVal['additional_price'] ?? 0);
                $optionLabel     = $optGroup['option_name'] . ': ' . $optVal['value'];
            }

            $unitPrice = (int)$goods['price'] + $additionalPrice;
            $total     = $unitPrice * $qty;

            $orderData['total_price'] = $total;

            $db->transStart();
            $orderModel->insert($orderData);
            $orderIdx = (int) $orderModel->getInsertID();

            (new OrderItemModel())->insert([
                'order_idx'        => $orderIdx,
                'goods_idx'        => $goodsIdx,
                'vendor_idx'       => null,
                'option_value_idx' => $optValIdx,
                'goods_name'       => $goods['name'],
                'option_label'     => $optionLabel,
                'quantity'         => $qty,
                'unit_price'       => $unitPrice,
            ]);

            $db->transComplete();

            echo json_encode([
                'success'     => $db->transStatus(),
                'order_idx'   => $orderIdx,
                'order_no'    => $orderData['order_no'],
                'total_price' => $total,
            ]);
            return;
        }

        /* ── 장바구니 모드 ── */
        $cart  = new CartModel();
        $items = $cart->getCartItems($userIdx);

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => '장바구니가 비어있습니다.']);
            return;
        }

        $cartIdsParam = trim($post['cart_ids'] ?? '');
        if ($cartIdsParam !== '') {
            $allowedIds = array_map('intval', explode(',', $cartIdsParam));
            $items = array_values(array_filter(
                $items,
                fn($i) => in_array((int)$i['idx'], $allowedIds, true)
            ));
        }

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => '선택된 상품이 없습니다.']);
            return;
        }

        $total = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $orderData['total_price'] = $total;

        $db->transStart();
        $orderModel->insert($orderData);
        $orderIdx  = (int) $orderModel->getInsertID();
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
     * body JSON: { payment_id, order_idx, buy_now? }
     * #6 세션 만료 시 JSON 401 반환 (AJAX 전용 로그인 체크)
     */
    public function verify(): void
    {
        /* #6 AJAX 엔드포인트 — 세션 만료 시 HTML 리다이렉트 대신 JSON 401 */
        $userIdx = $this->userIdxForAjax();
        if ($userIdx === false) return;

        $body      = $this->request->getJSON(true) ?? [];
        $paymentId = trim($body['payment_id'] ?? '');
        $orderIdx  = (int)($body['order_idx'] ?? 0);
        $isBuyNow  = !empty($body['buy_now']);

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

        $easyPayProvider = strtolower($result['data']['method']['easyPay']['provider'] ?? '');
        $payKind = ($methodType === 'EasyPay' && $easyPayProvider === 'kakaopay') ? 'kakao' : 'inicis';

        $pgTxId = $result['data']['pgTxId'] ?? $paymentId;
        $orderModel->markPaid($orderIdx, $pgTxId, $payMethod, $payKind);

        /* #5 재고 차감 — 트랜잭션으로 감싸 실패 시 롤백 */
        $goodsModel       = new GoodsModel();
        $optionValueModel = new GoodsOptionValueModel();
        $itemModel        = new OrderItemModel();
        $db               = \Config\Database::connect();

        $db->transStart();
        foreach ($itemModel->getByOrder($orderIdx) as $item) {
            $ok = $goodsModel->decreaseStock((int)$item['goods_idx'], (int)$item['quantity']);
            if (!$ok) {
                $db->transRollback();
                /* 결제는 완료됐으나 재고 부족 — 관리자 확인 필요 로그 */
                log_message('error', sprintf(
                    '[Order::verify] 재고 차감 실패 order_idx=%d goods_idx=%d qty=%d',
                    $orderIdx, $item['goods_idx'], $item['quantity']
                ));
                echo json_encode(['success' => false, 'message' => '재고 처리 중 오류가 발생했습니다. 고객센터로 문의해주세요.']);
                return;
            }
            if ($item['option_value_idx']) {
                $optionValueModel->decreaseStock((int)$item['option_value_idx'], (int)$item['quantity']);
            }
        }
        $db->transComplete();

        /* 즉시구매는 장바구니와 무관 — clearByUser 건너뜀 */
        if (!$isBuyNow) {
            (new CartModel())->clearByUser($userIdx);
        }

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

        // 조회된 주문 목록의 환불 상태를 한 번에 조회
        $orderIdxList    = array_map('intval', array_column($orders, 'idx'));
        $refundStatusMap = !empty($orderIdxList)
            ? (new RefundRequestModel())->getRefundStatusMap($orderIdxList)
            : [];

        return view('service/mypage/orders', [
            'orders'          => $orders,
            'pager'           => $orderModel->pager,
            'labels'          => OrderModel::STATUS_LABELS,
            'refundStatusMap' => $refundStatusMap,
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

        // 해당 주문의 환불 상태 조회 (pending 우선)
        $refundStatusMap = (new RefundRequestModel())->getRefundStatusMap([$idx]);
        $refundStatus    = $refundStatusMap[$idx] ?? null;

        return view('service/mypage/order_detail', [
            'order'        => $order,
            'items'        => $items,
            'labels'       => OrderModel::STATUS_LABELS,
            'refundStatus' => $refundStatus,
        ]);
    }

    /**
     * POST /mypage/orders/{idx}/refund — 환불 요청 접수 (AJAX)
     * 허용 상태: paid / preparing / delivered
     * 이미지: 최대 3장, jpg/png/gif, 파일당 10MB 이하
     */
    public function requestRefund(int $orderIdx): void
    {
        // 예외가 HTML 에러 페이지로 출력되는 것을 막고 JSON으로 반환
        try {
            $this->_requestRefundInner($orderIdx);
        } catch (\Throwable $e) {
            log_message('error', '[requestRefund] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['success' => false, 'message' => '[서버오류] ' . $e->getMessage()]);
        }
    }

    private function _requestRefundInner(int $orderIdx): void
    {
        // Debug Toolbar가 JSON 응답에 HTML을 삽입하지 않도록 Content-Type 명시
        $this->response->setContentType('application/json');

        $userIdx = $this->userIdxForAjax();
        if ($userIdx === false) return;

        $order = (new OrderModel())->getDetail($orderIdx, $userIdx);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => '주문을 찾을 수 없습니다.']);
            return;
        }

        if (!in_array($order['status'], ['paid', 'preparing', 'delivered'], true)) {
            echo json_encode(['success' => false, 'message' => '환불 요청이 불가능한 주문 상태입니다.']);
            return;
        }

        // 동일 주문의 pending 환불 요청이 이미 존재하면 중복 접수 차단
        $existingRefund = (new RefundRequestModel())->where('order_idx', $orderIdx)->where('status', 'pending')->first();
        if ($existingRefund) {
            echo json_encode(['success' => false, 'message' => '이미 대기 중인 환불 요청이 있습니다.']);
            return;
        }

        // 선택 상품 검증
        $itemIdxList = $this->request->getPost('item_idxs') ?? [];
        if (empty($itemIdxList) || !is_array($itemIdxList)) {
            echo json_encode(['success' => false, 'message' => '환불할 상품을 1개 이상 선택해주세요.']);
            return;
        }
        // 과도한 배열 크기로 인한 DoS 방지
        if (count($itemIdxList) > 50) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        // MySQL이 idx를 문자열로 반환하므로 intval()로 변환 후 비교
        $validIdxs = array_map('intval', array_column((new OrderItemModel())->getByOrder($orderIdx), 'idx'));
        foreach ($itemIdxList as $itemIdx) {
            if (!in_array((int) $itemIdx, $validIdxs, true)) {
                echo json_encode(['success' => false, 'message' => '잘못된 상품 정보입니다.']);
                return;
            }
        }

        // 환불 사유 검증
        $reason = trim($this->request->getPost('reason') ?? '');
        $validReasons = ['change_of_mind', 'defective', 'wrong_item', 'delay', 'not_as_described', 'duplicate', 'direct'];
        if (!in_array($reason, $validReasons, true)) {
            echo json_encode(['success' => false, 'message' => '환불 사유를 선택해주세요.']);
            return;
        }

        $reasonText = null;
        if ($reason === 'direct') {
            $reasonText = trim($this->request->getPost('reason_text') ?? '');
            if ($reasonText === '') {
                echo json_encode(['success' => false, 'message' => '직접 입력 사유를 작성해주세요.']);
                return;
            }
            if (mb_strlen($reasonText) > 200) {
                echo json_encode(['success' => false, 'message' => '사유는 200자 이하로 입력해주세요.']);
                return;
            }
        }

        // 이미지 업로드 처리
        $uploadedPaths = [];
        $images = $this->request->getFiles()['images'] ?? [];
        if (!empty($images) && is_array($images)) {
            $validImages = array_filter($images, fn($f) => $f->isValid() && !$f->hasMoved());
            if (count($validImages) > 3) {
                echo json_encode(['success' => false, 'message' => '이미지는 최대 3장까지 첨부 가능합니다.']);
                return;
            }
            $uploadDir = FCPATH . 'uploads/refunds/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            foreach ($validImages as $img) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($img->getMimeType(), $allowedMimes, true)) {
                    // 이미 저장된 파일 정리 후 응답
                    foreach ($uploadedPaths as $p) { $f = FCPATH . ltrim($p, '/'); if (file_exists($f)) unlink($f); }
                    echo json_encode(['success' => false, 'message' => 'jpg, png, gif 형식만 첨부 가능합니다.']);
                    return;
                }
                if ($img->getSizeByUnit('mb') > 10) {
                    foreach ($uploadedPaths as $p) { $f = FCPATH . ltrim($p, '/'); if (file_exists($f)) unlink($f); }
                    echo json_encode(['success' => false, 'message' => '이미지 1장당 최대 10MB까지 업로드 가능합니다.']);
                    return;
                }
                $newName = $img->getRandomName();
                // move() 실패 시 이미 저장된 파일 정리
                if (!$img->move($uploadDir, $newName)) {
                    foreach ($uploadedPaths as $p) { $f = FCPATH . ltrim($p, '/'); if (file_exists($f)) unlink($f); }
                    echo json_encode(['success' => false, 'message' => '이미지 업로드에 실패했습니다.']);
                    return;
                }
                $uploadedPaths[] = '/uploads/refunds/' . $newName;
            }
        }

        // 트랜잭션으로 DB 저장
        $db = \Config\Database::connect();
        $db->transStart();

        $refundModel = new RefundRequestModel();
        $refundModel->insert([
            'order_idx'   => $orderIdx,
            'user_idx'    => $userIdx,
            'reason'      => $reason,
            'reason_text' => $reasonText,
            'status'      => 'pending',
        ]);
        $refundIdx = (int) $refundModel->getInsertID();

        // INSERT 실패(ID=0) 감지 — 트랜잭션 즉시 롤백 후 파일 정리
        if ($refundIdx === 0) {
            $db->transRollback();
            foreach ($uploadedPaths as $p) { $f = FCPATH . ltrim($p, '/'); if (file_exists($f)) unlink($f); }
            echo json_encode(['success' => false, 'message' => '환불 요청 중 오류가 발생했습니다.']);
            return;
        }

        (new RefundRequestItemModel())->insertItems($refundIdx, $itemIdxList);

        if (!empty($uploadedPaths)) {
            $imageModel = new RefundRequestImageModel();
            foreach ($uploadedPaths as $path) {
                $imageModel->insert(['refund_request_idx' => $refundIdx, 'file_path' => $path]);
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            foreach ($uploadedPaths as $path) {
                $fullPath = FCPATH . ltrim($path, '/');
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            echo json_encode(['success' => false, 'message' => '환불 요청 중 오류가 발생했습니다.']);
            return;
        }

        echo json_encode(['success' => true, 'message' => '환불 요청이 접수되었습니다.']);
    }
}
