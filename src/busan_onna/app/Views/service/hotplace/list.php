<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $activeDistrict ? esc($activeDistrict) . ' 핫플레이스' : '지역별 핫플레이스' ?> - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
</head>
<body>

<!-- ===================== 헤더 ===================== -->
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">
            <a href="/" class="logo">
                <span class="logo-main">부산온나</span>
                <span class="logo-sub">BUSAN ONNA</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="/spots">관광지</a></li>
                    <li><a href="/restaurants">맛집</a></li>
                    <li><a href="/festivals">축제</a></li>
                    <li><a href="/travel-courses">여행코스</a></li>
                </ul>
            </nav>
            <div class="header-auth">
                <?php if (session()->get('user.idx')): ?>
                    <span class="user-greeting">안녕하세요, <?= esc(session()->get('user.id')) ?>님</span>
                    <a href="/auth/logout" class="btn-auth logout">로그아웃</a>
                <?php else: ?>
                    <button type="button" class="btn-auth login" id="btnOpenLogin">로그인</button>
                    <button type="button" class="btn-auth signup" id="btnOpenSignup">회원가입</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero page-hero--hotplace">
    <div class="container">
        <h1>📍 <?= $activeDistrict ? esc($activeDistrict) . ' 핫플레이스' : '지역별 핫플레이스' ?></h1>
        <p>부산의 인기 명소, 맛집, 축제를 한눈에 찾아보세요</p>
    </div>
</section>

<!-- ===================== 지역구 탭 ===================== -->
<?php
// 탭 전환 시 tab 파라미터 유지, 지역 변경 시 카테고리·검색은 초기화
$tabQuery = $activeTab !== 'spot' ? '?tab=' . urlencode($activeTab) : '';
?>
<div class="hotplace-region-bar">
    <div class="container">
        <div class="hotplace-region-tabs">
            <a href="/hotplace<?= $tabQuery ?>"
               class="region-tab <?= $activeIdx === 0 ? 'active' : '' ?>">
                전체
            </a>
            <?php foreach ($regionList as $region): ?>
            <a href="/hotplace/<?= (int)$region['idx'] ?><?= $tabQuery ?>"
               class="region-tab <?= $activeIdx === (int)$region['idx'] ? 'active' : '' ?>">
                <?= esc($region['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ===================== 콘텐츠 탭 (관광지/맛집/축제) ===================== -->
<?php
// 현재 지역 경로 부분
$idxPath = $activeIdx > 0 ? '/' . $activeIdx : '';
?>
<div class="hotplace-type-bar">
    <div class="container">
        <div class="hotplace-type-tabs">
            <?php
            $tabItems = [
                'spot'       => '🗺️ 관광지',
                'restaurant' => '🍽️ 맛집',
                'festival'   => '🎉 축제',
            ];
            foreach ($tabItems as $tabKey => $tabLabel):
                $tabHref = '/hotplace' . $idxPath . '?tab=' . $tabKey;
                if ($activeSearch !== '') {
                    $tabHref .= '&q=' . urlencode($activeSearch);
                }
            ?>
            <a href="<?= esc($tabHref) ?>"
               class="type-tab <?= $activeTab === $tabKey ? 'active' : '' ?>">
                <?= $tabLabel ?>
                <?php if ($activeTab === $tabKey): ?>
                <span class="type-tab-count"><?= $totalCount ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ===================== 필터 바 ===================== -->
<?php
$formAction = '/hotplace' . $idxPath;
?>
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= esc($formAction) ?>" id="filterForm">
            <input type="hidden" name="tab" value="<?= esc($activeTab) ?>">

            <div class="filter-search">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" name="q"
                       placeholder="이름, 해시태그 검색"
                       value="<?= esc($activeSearch) ?>"
                       autocomplete="off">
                <div class="suggest-dropdown" id="suggestDropdown"></div>
            </div>

            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">🗂️ 전체 카테고리</option>
                <?php foreach ($categories as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $activeCategory == $num ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="filter-submit-btn">검색</button>

            <?php if ($activeSearch || $activeCategory): ?>
            <a href="<?= esc($formAction) ?>?tab=<?= urlencode($activeTab) ?>" class="filter-reset-btn">초기화</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ===================== 결과 + 뷰 전환 ===================== -->
<section class="restaurant-section">
    <div class="container">
        <div class="view-controls">
            <p class="result-count">
                총 <strong><?= $totalCount ?></strong>개
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

        <?php if (empty($items)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">📍</div>
            <h3>검색 결과가 없습니다</h3>
            <p>다른 지역이나 필터를 사용해보세요</p>
        </div>

        <?php else: ?>

        <?php if ($activeTab === 'spot'): ?>
        <!-- ==================== 관광지 카드/리스트 뷰 ==================== -->
        <?php
        $catEmoji = [1=>'🏖️', 2=>'🌲', 3=>'🏛️', 4=>'🖼️', 5=>'🎡', 6=>'🌃', 7=>'🛍️', 8=>'📍'];
        $catColor = [1=>'#0984e3', 2=>'#00b894', 3=>'#6c5ce7', 4=>'#e17055', 5=>'#fd79a8', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];
        ?>
        <!-- 카드 뷰 -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($items as $s): ?>
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
        <!-- 리스트 뷰 -->
        <div class="restaurant-list-view" id="listView" style="display:none;">
            <?php foreach ($items as $s): ?>
            <?php
            $catNum  = (int)($s['category_num'] ?? 8);
            $starVal = (float)($s['star_point']  ?? 0);
            $color   = $catColor[$catNum] ?? '#b2bec3';
            $emoji   = $catEmoji[$catNum] ?? '📍';
            ?>
            <a class="r-list-item" href="/spots/<?= (int)$s['idx'] ?>" style="text-decoration:none;color:inherit;">
                <div class="r-list-cat" style="background: <?= $color ?>22;">
                    <span style="font-size:22px;"><?= $emoji ?></span>
                </div>
                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($s['name']) ?></span>
                        <span class="r-list-category-badge" style="background: <?= $color ?>;">
                            <?= esc($categories[$catNum] ?? '기타') ?>
                        </span>
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
                    </div>
                    <div class="r-list-info">
                        <?php if (!empty($s['district'])): ?><span>📍 <?= esc($s['district']) ?></span><?php endif; ?>
                        <?php if (!empty($s['open_time'])): ?><span>🕐 <?= esc($s['open_time']) ?></span><?php endif; ?>
                        <?php if (!empty($s['parking'])): ?><span>🅿️ 주차가능</span><?php endif; ?>
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

        <?php elseif ($activeTab === 'restaurant'): ?>
        <!-- ==================== 맛집 카드/리스트 뷰 ==================== -->
        <?php
        $rCatEmoji = [1=>'🍲', 2=>'🍣', 3=>'🥢', 4=>'🍝', 5=>'🥞', 6=>'☕', 7=>'🍽️', 8=>'🍴'];
        $rCatColor = [1=>'#e55039', 2=>'#6c5ce7', 3=>'#e17055', 4=>'#00b894', 5=>'#fdcb6e', 6=>'#a29bfe', 7=>'#fab1a0', 8=>'#b2bec3'];
        ?>
        <!-- 카드 뷰 -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($items as $r): ?>
            <?php
            $catNum   = (int)($r['category_num'] ?? 8);
            $starVal  = (float)($r['star_point']  ?? 0);
            $priceNum = (int)($r['price_range']   ?? 1);
            $color    = $rCatColor[$catNum] ?? '#b2bec3';
            $emoji    = $rCatEmoji[$catNum] ?? '🍴';
            ?>
            <a class="r-card" href="/restaurants/<?= (int)$r['idx'] ?>" style="text-decoration:none;color:inherit;">
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
                    <span class="price-badge"><?= esc($priceRanges[$priceNum] ?? '') ?></span>
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
        <!-- 리스트 뷰 -->
        <div class="restaurant-list-view" id="listView" style="display:none;">
            <?php foreach ($items as $r): ?>
            <?php
            $catNum   = (int)($r['category_num'] ?? 8);
            $starVal  = (float)($r['star_point']  ?? 0);
            $priceNum = (int)($r['price_range']   ?? 1);
            $color    = $rCatColor[$catNum] ?? '#b2bec3';
            $emoji    = $rCatEmoji[$catNum] ?? '🍴';
            ?>
            <a class="r-list-item" href="/restaurants/<?= (int)$r['idx'] ?>" style="text-decoration:none;color:inherit;">
                <div class="r-list-cat" style="background: <?= $color ?>22;">
                    <span style="font-size:22px;"><?= $emoji ?></span>
                </div>
                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($r['name']) ?></span>
                        <span class="r-list-category-badge" style="background: <?= $color ?>;">
                            <?= esc($categories[$catNum] ?? '기타') ?>
                        </span>
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
                    </div>
                    <div class="r-list-info">
                        <?php if (!empty($r['district'])): ?><span>📍 <?= esc($r['district']) ?></span><?php endif; ?>
                        <?php if (!empty($r['open_time'])): ?><span>🕐 <?= esc($r['open_time']) ?></span><?php endif; ?>
                        <?php if (!empty($r['parking'])): ?><span>🅿️ 주차가능</span><?php endif; ?>
                    </div>
                    <div class="r-list-footer">
                        <span class="price-badge"><?= esc($priceRanges[$priceNum] ?? '') ?></span>
                    </div>
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

        <?php else: ?>
        <!-- ==================== 축제 카드/리스트 뷰 ==================== -->
        <?php
        $eCatEmoji = [1=>'🎵', 2=>'🎨', 3=>'🌊', 4=>'🍜', 5=>'⚽', 6=>'🎆', 7=>'🏛️', 8=>'🎪'];
        $eCatColor = [1=>'#6c5ce7', 2=>'#e17055', 3=>'#0984e3', 4=>'#e55039', 5=>'#00b894', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];
        $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
        $statusColor = ['ongoing' => '#00b894', 'upcoming' => '#0984e3', 'ended' => '#b2bec3'];
        ?>
        <!-- 카드 뷰 -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($items as $f): ?>
            <?php
            $catNum  = (int)($f['category_num'] ?? 8);
            $starVal = (float)($f['star_point']  ?? 0);
            $color   = $eCatColor[$catNum] ?? '#b2bec3';
            $emoji   = $eCatEmoji[$catNum] ?? '🎪';
            $status  = $f['status'] ?? '';
            ?>
            <a class="r-card" href="/festivals/<?= (int)$f['idx'] ?>" style="text-decoration:none;color:inherit;">
                <div class="r-card-thumb">
                    <?php if (!empty($f['thumbnail'])): ?>
                        <img src="<?= esc($f['thumbnail']) ?>" alt="<?= esc($f['name']) ?>"
                             onerror="this.onerror=null; this.src='/img/no-image.svg';">
                    <?php else: ?>
                        <div class="r-card-thumb-default" style="background: <?= $color ?>22;">
                            <span><?= $emoji ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="r-card-category" style="background: <?= $color ?>;">
                        <?= esc($categories[$catNum] ?? '기타') ?>
                    </span>
                    <?php if (!empty($status) && isset($statusLabel[$status])): ?>
                    <span class="r-card-parking" style="background: <?= $statusColor[$status] ?>;">
                        <?= $statusLabel[$status] ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="r-card-body">
                    <h3 class="r-card-name"><?= esc($f['name']) ?></h3>
                    <div class="r-card-meta">
                        <?php if (!empty($f['district'])): ?>
                        <span class="r-card-district">📍 <?= esc($f['district']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($f['start_date'])): ?>
                        <span class="r-card-hours">📅 <?= esc($f['start_date']) ?><?= !empty($f['end_date']) ? ' ~ ' . esc($f['end_date']) : '' ?></span>
                        <?php endif; ?>
                    </div>
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
                    <?php if (!empty($f['is_free'])): ?>
                    <span class="price-badge free-badge">🎫 무료</span>
                    <?php endif; ?>
                    <?php if (!empty($f['tags'])): ?>
                    <div class="r-card-tags">
                        <?php foreach ($f['tags'] as $tag): ?>
                        <span class="r-tag">#<?= esc($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <!-- 리스트 뷰 -->
        <div class="restaurant-list-view" id="listView" style="display:none;">
            <?php foreach ($items as $f): ?>
            <?php
            $catNum  = (int)($f['category_num'] ?? 8);
            $starVal = (float)($f['star_point']  ?? 0);
            $color   = $eCatColor[$catNum] ?? '#b2bec3';
            $emoji   = $eCatEmoji[$catNum] ?? '🎪';
            $status  = $f['status'] ?? '';
            ?>
            <a class="r-list-item" href="/festivals/<?= (int)$f['idx'] ?>" style="text-decoration:none;color:inherit;">
                <div class="r-list-cat" style="background: <?= $color ?>22;">
                    <span style="font-size:22px;"><?= $emoji ?></span>
                </div>
                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($f['name']) ?></span>
                        <span class="r-list-category-badge" style="background: <?= $color ?>;">
                            <?= esc($categories[$catNum] ?? '기타') ?>
                        </span>
                        <?php if (!empty($status) && isset($statusLabel[$status])): ?>
                        <span class="r-list-category-badge" style="background: <?= $statusColor[$status] ?>;">
                            <?= $statusLabel[$status] ?>
                        </span>
                        <?php endif; ?>
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
                    </div>
                    <div class="r-list-info">
                        <?php if (!empty($f['district'])): ?><span>📍 <?= esc($f['district']) ?></span><?php endif; ?>
                        <?php if (!empty($f['start_date'])): ?><span>📅 <?= esc($f['start_date']) ?><?= !empty($f['end_date']) ? ' ~ ' . esc($f['end_date']) : '' ?></span><?php endif; ?>
                    </div>
                    <div class="r-list-footer">
                        <?php if (!empty($f['is_free'])): ?>
                        <span class="price-badge free-badge">🎫 무료</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($f['tags'])): ?>
                    <div class="r-list-tags">
                        <?php foreach ($f['tags'] as $tag): ?>
                        <span class="r-tag">#<?= esc($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($pager && $pager->getPageCount() > 1): ?>
        <div class="pager-wrap">
            <?= $pager->links('default', 'service_pager') ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<!-- ===================== 푸터 ===================== -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="footer-logo">부산온나</span>
                <p>부산 여행의 시작과 끝<br>설레는 부산 여행을 부산온나와 함께하세요</p>
            </div>
            <div class="footer-nav">
                <h4>바로가기</h4>
                <ul>
                    <li><a href="/spots">관광지</a></li>
                    <li><a href="/restaurants">맛집</a></li>
                    <li><a href="/festivals">축제·행사</a></li>
                    <li><a href="/travel-courses">여행코스</a></li>
                </ul>
            </div>
            <div class="footer-nav">
                <h4>이용안내</h4>
                <ul>
                    <li><a href="#">공지사항</a></li>
                    <li><a href="#">이용약관</a></li>
                    <li><a href="#">개인정보처리방침</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>고객센터</h4>
                <p>운영시간 평일 09:00 ~ 18:00</p>
                <p>이메일 contact@busanonna.kr</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2024 부산온나. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- ===================== 로그인 모달 ===================== -->
<div class="modal-overlay" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
    <div class="modal-box modal-box--sm">
        <div class="modal-header">
            <h2 class="modal-title" id="loginModalTitle">로그인</h2>
            <button type="button" class="modal-close" id="btnCloseLogin" aria-label="닫기">&times;</button>
        </div>
        <form class="signup-form" id="loginForm" novalidate>
            <?= csrf_field() ?>
            <div class="form-group" id="lfg-id">
                <label class="form-label" for="loginId">아이디 <span class="required">*</span></label>
                <input type="text" id="loginId" name="id" class="form-input" placeholder="아이디 입력"
                       autocomplete="username" maxlength="50" value="<?= esc($saved_id) ?>">
                <span class="form-error" id="lerr-id"></span>
            </div>
            <div class="form-group" id="lfg-password">
                <label class="form-label" for="loginPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="loginPw" name="password" class="form-input" placeholder="비밀번호 입력"
                       autocomplete="current-password" maxlength="100">
                <span class="form-error" id="lerr-password"></span>
            </div>
            <div class="login-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="save_id" id="chkSaveId" value="1" <?= $saved_id ? 'checked' : '' ?>>
                    <span class="checkbox-text">아이디 저장</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="keep_login" id="chkKeepLogin" value="1">
                    <span class="checkbox-text">상시 로그인</span>
                </label>
            </div>
            <div class="form-msg" id="loginFormMsg" style="display:none"></div>
            <button type="submit" class="btn-submit" id="btnSubmitLogin">로그인</button>
            <div class="login-footer">
                <span>아직 회원이 아니신가요?</span>
                <button type="button" class="link-btn" id="btnSwitchToSignup">회원가입</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== 회원가입 모달 ===================== -->
<div class="modal-overlay" id="signupModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">회원가입</h2>
            <button type="button" class="modal-close" id="btnCloseSignup" aria-label="닫기">&times;</button>
        </div>
        <form class="signup-form" id="signupForm" novalidate>
            <?= csrf_field() ?>
            <div class="form-group" id="fg-id">
                <label class="form-label" for="signupId">아이디 <span class="required">*</span></label>
                <input type="text" id="signupId" name="id" class="form-input" placeholder="영문·숫자 4자 이상"
                       autocomplete="username" maxlength="50">
                <span class="form-error" id="err-id"></span>
            </div>
            <div class="form-group" id="fg-password">
                <label class="form-label" for="signupPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="signupPw" name="password" class="form-input" placeholder="8자 이상 입력"
                       autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password"></span>
            </div>
            <div class="form-group" id="fg-password_confirm">
                <label class="form-label" for="signupPwConfirm">비밀번호 확인 <span class="required">*</span></label>
                <input type="password" id="signupPwConfirm" name="password_confirm" class="form-input"
                       placeholder="비밀번호를 다시 입력" autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password_confirm"></span>
            </div>
            <div class="form-group" id="fg-email">
                <label class="form-label" for="emailLocal">이메일 <span class="required">*</span></label>
                <div class="email-wrap">
                    <input type="text" id="emailLocal" class="form-input email-local" placeholder="이메일 아이디" autocomplete="email">
                    <span class="at-sign">@</span>
                    <select id="emailDomainSelect" class="form-select email-domain-select">
                        <option value="naver.com">naver.com</option>
                        <option value="gmail.com">gmail.com</option>
                        <option value="daum.net">daum.net</option>
                        <option value="kakao.com">kakao.com</option>
                        <option value="nate.com">nate.com</option>
                        <option value="direct">직접입력</option>
                    </select>
                    <input type="text" id="emailDomainDirect" class="form-input email-domain-direct"
                           placeholder="도메인 직접 입력" style="display:none">
                </div>
                <input type="hidden" name="email" id="emailFull">
                <span class="form-error" id="err-email"></span>
            </div>
            <div class="form-group" id="fg-phone">
                <label class="form-label" for="signupPhone">휴대폰 번호</label>
                <input type="tel" id="signupPhone" name="phone" class="form-input"
                       placeholder="010-0000-0000" maxlength="13">
                <span class="form-error" id="err-phone"></span>
            </div>
            <div class="form-msg" id="formMsg" style="display:none"></div>
            <button type="submit" class="btn-submit" id="btnSubmitSignup">가입하기</button>
        </form>
    </div>
</div>

<script src="/js/busan.js"></script>
<script src="/js/service-common.js"></script>
</body>
</html>
