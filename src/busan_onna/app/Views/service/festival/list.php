<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>축제·행사 - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
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
                    <li><a href="/festivals" class="active">축제</a></li>
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
<section class="page-hero page-hero--festival">
    <div class="container">
        <h1>🎉 부산 축제·행사</h1>
        <p>부산의 다채로운 문화 축제와 행사 정보를 한눈에 확인하세요</p>
    </div>
</section>

<!-- ===================== 필터 바 ===================== -->
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="/festivals" id="filterForm">
            <div class="filter-search">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" name="q"
                       placeholder="축제 이름, 해시태그, 지역 검색"
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

            <!-- 무료/유료 필터 -->
            <select name="is_free" class="filter-select" onchange="this.form.submit()">
                <option value="">💰 전체</option>
                <option value="1" <?= $activeIsFree === '1' ? 'selected' : '' ?>>무료</option>
                <option value="0" <?= $activeIsFree === '0' ? 'selected' : '' ?>>유료</option>
            </select>

            <button type="submit" class="filter-submit-btn">검색</button>

            <?php if ($activeSearch || $activeDistrict || $activeCategory || $activeIsFree !== ''): ?>
            <a href="/festivals" class="filter-reset-btn">초기화</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ===================== 결과 + 뷰 전환 ===================== -->
<section class="restaurant-section">
    <div class="container">
        <div class="view-controls">
            <p class="result-count">
                총 <strong><?= $totalCount ?></strong>개의 축제·행사
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

        <?php if (empty($festivals)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🎉</div>
            <h3>검색 결과가 없습니다</h3>
            <p>다른 검색어나 필터를 사용해보세요</p>
        </div>

        <?php else: ?>

        <?php
        $catEmoji = [1=>'🎵', 2=>'🎨', 3=>'🌊', 4=>'🍜', 5=>'⚽', 6=>'🎉', 7=>'🏢', 8=>'🎪'];
        $catColor = [1=>'#6c5ce7', 2=>'#fd79a8', 3=>'#0984e3', 4=>'#e17055', 5=>'#00b894', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];

        $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
        $statusColor = ['ongoing' => '#00b894', 'upcoming' => '#0984e3', 'ended'  => '#b2bec3'];
        ?>

        <!-- ---- 카드 뷰 ---- -->
        <div class="restaurant-grid" id="cardView">
            <?php foreach ($festivals as $f): ?>
            <?php
            $catNum  = (int)($f['category_num'] ?? 8);
            $starVal = (float)($f['star_point']  ?? 0);
            $color   = $catColor[$catNum] ?? '#b2bec3';
            $emoji   = $catEmoji[$catNum] ?? '🎪';
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
                    <?php if ($status): ?>
                    <span class="r-card-parking" style="background: <?= $statusColor[$status] ?? '#888' ?>;">
                        <?= $statusLabel[$status] ?? '' ?>
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
                        <span class="r-card-hours">
                            📅 <?= esc($f['start_date']) ?>
                            <?= !empty($f['end_date']) ? ' ~ ' . esc($f['end_date']) : '' ?>
                        </span>
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

                    <!-- 무료/유료 배지 -->
                    <?php if (!empty($f['is_free'])): ?>
                    <span class="price-badge free-badge">🎫 무료</span>
                    <?php else: ?>
                    <span class="price-badge">🎫 유료</span>
                    <?php endif; ?>

                    <!-- 주최 -->
                    <?php if (!empty($f['host'])): ?>
                    <p class="festival-host">🏢 <?= esc($f['host']) ?></p>
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

        <!-- ---- 리스트 뷰 ---- -->
        <div class="restaurant-list-view" id="listView" style="display:none;">
            <?php foreach ($festivals as $f): ?>
            <?php
            $catNum  = (int)($f['category_num'] ?? 8);
            $starVal = (float)($f['star_point']  ?? 0);
            $color   = $catColor[$catNum] ?? '#b2bec3';
            $emoji   = $catEmoji[$catNum] ?? '🎪';
            $status  = $f['status'] ?? '';
            ?>
            <a class="r-list-item" href="/festivals/<?= (int)$f['idx'] ?>" style="text-decoration:none; color:inherit;">
                <div class="r-list-cat" style="background: <?= $color ?>22;">
                    <span style="font-size:22px;"><?= $emoji ?></span>
                </div>

                <div class="r-list-body">
                    <div class="r-list-header">
                        <span class="r-list-name"><?= esc($f['name']) ?></span>
                        <span class="r-list-category-badge" style="background: <?= $color ?>;">
                            <?= esc($categories[$catNum] ?? '기타') ?>
                        </span>
                        <?php if ($status): ?>
                        <span class="festival-status-badge" style="background: <?= $statusColor[$status] ?? '#888' ?>22; color: <?= $statusColor[$status] ?? '#888' ?>; border: 1px solid <?= $statusColor[$status] ?? '#888' ?>40;">
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
                        <?php if (!empty($f['district'])): ?>
                        <span>📍 <?= esc($f['district']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($f['start_date'])): ?>
                        <span>📅 <?= esc($f['start_date']) ?><?= !empty($f['end_date']) ? ' ~ ' . esc($f['end_date']) : '' ?></span>
                        <?php endif; ?>
                        <?php if (!empty($f['host'])): ?>
                        <span>🏢 <?= esc($f['host']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="r-list-footer">
                        <?php if (!empty($f['is_free'])): ?>
                        <span class="price-badge free-badge">🎫 무료</span>
                        <?php else: ?>
                        <span class="price-badge">🎫 유료</span>
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

        <?php if ($pager->getPageCount() > 1): ?>
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

<?= view('modules/auth/login_modal') ?>

<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script>
/* 자동완성 suggest URL */
const SUGGEST_URL = '/festivals/suggest';
</script>
<script src="/js/service-common.js"></script>
</body>
</html>
