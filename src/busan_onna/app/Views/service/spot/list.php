<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    /* ---- SEO 변수 계산 ---- */
    $currPage = (int)(service('request')->getGet('page') ?? 1);

    if (!empty($activeDistrict)) {
        $seoTitle = esc($activeDistrict) . ' 관광지 | 부산온나';
        $seoDesc  = '부산 ' . esc($activeDistrict) . ' 관광지 목록. 해당 지역의 명소와 볼거리를 한눈에 확인하세요.';
    } elseif (!empty($activeCategory) && isset($categories[(int)$activeCategory])) {
        $catName  = $categories[(int)$activeCategory];
        $seoTitle = esc($catName) . ' | 부산 관광지 - 부산온나';
        $seoDesc  = '부산 ' . esc($catName) . ' 관광지 목록. 부산의 다양한 ' . esc($catName) . ' 명소를 탐색하세요.';
    } else {
        $seoTitle = '부산 관광지 | 부산온나 - 부산 여행의 시작';
        $seoDesc  = '해운대부터 감천문화마을까지 부산의 숨겨진 명소를 발견하세요. 해변, 자연, 문화시설 등 부산 전 지역 관광지 정보를 제공합니다.';
    }
    if ($currPage > 1) { $seoTitle .= ' - ' . $currPage . '페이지'; }

    $canonParams = [];
    if (!empty($activeDistrict)) $canonParams[] = 'district=' . rawurlencode($activeDistrict);
    if (!empty($activeCategory)) $canonParams[] = 'category=' . rawurlencode($activeCategory);
    if ($currPage > 1)           $canonParams[] = 'page=' . $currPage;
    $canonicalUrl = 'https://busanonna.com/spots' . (!empty($canonParams) ? '?' . implode('&', $canonParams) : '');

    $metaRobots = !empty($activeSearch) ? 'noindex, follow' : 'index, follow';
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= $seoDesc ?>">
    <meta name="keywords"    content="부산관광지, 부산명소, 부산여행, 해운대, 광안리, 감천문화마을, 부산여행코스, 부산온나">
    <meta name="robots"      content="<?= $metaRobots ?>">
    <link rel="canonical"    href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type"         content="website">
    <meta property="og:title"        content="<?= $seoTitle ?>">
    <meta property="og:description"  content="<?= $seoDesc ?>">
    <meta property="og:url"          content="<?= $canonicalUrl ?>">
    <meta property="og:image"        content="https://busanonna.com/img/og-spot.jpg">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"    content="부산온나">
    <meta property="og:locale"       content="ko_KR">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $seoTitle ?>">
    <meta name="twitter:description" content="<?= $seoDesc ?>">
    <meta name="twitter:image"       content="https://busanonna.com/img/og-spot.jpg">

    <!-- 구조화 데이터 (JSON-LD) - ItemList -->
    <script type="application/ld+json">
    <?php
    $ldItems = [];
    $ldOffset = ($currPage - 1) * 9;
    foreach (($spots ?? []) as $i => $s) {
        $ldItems[] = [
            '@type'    => 'ListItem',
            'position' => $ldOffset + $i + 1,
            'name'     => $s['name'],
            'url'      => 'https://busanonna.com/spots/' . (int)$s['idx'],
        ];
    }
    echo json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => '부산 관광지 목록',
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

<?= view('service/partials/header', ['activeNav' => 'spots']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero page-hero--spot">
    <div class="container">
        <h1>🗺️ 부산 관광지</h1>
        <p>해운대부터 감천문화마을까지, 부산의 숨겨진 명소를 발견하세요</p>
    </div>
</section>

<!-- ===================== 필터 바 ===================== -->
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="/spots" id="filterForm">
            <div class="filter-search">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" name="q"
                       placeholder="관광지 이름, 해시태그, 지역 검색"
                       value="<?= esc($activeSearch) ?>"
                       autocomplete="off">
                <div class="suggest-dropdown" id="suggestDropdown"></div>
            </div>

            <select name="district" class="filter-select" onchange="this.form.submit()">
                <option value="">📍 전체 지역</option>
                <?php foreach ($districtList as $d): ?>
                    <option value="<?= esc($d) ?>" <?= $activeDistrict === $d ? 'selected' : '' ?>>
                        <?= esc($d) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">🗂️ 전체 카테고리</option>
                <?php foreach ($categories as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $activeCategory == $num ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="filter-submit-btn">검색</button>

            <?php if ($activeSearch || $activeDistrict || $activeCategory): ?>
            <a href="/spots" class="filter-reset-btn">초기화</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ===================== 결과 + 뷰 전환 ===================== -->
<section class="restaurant-section">
    <div class="container">
        <div class="view-controls">
            <p class="result-count">
                총 <strong><?= $totalCount ?></strong>개의 관광지
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
                <button class="view-btn active" id="btnCardView" title="카드 보기">⊞</button>
                <button class="view-btn" id="btnListView" title="리스트 보기">☰</button>
            </div>
        </div>

        <?php if (empty($spots)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🗺️</div>
            <h3>검색 결과가 없습니다</h3>
            <p>다른 검색어나 필터를 사용해보세요</p>
        </div>

        <?php else: ?>

        <?php
        $catEmoji = [1=>'🏖️', 2=>'🌲', 3=>'🏛️', 4=>'🖼️', 5=>'🎡', 6=>'🌃', 7=>'🛍️', 8=>'📍'];
        $catColor = [1=>'#0984e3', 2=>'#00b894', 3=>'#6c5ce7', 4=>'#e17055', 5=>'#fd79a8', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];
        ?>

        <!-- ---- 카드 뷰 ---- -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($spots as $s): ?>
            <?php
            $catNum  = (int)($s['category_num'] ?? 8);
            $starVal = (float)($s['star_point']  ?? 0);
            $color   = $catColor[$catNum] ?? '#b2bec3';
            $emoji   = $catEmoji[$catNum] ?? '📍';
            ?>
            <a class="r-card" href="/spots/<?= (int)$s['idx'] ?>" style="text-decoration:none;color:inherit;">
                <div class="r-card-thumb">
                    <?php if (!empty($s['thumbnail'])): ?>
                        <img src="<?= esc($s['thumbnail']) ?>" alt="<?= esc($s['name']) ?>"
                             onerror="this.onerror=null; this.src='/img/no-image.svg';">
                    <?php else: ?>
                        <div class="r-card-thumb-default" style="background: <?= $color ?>22;">
                            <span><?= $emoji ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="r-card-category" style="background: <?= $color ?>;">
                        <?= esc($categories[$catNum] ?? '기타') ?>
                    </span>
                    <?php if (!empty($s['parking'])): ?>
                    <span class="r-card-parking">🅿️ 주차가능</span>
                    <?php endif; ?>
                </div>

                <div class="r-card-body">
                    <h3 class="r-card-name"><?= esc($s['name']) ?></h3>

                    <div class="r-card-meta">
                        <?php if (!empty($s['district'])): ?>
                        <span class="r-card-district">📍 <?= esc($s['district']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($s['open_time'])): ?>
                        <span class="r-card-hours">🕐 <?= esc($s['open_time']) ?></span>
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
                            <?= str_repeat('★', $full) ?><?= $half ? '⭒' : '' ?><?= str_repeat('☆', $empty) ?>
                        </span>
                        <span class="stars-score"><?= number_format($starVal, 1) ?></span>
                    </div>
                    <?php endif; ?>
                    */ ?>
                    <!-- 좋아요 카운트 (0이면 표시 안 함) -->
                    <?php if (($s['like_count'] ?? 0) > 0): ?>
                    <div class="r-card-likes">♥ <?= (int)$s['like_count'] ?></div>
                    <?php endif; ?>

                    <!-- 입장료 -->
                    <?php if (!empty($s['admission_fee'])): ?>
                    <span class="price-badge">🎫 <?= esc($s['admission_fee']) ?></span>
                    <?php else: ?>
                    <span class="price-badge free-badge">🎫 무료</span>
                    <?php endif; ?>

                    <?php if (!empty($s['tags'])): ?>
                    <div class="r-card-tags">
                        <?php foreach ($s['tags'] as $tag): ?>
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
            <?php foreach ($spots as $s): ?>
            <?php
            $catNum  = (int)($s['category_num'] ?? 8);
            $starVal = (float)($s['star_point']  ?? 0);
            $color   = $catColor[$catNum] ?? '#b2bec3';
            $emoji   = $catEmoji[$catNum] ?? '📍';
            ?>
            <a class="r-list-item" href="/spots/<?= (int)$s['idx'] ?>" style="text-decoration:none; color:inherit;">
                <div class="r-list-cat" style="background: <?= $color ?>22;">
                    <span style="font-size:22px;"><?= $emoji ?></span>
                </div>

                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($s['name']) ?></span>
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
                        <?php if (($s['like_count'] ?? 0) > 0): ?>
                        <span class="r-list-likes">♥ <?= (int)$s['like_count'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="r-list-info">
                        <?php if (!empty($s['district'])): ?>
                        <span>📍 <?= esc($s['district']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($s['open_time'])): ?>
                        <span>🕐 <?= esc($s['open_time']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($s['parking'])): ?>
                        <span>🅿️ 주차가능</span>
                        <?php endif; ?>
                    </div>

                    <div class="r-list-footer">
                        <?php if (!empty($s['admission_fee'])): ?>
                        <span class="price-badge">🎫 <?= esc($s['admission_fee']) ?></span>
                        <?php else: ?>
                        <span class="price-badge free-badge">🎫 무료</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($s['tags'])): ?>
                    <div class="r-list-tags">
                        <?php foreach ($s['tags'] as $tag): ?>
                        <span class="r-tag">#<?= esc($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

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
const SUGGEST_URL = '/spots/suggest';
</script>
<script src="/js/service-common.js"></script>
</body>
</html>
