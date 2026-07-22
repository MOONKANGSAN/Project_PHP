<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($festival['name']) ?> - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
    <style>
        /* ===== 축제·행사 뷰 페이지 전용 레이아웃 ===== */

        body { background: #f0f2f5; }

        /* 헤더를 패널과 동일한 너비/위치로 오버라이드 */
        .site-header {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            width: 50vw;
            min-width: 360px;
            max-width: 760px;
            border-radius: 0 0 10px 10px;
        }

        .rv-page {
            padding-top: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 60px;
        }

        /* 중앙 패널 */
        .rv-panel {
            width: 50vw;
            min-width: 360px;
            max-width: 760px;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            border-radius: 0 0 16px 16px;
            overflow: hidden;
        }

        /* 이미지 슬라이더 */
        .rv-slider {
            position: relative;
            width: 100%;
            height: 360px;
            overflow: hidden;
            background: #1a1a2e;
        }
        .rv-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.8s ease; }
        .rv-slide.active { opacity: 1; }
        .rv-slide img { width: 100%; height: 100%; object-fit: cover; }
        .rv-slide-default {
            background: linear-gradient(135deg, #6c5ce7, #fd79a8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 90px;
        }
        .rv-slider-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.04), rgba(0,0,0,0.40));
            z-index: 1; pointer-events: none;
        }
        .rv-img-counter {
            position: absolute; top: 12px; right: 14px; z-index: 3;
            background: rgba(0,0,0,0.42); color: #fff;
            font-size: 12px; padding: 3px 10px; border-radius: 20px;
        }
        .rv-slider-controls {
            position: absolute; bottom: 16px; left: 50%;
            transform: translateX(-50%); z-index: 3;
            display: flex; align-items: center; gap: 10px;
        }
        .rv-arrow {
            background: rgba(255,255,255,0.28); backdrop-filter: blur(4px);
            border: none; color: #fff; width: 32px; height: 32px;
            border-radius: 50%; font-size: 20px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .rv-arrow:hover { background: rgba(255,255,255,0.55); }
        .rv-dots { display: flex; gap: 7px; }
        .rv-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(255,255,255,0.45); cursor: pointer;
            transition: background 0.25s, transform 0.25s;
        }
        .rv-dot.active { background: #fff; transform: scale(1.35); }

        /* 콘텐츠 */
        .rv-content { padding: 0 20px; }

        /* 기본 정보 헤더 */
        .rv-header { padding: 20px 0 18px; border-bottom: 1px solid #eee; }
        .rv-name { font-size: 21px; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; }
        .rv-badge-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }

        .rv-cat-badge { font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600; color: #fff; }

        /* 진행 상태 뱃지 */
        .rv-status-badge {
            font-size: 12px; padding: 3px 10px;
            border-radius: 20px; font-weight: 600; color: #fff;
        }
        .rv-status-ongoing  { background: #00b894; }
        .rv-status-upcoming { background: #0984e3; }
        .rv-status-ended    { background: #b2bec3; }

        /* 무료/유료 뱃지 */
        .rv-free-badge {
            font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 500;
            background: #e3f2fd; color: #1565c0;
        }
        .rv-paid-badge {
            font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 500;
            background: #fff3e0; color: #e67e22;
        }

        .rv-rating-row { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; }
        .rv-stars { color: #f39c12; font-size: 15px; letter-spacing: 1px; }
        .rv-rating-score { font-weight: 700; font-size: 15px; color: #1a1a2e; }
        .rv-meta-list { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
        .rv-meta-item { display: flex; align-items: flex-start; gap: 7px; font-size: 13px; color: #444; }
        .rv-meta-item .rv-icon { flex-shrink: 0; }
        .rv-tag-row { display: flex; gap: 6px; flex-wrap: wrap; }
        .rv-tag { background: #f0f4ff; color: #4a90e2; font-size: 12px; padding: 3px 10px; border-radius: 20px; }

        /* 탭 */
        .rv-tabs {
            position: sticky; top: 68px; z-index: 20;
            background: #fff; display: flex;
            border-bottom: 2px solid #eee;
            margin: 0 -20px; padding: 0 20px;
        }
        .rv-tab {
            padding: 13px 22px; font-size: 14px; font-weight: 500;
            color: #aaa; cursor: pointer;
            border-bottom: 2px solid transparent; margin-bottom: -2px;
            transition: color 0.2s, border-color 0.2s; user-select: none;
        }
        .rv-tab.active { color: #1a1a2e; border-bottom-color: #1a1a2e; font-weight: 700; }
        .rv-tab-pane { display: none; }
        .rv-tab-pane.active { display: block; }

        /* 공통 섹션 */
        .rv-section { padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
        .rv-section:last-child { border-bottom: none; padding-bottom: 10px; }
        .rv-section-title { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; }

        /* 정보 행 */
        .rv-info-row { display: flex; padding: 5px 0; font-size: 13px; gap: 12px; }
        .rv-info-key { color: #6c5ce7; font-weight: 600; min-width: 80px; }
        .rv-info-val { color: #333; }

        /* 소개 텍스트 */
        .rv-intro-text { font-size: 13px; color: #444; line-height: 1.85; white-space: pre-wrap; }

        /* 외부 링크 버튼 */
        .rv-detail-link {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 12px; padding: 8px 16px;
            background: #6c5ce7; color: #fff;
            font-size: 13px; font-weight: 600;
            border-radius: 8px; text-decoration: none;
            transition: background 0.2s;
        }
        .rv-detail-link:hover { background: #5a4bd1; }

        /* 지도 */
        .rv-map-box {
            width: 100%; height: 200px;
            border-radius: 10px; overflow: hidden;
            border: 1px solid #ddd; margin-bottom: 10px;
            background: #e8ece8;
            display: flex; align-items: center; justify-content: center;
        }
        #naverMap { width: 100%; height: 100%; }
        .rv-map-placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: #888; font-size: 13px; }
        .rv-map-placeholder .map-icon { font-size: 38px; }
        .rv-address-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .rv-address-text { font-size: 13px; color: #333; flex: 1; }
        .rv-copy-btn { font-size: 12px; color: #666; border: 1px solid #ddd; padding: 4px 10px; border-radius: 4px; cursor: pointer; background: #fff; flex-shrink: 0; }
        .rv-copy-btn:hover { background: #f5f5f5; }
        .rv-map-link { display: inline-block; margin-top: 8px; font-size: 12px; color: #4a90e2; }
        .rv-map-link:hover { text-decoration: underline; }

        /* 주차 */
        .rv-park-ok { color: #27ae60; font-weight: 500; }
        .rv-park-no { color: #aaa; }

        /* 편의시설 */
        .rv-facilities { display: flex; gap: 24px; flex-wrap: wrap; }
        .rv-facility { display: flex; flex-direction: column; align-items: center; gap: 5px; font-size: 12px; color: #666; min-width: 52px; }
        .rv-facility-icon { font-size: 26px; }

        /* 빈 탭 */
        .rv-empty-tab { padding: 50px 0; text-align: center; color: #bbb; font-size: 14px; }

        /* 반응형 */
        @media (max-width: 800px) {
            .site-header { width: 100%; min-width: 100%; border-radius: 0; }
            .rv-panel    { width: 100%; min-width: 100%; border-radius: 0; }
            .rv-slider   { height: 240px; }
        }
    </style>
</head>
<body>

<!-- 상단 내비바 — 패널과 동일한 너비/위치 -->
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

<!-- 페이지 본문 -->
<div class="rv-page">
    <div class="rv-panel">

        <!-- 이미지 슬라이더 -->
        <?php
        $catColor    = [1=>'#6c5ce7', 2=>'#fd79a8', 3=>'#0984e3', 4=>'#e17055', 5=>'#00b894', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];
        $catEmoji    = [1=>'🎵', 2=>'🎨', 3=>'🌊', 4=>'🍜', 5=>'⚽', 6=>'🎉', 7=>'🏢', 8=>'🎪'];
        $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
        $catNum      = (int)($festival['category_num'] ?? 8);
        $starVal     = (float)($festival['star_point'] ?? 0);
        $color       = $catColor[$catNum] ?? '#b2bec3';
        $emoji       = $catEmoji[$catNum] ?? '🎪';
        $status      = $festival['status'] ?? '';
        $imgCount    = count($thumbnails);
        ?>
        <div class="rv-slider" id="rvSlider">
            <?php if (empty($thumbnails)): ?>
            <div class="rv-slide rv-slide-default active"><span><?= $emoji ?></span></div>
            <?php else: ?>
            <?php foreach ($thumbnails as $i => $thumb): ?>
            <div class="rv-slide <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= esc($thumb['img_url']) ?>" alt="<?= esc($festival['name']) ?> 이미지 <?= $i + 1 ?>"
                     onerror="this.onerror=null; this.src='/img/no-image.svg';">
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="rv-slider-overlay"></div>

            <?php if ($imgCount > 1): ?>
            <div class="rv-img-counter" id="rvCounter">1 / <?= $imgCount ?></div>
            <div class="rv-slider-controls">
                <button class="rv-arrow" id="rvPrev">&#8249;</button>
                <div class="rv-dots" id="rvDots">
                    <?php for ($i = 0; $i < $imgCount; $i++): ?>
                    <span class="rv-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
                    <?php endfor; ?>
                </div>
                <button class="rv-arrow" id="rvNext">&#8250;</button>
            </div>
            <?php endif; ?>
        </div>

        <!-- 콘텐츠 -->
        <div class="rv-content">

            <!-- 기본 정보 -->
            <div class="rv-header">
                <h1 class="rv-name"><?= esc($festival['name']) ?></h1>

                <div class="rv-badge-row">
                    <span class="rv-cat-badge" style="background:<?= $color ?>;">
                        <?= esc($categories[$catNum] ?? '기타') ?>
                    </span>
                    <?php if ($status): ?>
                    <span class="rv-status-badge rv-status-<?= $status ?>">
                        <?= $statusLabel[$status] ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($festival['is_free'])): ?>
                    <span class="rv-free-badge">🎫 무료</span>
                    <?php else: ?>
                    <span class="rv-paid-badge">🎫 유료</span>
                    <?php endif; ?>
                </div>

                <?php if ($starVal > 0):
                    $full  = (int)floor($starVal);
                    $half  = ($starVal - $full) >= 0.5 ? 1 : 0;
                    $empty = 5 - $full - $half;
                ?>
                <div class="rv-rating-row">
                    <span class="rv-stars">
                        <?= str_repeat('★', $full) ?><?= $half ? '⭒' : '' ?><?= str_repeat('☆', $empty) ?>
                    </span>
                    <span class="rv-rating-score"><?= number_format($starVal, 1) ?></span>
                </div>
                <?php endif; ?>

                <div class="rv-meta-list">
                    <?php if (!empty($festival['start_date'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">📅</span>
                        <span>
                            <?= esc($festival['start_date']) ?>
                            <?= !empty($festival['end_date']) ? ' ~ ' . esc($festival['end_date']) : '' ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($festival['address1'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">📍</span>
                        <span><?= esc($festival['address1']) ?><?= !empty($festival['address2']) ? ' ' . esc($festival['address2']) : '' ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($festival['host'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">🏢</span>
                        <span><?= esc($festival['host']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tags)): ?>
                <div class="rv-tag-row">
                    <?php foreach ($tags as $tag): ?>
                    <span class="rv-tag">#<?= esc($tag['name']) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- 탭 -->
            <div class="rv-tabs">
                <div class="rv-tab active" data-tab="home">홈</div>
                <div class="rv-tab" data-tab="detail">상세</div>
                <div class="rv-tab" data-tab="review">리뷰</div>
            </div>

            <!-- 홈 탭 -->
            <div class="rv-tab-pane active" id="tab-home">

                <?php if (!empty($festival['info'])): ?>
                <div class="rv-section">
                    <h3 class="rv-section-title">행사 소개</h3>
                    <p class="rv-intro-text"><?= esc($festival['info']) ?></p>
                </div>
                <?php endif; ?>

                <div class="rv-section">
                    <h3 class="rv-section-title">행사 정보</h3>
                    <?php if (!empty($festival['start_date'])): ?>
                    <div class="rv-info-row">
                        <span class="rv-info-key">행사 기간</span>
                        <span class="rv-info-val">
                            <?= esc($festival['start_date']) ?>
                            <?= !empty($festival['end_date']) ? ' ~ ' . esc($festival['end_date']) : '' ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($festival['host'])): ?>
                    <div class="rv-info-row">
                        <span class="rv-info-key">주최</span>
                        <span class="rv-info-val"><?= esc($festival['host']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="rv-info-row">
                        <span class="rv-info-key">입장료</span>
                        <span class="rv-info-val"><?= !empty($festival['is_free']) ? '무료' : '유료' ?></span>
                    </div>
                    <div class="rv-info-row">
                        <span class="rv-info-key">카테고리</span>
                        <span class="rv-info-val"><?= esc($categories[$catNum] ?? '기타') ?></span>
                    </div>
                    <?php if (!empty($festival['detail_url'])): ?>
                    <a class="rv-detail-link" href="<?= esc($festival['detail_url']) ?>" target="_blank" rel="noopener">
                        공식 홈페이지 →
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($festival['address1'])): ?>
                <div class="rv-section">
                    <h3 class="rv-section-title">위치정보</h3>
                    <?php if (!empty($festival['latitude']) && !empty($festival['longitude'])): ?>
                    <div class="rv-map-box"><div id="naverMap"></div></div>
                    <?php else: ?>
                    <div class="rv-map-box">
                        <div class="rv-map-placeholder">
                            <span class="map-icon">🗺️</span>
                            <p>위치 정보가 없습니다</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="rv-address-row">
                        <span class="rv-address-text"><?= esc($festival['address1']) ?><?= !empty($festival['address2']) ? ' ' . esc($festival['address2']) : '' ?></span>
                        <button class="rv-copy-btn" id="btnCopyAddr">주소복사</button>
                    </div>
                    <a class="rv-map-link" href="https://map.naver.com/search/<?= urlencode($festival['address1']) ?>" target="_blank" rel="noopener">네이버 지도에서 보기 →</a>
                </div>
                <?php endif; ?>

                <div class="rv-section">
                    <h3 class="rv-section-title">주차정보</h3>
                    <div class="rv-info-row">
                        <span class="rv-info-key">주차 여부</span>
                        <?php if (!empty($festival['parking'])): ?>
                        <span class="rv-info-val rv-park-ok">🅿️ 주차 가능</span>
                        <?php else: ?>
                        <span class="rv-info-val rv-park-no">주차 불가</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rv-section">
                    <h3 class="rv-section-title">편의시설</h3>
                    <div class="rv-facilities">
                        <div class="rv-facility"><span class="rv-facility-icon">🪑</span><span>단체석 구비</span></div>
                        <div class="rv-facility"><span class="rv-facility-icon">📶</span><span>무선 인터넷</span></div>
                        <div class="rv-facility"><span class="rv-facility-icon">🚻</span><span>남녀 화장실 구분</span></div>
                    </div>
                </div>

            </div>

            <!-- 상세 탭 -->
            <div class="rv-tab-pane" id="tab-detail">
                <div class="rv-section"><div class="rv-empty-tab">추가 상세 정보가 없습니다</div></div>
            </div>

            <!-- 리뷰 탭 -->
            <div class="rv-tab-pane" id="tab-review">
                <div class="rv-section"><div class="rv-empty-tab">등록된 리뷰가 없습니다</div></div>
            </div>

        </div><!-- /rv-content -->
    </div><!-- /rv-panel -->
</div><!-- /rv-page -->

<?= view('modules/auth/login_modal') ?>

<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<?php if (!empty($naverMapClientId) && !empty($festival['latitude']) && !empty($festival['longitude'])): ?>
<script src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= esc($naverMapClientId) ?>"></script>
<?php endif; ?>
<script>
/* 이미지 슬라이더 */
(function () {
    var slides  = document.querySelectorAll('#rvSlider .rv-slide');
    var dots    = document.querySelectorAll('#rvDots .rv-dot');
    var counter = document.getElementById('rvCounter');
    if (slides.length <= 1) return;

    var current = 0, timer;

    function goTo(n) {
        slides[current].classList.remove('active');
        dots[current] && dots[current].classList.remove('active');
        current = (n + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current] && dots[current].classList.add('active');
        if (counter) counter.textContent = (current + 1) + ' / ' + slides.length;
    }
    function startAuto() { timer = setInterval(function () { goTo(current + 1); }, 4000); }
    function resetAuto()  { clearInterval(timer); startAuto(); }

    document.getElementById('rvPrev') && document.getElementById('rvPrev').addEventListener('click', function () { goTo(current - 1); resetAuto(); });
    document.getElementById('rvNext') && document.getElementById('rvNext').addEventListener('click', function () { goTo(current + 1); resetAuto(); });
    dots.forEach(function (dot, i) { dot.addEventListener('click', function () { goTo(i); resetAuto(); }); });

    startAuto();
})();

/* 탭 전환 */
document.querySelectorAll('.rv-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.rv-tab').forEach(function (t) { t.classList.remove('active'); });
        document.querySelectorAll('.rv-tab-pane').forEach(function (p) { p.classList.remove('active'); });
        tab.classList.add('active');
        var pane = document.getElementById('tab-' + tab.dataset.tab);
        if (pane) pane.classList.add('active');
    });
});

/* 네이버 지도 초기화 */
<?php if (!empty($naverMapClientId) && !empty($festival['latitude']) && !empty($festival['longitude'])): ?>
(function () {
    var lat   = <?= (float)$festival['latitude'] ?>;
    var lng   = <?= (float)$festival['longitude'] ?>;
    var name  = <?= json_encode($festival['name'], JSON_UNESCAPED_UNICODE) ?>;
    var mapEl = document.getElementById('naverMap');
    if (!mapEl || typeof naver === 'undefined') return;

    var center = new naver.maps.LatLng(lat, lng);
    var map    = new naver.maps.Map(mapEl, {
        center: center, zoom: 16,
        mapTypeControl: false, scaleControl: false,
        logoControl: true, mapDataControl: false,
    });
    var marker = new naver.maps.Marker({ position: center, map: map });

    naver.maps.Event.addListener(marker, 'click', function () {
        window.open(
            'https://map.naver.com/index.nhn?lat=' + lat + '&lng=' + lng + '&zoom=16&title=' + encodeURIComponent(name),
            '_blank'
        );
    });

    document.querySelectorAll('.rv-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (tab.dataset.tab === 'home') {
                setTimeout(function () { naver.maps.Event.trigger(map, 'resize'); }, 50);
            }
        });
    });
})();
<?php endif; ?>

/* 주소 복사 */
(function () {
    var btn  = document.getElementById('btnCopyAddr');
    var addr = <?= json_encode(
        ($festival['address1'] ?? '') . (!empty($festival['address2']) ? ' ' . $festival['address2'] : ''),
        JSON_UNESCAPED_UNICODE
    ) ?>;
    if (!btn || !addr) return;
    btn.addEventListener('click', function () {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(addr).then(function () { alert('주소가 복사되었습니다.'); });
        } else {
            var el = document.createElement('textarea');
            el.value = addr; document.body.appendChild(el); el.select();
            document.execCommand('copy'); document.body.removeChild(el);
            alert('주소가 복사되었습니다.');
        }
    });
})();
</script>
</body>
</html>
