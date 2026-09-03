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
            padding: 116px 0 80px;
        }
        .cart-section h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        /* 장바구니 비어있을 때 안내 */
        .cart-empty {
            text-align: center;
            padding: 80px 20px;
            color: #adb5bd;
        }
        .cart-empty-icon { font-size: 64px; margin-bottom: 16px; }
        .cart-empty h3 { font-size: 20px; color: #868e96; margin: 0 0 24px; }
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
        .cart-table-wrap { overflow-x: auto; }
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
        /* 체크박스 열 헤더: 가운데 정렬 */
        .cart-table thead th:first-child {
            text-align: center;
            width: 48px;
        }
        .cart-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: background .1s;
        }
        .cart-table tbody tr:hover { background: #fafafa; }
        /* 체크 해제된 행: 흐리게 */
        .cart-table tbody tr.row-unchecked {
            opacity: 0.45;
        }
        .cart-table td { padding: 16px; vertical-align: middle; }
        /* 체크박스 열 td: 가운데 정렬 */
        .cart-table td:first-child { text-align: center; }

        /* =====================================================
           커스텀 체크박스
           ===================================================== */
        .cb-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            user-select: none;
        }
        /* 실제 input은 완전히 숨김 */
        .cb-wrap input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }
        /* 커스텀 박스 */
        .cb-box {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid #ced4da;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color .18s, background .18s, box-shadow .18s;
            flex-shrink: 0;
        }
        /* 체크 아이콘 SVG (기본 숨김) */
        .cb-box svg {
            width: 12px;
            height: 12px;
            stroke: #fff;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0;
            transform: scale(0.6);
            transition: opacity .15s, transform .15s;
        }
        /* checked 상태 */
        .cb-wrap input[type="checkbox"]:checked + .cb-box {
            background: #e55039;
            border-color: #e55039;
            box-shadow: 0 2px 8px rgba(229,80,57,.25);
        }
        .cb-wrap input[type="checkbox"]:checked + .cb-box svg {
            opacity: 1;
            transform: scale(1);
        }
        /* 호버 */
        .cb-wrap:hover .cb-box {
            border-color: #e55039;
        }
        /* 전체선택 체크박스: 헤더에 배치 */
        .cb-all-wrap .cb-box {
            border-radius: 6px;
        }

        /* 썸네일 */
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

        /* 상품명·옵션 */
        .cart-goods-name { font-weight: 600; margin: 0 0 4px; font-size: 15px; }
        .cart-option-label { font-size: 12px; color: #868e96; }

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
        .cart-price { font-weight: 700; color: #e55039; white-space: nowrap; }

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
        .cart-summary-info { display: flex; align-items: center; gap: 12px; }
        .cart-selected-count { font-size: 14px; color: #868e96; }
        .cart-selected-count strong { color: #e55039; }
        .cart-total-label { font-size: 16px; color: #495057; }
        .cart-total-amount { font-size: 24px; font-weight: 800; color: #e55039; }

        /* 주문하기 버튼 */
        .btn-order {
            display: inline-block;
            padding: 14px 36px;
            background: #e55039;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-order:hover { background: #c0392b; }
        .btn-order:disabled,
        .btn-order:disabled:hover {
            background: #ced4da;
            color: #fff;
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* 반응형 */
        @media (max-width: 640px) {
            /* 모바일에서 체크박스 열은 항상 표시 */
            .cart-table thead th:nth-child(2) { display: none; }
            .cart-table tbody td:nth-child(2) { display: none; }
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
        <div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <h3>장바구니가 비어있습니다</h3>
            <a href="/goods" class="btn-go-goods">굿즈 보러가기</a>
        </div>

        <?php else: ?>

        <!-- 장바구니 상품 테이블 -->
        <div class="cart-table-wrap">
            <table class="cart-table" id="cartTable">
                <thead>
                    <tr>
                        <!-- 전체 선택 체크박스 -->
                        <th>
                            <label class="cb-wrap cb-all-wrap" title="전체 선택">
                                <input type="checkbox" id="cbAll" checked>
                                <span class="cb-box">
                                    <svg viewBox="0 0 12 12" fill="none">
                                        <polyline points="1.5,6 5,9.5 10.5,2.5"/>
                                    </svg>
                                </span>
                            </label>
                        </th>
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
                $unitPrice = (int)$item['price'] + (int)($item['additional_price'] ?? 0);
                $itemTotal = $unitPrice * (int)$item['quantity'];
                ?>
                <tr data-cart-idx="<?= (int)$item['idx'] ?>"
                    data-unit-price="<?= $unitPrice ?>"
                    data-quantity="<?= (int)$item['quantity'] ?>">

                    <!-- 개별 체크박스 (기본값 checked) -->
                    <td>
                        <label class="cb-wrap">
                            <input type="checkbox"
                                   class="cb-item"
                                   data-cart-idx="<?= (int)$item['idx'] ?>"
                                   data-item-total="<?= $itemTotal ?>"
                                   checked>
                            <span class="cb-box">
                                <svg viewBox="0 0 12 12" fill="none">
                                    <polyline points="1.5,6 5,9.5 10.5,2.5"/>
                                </svg>
                            </span>
                        </label>
                    </td>

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

                    <!-- 수량 입력 -->
                    <td>
                        <input type="number"
                               class="cart-qty-input"
                               value="<?= (int)$item['quantity'] ?>"
                               min="1"
                               data-cart-idx="<?= (int)$item['idx'] ?>"
                               data-unit-price="<?= $unitPrice ?>">
                    </td>

                    <!-- 금액 -->
                    <td class="cart-price item-total-cell"><?= number_format($itemTotal) ?>원</td>

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
            <div class="cart-summary-info">
                <span class="cart-selected-count">
                    선택 <strong id="selectedCount"><?= count($cartItems) ?></strong>개
                </span>
                <span class="cart-total-label">합계</span>
                <span class="cart-total-amount" id="cartTotalAmount"><?= number_format($total) ?>원</span>
            </div>
            <button type="button" class="btn-order" id="btnOrder">주문하기</button>
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
    'use strict';

    /* -------------------------------------------------------
       체크박스 & 합계 재계산
       ------------------------------------------------------- */
    var cbAll          = document.getElementById('cbAll');
    var cbItems        = document.querySelectorAll('.cb-item');
    var totalAmountEl  = document.getElementById('cartTotalAmount');
    var selectedCountEl = document.getElementById('selectedCount');
    var btnOrder       = document.getElementById('btnOrder');

    /* 선택된 항목만 합계 계산 후 UI 갱신 */
    function recalcTotal() {
        var sum   = 0;
        var count = 0;

        cbItems.forEach(function (cb) {
            var row       = cb.closest('tr');
            var unitPrice = parseInt(row.dataset.unitPrice, 10) || 0;
            var qty       = parseInt(row.dataset.quantity,  10) || 1;

            if (cb.checked) {
                sum   += unitPrice * qty;
                count += 1;
                row.classList.remove('row-unchecked');
            } else {
                row.classList.add('row-unchecked');
            }
        });

        /* 금액·카운트 텍스트 갱신 */
        totalAmountEl.textContent  = sum.toLocaleString('ko-KR') + '원';
        selectedCountEl.textContent = count;

        /* 전체선택 체크박스 indeterminate / checked 동기화 */
        var checkedCount = document.querySelectorAll('.cb-item:checked').length;
        if (checkedCount === 0) {
            cbAll.checked       = false;
            cbAll.indeterminate = false;
            btnOrder.disabled   = true;
        } else if (checkedCount === cbItems.length) {
            cbAll.checked       = true;
            cbAll.indeterminate = false;
            btnOrder.disabled   = false;
        } else {
            cbAll.checked       = false;
            cbAll.indeterminate = true;
            btnOrder.disabled   = false;
        }
    }

    /* 개별 체크박스 변경 시 */
    cbItems.forEach(function (cb) {
        cb.addEventListener('change', recalcTotal);
    });

    /* 전체 선택/해제 */
    cbAll.addEventListener('change', function () {
        cbItems.forEach(function (cb) {
            cb.checked = cbAll.checked;
        });
        recalcTotal();
    });

    /* 초기 실행 (기본 all-checked 상태 반영) */
    recalcTotal();

    /* -------------------------------------------------------
       수량 변경 → POST /cart/update
       ------------------------------------------------------- */
    document.querySelectorAll('.cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var cartIdx   = parseInt(input.dataset.cartIdx, 10);
            var unitPrice = parseInt(input.dataset.unitPrice, 10) || 0;
            var qty       = Math.max(1, parseInt(input.value, 10) || 1);
            input.value   = qty;

            /* tr의 data-quantity 즉시 갱신 → recalcTotal에서 사용 */
            var row = input.closest('tr');
            row.dataset.quantity = qty;

            /* 금액 셀 즉시 갱신 */
            var totalCell = row.querySelector('.item-total-cell');
            if (totalCell) {
                totalCell.textContent = (unitPrice * qty).toLocaleString('ko-KR') + '원';
            }

            recalcTotal();

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
                if (!data.success) {
                    alert(data.message || '수정에 실패했습니다.');
                    location.reload();
                }
            })
            .catch(function () {
                alert('네트워크 오류가 발생했습니다.');
            });
        });
    });

    /* -------------------------------------------------------
       삭제 버튼
       ------------------------------------------------------- */
    document.querySelectorAll('.btn-cart-remove').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('장바구니에서 삭제하시겠습니까?')) return;

            var cartIdx = parseInt(btn.dataset.cartIdx, 10);

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

    /* -------------------------------------------------------
       주문하기 — 선택된 cart_idx만 쿼리스트링으로 전달
       ------------------------------------------------------- */
    btnOrder.addEventListener('click', function () {
        /* disabled 상태이면 동작 차단 */
        if (btnOrder.disabled) return;

        var selected = [];
        document.querySelectorAll('.cb-item:checked').forEach(function (cb) {
            selected.push(cb.dataset.cartIdx);
        });

        if (selected.length === 0) {
            alert('주문할 상품을 선택해주세요.');
            return;
        }

        /* /order?cart_ids=1,2,3 형식으로 이동 */
        window.location.href = '/order?cart_ids=' + selected.join(',');
    });

})();
</script>
</body>
</html>
