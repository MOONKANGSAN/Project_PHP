<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    /* ---- SEO 변수 계산 ---- */
    $currPage = (int)(service('request')->getGet('page') ?? 1);

    $seoTitle = '부산온나 이벤트 | 부산 여행의 시작';
    $seoDesc  = '부산온나에서 진행 중인 다양한 이벤트를 확인하세요. 방문 인증, 투표, 공모전 등 다양한 이벤트에 참여해보세요.';

    if ($currPage > 1) { $seoTitle .= ' - ' . $currPage . '페이지'; }

    $canonParams = [];
    if (!empty($activeType))    $canonParams[] = 'type='   . rawurlencode($activeType);
    if (!empty($activeStatus))  $canonParams[] = 'status=' . rawurlencode($activeStatus);
    if (!empty($activeSearch))  $canonParams[] = 'q='      . rawurlencode($activeSearch);
    if ($currPage > 1)          $canonParams[] = 'page='   . $currPage;
    $canonicalUrl = 'https://busanonna.com/events' . (!empty($canonParams) ? '?' . implode('&', $canonParams) : '');

    $metaRobots = !empty($activeSearch) ? 'noindex, follow' : 'index, follow';
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= $seoDesc ?>">
    <meta name="robots"      content="<?= $metaRobots ?>">
    <link rel="canonical"    href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type"         content="website">
    <meta property="og:title"        content="<?= $seoTitle ?>">
    <meta property="og:description"  content="<?= $seoDesc ?>">
    <meta property="og:url"          content="<?= $canonicalUrl ?>">
    <meta property="og:image"        content="https://busanonna.com/img/og-event.jpg">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"    content="부산온나">
    <meta property="og:locale"       content="ko_KR">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $seoTitle ?>">
    <meta name="twitter:description" content="<?= $seoDesc ?>">
    <meta name="twitter:image"       content="https://busanonna.com/img/og-event.jpg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'events']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero page-hero--festival">
    <div class="container">
        <h1>🎊 부산온나 이벤트</h1>
        <p>다양한 이벤트에 참여하고 부산 여행을 더욱 풍성하게 즐겨보세요</p>
    </div>
</section>

<!-- ===================== 필터 바 ===================== -->
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="/events" id="filterForm">
            <div class="filter-search">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" name="q"
                       placeholder="이벤트 이름 검색"
                       value="<?= esc($activeSearch) ?>"
                       autocomplete="off">
            </div>

            <!-- 이벤트 유형 필터 -->
            <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">🗂️ 전체 유형</option>
                <?php foreach (\App\Models\SiteEventModel::TYPES as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $activeType == $num ? 'selected' : '' ?>>
                        <?= \App\Models\SiteEventModel::TYPE_EMOJI[$num] ?> <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- 진행 상태 필터 -->
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">📅 전체 상태</option>
                <option value="ongoing"  <?= $activeStatus === 'ongoing'  ? 'selected' : '' ?>>진행중</option>
                <option value="upcoming" <?= $activeStatus === 'upcoming' ? 'selected' : '' ?>>예정</option>
                <option value="ended"    <?= $activeStatus === 'ended'    ? 'selected' : '' ?>>종료</option>
            </select>
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="" <?= $activeSort === '' ? 'selected' : '' ?>>🆕 최신순</option>
                <option value="name" <?= $activeSort === 'name' ? 'selected' : '' ?>>🔤 가나다순</option>
            </select>

            <button type="submit" class="filter-submit-btn">검색</button>

            <?php if ($activeSearch || $activeType || $activeStatus): ?>
            <a href="/events" class="filter-reset-btn">초기화</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ===================== 결과 + 뷰 전환 ===================== -->
<section class="restaurant-section">
    <div class="container">
        <div class="view-controls">
            <p class="result-count">
                총 <strong><?= $totalCount ?></strong>개의 이벤트
                <?php if ($totalCount > 0): ?>
                <?php
                $perPage = 9;
                $from    = ($currPage - 1) * $perPage + 1;
                $to      = min($currPage * $perPage, $totalCount);
                ?>
                <span class="result-range">(<?= $from ?>–<?= $to ?>번째)</span>
                <?php endif; ?>
            </p>
            <div class="view-toggle">
                <button class="view-btn active" id="btnCardView" title="카드 보기">⊞</button>
                <button class="view-btn" id="btnListView" title="리스트 보기">☰</button>
            </div>
        </div>

        <?php if (empty($events)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🎊</div>
            <h3>진행 중인 이벤트가 없습니다</h3>
            <p>다른 검색어나 필터를 사용해보세요</p>
        </div>

        <?php else: ?>

        <?php
        $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
        $statusColor = ['ongoing' => '#00b894', 'upcoming' => '#0984e3', 'ended' => '#b2bec3'];
        ?>

        <!-- ---- 카드 뷰 ---- -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($events as $e): ?>
            <?php
            $typeNum   = (int)($e['event_type'] ?? 4);
            $typeLabel = \App\Models\SiteEventModel::TYPES[$typeNum]  ?? '기타';
            $typeEmoji = \App\Models\SiteEventModel::TYPE_EMOJI[$typeNum] ?? '🎉';
            $typeColor = \App\Models\SiteEventModel::TYPE_COLOR[$typeNum] ?? '#fdcb6e';
            $evStatus  = $e['event_status'] ?? '';
            ?>
            <a class="r-card" href="/events/<?= (int)$e['idx'] ?>" style="text-decoration:none;color:inherit;">
                <div class="r-card-thumb">
                    <?php if (!empty($e['thumb_url'])): ?>
                        <img src="<?= esc($e['thumb_url']) ?>" alt="<?= esc($e['title']) ?>"
                             onerror="this.onerror=null; this.src='/img/no-image.svg';">
                    <?php else: ?>
                        <div class="r-card-thumb-default" style="background: <?= $typeColor ?>22;">
                            <span><?= $typeEmoji ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="r-card-category" style="background: <?= $typeColor ?>;">
                        <?= esc($typeLabel) ?>
                    </span>
                    <?php if ($evStatus): ?>
                    <span class="r-card-parking" style="background: <?= $statusColor[$evStatus] ?? '#888' ?>;">
                        <?= $statusLabel[$evStatus] ?? '' ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="r-card-body">
                    <h3 class="r-card-name"><?= esc($e['title']) ?></h3>

                    <div class="r-card-meta">
                        <?php if (!empty($e['start_date'])): ?>
                        <span class="r-card-hours">
                            📅 <?= esc($e['start_date']) ?>
                            <?= !empty($e['end_date']) ? ' ~ ' . esc($e['end_date']) : '' ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if (($e['like_cnt'] ?? 0) > 0): ?>
                    <div class="r-card-likes">♥ <?= (int)$e['like_cnt'] ?></div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- ---- 리스트 뷰 ---- -->
        <div class="restaurant-list-view" id="listView" style="display:none;">
            <?php foreach ($events as $e): ?>
            <?php
            $typeNum   = (int)($e['event_type'] ?? 4);
            $typeLabel = \App\Models\SiteEventModel::TYPES[$typeNum]  ?? '기타';
            $typeEmoji = \App\Models\SiteEventModel::TYPE_EMOJI[$typeNum] ?? '🎉';
            $typeColor = \App\Models\SiteEventModel::TYPE_COLOR[$typeNum] ?? '#fdcb6e';
            $evStatus  = $e['event_status'] ?? '';
            ?>
            <a class="r-list-item" href="/events/<?= (int)$e['idx'] ?>" style="text-decoration:none; color:inherit;">
                <div class="r-list-cat" style="background: <?= $typeColor ?>22;">
                    <span style="font-size:22px;"><?= $typeEmoji ?></span>
                </div>

                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($e['title']) ?></span>
                        <span class="r-list-category-badge" style="background: <?= $typeColor ?>;">
                            <?= esc($typeLabel) ?>
                        </span>
                        <?php if ($evStatus): ?>
                        <span class="festival-status-badge" style="background: <?= $statusColor[$evStatus] ?? '#888' ?>22; color: <?= $statusColor[$evStatus] ?? '#888' ?>; border: 1px solid <?= $statusColor[$evStatus] ?? '#888' ?>40;">
                            <?= $statusLabel[$evStatus] ?>
                        </span>
                        <?php endif; ?>
                        <?php if (($e['like_cnt'] ?? 0) > 0): ?>
                        <span class="r-list-likes">♥ <?= (int)$e['like_cnt'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="r-list-info">
                        <?php if (!empty($e['start_date'])): ?>
                        <span>📅 <?= esc($e['start_date']) ?><?= !empty($e['end_date']) ? ' ~ ' . esc($e['end_date']) : '' ?></span>
                        <?php endif; ?>
                    </div>
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
<script src="/js/service-common.js"></script>
</body>
</html>
