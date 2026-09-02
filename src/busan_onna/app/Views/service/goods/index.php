<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    /* ---- SEO 변수 계산 ---- */
    $currPage = (int)(service('request')->getGet('page') ?? 1);

    // 필터 상태에 따른 동적 타이틀·설명
    $seoTitle = '부산온나 굿즈 | 부산 여행 기념품';
    $seoDesc  = '부산온나 공식 굿즈 스토어. 부산 여행의 추억을 담은 기념품, 소품, 의류 등 다양한 상품을 만나보세요.';
    if (!empty($q)) {
        $seoTitle = esc($q) . ' 굿즈 검색 | 부산온나';
        $seoDesc  = '부산온나 굿즈 중 "' . esc($q) . '" 검색 결과입니다.';
    }
    if ($currPage > 1) { $seoTitle .= ' - ' . $currPage . '페이지'; }

    // canonical URL (검색어 제외, 필터+페이지만)
    $canonParams = [];
    if (!empty($deliveryType)) $canonParams[] = 'delivery_type=' . rawurlencode($deliveryType);
    if ($currPage > 1)         $canonParams[] = 'page=' . $currPage;
    $canonicalUrl = 'https://busanonna.com/goods' . (!empty($canonParams) ? '?' . implode('&', $canonParams) : '');

    // 검색어가 있으면 noindex
    $metaRobots = !empty($q) ? 'noindex, follow' : 'index, follow';
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= $seoDesc ?>">
    <meta name="robots"      content="<?= $metaRobots ?>">
    <link rel="canonical"    href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="<?= $seoTitle ?>">
    <meta property="og:description" content="<?= $seoDesc ?>">
    <meta property="og:url"         content="<?= $canonicalUrl ?>">
    <meta property="og:image"       content="https://busanonna.com/img/og-goods.jpg">
    <meta property="og:site_name"   content="부산온나">
    <meta property="og:locale"      content="ko_KR">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 굿즈 목록 전용 스타일 ---- */
        .goods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }
        .goods-card {
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
        .goods-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            transform: translateY(-2px);
        }
        .goods-card-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .goods-card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .goods-card-thumb-default {
            font-size: 48px;
        }
        .goods-card-body {
            padding: 14px 16px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .goods-card-name {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .goods-card-price {
            font-size: 16px;
            font-weight: 700;
            color: #e55039;
            margin: 0;
        }
        .goods-card-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: auto;
        }
        .badge-delivery {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 500;
        }
        .badge-parcel  { background: #e3f2fd; color: #1565c0; }
        .badge-pickup  { background: #e8f5e9; color: #2e7d32; }
        .badge-soldout {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,.45);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* 필터 바 */
        .filter-bar    { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filter-search { position: relative; flex: 1; min-width: 200px; }
        .filter-search input {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
        }
        .filter-select {
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
        }
        .filter-submit-btn {
            padding: 10px 20px;
            background: #e55039;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
        }
        .filter-reset-btn {
            padding: 10px 16px;
            background: #f8f9fa;
            color: #666;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
        }
        .filter-section { background: #f8f9fa; padding: 20px 0; border-bottom: 1px solid #e9ecef; }
        .pager-wrap     { margin-top: 40px; text-align: center; }
        .empty-result   { text-align: center; padding: 80px 20px; color: #adb5bd; }
        .empty-result h3 { font-size: 20px; margin: 16px 0 8px; color: #868e96; }
        .result-count   { font-size: 14px; color: #666; margin-bottom: 8px; }
        .result-count strong { color: #333; }
        .goods-section  { padding: 40px 0 80px; }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'goods']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>부산온나 굿즈</h1>
        <p>부산 여행의 추억을 담은 특별한 기념품을 만나보세요</p>
    </div>
</section>

<!-- ===================== 필터 바 ===================== -->
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="/goods" id="filterForm">

            <!-- 검색 -->
            <div class="filter-search">
                <input type="text" name="q"
                       placeholder="상품명 검색"
                       value="<?= esc($q) ?>"
                       autocomplete="off">
            </div>

            <!-- 배송 유형 필터 -->
            <select name="delivery_type" class="filter-select" onchange="this.form.submit()">
                <option value="">전체 배송유형</option>
                <option value="parcel" <?= $deliveryType === 'parcel' ? 'selected' : '' ?>>택배</option>
                <option value="pickup" <?= $deliveryType === 'pickup' ? 'selected' : '' ?>>픽업</option>
            </select>

            <!-- 정렬 -->
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>최신순</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>가격 낮은순</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>가격 높은순</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>가나다순</option>
            </select>

            <button type="submit" class="filter-submit-btn">검색</button>

            <?php if ($q || $deliveryType): ?>
            <a href="/goods" class="filter-reset-btn">초기화</a>
            <?php endif; ?>

        </form>
    </div>
</div>

<!-- ===================== 상품 목록 ===================== -->
<section class="goods-section">
    <div class="container">

        <?php if (empty($items)): ?>
        <!-- 빈 결과 -->
        <div class="empty-result">
            <div style="font-size:48px;">🛍️</div>
            <h3>상품이 없습니다</h3>
            <p>다른 검색어나 필터를 사용해보세요</p>
        </div>

        <?php else: ?>

        <!-- 결과 건수 -->
        <p class="result-count">
            총 <strong><?= $pager->getTotal() ?></strong>개의 상품
        </p>

        <!-- 상품 카드 그리드 (12개/페이지) -->
        <div class="goods-grid">
            <?php foreach ($items as $item): ?>
            <?php
            /* 배송유형 뱃지 클래스·레이블 결정 */
            $dtClass = $item['delivery_type'] === 'pickup' ? 'badge-pickup' : 'badge-parcel';
            $dtLabel = $item['delivery_type'] === 'pickup' ? '픽업'         : '택배';
            $soldOut = (int)($item['stock'] ?? 0) <= 0;
            ?>
            <a class="goods-card" href="/goods/<?= (int)$item['idx'] ?>">
                <!-- 썸네일 -->
                <div class="goods-card-thumb">
                    <?php if (!empty($item['thumbnail'])): ?>
                        <img src="<?= esc($item['thumbnail']) ?>"
                             alt="<?= esc($item['name']) ?>"
                             onerror="this.onerror=null; this.src='/img/no-image.svg';">
                    <?php else: ?>
                        <div class="goods-card-thumb-default">🛍️</div>
                    <?php endif; ?>

                    <?php if ($soldOut): ?>
                    <div class="badge-soldout">품절</div>
                    <?php endif; ?>
                </div>

                <div class="goods-card-body">
                    <h3 class="goods-card-name"><?= esc($item['name']) ?></h3>
                    <p class="goods-card-price"><?= number_format((int)($item['price'] ?? 0)) ?>원</p>
                    <div class="goods-card-badges">
                        <span class="badge-delivery <?= $dtClass ?>"><?= $dtLabel ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($pager->getPageCount() > 1): ?>
        <div class="pager-wrap">
            <?= $pager->links('default', 'service_pager') ?>
        </div>
        <?php endif; ?>

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
</body>
</html>
