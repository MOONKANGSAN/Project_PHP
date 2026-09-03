# PortOne V2 결제 API 전면 재구성 구현 계획

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 카드(이니시스)·카카오페이 결제를 PortOne V2 API 단일 방식으로 재구성한다.

**Architecture:** 프론트엔드는 V2 SDK(`cdn.portone.io/v2/browser-sdk.js`)만 사용해 두 결제 수단 모두 `PortOne.requestPayment()`로 호출한다. 서버는 V2 REST API(`api.portone.io`)에 API Secret을 직접 Authorization 헤더로 사용해 결제를 검증한다. V1 SDK와 관련 코드를 완전히 제거해 분기 로직을 없앤다.

**Tech Stack:** CodeIgniter 4, PortOne V2 Browser SDK, PortOne V2 REST API (cURL), PHP 8.1+

---

## 변경 대상 파일 구조

| 파일 | 변경 방향 |
|---|---|
| `busan_onna/.env` | V1 키 제거, V2 키 정리 및 신규 이니시스 채널키 반영 |
| `busan_onna/app/Libraries/PortOnePayment.php` | V1 메서드 전부 제거, V2 단일 `verify()` 로 재작성 |
| `busan_onna/app/Controllers/Order.php` | `form()` — V2 키만 뷰 전달. `verify()` — V2 전용 |
| `busan_onna/app/Views/service/order/form.php` | V1 SDK·IMP.init 제거, V2 SDK 단일화, JS 흐름 재작성 |

---

## API 키 정리 (투입 값)

| 항목 | 값 |
|---|---|
| V2 Store ID | `store-a672e4b2-e565-4fbc-bf8d-2849bc6bb0be` |
| V2 API Secret | `PYqGNFxVKdoYAVEJ0WwVqMayi5FMhPba1qHkbWrQYFB6KlAY7wKCzN9iNYzPosQWlEI3RUzPhtMu9a36` |
| 이니시스 V2 채널키 | `channel-key-2497c7f5-e66f-45b9-ae1d-74b7f87ad00d` |
| 카카오페이 CID (참고) | `TC0ONETIME` |
| 카카오페이 V2 채널키 | **미발급** — 어드민에서 V2 채널 등록 후 `.env`에 직접 기입 필요 |

> **카카오페이 V2 채널키 발급 방법:**
> PortOne 어드민(admin.portone.io) → 결제 연동 → 테스트 연동 관리 → V2 결제 모듈 → 채널 추가 → 카카오페이 선택 → CID: `TC0ONETIME` 입력 → 채널키 복사

---

## 결제 플로우 설계 (V2 전용)

```
[form.php JS]
    ↓ 1. POST /order/store  → pending 주문 생성
    ↓ 2. PortOne.requestPayment({ channelKey, paymentId=orderNo, ... })
    ↓ 3. 결제 완료 rsp.paymentId 수신
    ↓ 4. POST /order/verify { payment_id, order_idx }
[Order.php::verify()]
    ↓ 5. PortOnePayment::verify(paymentId, expectedAmount)
[PortOnePayment.php]
    ↓ 6. GET https://api.portone.io/payments/{paymentId}
         Authorization: PortOne {V2_API_SECRET}
    ↓ 7. status=PAID + amount.total 일치 확인
[Order.php::verify()]
    ↓ 8. markPaid() + 재고 차감 + 장바구니 비우기
    ↓ 9. redirect /order/complete/{idx}
```

---

## Task 1: `.env` — V2 전용으로 정리

**Files:**
- Modify: `busan_onna/.env`

- [ ] **Step 1: .env 수정**

기존 V1 전용 키(`PORTONE_IMP_KEY`, `PORTONE_IMP_SECRET`, `PORTONE_IMP_CODE`, 구 채널키)를 제거하고 V2 키만 남긴다.

```env
#--------------------------------------------------------------------
# PORTONE V2 결제 설정
# 어드민: https://admin.portone.io
#--------------------------------------------------------------------

# 상점 아이디 (프론트 SDK storeId 파라미터)
PORTONE_V2_STORE_ID=store-a672e4b2-e565-4fbc-bf8d-2849bc6bb0be

# V2 API Secret (서버 결제 검증용 — Authorization: PortOne {secret})
PORTONE_V2_API_SECRET=PYqGNFxVKdoYAVEJ0WwVqMayi5FMhPba1qHkbWrQYFB6KlAY7wKCzN9iNYzPosQWlEI3RUzPhtMu9a36

# 이니시스 V2 채널키 (어드민 > 결제 연동 > V2 > KG이니시스 채널)
PORTONE_INICIS_CHANNEL_KEY=channel-key-2497c7f5-e66f-45b9-ae1d-74b7f87ad00d

# 카카오페이 V2 채널키 — 어드민에서 V2 채널 등록 후 아래에 입력
PORTONE_KAKAO_CHANNEL_KEY=여기에입력
```

---

## Task 2: `PortOnePayment.php` — V2 전용 재작성

**Files:**
- Modify: `busan_onna/app/Libraries/PortOnePayment.php`

- [ ] **Step 1: PortOnePayment.php 전면 재작성**

V1 메서드(`getToken`, `verify`, `verifyV2`) 전부 제거. V2 단일 `verify()` + 내부 `get()` 만 남긴다.

```php
<?php
namespace App\Libraries;

/**
 * PortOne V2 REST API 결제 검증 라이브러리
 * - 이니시스 카드, 카카오페이 공통 사용
 * - API Secret을 Authorization 헤더에 직접 사용 (토큰 교환 불필요)
 * - 엔드포인트: GET https://api.portone.io/payments/{paymentId}
 */
class PortOnePayment
{
    private string $apiSecret;
    private string $baseUrl = 'https://api.portone.io';

    public function __construct()
    {
        $this->apiSecret = env('PORTONE_V2_API_SECRET', '');
    }

    /**
     * paymentId(=주문번호)로 결제 검증 — 상태·금액 일치 확인
     *
     * @return array{valid: bool, data: array|null, error: string}
     */
    public function verify(string $paymentId, int $expectedAmount): array
    {
        try {
            $url      = $this->baseUrl . '/payments/' . urlencode($paymentId);
            $response = $this->get($url);

            log_message('debug', '[PortOne V2] verify paymentId=' . $paymentId
                . ' status=' . ($response['status'] ?? 'N/A'));

            // 오류 응답: type 또는 code 필드가 있고 status 없음
            if (isset($response['type']) || (isset($response['code']) && !isset($response['status']))) {
                $msg = $response['message'] ?? ($response['type'] ?? ($response['code'] ?? 'API 오류'));
                return ['valid' => false, 'data' => null, 'error' => $msg];
            }

            if (($response['status'] ?? '') !== 'PAID') {
                return ['valid'  => false, 'data' => $response,
                        'error' => '미결제 상태: ' . ($response['status'] ?? 'UNKNOWN')];
            }

            $paidAmount = (int)($response['amount']['total'] ?? 0);
            if ($paidAmount !== $expectedAmount) {
                return ['valid'  => false, 'data' => $response,
                        'error' => "금액 불일치 (기대: {$expectedAmount}, 실제: {$paidAmount})"];
            }

            return ['valid' => true, 'data' => $response, 'error' => ''];

        } catch (\Throwable $e) {
            log_message('error', '[PortOne V2] exception: ' . $e->getMessage());
            return ['valid' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * V2 REST API GET 요청
     * Authorization: PortOne {apiSecret} — 토큰 교환 없이 Secret 직접 사용
     */
    private function get(string $url): array
    {
        $sslVerify = (ENVIRONMENT === 'production');
        $curl      = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: PortOne ' . $this->apiSecret,
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
            CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
        ]);

        $raw      = curl_exec($curl);
        $error    = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        log_message('debug', '[PortOne V2] GET ' . $url . ' → HTTP ' . $httpCode);

        if ($raw === false) {
            throw new \RuntimeException('cURL 오류: ' . $error);
        }

        $decoded = json_decode($raw, true);
        if ($decoded === null) {
            throw new \RuntimeException('응답 파싱 실패: ' . $raw);
        }

        return $decoded;
    }
}
```

---

## Task 3: `Order.php` — V2 전용으로 단순화

**Files:**
- Modify: `busan_onna/app/Controllers/Order.php`

- [ ] **Step 1: Order.php 수정**

`form()`: impCode 제거, V2 키 3개만 뷰 전달.
`verify()`: V1/V2 분기 제거, `payment_id` 단일 처리. 결제 수단은 V2 응답 `method.type`에서 추출.

```php
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
 * - form()          : GET  /order             주문서 폼
 * - store()         : POST /order/store       주문 레코드 생성 (pending)
 * - verify()        : POST /order/verify      PortOne V2 결제 검증 후 주문 확정
 * - complete()      : GET  /order/complete/N  주문 완료 페이지
 * - myOrders()      : GET  /mypage/orders     내 주문 목록
 * - myOrderDetail() : GET  /mypage/orders/N   내 주문 상세
 */
class Order extends BaseController
{
    /**
     * 로그인 체크 — 미로그인 시 로그인 페이지로 리다이렉트
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
     * V2 결제에 필요한 storeId, 이니시스 채널키, 카카오페이 채널키만 뷰에 전달
     */
    public function form(): string
    {
        $userIdx = $this->userIdx();
        $cart    = new CartModel();
        $items   = $cart->getCartItems($userIdx);

        if (empty($items)) {
            return redirect()->to('/cart')->with('error', '장바구니가 비어있습니다.');
        }

        $total   = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $pickups = (new PickupLocationModel())->getActive();

        return view('service/order/form', [
            'cartItems'        => $items,
            'total'            => $total,
            'pickups'          => $pickups,
            'v2StoreId'        => env('PORTONE_V2_STORE_ID', ''),
            'inicisChannelKey' => env('PORTONE_INICIS_CHANNEL_KEY', ''),
            'kakaoChannelKey'  => env('PORTONE_KAKAO_CHANNEL_KEY', ''),
        ]);
    }

    /**
     * POST /order/store — 주문 레코드 생성 (결제 전 pending 상태)
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

        $total        = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $deliveryType = (int) ($post['delivery_type'] ?? 1);

        $orderModel = new OrderModel();
        $db         = \Config\Database::connect();
        $db->transStart();

        $orderData = [
            'status'        => 'pending',
            'order_no'      => $orderModel->generateOrderNo(),
            'user_idx'      => $userIdx,
            'total_price'   => $total,
            'delivery_type' => $deliveryType,
        ];

        if ($deliveryType === 1) {
            $orderData['recipient_name']   = trim($post['recipient_name']   ?? '');
            $orderData['recipient_phone']  = trim($post['recipient_phone']  ?? '');
            $orderData['delivery_address'] = trim($post['delivery_address'] ?? '');
        } else {
            $orderData['pickup_location_idx'] = (int) ($post['pickup_location_idx'] ?? 0);
        }

        $orderModel->insert($orderData);
        $orderIdx = (int) $orderModel->getInsertID();

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

        // V2 응답에서 결제 수단 추출 (Card | EasyPay)
        $methodType = $result['data']['method']['type'] ?? 'card';
        $payMethod  = match ($methodType) {
            'Card'    => 'card',
            'EasyPay' => strtolower($result['data']['method']['easyPay']['provider'] ?? 'easypay'),
            default   => strtolower($methodType),
        };

        // 주문 상태 paid 처리 — pgTxId(PG 트랜잭션ID) 또는 paymentId 저장
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
```

---

## Task 4: `form.php` — V2 SDK 단일화, JS 재작성

**Files:**
- Modify: `busan_onna/app/Views/service/order/form.php`

- [ ] **Step 1: form.php 재작성**

V1 SDK(`cdn.iamport.kr`) 및 `IMP.init()`, `IMP.request_pay()` 전부 제거.
V2 SDK만 로드. 두 결제 수단 모두 `PortOne.requestPayment()` 사용.
verify POST 바디: `{ payment_id, order_idx }` (version 필드 불필요).

PHP `<head>` 변경:
```html
<!-- PortOne V2 SDK (이니시스 카드 + 카카오페이 공통) -->
<script src="https://cdn.portone.io/v2/browser-sdk.js"></script>
```

JS 전체 재작성 (핵심 부분):
```javascript
(function () {
    // V2 설정값 (PHP → JS)
    const V2_STORE_ID       = '<?= esc($v2StoreId) ?>';
    const INICIS_CHANNEL_KEY = '<?= esc($inicisChannelKey) ?>';
    const KAKAO_CHANNEL_KEY  = '<?= esc($kakaoChannelKey) ?>';

    let selectedMethod = 'inicis';

    // 결제 수단 선택
    document.querySelectorAll('.pay-method-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedMethod = btn.dataset.method;
        });
    });

    const btnPay       = document.getElementById('btnPay');
    const radios       = document.querySelectorAll('input[name="delivery_type"]');
    const fieldsParcel = document.getElementById('fieldsParcel');
    const fieldsPickup = document.getElementById('fieldsPickup');

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.value === '1') {
                fieldsParcel.classList.add('active');
                fieldsPickup.classList.remove('active');
            } else {
                fieldsParcel.classList.remove('active');
                fieldsPickup.classList.add('active');
            }
        });
    });

    function getDeliveryType() {
        const checked = document.querySelector('input[name="delivery_type"]:checked');
        return checked ? checked.value : '1';
    }

    function resetBtn() {
        btnPay.disabled    = false;
        btnPay.textContent = '결제하기 (<?= number_format($total) ?>원)';
    }

    // 서버 결제 검증 요청 — payment_id(=주문번호)를 서버로 전송
    function callVerify(paymentId, orderIdx) {
        fetch('/order/verify', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body    : JSON.stringify({ payment_id: paymentId, order_idx: orderIdx }),
        })
        .then(res => res.json())
        .then(function (data) {
            if (data.success) {
                location.href = '/order/complete/' + data.order_idx;
            } else {
                alert(data.message || '결제 검증에 실패했습니다.');
                resetBtn();
            }
        })
        .catch(function () {
            alert('네트워크 오류가 발생했습니다.');
            resetBtn();
        });
    }

    btnPay.addEventListener('click', function () {
        const deliveryType = getDeliveryType();

        if (deliveryType === '1') {
            const name    = document.getElementById('recipientName').value.trim();
            const phone   = document.getElementById('recipientPhone').value.trim();
            const address = document.getElementById('deliveryAddress').value.trim();
            if (!name || !phone || !address) {
                alert('수령인 이름, 연락처, 배송지를 모두 입력해주세요.');
                return;
            }
        } else {
            if (!document.getElementById('pickupLocation').value) {
                alert('픽업 장소를 선택해주세요.');
                return;
            }
        }

        btnPay.disabled    = true;
        btnPay.textContent = '처리 중...';

        // 1단계: 주문 레코드 생성 (pending)
        const formData = new URLSearchParams();
        formData.append('delivery_type', deliveryType);

        if (deliveryType === '1') {
            formData.append('recipient_name',   document.getElementById('recipientName').value.trim());
            formData.append('recipient_phone',  document.getElementById('recipientPhone').value.trim());
            formData.append('delivery_address', document.getElementById('deliveryAddress').value.trim());
        } else {
            formData.append('pickup_location_idx', document.getElementById('pickupLocation').value);
        }

        fetch('/order/store', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body    : formData.toString(),
        })
        .then(res => res.json())
        .then(function (data) {
            if (!data.success) {
                alert(data.message || '주문 생성에 실패했습니다.');
                resetBtn();
                return;
            }

            const orderIdx   = data.order_idx;
            const orderNo    = data.order_no;    // PortOne paymentId로 사용
            const totalPrice = data.total_price;

            const buyerName = deliveryType === '1'
                ? document.getElementById('recipientName').value.trim() : '픽업';
            const buyerTel  = deliveryType === '1'
                ? document.getElementById('recipientPhone').value.trim() : '';

            // 2단계: 결제수단별 V2 요청 파라미터 구성
            const channelKey = selectedMethod === 'kakao' ? KAKAO_CHANNEL_KEY : INICIS_CHANNEL_KEY;

            const payParams = {
                storeId    : V2_STORE_ID,
                channelKey : channelKey,
                paymentId  : orderNo,        // 우리 주문번호 = PortOne paymentId
                orderName  : '부산온나 굿즈 주문',
                totalAmount: totalPrice,
                currency   : 'KRW',
                customer   : { fullName: buyerName, phoneNumber: buyerTel },
            };

            if (selectedMethod === 'kakao') {
                payParams.payMethod = 'EASY_PAY';
                payParams.easyPay  = { easyPayProvider: 'KAKAOPAY' };
            } else {
                payParams.payMethod = 'CARD';
            }

            // 2단계: PortOne V2 결제창 호출
            PortOne.requestPayment(payParams)
                .then(function (rsp) {
                    // rsp.code 존재 시 오류 또는 사용자 취소
                    if (rsp && rsp.code !== undefined) {
                        alert(rsp.message || '결제가 취소되었습니다.');
                        resetBtn();
                        return;
                    }
                    // 3단계: 서버 결제 검증 (paymentId = orderNo)
                    callVerify(orderNo, orderIdx);
                })
                .catch(function () {
                    alert('결제 처리 중 오류가 발생했습니다.');
                    resetBtn();
                });
        })
        .catch(function () {
            alert('네트워크 오류가 발생했습니다.');
            resetBtn();
        });
    });
})();
```

---

## 주의 사항

1. **카카오페이 V2 채널키 미발급**: `.env`의 `PORTONE_KAKAO_CHANNEL_KEY` 값이 `여기에입력` 상태이면 카카오페이 결제창이 열리지 않는다. 어드민에서 채널 등록 후 발급받은 채널키를 기입해야 한다.

2. **이니시스 V2 전환**: 기존 V1 채널키(`channel-key-1e20970c-...`)는 더 이상 사용하지 않는다. 신규 채널키(`channel-key-2497c7f5-...`)로 대체되었다.

3. **테스트 환경**: 두 결제 수단 모두 테스트 환경이므로 실제 금액이 청구되지 않는다.
