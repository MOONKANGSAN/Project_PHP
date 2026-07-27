<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>부산온나 - 부산 여행의 시작</title>
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

<!-- ===================== 메인 배너 슬라이더 ===================== -->
<section class="main-banner">
    <div class="banner-slider">
        <?php if (empty($banners)): ?>
        <!-- 등록된 활성 배너가 없을 때 기본 슬라이드 -->
        <div class="banner-slide banner-default active">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <span class="banner-location">📍 부산광역시</span>
                <h1 class="banner-title">부산에 오신 걸 환영합니다</h1>
                <p class="banner-subtitle">설레는 부산 여행을 부산온나와 함께하세요</p>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($banners as $i => $banner): ?>
        <div class="banner-slide <?= $i === 0 ? 'active' : '' ?>">
            <!-- 실제 등록 이미지 -->
            <img class="banner-bg-img"
                 src="<?= esc($banner['image_url']) ?>"
                 alt="<?= esc($banner['alt_text'] ?? '') ?>"
                 onerror="this.onerror=null; this.src='/img/no-image.svg';">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <?php if (!empty($banner['location'])): ?>
                <span class="banner-location">📍 <?= esc($banner['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($banner['title'])): ?>
                <h1 class="banner-title"><?= esc($banner['title']) ?></h1>
                <?php endif; ?>
                <?php if (!empty($banner['subtitle'])): ?>
                <p class="banner-subtitle"><?= esc($banner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($banner['link_url'])): ?>
                <a href="<?= esc($banner['link_url']) ?>" class="btn-banner">지금 탐험하기 →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($banners) && count($banners) > 1): ?>
    <div class="banner-controls">
        <button class="banner-arrow prev" id="bannerPrev">&#8249;</button>
        <div class="banner-dots">
            <?php foreach ($banners as $i => $banner): ?>
            <span class="dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
            <?php endforeach; ?>
        </div>
        <button class="banner-arrow next" id="bannerNext">&#8250;</button>
    </div>
    <?php endif; ?>

    <!-- 하단 웨이브 -->
    <div class="banner-wave">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none">
            <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#ffffff"/>
        </svg>
    </div>
</section>

<!-- ===================== 부산 지도 섹션 ===================== -->
<section class="map-section">
    <div class="container">
        <div class="section-header fade-in">
            <h2>지역별 탐색</h2>
            <p>관심있는 지역을 선택하면 해당 지역의 주요 명소를 확인할 수 있습니다</p>
        </div>
        <div class="map-container fade-in">
            <!-- 실제 부산 행정구역 SVG 지도 -->
            <div class="busan-map-wrap">
                <?php echo file_get_contents(FCPATH . 'busan_map.svg') ?>
                <div class="map-legend">
                    <span class="legend-sea">■ 바다</span>
                    <span class="legend-land">■ 육지</span>
                </div>
            </div>

            <!-- 지역 정보 패널 -->
            <div class="map-info-panel" id="mapInfoPanel">
                <div class="map-info-default">
                    <div class="map-default-icon">🗺️</div>
                    <p>지역 마커에 마우스를<br>올려보세요</p>
                    <span>해당 지역의 주요 명소를<br>확인할 수 있습니다</span>
                </div>
                <div class="map-info-content" id="mapInfoContent" style="display:none"></div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 추천 관광지 ===================== -->
<section class="spots-section">
    <div class="container">
        <div class="section-header-row fade-in">
            <div class="section-header">
                <h2>추천 관광지</h2>
                <p>부산에서 꼭 가봐야 할 대표 명소들</p>
            </div>
            <a href="/spots" class="btn-more">더보기 →</a>
        </div>

        <?php if (empty($spots)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🗺️</div>
            <p>등록된 관광지가 없습니다</p>
        </div>
        <?php else: ?>
        <?php
        $catEmoji = [1=>'🏖️', 2=>'🌲', 3=>'🏛️', 4=>'🖼️', 5=>'🎡', 6=>'🌃', 7=>'🛍️', 8=>'📍'];
        $catColor = [1=>'#0984e3', 2=>'#00b894', 3=>'#6c5ce7', 4=>'#e17055', 5=>'#fd79a8', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];
        ?>
        <div class="restaurant-grid">
            <?php foreach ($spots as $i => $s): ?>
            <?php
            $catNum  = (int)($s['category_num'] ?? 8);
            $starVal = (float)($s['star_point']  ?? 0);
            $color   = $catColor[$catNum] ?? '#b2bec3';
            $emoji   = $catEmoji[$catNum] ?? '📍';
            ?>
            <a class="r-card fade-in" href="/spots/<?= (int)$s['idx'] ?>" style="--delay: <?= $i * 80 ?>ms; text-decoration:none; color:inherit;">
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
                        <?= esc($placeCategories[$catNum] ?? '기타') ?>
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
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== 인기 맛집 ===================== -->
<section class="food-section">
    <div class="container">
        <div class="section-header-row fade-in">
            <div class="section-header">
                <h2>부산 대표 먹거리</h2>
                <p>부산에서 꼭 먹어봐야 할 향토 음식들</p>
            </div>
            <a href="/restaurants" class="btn-more">더보기 →</a>
        </div>

        <?php if (empty($restaurants)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🍽️</div>
            <p>등록된 맛집이 없습니다</p>
        </div>
        <?php else: ?>
        <?php
        $rCatEmoji = [1=>'🍲', 2=>'🍣', 3=>'🥢', 4=>'🍝', 5=>'🥞', 6=>'☕', 7=>'🍽️', 8=>'🍴'];
        $rCatColor = [1=>'#e55039', 2=>'#6c5ce7', 3=>'#e17055', 4=>'#00b894', 5=>'#fdcb6e', 6=>'#a29bfe', 7=>'#fab1a0', 8=>'#b2bec3'];
        ?>
        <div class="restaurant-grid">
            <?php foreach ($restaurants as $i => $r): ?>
            <?php
            $catNum   = (int)($r['category_num'] ?? 8);
            $starVal  = (float)($r['star_point']  ?? 0);
            $priceNum = (int)($r['price_range']   ?? 1);
            $color    = $rCatColor[$catNum] ?? '#b2bec3';
            $emoji    = $rCatEmoji[$catNum] ?? '🍴';
            ?>
            <a class="r-card fade-in" href="/restaurants/<?= (int)$r['idx'] ?>" style="--delay: <?= $i * 80 ?>ms; text-decoration:none; color:inherit;">
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
                        <?= esc($restaurantCategories[$catNum] ?? '기타') ?>
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
                    <span class="price-badge"><?= esc($restaurantPrices[$priceNum] ?? '') ?></span>
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
        <?php endif; ?>
    </div>
</section>

<!-- ===================== 추천 여행 코스 ===================== -->
<section class="courses-section">
    <div class="container">
        <div class="section-header-row fade-in">
            <div class="section-header">
                <h2>추천 여행 코스</h2>
                <p>전문가가 큐레이션한 부산 맞춤 코스</p>
            </div>
            <a href="/travel-courses" class="btn-more">더보기 →</a>
        </div>

        <?php if (empty($courses)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🗓️</div>
            <p>등록된 여행코스가 없습니다</p>
        </div>
        <?php else: ?>
        <div class="courses-grid">
            <?php foreach ($courses as $i => $c): ?>
            <?php
            // 항목 이름 최대 4개만 route에 표시
            $routeItems = array_slice($c['items'] ?? [], 0, 4);
            $itemCount  = count($c['items'] ?? []);
            ?>
            <div class="course-card fade-in" style="--course-color: <?= $c['color'] ?>; --delay: <?= $i * 120 ?>ms">
                <div class="course-top">
                    <?php if (!empty($c['sido'])): ?>
                    <span class="course-theme">📍 <?= esc($c['sido']) ?></span>
                    <?php endif; ?>
                    <span class="course-duration"><?= $itemCount ?>개 장소</span>
                </div>
                <h3 class="course-title"><?= esc($c['title']) ?></h3>
                <?php if (!empty($c['description'])): ?>
                <p class="course-desc"><?= esc(mb_substr($c['description'], 0, 60)) ?><?= mb_strlen($c['description']) > 60 ? '…' : '' ?></p>
                <?php endif; ?>
                <?php if (!empty($routeItems)): ?>
                <div class="course-route">
                    <?php foreach ($routeItems as $j => $item): ?>
                    <?php if ($j > 0): ?><span class="route-arrow">→</span><?php endif; ?>
                    <span class="route-stop"><?= esc($item['name']) ?></span>
                    <?php endforeach; ?>
                    <?php if ($itemCount > 4): ?>
                    <span class="route-arrow">→</span>
                    <span class="route-stop" style="color: <?= $c['color'] ?>; font-weight:600;">+<?= $itemCount - 4 ?>곳</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <a href="/travel-courses/<?= (int)$c['idx'] ?>" class="btn-course"
                   style="background: <?= $c['color'] ?>">코스 보기</a>
            </div>
            <?php endforeach; ?>
        </div>
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
                    <li><a href="/customer?tab=notice">공지사항</a></li>
                    <li><a href="/customer?tab=faq">FAQs</a></li>
                    <li><a href="/customer?tab=inquiry">고객문의</a></li>
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

<!-- 지역별 탐색 DB 데이터 주입 -->
<script>
// busan_maps 지역 목록: { idx → { idx, name, sort_order } }
window.regionList = <?= json_encode(
    array_column($regionList ?? [], null, 'idx'),
    JSON_UNESCAPED_UNICODE
) ?>;

// 지역별 TOP5: { main_idx → [ { title, link_url, content_type, content_idx, sort_order }, ... ] }
window.regionTop5 = <?= json_encode(
    $regionTop5 ?? [],
    JSON_UNESCAPED_UNICODE
) ?>;
</script>
<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
</body>
</html>
