<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>주문서 | 부산온나</title>
    <meta name="description" content="부산온나 주문서 - 배송 정보를 입력하고 결제하세요.">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <!-- PortOne V2 SDK (이니시스 카드 + 카카오페이 공통) -->
    <script src="https://cdn.portone.io/v2/browser-sdk.js"></script>

    <style>
        /* ---- 주문서 페이지 전용 스타일 ---- */
        .order-section {
            padding: 116px 0 80px; /* 68px 고정 네비바 + 48px 여백 */
        }
        .order-section h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        /* 주문서 레이아웃: 좌(상품 요약) + 우(배송 정보) */
        .order-layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 40px;
            align-items: flex-start;
        }
        @media (max-width: 900px) {
            .order-layout { grid-template-columns: 1fr; }
        }

        /* 섹션 카드 */
        .order-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 28px;
        }
        .order-card h3 {
            font-size: 17px;
            font-weight: 700;
            margin: 0 0 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        /* 주문 상품 테이블 */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .order-table thead th {
            padding: 10px 12px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        .order-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        .order-table td {
            padding: 14px 12px;
            vertical-align: middle;
        }
        .order-goods-name {
            font-weight: 600;
            font-size: 14px;
            margin: 0 0 4px;
        }
        .order-option-label {
            font-size: 12px;
            color: #868e96;
        }
        .order-price {
            font-weight: 700;
            color: #e55039;
            white-space: nowrap;
        }

        /* 합계 */
        .order-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 2px solid #e9ecef;
        }
        .order-total-label {
            font-size: 16px;
            color: #495057;
        }
        .order-total-amount {
            font-size: 22px;
            font-weight: 800;
            color: #e55039;
        }

        /* 배송 방법 라디오 */
        .delivery-type-group {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .delivery-type-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: border-color .15s, background .15s;
        }
        .delivery-type-btn input[type="radio"] {
            accent-color: #e55039;
        }
        .delivery-type-btn:has(input:checked) {
            border-color: #e55039;
            background: #fff5f3;
        }

        /* 폼 필드 */
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color .15s;
        }
        .form-control:focus {
            outline: none;
            border-color: #e55039;
        }

        /* 결제 수단 선택 */
        .pay-method-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .pay-method-btn {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 14px 8px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            background: #fff;
            transition: border-color .15s, background .15s, color .15s;
            user-select: none;
        }
        .pay-method-btn img {
            height: 24px;
            object-fit: contain;
        }
        .pay-method-btn.active {
            border-color: #e55039;
            background: #fff5f3;
            color: #e55039;
        }

        /* 결제 버튼 */
        .btn-pay {
            display: block;
            width: 100%;
            padding: 16px;
            margin-top: 4px;
            background: #e55039;
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: background .2s;
        }
        .btn-pay:hover { background: #c0392b; }
        .btn-pay:disabled {
            background: #adb5bd;
            cursor: not-allowed;
        }

        /* 배송 섹션 토글 */
        .delivery-fields { display: none; }
        .delivery-fields.active { display: block; }

        /* 주문자 readonly 필드 */
        .form-control[readonly] {
            background: #f8f9fa;
            color: #6c757d;
            cursor: default;
        }

        /* 주소 검색 버튼 */
        .addr-search-wrap {
            display: flex;
            gap: 8px;
        }
        .addr-search-wrap .form-control {
            flex: 1;
        }
        .btn-addr-search {
            flex-shrink: 0;
            padding: 0 16px;
            height: 42px;
            background: #03c75a;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background .15s;
        }
        .btn-addr-search:hover { background: #02a44c; }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'goods']) ?>

<!-- ===================== 페이지 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>주문서</h1>
        <p>배송 정보를 입력하고 결제를 완료하세요</p>
    </div>
</section>

<!-- ===================== 주문서 본문 ===================== -->
<section class="order-section">
    <div class="container">
        <div class="order-layout">

            <!-- 왼쪽: 주문 상품 요약 -->
            <div>
                <div class="order-card">
                    <h3>주문 상품</h3>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>상품명 / 옵션</th>
                                <th>단가</th>
                                <th>수량</th>
                                <th>금액</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <?php
                        /* 항목별 단가 = 상품가격 + 옵션 추가금액 */
                        $unitPrice = (int)$item['price'] + (int)($item['additional_price'] ?? 0);
                        $itemTotal = $unitPrice * (int)$item['quantity'];
                        ?>
                        <tr>
                            <td>
                                <p class="order-goods-name"><?= esc($item['goods_name']) ?></p>
                                <?php if (!empty($item['option_name']) && !empty($item['option_value'])): ?>
                                <span class="order-option-label">
                                    <?= esc($item['option_name']) ?>: <?= esc($item['option_value']) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($unitPrice) ?>원</td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td class="order-price"><?= number_format($itemTotal) ?>원</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- 총 결제금액 -->
                    <div class="order-total-row">
                        <span class="order-total-label">총 결제금액</span>
                        <span class="order-total-amount"><?= number_format($total) ?>원</span>
                    </div>
                </div>
            </div>

            <!-- 오른쪽: 배송 정보 + 결제 -->
            <div>
                <div class="order-card">
                    <h3>배송 정보</h3>

                    <!-- 배송 방법 선택 -->
                    <div class="delivery-type-group">
                        <label class="delivery-type-btn">
                            <input type="radio" name="delivery_type" value="1" checked>
                            택배 배송
                        </label>
                        <label class="delivery-type-btn">
                            <input type="radio" name="delivery_type" value="2">
                            픽업
                        </label>
                    </div>

                    <!-- 택배 입력 폼 -->
                    <div class="delivery-fields active" id="fieldsParcel">

                        <!-- 주문자: 로그인 유저 닉네임 (읽기 전용) -->
                        <div class="form-group">
                            <label for="ordererName">주문자</label>
                            <input type="text" id="ordererName" class="form-control"
                                   value="<?= esc($userName ?? '') ?>" readonly>
                        </div>

                        <!-- 수령인 이름 -->
                        <div class="form-group">
                            <label for="recipientName">수령인 이름 <span style="color:#e55039">*</span></label>
                            <input type="text" id="recipientName" name="recipient_name"
                                   class="form-control" placeholder="홍길동">
                        </div>

                        <!-- 수령인 연락처: 숫자만, 3-4-4 자동 하이픈 -->
                        <div class="form-group">
                            <label for="recipientPhone">수령인 연락처 <span style="color:#e55039">*</span></label>
                            <input type="tel" id="recipientPhone" name="recipient_phone"
                                   class="form-control" placeholder="010-0000-0000" maxlength="13"
                                   value="<?= esc(preg_replace('/(\d{3})(\d{3,4})(\d{4})/', '$1-$2-$3', preg_replace('/[^0-9]/', '', $userPhone ?? ''))) ?>">
                        </div>

                        <!-- 배송지 주소: 다음 우편번호 API -->
                        <div class="form-group">
                            <label>배송지 주소 <span style="color:#e55039">*</span></label>
                            <div class="addr-search-wrap">
                                <input type="text" id="deliveryAddress" name="delivery_address"
                                       class="form-control" placeholder="주소 검색 버튼을 눌러주세요" readonly>
                                <button type="button" id="btnAddrSearch" class="btn-addr-search">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"/>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                    주소 검색
                                </button>
                            </div>
                        </div>

                        <!-- 배송지 상세 주소 -->
                        <div class="form-group">
                            <label for="deliveryAddress2">배송지 상세 주소</label>
                            <input type="text" id="deliveryAddress2" name="delivery_address2"
                                   class="form-control" placeholder="건물명, 동, 호수 등">
                        </div>

                    </div>

                    <!-- 픽업 선택 폼 -->
                    <div class="delivery-fields" id="fieldsPickup">
                        <div class="form-group">
                            <label for="pickupLocation">픽업 장소</label>
                            <select id="pickupLocation" name="pickup_location_idx" class="form-control">
                                <option value="">픽업 장소를 선택하세요</option>
                                <?php foreach ($pickups as $p): ?>
                                <option value="<?= (int)$p['idx'] ?>">
                                    <?= esc($p['name']) ?>
                                    <?php if (!empty($p['address'])): ?>
                                    — <?= esc($p['address']) ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- 결제 수단 선택 -->
                <div class="order-card" style="margin-top:16px;">
                    <h3>결제 수단</h3>
                    <div class="pay-method-group">
                        <button type="button" class="pay-method-btn active" data-method="inicis">
                            <svg width="28" height="20" viewBox="0 0 28 20" fill="none">
                                <rect width="28" height="20" rx="4" fill="#1a4fd8"/>
                                <text x="14" y="14" text-anchor="middle" fill="#fff" font-size="8" font-weight="700" font-family="sans-serif">CARD</text>
                            </svg>
                            신용카드
                        </button>
                        <button type="button" class="pay-method-btn" data-method="kakao">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                                <circle cx="14" cy="14" r="14" fill="#FEE500"/>
                                <path d="M14 6.5C9.306 6.5 5.5 9.48 5.5 13.15c0 2.35 1.56 4.41 3.91 5.6l-.98 3.64c-.08.3.27.54.54.37L13.3 20.4c.23.02.46.03.7.03 4.694 0 8.5-2.98 8.5-6.65C22.5 9.48 18.694 6.5 14 6.5z" fill="#3C1E1E"/>
                            </svg>
                            카카오페이
                        </button>
                    </div>

                    <!-- 결제하기 버튼 -->
                    <button type="button" id="btnPay" class="btn-pay">
                        결제하기 (<?= number_format($total) ?>원)
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>

<!-- 다음 우편번호 API -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<script>
/* ===== 수령인 연락처 자동 하이픈 (3-4-4) ===== */
(function () {
    var phoneInput = document.getElementById('recipientPhone');
    if (!phoneInput) return;

    /* 초기값이 숫자로만 돼 있으면 하이픈 형식으로 변환 */
    phoneInput.value = formatPhone(phoneInput.value);

    phoneInput.addEventListener('input', function () {
        /* 숫자 외 문자 제거 후 최대 11자리 */
        var digits = this.value.replace(/\D/g, '').slice(0, 11);
        this.value = formatPhone(digits);
    });

    /* keydown: 숫자·제어키만 허용 */
    phoneInput.addEventListener('keydown', function (e) {
        var allowed = [
            'Backspace','Delete','Tab','Enter','Escape','ArrowLeft','ArrowRight',
            'Home','End',
        ];
        if (allowed.indexOf(e.key) !== -1) return;
        if (e.ctrlKey || e.metaKey) return;  /* Ctrl+C, Ctrl+V 등 */
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    function formatPhone(val) {
        var d = val.replace(/\D/g, '').slice(0, 11);
        if (d.length <= 3)  return d;
        if (d.length <= 7)  return d.slice(0, 3) + '-' + d.slice(3);
        return d.slice(0, 3) + '-' + d.slice(3, 7) + '-' + d.slice(7);
    }
}());

/* ===== 다음 우편번호 API — 배송지 주소 검색 ===== */
(function () {
    var btnSearch = document.getElementById('btnAddrSearch');
    if (!btnSearch) return;

    function openPostcode() {
        new daum.Postcode({
            oncomplete: function (data) {
                /* 도로명 주소 우선, 없으면 지번 */
                var addr = data.roadAddress || data.jibunAddress;
                var addrInput = document.getElementById('deliveryAddress');
                addrInput.value = addr;

                /* 검색 후 상세주소 input에 포커스 */
                var addr2Input = document.getElementById('deliveryAddress2');
                if (addr2Input) addr2Input.focus();
            },
        }).open();
    }

    btnSearch.addEventListener('click', openPostcode);

    /* readonly 주소 input 클릭 시에도 팝업 오픈 */
    var addrInput = document.getElementById('deliveryAddress');
    if (addrInput) {
        addrInput.addEventListener('click', openPostcode);
    }
}());

/* ===== 주문서 페이지 스크립트 (PortOne V2 전용) ===== */
(function () {

    /* V2 설정값 (PHP → JS) */
    const V2_STORE_ID        = '<?= esc($v2StoreId) ?>';
    const INICIS_CHANNEL_KEY = '<?= esc($inicisChannelKey) ?>';
    const KAKAO_CHANNEL_KEY  = '<?= esc($kakaoChannelKey) ?>';
    const USER_EMAIL         = '<?= esc($userEmail) ?>';

    /* 즉시구매 모드 여부 및 관련 데이터 */
    const IS_BUY_NOW        = <?= ($isBuyNow ?? false) ? 'true' : 'false' ?>;
    const BUY_NOW_GOODS_IDX = <?= (int)($buyNowGoodsIdx  ?? 0) ?>;
    const BUY_NOW_QTY       = <?= (int)($buyNowQty       ?? 1) ?>;
    const BUY_NOW_OPT_VAL   = <?= (int)($buyNowOptValIdx ?? 0) ?>;

    /* 현재 선택된 결제 수단 */
    let selectedMethod = 'inicis';

    /* 결제 수단 버튼 클릭 */
    document.querySelectorAll('.pay-method-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.pay-method-btn').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            selectedMethod = btn.dataset.method;
        });
    });

    const btnPay       = document.getElementById('btnPay');
    const radios       = document.querySelectorAll('input[name="delivery_type"]');
    const fieldsParcel = document.getElementById('fieldsParcel');
    const fieldsPickup = document.getElementById('fieldsPickup');

    /* 배송 방법 라디오 변경 시 입력 폼 토글 */
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

    /* 3단계: 서버 결제 검증 — buy_now 플래그 포함해 전송 */
    function callVerify(paymentId, orderIdx) {
        fetch('/order/verify', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body    : JSON.stringify({ payment_id: paymentId, order_idx: orderIdx, buy_now: IS_BUY_NOW }),
        })
        .then(function (res) { return res.json(); })
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

    /**
     * 결제하기 버튼 클릭 처리
     * 1단계: POST /order/store  — pending 주문 생성
     * 2단계: PortOne.requestPayment — V2 결제창 호출 (이니시스·카카오페이 공통)
     * 3단계: POST /order/verify — 서버 금액 검증 + 주문 확정
     * 4단계: /order/complete/{idx} 이동
     */
    btnPay.addEventListener('click', function () {
        const deliveryType = getDeliveryType();

        /* 배송 정보 유효성 검사 */
        if (deliveryType === '1') {
            const name    = document.getElementById('recipientName').value.trim();
            const phone   = document.getElementById('recipientPhone').value.trim();
            const address = document.getElementById('deliveryAddress').value.trim();
            if (!name) { alert('수령인 이름을 입력해주세요.'); return; }
            if (!phone || phone.replace(/-/g, '').length < 9) {
                alert('올바른 연락처를 입력해주세요.'); return;
            }
            if (!address) { alert('배송지 주소를 검색해주세요.'); return; }
        } else {
            if (!document.getElementById('pickupLocation').value) {
                alert('픽업 장소를 선택해주세요.');
                return;
            }
        }

        btnPay.disabled    = true;
        btnPay.textContent = '처리 중...';

        /* 1단계: pending 주문 레코드 생성 */
        const formData = new URLSearchParams();
        formData.append('delivery_type', deliveryType);

        /* 즉시구매 / 장바구니 모드 분기 */
        if (IS_BUY_NOW) {
            formData.append('buy_now',   '1');
            formData.append('goods_idx', BUY_NOW_GOODS_IDX);
            formData.append('qty',       BUY_NOW_QTY);
            if (BUY_NOW_OPT_VAL) formData.append('option_value_idx', BUY_NOW_OPT_VAL);
        } else {
            formData.append('cart_ids', '<?= esc($cartIds ?? '') ?>');
        }

        if (deliveryType === '1') {
            formData.append('recipient_name',    document.getElementById('recipientName').value.trim());
            formData.append('recipient_phone',   document.getElementById('recipientPhone').value.trim());
            formData.append('delivery_address',  document.getElementById('deliveryAddress').value.trim());
            formData.append('delivery_address2', document.getElementById('deliveryAddress2').value.trim());
        } else {
            formData.append('pickup_location_idx', document.getElementById('pickupLocation').value);
        }

        fetch('/order/store', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body    : formData.toString(),
        })
        .then(function (res) { return res.json(); })
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

            /* 2단계: V2 결제 파라미터 구성 — 결제 수단별 channelKey·payMethod 분기 */
            const payParams = {
                storeId    : V2_STORE_ID,
                channelKey : selectedMethod === 'kakao' ? KAKAO_CHANNEL_KEY : INICIS_CHANNEL_KEY,
                paymentId  : orderNo,
                orderName  : '부산온나 굿즈 주문',
                totalAmount: totalPrice,
                currency   : 'KRW',
                customer   : { fullName: buyerName, phoneNumber: buyerTel, email: USER_EMAIL },
            };

            if (selectedMethod === 'kakao') {
                payParams.payMethod = 'EASY_PAY';
                payParams.easyPay  = { easyPayProvider: 'KAKAOPAY' };
            } else {
                payParams.payMethod = 'CARD';
            }

            /* 2단계: PortOne V2 결제창 호출 */
            PortOne.requestPayment(payParams)
                .then(function (rsp) {
                    /* rsp.code 존재 시 오류 또는 사용자 취소 */
                    if (rsp && rsp.code !== undefined) {
                        console.error('[PortOne] then 오류 rsp:', JSON.stringify(rsp));
                        alert('[' + rsp.code + '] ' + (rsp.message || '결제가 취소되었습니다.'));
                        resetBtn();
                        return;
                    }
                    /* 3단계: 서버 검증 */
                    callVerify(orderNo, orderIdx);
                })
                .catch(function (err) {
                    /* SDK가 Promise reject 시 — 400 Bad Request 등 준비 단계 오류 */
                    console.error('[PortOne] catch 오류 err:', err);
                    var msg = (err && err.message)
                        ? err.message
                        : (err && err.code ? err.code : JSON.stringify(err));
                    alert('[결제 준비 오류] ' + (msg || '알 수 없는 오류'));
                    resetBtn();
                });
        })
        .catch(function () {
            alert('네트워크 오류가 발생했습니다.');
            resetBtn();
        });
    });

})();
</script>
</body>
</html>
