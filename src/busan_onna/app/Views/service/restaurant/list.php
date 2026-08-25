<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    /* ---- SEO 변수 계산 ---- */
    $currPage = (int)(service('request')->getGet('page') ?? 1);

    // 활성 필터에 따른 동적 타이틀·설명
    if (!empty($activeDistrict)) {
        $seoTitle = esc($activeDistrict) . ' 맛집 | 부산온나';
        $seoDesc  = '부산 ' . esc($activeDistrict) . ' 맛집 목록. 지역 대표 음식점부터 숨은 맛집까지 한눈에 탐색하세요.';
    } elseif (!empty($activeCategory) && isset($categories[(int)$activeCategory])) {
        $catName  = $categories[(int)$activeCategory];
        $seoTitle = esc($catName) . ' 맛집 | 부산온나';
        $seoDesc  = '부산 ' . esc($catName) . ' 맛집 목록. 부산의 다양한 ' . esc($catName) . ' 음식점을 탐색하세요.';
    } else {
        $seoTitle = '부산 맛집 | 부산온나 - 부산 여행의 시작';
        $seoDesc  = '부산 곳곳의 숨겨진 맛집부터 대표 맛집까지. 해운대, 광안리, 자갈치 등 부산 전 지역 맛집 정보를 한눈에 탐색하세요.';
    }
    if ($currPage > 1) { $seoTitle .= ' - ' . $currPage . '페이지'; }

    // canonical URL (검색어 제외, 필터+페이지만 포함)
    $canonParams = [];
    if (!empty($activeDistrict)) $canonParams[] = 'district=' . rawurlencode($activeDistrict);
    if (!empty($activeCategory)) $canonParams[] = 'category=' . rawurlencode($activeCategory);
    if ($currPage > 1)           $canonParams[] = 'page=' . $currPage;
    $canonicalUrl = 'https://busanonna.com/restaurants' . (!empty($canonParams) ? '?' . implode('&', $canonParams) : '');

    // 검색어 있으면 noindex (검색결과 중복 색인 방지)
    $metaRobots = !empty($activeSearch) ? 'noindex, follow' : 'index, follow';
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= $seoDesc ?>">
    <meta name="keywords"    content="부산맛집, 부산음식점, 부산여행맛집, 해운대맛집, 광안리맛집, 자갈치맛집, 부산온나">
    <meta name="robots"      content="<?= $metaRobots ?>">
    <link rel="canonical"    href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type"         content="website">
    <meta property="og:title"        content="<?= $seoTitle ?>">
    <meta property="og:description"  content="<?= $seoDesc ?>">
    <meta property="og:url"          content="<?= $canonicalUrl ?>">
    <meta property="og:image"        content="https://busanonna.com/img/og-restaurant.jpg">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"    content="부산온나">
    <meta property="og:locale"       content="ko_KR">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $seoTitle ?>">
    <meta name="twitter:description" content="<?= $seoDesc ?>">
    <meta name="twitter:image"       content="https://busanonna.com/img/og-restaurant.jpg">

    <!-- 구조화 데이터 (JSON-LD) - ItemList -->
    <script type="application/ld+json">
    <?php
    $ldItems = [];
    $ldOffset = ($currPage - 1) * 9;
    foreach (($restaurants ?? []) as $i => $r) {
        $ldItems[] = [
            '@type'    => 'ListItem',
            'position' => $ldOffset + $i + 1,
            'name'     => $r['name'],
            'url'      => 'https://busanonna.com/restaurants/' . (int)$r['idx'],
        ];
    }
    echo json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => '부산 맛집 목록',
        'description'     => strip_tags($seoDesc),
        'url'             => $canonicalUrl,
        'numberOfItems'   => $totalCount ?? 0,
        'itemListElement' => $ldItems,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    ?>
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'restaurants']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>🍽️ 부산 맛집</h1>
        <p>부산 곳곳의 숨겨진 맛집부터 대표 맛집까지 한 번에 탐색하세요</p>
    </div>
</section>

<!-- ===================== 필터 바 ===================== -->
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="/restaurants" id="filterForm">
            <!-- 검색 -->
            <div class="filter-search">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" name="q"
                       placeholder="맛집 이름, 해시태그, 지역 검색"
                       value="<?= esc($activeSearch) ?>"
                       autocomplete="off">
                <!-- AJAX 자동완성 드롭다운 -->
                <div class="suggest-dropdown" id="suggestDropdown"></div>
            </div>

            <!-- 구 필터 -->
            <select name="district" class="filter-select" onchange="this.form.submit()">
                <option value="">📍 전체 지역</option>
                <?php foreach ($districtList as $d): ?>
                    <option value="<?= esc($d) ?>" <?= $activeDistrict === $d ? 'selected' : '' ?>>
                        <?= esc($d) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- 카테고리 필터 -->
            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">🍴 전체 카테고리</option>
                <?php foreach ($categories as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $activeCategory == $num ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="" <?= $activeSort === '' ? 'selected' : '' ?>>🆕 최신순</option>
                <option value="like" <?= $activeSort === 'like' ? 'selected' : '' ?>>❤️ 좋아요순</option>
                <option value="name" <?= $activeSort === 'name' ? 'selected' : '' ?>>🔤 가나다순</option>
            </select>

            <button type="submit" class="filter-submit-btn">검색</button>

            <?php if ($activeSearch || $activeDistrict || $activeCategory): ?>
            <a href="/restaurants" class="filter-reset-btn">초기화</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ===================== 결과 + 뷰 전환 ===================== -->
<section class="restaurant-section">
    <div class="container">
        <div class="view-controls">
            <p class="result-count">
                총 <strong><?= $totalCount ?></strong>개의 맛집
                <?php if ($totalCount > 0): ?>
                <?php
                $perPage  = 9;
                $currPage = (int)(service('request')->getGet('page') ?? 1);
                $from     = ($currPage - 1) * $perPage + 1;
                $to       = min($currPage * $perPage, $totalCount);
                ?>
                <span class="result-range">(<?= $from ?>–<?= $to ?>번째)</span>
                <?php endif; ?>
            </p>
            <div class="view-toggle">
                <button class="view-btn active" id="btnCardView" title="카드 보기">
                    ⊞
                </button>
                <button class="view-btn" id="btnListView" title="리스트 보기">
                    ☰
                </button>
            </div>
        </div>

        <?php if (empty($restaurants)): ?>
        <!-- 빈 결과 -->
        <div class="empty-result">
            <div class="empty-result-icon">🍽️</div>
            <h3>검색 결과가 없습니다</h3>
            <p>다른 검색어나 필터를 사용해보세요</p>
        </div>

        <?php else: ?>

        <?php
        /* 카테고리별 이모지 & 색상 매핑 */
        $catEmoji = [1=>'🍲', 2=>'🍣', 3=>'🥢', 4=>'🍝', 5=>'🥞', 6=>'☕', 7=>'🍽️', 8=>'🍴'];
        $catColor = [1=>'#e55039', 2=>'#6c5ce7', 3=>'#e17055', 4=>'#00b894', 5=>'#fdcb6e', 6=>'#a29bfe', 7=>'#fab1a0', 8=>'#b2bec3'];
        ?>

        <!-- ---- 카드 뷰 ---- -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($restaurants as $r): ?>
            <?php
            $catNum   = (int)($r['category_num'] ?? 8);
            $starVal  = (float)($r['star_point']  ?? 0);
            $priceNum = (int)($r['price_range']   ?? 1);
            $color    = $catColor[$catNum] ?? '#b2bec3';
            $emoji    = $catEmoji[$catNum] ?? '🍴';
            ?>
            <a class="r-card" href="/restaurants/<?= (int)$r['idx'] ?>" style="text-decoration:none;color:inherit;">
                <!-- 썸네일 (카드 뷰에서만 출력) -->
                <div class="r-card-thumb">
                    <?php if (!empty($r['thumbnail'])): ?>
                        <img src="<?= esc($r['thumbnail']) ?>" alt="<?= esc($r['name']) ?>"
                             onerror="this.onerror=null; this.src='/img/no-image.svg';">
                    <?php else: ?>
                        <div class="r-card-thumb-default" style="background: <?= $color ?>22;">
                            <span><?= $emoji ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="r-card-category" style="background: <?= $color ?>;">
                        <?= esc($categories[$catNum] ?? '기타') ?>
                    </span>
                    <?php if (!empty($r['parking'])): ?>
                    <span class="r-card-parking">🅿️ 주차가능</span>
                    <?php endif; ?>
                </div>

                <div class="r-card-body">
                    <h3 class="r-card-name"><?= esc($r['name']) ?></h3>

                    <div class="r-card-meta">
                        <?php if (!empty($r['district'])): ?>
                        <span class="r-card-district">📍 <?= esc($r['district']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['open_time'])): ?>
                        <span class="r-card-hours">🕐 <?= esc($r['open_time']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php /* 별점 비활성화
                    <?php if ($starVal > 0): ?>
                    <div class="r-card-stars">
                        <?php
                        $full  = (int) floor($starVal);
                        $half  = ($starVal - $full) >= 0.5 ? 1 : 0;
                        $empty = 5 - $full - $half;
                        ?>
                        <span class="stars-text">
                            <?= str_repeat('★', $full) ?>
                            <?= $half ? '⭒' : '' ?>
                            <?= str_repeat('☆', $empty) ?>
                        </span>
                        <span class="stars-score"><?= number_format($starVal, 1) ?></span>
                    </div>
                    <?php endif; ?>
                    */ ?>
                    <!-- 좋아요 카운트 (0이면 표시 안 함) -->
                    <?php if (($r['like_cnt'] ?? 0) > 0): ?>
                    <div class="r-card-likes">♥ <?= (int)$r['like_cnt'] ?></div>
                    <?php endif; ?>

                    <!-- 가격대 -->
                    <span class="price-badge"><?= esc($priceRanges[$priceNum] ?? '') ?></span>

                    <!-- 해시태그 -->
                    <?php if (!empty($r['tags'])): ?>
                    <div class="r-card-tags">
                        <?php foreach ($r['tags'] as $tag): ?>
                        <span class="r-tag">#<?= esc($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- ---- 리스트 뷰 ---- -->
        <div class="restaurant-list-view" id="listView" style="display:none;">
            <?php foreach ($restaurants as $r): ?>
            <?php
            $catNum   = (int)($r['category_num'] ?? 8);
            $starVal  = (float)($r['star_point']  ?? 0);
            $priceNum = (int)($r['price_range']   ?? 1);
            $color    = $catColor[$catNum] ?? '#b2bec3';
            $emoji    = $catEmoji[$catNum] ?? '🍴';
            ?>
            <a class="r-list-item" href="/restaurants/<?= (int)$r['idx'] ?>" style="text-decoration:none; color:inherit;">
                <!-- 카테고리 색상 블록 (리스트 뷰에서 사진 대신) -->
                <div class="r-list-cat" style="background: <?= $color ?>22;">
                    <span style="font-size:22px;"><?= $emoji ?></span>
                </div>

                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($r['name']) ?></span>
                        <span class="r-list-category-badge" style="background: <?= $color ?>;">
                            <?= esc($categories[$catNum] ?? '기타') ?>
                        </span>
                        <?php /* 별점 비활성화
                        <?php if ($starVal > 0): ?>
                        <span class="stars-text" style="font-size:13px; color:#f39c12;">
                            <?php
                            $full  = (int) floor($starVal);
                            $half  = ($starVal - $full) >= 0.5 ? 1 : 0;
                            $empty = 5 - $full - $half;
                            echo str_repeat('★', $full) . ($half ? '⭒' : '') . str_repeat('☆', $empty);
                            ?>
                        </span>
                        <span style="font-size:13px; font-weight:700;"><?= number_format($starVal, 1) ?></span>
                        <?php endif; ?>
                        */ ?>
                        <?php if (($r['like_cnt'] ?? 0) > 0): ?>
                        <span class="r-list-likes">♥ <?= (int)$r['like_cnt'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="r-list-info">
                        <?php if (!empty($r['district'])): ?>
                        <span>📍 <?= esc($r['district']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['open_time'])): ?>
                        <span>🕐 <?= esc($r['open_time']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['phone'])): ?>
                        <span>📞 <?= esc($r['phone']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['parking'])): ?>
                        <span>🅿️ 주차가능</span>
                        <?php endif; ?>
                    </div>

                    <!-- 가격대 -->
                    <div class="r-list-footer">
                        <span class="price-badge"><?= esc($priceRanges[$priceNum] ?? '') ?></span>
                    </div>

                    <!-- 해시태그 (가격대 아래 별도 행) -->
                    <?php if (!empty($r['tags'])): ?>
                    <div class="r-list-tags">
                        <?php foreach ($r['tags'] as $tag): ?>
                        <span class="r-tag">#<?= esc($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- ===================== 페이지네이션 ===================== -->
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
<script>
/* 자동완성 suggest URL */
const SUGGEST_URL = '/restaurants/suggest';
</script>
<script src="/js/service-common.js"></script>
</body>
</html>
