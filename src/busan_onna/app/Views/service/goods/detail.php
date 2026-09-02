<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    /* ---- SEO 변수 ---- */
    $seoTitle    = esc($goods['name']) . ' | 부산온나 굿즈';
    $seoDesc     = !empty($goods['description'])
        ? mb_substr(strip_tags($goods['description']), 0, 120)
        : '부산온나 공식 굿즈 ' . esc($goods['name']) . '. 부산 여행의 추억을 담은 특별한 기념품입니다.';
    $ogImage     = !empty($goods['thumbnail']) ? esc($goods['thumbnail']) : 'https://busanonna.com/img/og-goods.jpg';
    $canonicalUrl = 'https://busanonna.com/goods/' . (int)$goods['idx'];
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= $seoDesc ?>">
    <meta name="robots"      content="index, follow">
    <link rel="canonical"    href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="product">
    <meta property="og:title"       content="<?= $seoTitle ?>">
    <meta property="og:description" content="<?= $seoDesc ?>">
    <meta property="og:url"         content="<?= $canonicalUrl ?>">
    <meta property="og:image"       content="<?= $ogImage ?>">
    <meta property="og:site_name"   content="부산온나">
    <meta property="og:locale"      content="ko_KR">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 굿즈 상세 전용 스타일 ---- */
        .goods-detail-section {
            padding: 116px 0 80px; /* 68px 고정 네비바 + 48px 여백 */
        }
        .goods-detail-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: flex-start;
        }
        @media (max-width: 768px) {
            .goods-detail-wrap { grid-template-columns: 1fr; gap: 24px; }
        }

        /* 썸네일 영역 */
        .goods-thumb-box {
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .goods-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .goods-thumb-default {
            font-size: 80px;
        }

        /* 정보 영역 */
        .goods-info h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 12px;
            line-height: 1.4;
        }
        .goods-price-wrap {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 16px;
        }
        .goods-price {
            font-size: 28px;
            font-weight: 800;
            color: #e55039;
        }
        .goods-price-unit {
            font-size: 16px;
            color: #666;
        }
        .goods-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .badge-delivery {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-parcel { background: #e3f2fd; color: #1565c0; }
        .badge-pickup { background: #e8f5e9; color: #2e7d32; }

        /* 옵션 */
        .goods-option-group {
            margin-bottom: 16px;
        }
        .goods-option-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
            display: block;
        }
        .goods-option-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }

        /* 수량 */
        .qty-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        .qty-wrap label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
        }
        .qty-input {
            width: 80px;
            padding: 9px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
        }

        /* 최종 금액 */
        .goods-total-price {
            font-size: 14px;
            color: #444;
            margin-bottom: 20px;
        }
        .goods-total-price strong {
            font-size: 20px;
            color: #e55039;
            font-weight: 800;
        }

        /* 버튼 */
        .btn-cart {
            display: block;
            width: 100%;
            padding: 16px;
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
        .btn-cart:hover { background: #c0392b; }
        .btn-cart:disabled {
            background: #adb5bd;
            cursor: not-allowed;
        }

        /* 상품 설명 */
        .goods-desc {
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid #e9ecef;
        }
        .goods-desc h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .goods-desc-content {
            font-size: 15px;
            line-height: 1.8;
            color: #444;
        }

        /* 장바구니 결과 토스트 */
        .toast-msg {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: rgba(0,0,0,.8);
            color: #fff;
            padding: 12px 28px;
            border-radius: 30px;
            font-size: 14px;
            opacity: 0;
            transition: opacity .3s, transform .3s;
            pointer-events: none;
            z-index: 9999;
        }
        .toast-msg.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'goods']) ?>

<!-- ===================== 굿즈 상세 ===================== -->
<section class="goods-detail-section">
    <div class="container">

        <div class="goods-detail-wrap">

            <!-- 왼쪽: 썸네일 -->
            <div class="goods-thumb-box">
                <?php if (!empty($goods['thumbnail'])): ?>
                    <img src="<?= esc($goods['thumbnail']) ?>"
                         alt="<?= esc($goods['name']) ?>"
                         onerror="this.onerror=null; this.src='/img/no-image.svg';">
                <?php else: ?>
                    <div class="goods-thumb-default">🛍️</div>
                <?php endif; ?>
            </div>

            <!-- 오른쪽: 상품 정보 -->
            <div class="goods-info">
                <h1><?= esc($goods['name']) ?></h1>

                <!-- 가격 -->
                <div class="goods-price-wrap">
                    <span class="goods-price" id="displayPrice">
                        <?= number_format((int)($goods['price'] ?? 0)) ?>
                    </span>
                    <span class="goods-price-unit">원</span>
                </div>

                <!-- 배송 방식 뱃지 -->
                <div class="goods-badges">
                    <?php
                    /* 배송 유형이 'pickup'이면 픽업, 그 외는 택배 뱃지 */
                    $dtClass = ($goods['delivery_type'] ?? '') === 'pickup' ? 'badge-pickup' : 'badge-parcel';
                    $dtLabel = ($goods['delivery_type'] ?? '') === 'pickup' ? '픽업 전용'     : '택배 배송';
                    ?>
                    <span class="badge-delivery <?= $dtClass ?>"><?= $dtLabel ?></span>
                </div>

                <?php
                /* 재고 여부 판단 */
                $stock   = (int)($goods['stock'] ?? 0);
                $soldOut = $stock <= 0;
                /* 기본 상품 가격 (JS에서 옵션 추가금액을 더할 때 기준값) */
                $basePrice = (int)($goods['price'] ?? 0);
                ?>

                <!-- 옵션 선택 (옵션이 있을 때만 출력) -->
                <?php if (!empty($options)): ?>
                <?php foreach ($options as $opt): ?>
                <div class="goods-option-group">
                    <label class="goods-option-label" for="option_<?= (int)$opt['idx'] ?>">
                        <?= esc($opt['name']) ?>
                    </label>
                    <select class="goods-option-select option-select"
                            id="option_<?= (int)$opt['idx'] ?>"
                            data-option-idx="<?= (int)$opt['idx'] ?>">
                        <option value="">선택하세요</option>
                        <?php foreach ($opt['values'] as $val): ?>
                        <option value="<?= (int)$val['idx'] ?>"
                                data-additional="<?= (int)($val['additional_price'] ?? 0) ?>"
                                <?= (int)($val['stock'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                            <?= esc($val['name']) ?>
                            <?php if ((int)($val['additional_price'] ?? 0) > 0): ?>
                            (+<?= number_format((int)$val['additional_price']) ?>원)
                            <?php elseif ((int)($val['additional_price'] ?? 0) < 0): ?>
                            (<?= number_format((int)$val['additional_price']) ?>원)
                            <?php endif; ?>
                            <?= (int)($val['stock'] ?? 0) <= 0 ? '[품절]' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- 수량 입력 -->
                <div class="qty-wrap">
                    <label for="qtyInput">수량</label>
                    <input type="number" id="qtyInput" class="qty-input"
                           value="1" min="1" max="<?= $soldOut ? 0 : $stock ?>"
                           <?= $soldOut ? 'disabled' : '' ?>>
                </div>

                <!-- 최종 금액 실시간 표시 -->
                <div class="goods-total-price">
                    합계: <strong id="totalPrice"><?= number_format($basePrice) ?></strong>원
                </div>

                <!-- 장바구니 담기 버튼 (품절 시 비활성화) -->
                <button type="button" id="btnAddCart"
                        class="btn-cart"
                        data-goods-idx="<?= (int)$goods['idx'] ?>"
                        data-base-price="<?= $basePrice ?>"
                        <?= $soldOut ? 'disabled' : '' ?>>
                    <?= $soldOut ? '품절' : '장바구니 담기' ?>
                </button>

            </div><!-- /.goods-info -->
        </div><!-- /.goods-detail-wrap -->

        <!-- 상품 설명 -->
        <?php if (!empty($goods['description'])): ?>
        <div class="goods-desc">
            <h2>상품 설명</h2>
            <div class="goods-desc-content">
                <?= nl2br(esc($goods['description'])) ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- 장바구니 담기 결과 토스트 -->
<div class="toast-msg" id="toastMsg"></div>

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<script>
/* ===== 굿즈 상세 페이지 스크립트 ===== */
(function () {
    /* 기준 가격 (PHP에서 data-* 속성으로 주입) */
    const BASE_PRICE = parseInt(document.getElementById('btnAddCart').dataset.basePrice, 10) || 0;
    const GOODS_IDX  = parseInt(document.getElementById('btnAddCart').dataset.goodsIdx, 10);

    const qtyInput    = document.getElementById('qtyInput');
    const totalEl     = document.getElementById('totalPrice');
    const displayEl   = document.getElementById('displayPrice');
    const btnAddCart  = document.getElementById('btnAddCart');
    const toastEl     = document.getElementById('toastMsg');

    /* 옵션 추가금액 합산값을 담는 변수 */
    let additionalSum = 0;

    /**
     * 선택된 옵션들의 추가금액 합산 후 화면 가격 업데이트
     */
    function updatePrice() {
        const qty   = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        const unit  = BASE_PRICE + additionalSum;
        const total = unit * qty;

        displayEl.textContent = unit.toLocaleString('ko-KR');
        totalEl.textContent   = total.toLocaleString('ko-KR');
    }

    /* 옵션 select 변경 시 추가금액 재계산 */
    document.querySelectorAll('.option-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            /* 전체 option-select 를 순회해 선택된 추가금액 합산 */
            additionalSum = 0;
            document.querySelectorAll('.option-select').forEach(function (s) {
                const opt = s.options[s.selectedIndex];
                if (opt && opt.value !== '') {
                    additionalSum += parseInt(opt.dataset.additional, 10) || 0;
                }
            });
            updatePrice();
        });
    });

    /* 수량 변경 시 합계 업데이트 */
    qtyInput.addEventListener('input', updatePrice);

    /**
     * 토스트 메시지 표시
     * @param {string} msg  - 표시할 문자열
     * @param {boolean} isError - true 이면 배경을 붉게
     */
    function showToast(msg, isError) {
        toastEl.textContent   = msg;
        toastEl.style.background = isError ? 'rgba(200,0,0,.85)' : 'rgba(0,0,0,.8)';
        toastEl.classList.add('show');
        setTimeout(function () { toastEl.classList.remove('show'); }, 2800);
    }

    /**
     * 장바구니 담기 — Fetch API로 POST /cart/add 요청
     */
    btnAddCart.addEventListener('click', function () {
        if (btnAddCart.disabled) return;

        /* 옵션 선택값 수집 */
        const selectedOptions = [];
        document.querySelectorAll('.option-select').forEach(function (s) {
            if (s.value !== '') {
                selectedOptions.push({
                    option_idx       : parseInt(s.dataset.optionIdx, 10),
                    option_value_idx : parseInt(s.value, 10),
                });
            }
        });

        const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);

        const body = {
            goods_idx : GOODS_IDX,
            qty       : qty,
            options   : selectedOptions,
        };

        btnAddCart.disabled    = true;
        btnAddCart.textContent = '처리 중...';

        fetch('/cart/add', {
            method  : 'POST',
            headers : {
                'Content-Type'     : 'application/json',
                'X-Requested-With' : 'XMLHttpRequest',
            },
            body    : JSON.stringify(body),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                showToast('장바구니에 담았습니다!', false);
            } else {
                showToast(data.message || '오류가 발생했습니다.', true);
            }
        })
        .catch(function () {
            showToast('네트워크 오류가 발생했습니다.', true);
        })
        .finally(function () {
            btnAddCart.disabled    = false;
            btnAddCart.textContent = '장바구니 담기';
        });
    });

    /* 초기 가격 표시 */
    updatePrice();
})();
</script>
</body>
</html>
