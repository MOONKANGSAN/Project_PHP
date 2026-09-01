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

    <!-- PortOne 결제 SDK -->
    <script src="https://cdn.iamport.kr/v1/iamport.js"></script>

    <style>
        /* ---- 주문서 페이지 전용 스타일 ---- */
        .order-section {
            padding: 48px 0 80px;
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

        /* 결제 버튼 */
        .btn-pay {
            display: block;
            width: 100%;
            padding: 16px;
            margin-top: 24px;
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
                        <div class="form-group">
                            <label for="recipientName">수령인 이름</label>
                            <input type="text" id="recipientName" name="recipient_name"
                                   class="form-control" placeholder="홍길동">
                        </div>
                        <div class="form-group">
                            <label for="recipientPhone">수령인 연락처</label>
                            <input type="tel" id="recipientPhone" name="recipient_phone"
                                   class="form-control" placeholder="010-0000-0000">
                        </div>
                        <div class="form-group">
                            <label for="deliveryAddress">배송지 주소</label>
                            <input type="text" id="deliveryAddress" name="delivery_address"
                                   class="form-control" placeholder="도로명 주소를 입력하세요">
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

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<script>
/* ===== 주문서 페이지 스크립트 ===== */
(function () {

    /* PortOne IMP 초기화 */
    IMP.init('<?= esc($impCode) ?>');

    const btnPay       = document.getElementById('btnPay');
    const radios       = document.querySelectorAll('input[name="delivery_type"]');
    const fieldsParcel = document.getElementById('fieldsParcel');
    const fieldsPickup = document.getElementById('fieldsPickup');

    /**
     * 배송 방법 라디오 변경 시 해당 입력 폼 토글
     */
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

    /**
     * 현재 선택된 배송 유형 반환
     * @returns {string} '1' = 택배, '2' = 픽업
     */
    function getDeliveryType() {
        const checked = document.querySelector('input[name="delivery_type"]:checked');
        return checked ? checked.value : '1';
    }

    /**
     * 결제하기 버튼 클릭 처리
     * 1단계: POST /order/store 로 주문 레코드 생성 (pending)
     * 2단계: PortOne IMP.request_pay 로 결제창 호출
     * 3단계: POST /order/verify 로 서버 검증 후 주문 확정
     * 4단계: 성공 시 주문 완료 페이지로 이동
     */
    btnPay.addEventListener('click', function () {
        const deliveryType = getDeliveryType();

        /* 배송 정보 유효성 검사 */
        if (deliveryType === '1') {
            const name    = document.getElementById('recipientName').value.trim();
            const phone   = document.getElementById('recipientPhone').value.trim();
            const address = document.getElementById('deliveryAddress').value.trim();
            if (!name || !phone || !address) {
                alert('수령인 이름, 연락처, 배송지를 모두 입력해주세요.');
                return;
            }
        } else {
            const pickup = document.getElementById('pickupLocation').value;
            if (!pickup) {
                alert('픽업 장소를 선택해주세요.');
                return;
            }
        }

        btnPay.disabled    = true;
        btnPay.textContent = '처리 중...';

        /* 1단계: 주문 레코드 생성 (pending) */
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
            headers : {
                'Content-Type'     : 'application/x-www-form-urlencoded',
                'X-Requested-With' : 'XMLHttpRequest',
            },
            body    : formData.toString(),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) {
                alert(data.message || '주문 생성에 실패했습니다.');
                resetBtn();
                return;
            }

            const orderIdx  = data.order_idx;
            const orderNo   = data.order_no;
            const totalPrice = data.total_price;

            /* 2단계: PortOne 결제창 호출 */
            IMP.request_pay({
                pg          : 'html5_inicis',
                pay_method  : 'card',
                merchant_uid: orderNo,
                name        : '부산온나 굿즈 주문',
                amount      : totalPrice,
                buyer_name  : deliveryType === '1'
                                ? document.getElementById('recipientName').value.trim()
                                : '픽업',
                buyer_tel   : deliveryType === '1'
                                ? document.getElementById('recipientPhone').value.trim()
                                : '',
                buyer_addr  : deliveryType === '1'
                                ? document.getElementById('deliveryAddress').value.trim()
                                : '',
            }, function (rsp) {
                /* 결제창 콜백 */
                if (!rsp.success) {
                    alert(rsp.error_msg || '결제에 실패했습니다.');
                    resetBtn();
                    return;
                }

                /* 3단계: 서버 결제 검증 */
                fetch('/order/verify', {
                    method  : 'POST',
                    headers : {
                        'Content-Type'     : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                    },
                    body    : JSON.stringify({
                        imp_uid   : rsp.imp_uid,
                        order_idx : orderIdx,
                    }),
                })
                .then(function (res) { return res.json(); })
                .then(function (vData) {
                    if (vData.success) {
                        /* 4단계: 주문 완료 페이지로 이동 */
                        location.href = '/order/complete/' + vData.order_idx;
                    } else {
                        alert(vData.message || '결제 검증에 실패했습니다.');
                        resetBtn();
                    }
                })
                .catch(function () {
                    alert('네트워크 오류가 발생했습니다.');
                    resetBtn();
                });
            });
        })
        .catch(function () {
            alert('네트워크 오류가 발생했습니다.');
            resetBtn();
        });
    });

    /**
     * 결제 버튼 상태 초기화
     */
    function resetBtn() {
        btnPay.disabled    = false;
        btnPay.textContent = '결제하기 (<?= number_format($total) ?>원)';
    }

})();
</script>
</body>
</html>
