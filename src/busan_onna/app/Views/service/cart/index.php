<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>장바구니 | 부산온나</title>
    <meta name="description" content="부산온나 장바구니 - 담아두신 굿즈를 확인하고 주문하세요.">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 장바구니 페이지 전용 스타일 ---- */
        .cart-section {
            padding: 116px 0 80px; /* 68px 고정 네비바 + 48px 여백 */
        }
        .cart-section h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        /* 장바구니 비어있을 때 안내 영역 */
        .cart-empty {
            text-align: center;
            padding: 80px 20px;
            color: #adb5bd;
        }
        .cart-empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        .cart-empty h3 {
            font-size: 20px;
            color: #868e96;
            margin: 0 0 24px;
        }
        .btn-go-goods {
            display: inline-block;
            padding: 12px 28px;
            background: #e55039;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-go-goods:hover { background: #c0392b; }

        /* 장바구니 테이블 */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .cart-table thead th {
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        .cart-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        .cart-table tbody tr:hover {
            background: #fafafa;
        }
        .cart-table td {
            padding: 16px;
            vertical-align: middle;
        }

        /* 썸네일 셀 */
        .cart-thumb {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 8px;
            background: #f8f9fa;
            display: block;
        }
        .cart-thumb-default {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        /* 상품명 & 옵션 */
        .cart-goods-name {
            font-weight: 600;
            margin: 0 0 4px;
            font-size: 15px;
        }
        .cart-option-label {
            font-size: 12px;
            color: #868e96;
        }

        /* 수량 input */
        .cart-qty-input {
            width: 70px;
            padding: 7px 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
        }

        /* 금액 */
        .cart-price {
            font-weight: 700;
            color: #e55039;
            white-space: nowrap;
        }

        /* 삭제 버튼 */
        .btn-cart-remove {
            padding: 6px 14px;
            background: #fff;
            color: #868e96;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        .btn-cart-remove:hover {
            background: #e55039;
            color: #fff;
            border-color: #e55039;
        }

        /* 합계 영역 */
        .cart-summary {
            margin-top: 32px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .cart-total-label {
            font-size: 16px;
            color: #495057;
        }
        .cart-total-amount {
            font-size: 24px;
            font-weight: 800;
            color: #e55039;
        }
        .btn-order {
            display: inline-block;
            padding: 14px 36px;
            background: #e55039;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            border-radius: 10px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-order:hover { background: #c0392b; }

        /* 반응형 — 모바일에서 테이블 스크롤 */
        .cart-table-wrap {
            overflow-x: auto;
        }
        @media (max-width: 640px) {
            .cart-table thead th:nth-child(1) { display: none; }
            .cart-table tbody td:nth-child(1) { display: none; }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'goods']) ?>

<!-- ===================== 페이지 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>장바구니</h1>
        <p>담아두신 굿즈를 확인하고 주문하세요</p>
    </div>
</section>

<!-- ===================== 장바구니 본문 ===================== -->
<section class="cart-section">
    <div class="container">

        <?php if (empty($cartItems)): ?>
        <!-- 장바구니가 비어있을 때 안내 -->
        <div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <h3>장바구니가 비어있습니다</h3>
            <a href="/goods" class="btn-go-goods">굿즈 보러가기</a>
        </div>

        <?php else: ?>

        <!-- 장바구니 상품 테이블 -->
        <div class="cart-table-wrap">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>썸네일</th>
                        <th>상품명 / 옵션</th>
                        <th>단가</th>
                        <th>수량</th>
                        <th>금액</th>
                        <th>삭제</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cartItems as $item): ?>
                <?php
                /* 항목별 단가 = 상품가격 + 옵션 추가금액 */
                $unitPrice  = (int)$item['price'] + (int)($item['additional_price'] ?? 0);
                $itemTotal  = $unitPrice * (int)$item['quantity'];
                ?>
                <tr>
                    <!-- 썸네일 -->
                    <td>
                        <?php if (!empty($item['thumbnail'])): ?>
                            <img src="<?= esc($item['thumbnail']) ?>"
                                 alt="<?= esc($item['goods_name']) ?>"
                                 class="cart-thumb"
                                 onerror="this.onerror=null; this.src='/img/no-image.svg';">
                        <?php else: ?>
                            <div class="cart-thumb-default">🛍️</div>
                        <?php endif; ?>
                    </td>

                    <!-- 상품명 & 옵션 -->
                    <td>
                        <p class="cart-goods-name"><?= esc($item['goods_name']) ?></p>
                        <?php if (!empty($item['option_name']) && !empty($item['option_value'])): ?>
                        <span class="cart-option-label">
                            <?= esc($item['option_name']) ?>: <?= esc($item['option_value']) ?>
                            <?php if ((int)($item['additional_price'] ?? 0) !== 0): ?>
                            (<?= (int)$item['additional_price'] > 0 ? '+' : '' ?><?= number_format((int)$item['additional_price']) ?>원)
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </td>

                    <!-- 단가 -->
                    <td><?= number_format($unitPrice) ?>원</td>

                    <!-- 수량 입력 — 변경 시 JS가 /cart/update 호출 -->
                    <td>
                        <input type="number"
                               class="cart-qty-input"
                               value="<?= (int)$item['quantity'] ?>"
                               min="1"
                               data-cart-idx="<?= (int)$item['idx'] ?>">
                    </td>

                    <!-- 금액 -->
                    <td class="cart-price"><?= number_format($itemTotal) ?>원</td>

                    <!-- 삭제 버튼 -->
                    <td>
                        <button type="button"
                                class="btn-cart-remove"
                                data-cart-idx="<?= (int)$item['idx'] ?>">
                            삭제
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 합계 및 주문 버튼 -->
        <div class="cart-summary">
            <span class="cart-total-label">합계</span>
            <span class="cart-total-amount"><?= number_format($total) ?>원</span>
            <a href="/order" class="btn-order">주문하기</a>
        </div>

        <?php endif; ?>

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
/* ===== 장바구니 페이지 스크립트 ===== */
(function () {

    /**
     * 수량 input 변경 시 POST /cart/update 호출 후 페이지 새로고침
     */
    document.querySelectorAll('.cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const cartIdx = parseInt(input.dataset.cartIdx, 10);
            const qty     = Math.max(1, parseInt(input.value, 10) || 1);

            /* 유효 범위 강제 적용 */
            input.value = qty;

            fetch('/cart/update', {
                method  : 'POST',
                headers : {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                },
                body    : JSON.stringify({ cart_idx: cartIdx, quantity: qty }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    /* 수량·금액 재계산을 위해 페이지 새로고침 */
                    location.reload();
                } else {
                    alert(data.message || '수정에 실패했습니다.');
                }
            })
            .catch(function () {
                alert('네트워크 오류가 발생했습니다.');
            });
        });
    });

    /**
     * 삭제 버튼 클릭 시 confirm 후 POST /cart/remove 호출, 성공 시 새로고침
     */
    document.querySelectorAll('.btn-cart-remove').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('장바구니에서 삭제하시겠습니까?')) return;

            const cartIdx = parseInt(btn.dataset.cartIdx, 10);

            fetch('/cart/remove', {
                method  : 'POST',
                headers : {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                },
                body    : JSON.stringify({ cart_idx: cartIdx }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '삭제에 실패했습니다.');
                }
            })
            .catch(function () {
                alert('네트워크 오류가 발생했습니다.');
            });
        });
    });

})();
</script>
</body>
</html>
