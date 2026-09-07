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

        /* 수량 스텝퍼 */
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
        .qty-stepper {
            display: flex;
            align-items: center;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .qty-stepper button {
            width: 36px;
            height: 38px;
            background: #f8f9fa;
            border: none;
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, color .15s;
            flex-shrink: 0;
        }
        .qty-stepper button:hover:not(:disabled) {
            background: #e9ecef;
            color: #e55039;
        }
        .qty-stepper button:disabled {
            color: #adb5bd;
            cursor: not-allowed;
        }
        /* 수량 표시 — readonly, 직접 입력 불가 */
        .qty-input {
            width: 52px;
            height: 38px;
            border: none;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            color: #212529;
            background: #fff;
            cursor: default;
            user-select: none;
            outline: none;
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

        /* 구매 버튼 영역: 즉시 구매 + 장바구니 나란히 */
        .btn-group-purchase {
            display: flex;
            gap: 10px;
            align-items: stretch;
        }
        .btn-buy-now {
            flex: 1;
            padding: 16px;
            background: #e55039;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: background .2s;
        }
        .btn-buy-now:hover { background: #c0392b; }
        .btn-buy-now:disabled {
            background: #adb5bd;
            cursor: not-allowed;
        }
        /* 장바구니 아이콘+텍스트 버튼 */
        .btn-add-cart {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 16px 18px;
            background: #fff;
            color: #495057;
            font-size: 14px;
            font-weight: 600;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            white-space: nowrap;
            transition: border-color .2s, color .2s;
        }
        .btn-add-cart:hover {
            border-color: #e55039;
            color: #e55039;
        }
        .btn-add-cart:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* 상품 설명 (버튼 아래) */
        .goods-desc {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #e9ecef;
        }
        .goods-desc h2 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #333;
        }
        .goods-desc-content {
            font-size: 14px;
            line-height: 1.8;
            color: #555;
        }

        /* 다른 상품 섹션 (그리드 아래) */
        .other-goods-section {
            margin-top: 56px;
            padding-top: 40px;
            border-top: 2px solid #e9ecef;
        }
        .other-goods-section h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #212529;
        }
        .other-goods-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }
        @media (max-width: 900px) {
            .other-goods-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 560px) {
            .other-goods-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .other-goods-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s;
        }
        .other-goods-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,.10);
            transform: translateY(-3px);
        }
        .other-goods-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .other-goods-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .other-goods-thumb-default {
            font-size: 48px;
        }
        .other-goods-body {
            padding: 14px 16px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .other-goods-name {
            font-size: 14px;
            font-weight: 600;
            color: #212529;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .other-goods-price {
            font-size: 15px;
            font-weight: 800;
            color: #e55039;
            margin-top: auto;
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

            <!-- 오른쪽: 상품 정보 + 버튼 + 상품 설명 -->
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

                <!-- 수량 스텝퍼 (버튼으로만 조절, 직접 입력 불가) -->
                <div class="qty-wrap">
                    <label>수량</label>
                    <div class="qty-stepper">
                        <button type="button" id="btnQtyMinus" aria-label="수량 감소" <?= $soldOut ? 'disabled' : '' ?>>−</button>
                        <input type="text" id="qtyInput" class="qty-input"
                               value="1" readonly
                               <?= $soldOut ? 'disabled' : '' ?>>
                        <button type="button" id="btnQtyPlus" aria-label="수량 증가" <?= $soldOut ? 'disabled' : '' ?>>+</button>
                    </div>
                </div>

                <!-- 최종 금액 실시간 표시 -->
                <div class="goods-total-price">
                    합계: <strong id="totalPrice"><?= number_format($basePrice) ?></strong>원
                </div>

                <!-- 즉시 구매 + 장바구니 버튼 -->
                <div class="btn-group-purchase">
                    <!-- 즉시 구매: 장바구니 담기 후 주문 폼으로 즉시 이동 -->
                    <button type="button" id="btnBuyNow"
                            class="btn-buy-now"
                            data-goods-idx="<?= (int)$goods['idx'] ?>"
                            data-base-price="<?= $basePrice ?>"
                            <?= $soldOut ? 'disabled' : '' ?>>
                        <?= $soldOut ? '품절' : '즉시 구매' ?>
                    </button>

                    <!-- 장바구니 담기: 아이콘 + 텍스트 -->
                    <button type="button" id="btnAddCart"
                            class="btn-add-cart"
                            <?= $soldOut ? 'disabled' : '' ?>>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        장바구니
                    </button>
                </div>

                <!-- 상품 설명 (버튼 아래) -->
                <?php if (!empty($goods['description'])): ?>
                <div class="goods-desc">
                    <h2>상품 설명</h2>
                    <div class="goods-desc-content">
                        <?= nl2br(esc($goods['description'])) ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.goods-info -->
        </div><!-- /.goods-detail-wrap -->

        <!-- 다른 상품 카드 (기존 상품 설명 위치) -->
        <?php if (!empty($otherGoods)): ?>
        <div class="other-goods-section">
            <h2>다른 굿즈 보기</h2>
            <div class="other-goods-grid">
                <?php foreach ($otherGoods as $og): ?>
                <a href="/goods/<?= (int)$og['idx'] ?>" class="other-goods-card">
                    <div class="other-goods-thumb">
                        <?php if (!empty($og['thumbnail'])): ?>
                            <img src="<?= esc($og['thumbnail']) ?>"
                                 alt="<?= esc($og['name']) ?>"
                                 onerror="this.onerror=null; this.src='/img/no-image.svg';">
                        <?php else: ?>
                            <div class="other-goods-thumb-default">🛍️</div>
                        <?php endif; ?>
                    </div>
                    <div class="other-goods-body">
                        <p class="other-goods-name"><?= esc($og['name']) ?></p>
                        <p class="other-goods-price"><?= number_format((int)$og['price']) ?>원</p>
                    </div>
                </a>
                <?php endforeach; ?>
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
    const BASE_PRICE   = parseInt(document.getElementById('btnBuyNow').dataset.basePrice, 10) || 0;
    const GOODS_IDX    = parseInt(document.getElementById('btnBuyNow').dataset.goodsIdx, 10);
    const MAX_STOCK    = <?= $soldOut ? 0 : $stock ?>;
    /* PHP 세션에서 로그인 여부를 JS로 전달 */
    const IS_LOGGED_IN = <?= session()->get('user.idx') ? 'true' : 'false' ?>;

    const qtyInput    = document.getElementById('qtyInput');
    const btnQtyMinus = document.getElementById('btnQtyMinus');
    const btnQtyPlus  = document.getElementById('btnQtyPlus');
    const totalEl     = document.getElementById('totalPrice');
    const displayEl   = document.getElementById('displayPrice');
    const btnBuyNow   = document.getElementById('btnBuyNow');
    const btnAddCart  = document.getElementById('btnAddCart');
    const toastEl     = document.getElementById('toastMsg');

    /* 옵션 추가금액 합산값 */
    let additionalSum = 0;
    let currentQty    = 1;

    /**
     * 수량 버튼 활성화 상태 갱신 — 최소(1) 또는 최대(재고) 도달 시 비활성화
     */
    function syncQtyButtons() {
        btnQtyMinus.disabled = currentQty <= 1;
        btnQtyPlus.disabled  = MAX_STOCK > 0 && currentQty >= MAX_STOCK;
        qtyInput.value       = currentQty;
    }

    /**
     * 선택된 옵션들의 추가금액 합산 후 화면 가격 업데이트
     */
    function updatePrice() {
        const unit  = BASE_PRICE + additionalSum;
        const total = unit * currentQty;

        displayEl.textContent = unit.toLocaleString('ko-KR');
        totalEl.textContent   = total.toLocaleString('ko-KR');
    }

    /* 수량 감소 버튼 */
    btnQtyMinus.addEventListener('click', function () {
        if (currentQty > 1) {
            currentQty -= 1;
            syncQtyButtons();
            updatePrice();
        }
    });

    /* 수량 증가 버튼 */
    btnQtyPlus.addEventListener('click', function () {
        if (MAX_STOCK === 0 || currentQty < MAX_STOCK) {
            currentQty += 1;
            syncQtyButtons();
            updatePrice();
        }
    });

    /* 옵션 select 변경 시 추가금액 재계산 */
    document.querySelectorAll('.option-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
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

    /**
     * 토스트 메시지 표시
     */
    function showToast(msg, isError) {
        toastEl.textContent      = msg;
        toastEl.style.background = isError ? 'rgba(200,0,0,.85)' : 'rgba(0,0,0,.8)';
        toastEl.classList.add('show');
        setTimeout(function () { toastEl.classList.remove('show'); }, 2800);
    }

    /**
     * 현재 선택된 첫 번째 옵션 값 idx 반환 (없으면 0)
     */
    function getOptionValueIdx() {
        var sel = document.querySelector('.option-select');
        return (sel && sel.value) ? parseInt(sel.value, 10) : 0;
    }

    /**
     * 즉시 구매: 장바구니를 전혀 거치지 않고 buy_now 쿼리스트링으로 주문 폼 직행
     * 장바구니와 완전히 독립 동작 — 기존 장바구니에 영향 없음
     */
    btnBuyNow.addEventListener('click', function () {
        if (btnBuyNow.disabled) return;

        /* 미로그인 시 로그인 모달 표시 후 중단 */
        if (!IS_LOGGED_IN) {
            document.getElementById('btnOpenLogin').click();
            return;
        }

        var params = new URLSearchParams({
            buy_now  : '1',
            goods_idx: GOODS_IDX,
            qty      : currentQty,
        });
        var optValIdx = getOptionValueIdx();
        if (optValIdx) params.append('option_value_idx', optValIdx);

        location.href = '/order?' + params.toString();
    });

    /**
     * 장바구니 담기: 기존 /cart/add 흐름 유지, 토스트 알림
     */
    btnAddCart.addEventListener('click', function () {
        if (btnAddCart.disabled) return;

        /* 미로그인 시 로그인 모달 표시 후 중단 */
        if (!IS_LOGGED_IN) {
            document.getElementById('btnOpenLogin').click();
            return;
        }

        btnAddCart.disabled = true;

        var selectedOptions = [];
        document.querySelectorAll('.option-select').forEach(function (s) {
            if (s.value !== '') {
                selectedOptions.push({
                    option_idx       : parseInt(s.dataset.optionIdx, 10),
                    option_value_idx : parseInt(s.value, 10),
                });
            }
        });

        fetch('/cart/add', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body    : JSON.stringify({ goods_idx: GOODS_IDX, quantity: currentQty, options: selectedOptions }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            showToast(data.success ? '장바구니에 담았습니다!' : (data.message || '오류가 발생했습니다.'), !data.success);
        })
        .catch(function () {
            showToast('네트워크 오류가 발생했습니다.', true);
        })
        .finally(function () {
            btnAddCart.disabled = false;
        });
    });

    /* 초기 상태 설정 */
    syncQtyButtons();
    updatePrice();
})();
</script>
</body>
</html>
