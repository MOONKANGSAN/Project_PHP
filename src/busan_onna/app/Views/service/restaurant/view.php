<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    /* ---- SEO 변수 계산 ---- */
    $seoName     = $restaurant['name'] ?? '';
    $seoRawInfo  = strip_tags($restaurant['info'] ?? '');
    // meta description: info 앞 120자, 없으면 기본문구
    $seoDesc = $seoRawInfo !== ''
        ? mb_substr($seoRawInfo, 0, 120) . (mb_strlen($seoRawInfo) > 120 ? '…' : '')
        : esc($seoName) . '. 부산 맛집 정보 - 위치, 가격, 영업시간을 부산온나에서 확인하세요.';
    $seoTitle      = esc($seoName) . ' | 부산 맛집 - 부산온나';
    $canonicalUrl  = 'https://busanonna.com/restaurants/' . (int)$restaurant['idx'];
    // OG 이미지: 첫 번째 썸네일, 없으면 기본
    $ogImage = !empty($thumbnails[0]['img_url'])
        ? esc($thumbnails[0]['img_url'])
        : 'https://busanonna.com/img/og-restaurant.jpg';
    // 주소 구/군 추출
    preg_match('/부산광역시\s+(\S+(?:구|군))/', $restaurant['address1'] ?? '', $addrMatch);
    $district = $addrMatch[1] ?? '부산';
    // 가격대 레이블
    $priceLabel = $priceRanges[(int)($restaurant['price_range'] ?? 1)] ?? '';
    // 해시태그 키워드
    $tagKeywords = implode(', ', array_column($tags ?? [], 'name'));
    $seoKeywords = esc($seoName) . ', ' . esc($district) . ' 맛집, 부산맛집' . ($tagKeywords ? ', ' . esc($tagKeywords) : '') . ', 부산온나';
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= esc($seoDesc) ?>">
    <meta name="keywords"    content="<?= $seoKeywords ?>">
    <meta name="robots"      content="index, follow">
    <link rel="canonical"    href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type"         content="restaurant">
    <meta property="og:title"        content="<?= $seoTitle ?>">
    <meta property="og:description"  content="<?= esc($seoDesc) ?>">
    <meta property="og:url"          content="<?= $canonicalUrl ?>">
    <meta property="og:image"        content="<?= $ogImage ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"    content="부산온나">
    <meta property="og:locale"       content="ko_KR">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $seoTitle ?>">
    <meta name="twitter:description" content="<?= esc($seoDesc) ?>">
    <meta name="twitter:image"       content="<?= $ogImage ?>">

    <!-- 구조화 데이터 (JSON-LD) - Restaurant -->
    <script type="application/ld+json">
    <?php
    $ld = [
        '@context' => 'https://schema.org',
        '@type'    => 'Restaurant',
        'name'     => $seoName,
        'url'      => $canonicalUrl,
        'description' => $seoRawInfo !== '' ? mb_substr($seoRawInfo, 0, 200) : null,
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressCountry'  => 'KR',
            'addressRegion'   => '부산광역시',
            'streetAddress'   => $restaurant['address1'] ?? '',
        ],
        'servesCuisine' => $categories[(int)($restaurant['category_num'] ?? 8)] ?? '한식',
    ];
    if (!empty($restaurant['phone'])) {
        $ld['telephone'] = $restaurant['phone'];
    }
    if (!empty($thumbnails[0]['img_url'])) {
        $ld['image'] = $thumbnails[0]['img_url'];
    }
    if (!empty($restaurant['open_time'])) {
        $ld['openingHours'] = $restaurant['open_time'];
    }
    if (!empty($tagKeywords)) {
        $ld['keywords'] = $tagKeywords;
    }
    // null 값 제거
    $ld = array_filter($ld, fn($v) => $v !== null && $v !== '');
    echo json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

<!-- 페이지 본문 -->
<div class="rv-page rv-page--restaurant">
    <div class="rv-panel">

        <!-- 이미지 슬라이더 -->
        <?php
        $catColor = [1=>'#e55039', 2=>'#6c5ce7', 3=>'#e17055', 4=>'#00b894', 5=>'#fdcb6e', 6=>'#a29bfe', 7=>'#fab1a0', 8=>'#b2bec3'];
        $catEmoji = [1=>'🍲', 2=>'🍣', 3=>'🥢', 4=>'🍝', 5=>'🥞', 6=>'☕', 7=>'🍽️', 8=>'🍴'];
        $catNum   = (int)($restaurant['category_num'] ?? 8);
        $priceNum = (int)($restaurant['price_range']  ?? 1);
        $starVal  = (float)($restaurant['star_point'] ?? 0);
        $color    = $catColor[$catNum] ?? '#b2bec3';
        $emoji    = $catEmoji[$catNum] ?? '🍴';
        $imgCount = count($thumbnails);
        ?>
        <div class="rv-slider" id="rvSlider">
            <?php if (empty($thumbnails)): ?>
            <div class="rv-slide rv-slide-default active"><span><?= $emoji ?></span></div>
            <?php else: ?>
            <?php foreach ($thumbnails as $i => $thumb): ?>
            <div class="rv-slide <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= esc($thumb['img_url']) ?>" alt="<?= esc($restaurant['name']) ?> 이미지 <?= $i + 1 ?>"
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
                <!-- rv-name과 추천/비추천 버튼을 같은 행에 배치 -->
                <div class="rv-name-row">
                    <h1 class="rv-name"><?= esc($restaurant['name']) ?></h1>

                    <!-- 추천/비추천 영역 (기존 별점 대체) -->
                    <div class="rv-reaction-row">
                        <!-- 추천 버튼: 하트 아이콘 + 숫자 -->
                        <button class="rv-reaction-btn rv-reaction-like" id="btnLike" title="추천">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                                         2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
                                         C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
                                         c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                            <!-- 좋아요 수치는 화면에서만 숨김: id="likeCount"는 JS(toggle 응답 반영)에서 계속 사용하므로 DOM에서 제거하지 않고 display:none으로만 처리 -->
                            <span class="rv-reaction-count" id="likeCount" style="display:none;"><?= (int)$likeCount ?></span>
                        </button>

                        <!-- 비추천 버튼: 엄지 내림 아이콘 + 숫자 -->
                        <button class="rv-reaction-btn rv-reaction-dislike" id="btnDislike" title="비추천">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05
                                         C1.05 11.5 1 11.74 1 12v2c0 1.1.9 2 2 2h6.31
                                         l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23
                                         l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2z
                                         M19 3v12h4V3h-4z"/>
                            </svg>
                            <span class="rv-reaction-count" id="dislikeCount"><?= (int)$dislikeCount ?></span>
                        </button>
                    </div>
                </div>

                <div class="rv-badge-row">
                    <span class="rv-cat-badge" style="background:<?= $color ?>;"><?= esc($categories[$catNum] ?? '기타') ?></span>
                    <span class="rv-price-badge"><?= esc($priceRanges[$priceNum] ?? '') ?></span>
                </div>

                <div class="rv-meta-list">
                    <?php if (!empty($restaurant['address1'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">📍</span>
                        <span><?= esc($restaurant['address1']) ?><?= !empty($restaurant['address2']) ? ' ' . esc($restaurant['address2']) : '' ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['open_time'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">🕐</span>
                        <span>오늘 <?= esc($restaurant['open_time']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['phone'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">📞</span>
                        <span><?= esc($restaurant['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['parking'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">🅿️</span>
                        <span>주차 가능</span>
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

            <!-- 탭 메뉴 (임시 비활성화)
            <div class="rv-tabs">
                <div class="rv-tab active" data-tab="home">홈</div>
                <div class="rv-tab" data-tab="menu">메뉴</div>
                <div class="rv-tab" data-tab="review">리뷰</div>
            </div>
            -->

            <!-- 홈 탭 -->
            <div class="rv-tab-pane active" id="tab-home">

                <?php if (!empty($restaurant['info'])): ?>
                <div class="rv-section">
                    <h3 class="rv-section-title">매장소개</h3>
                    <p class="rv-intro-text"><?= esc($restaurant['info']) ?></p>
                </div>
                <?php endif; ?>

                <div class="rv-section">
                    <h3 class="rv-section-title">영업정보</h3>
                    <?php if (!empty($restaurant['open_time'])): ?>
                    <div class="rv-info-row"><span class="rv-info-key">운영시간</span><span class="rv-info-val"><?= esc($restaurant['open_time']) ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['phone'])): ?>
                    <div class="rv-info-row"><span class="rv-info-key">전화번호</span><span class="rv-info-val"><?= esc($restaurant['phone']) ?></span></div>
                    <?php endif; ?>
                    <div class="rv-info-row"><span class="rv-info-key">카테고리</span><span class="rv-info-val"><?= esc($categories[$catNum] ?? '기타') ?></span></div>
                    <div class="rv-info-row"><span class="rv-info-key">가격대</span><span class="rv-info-val"><?= esc($priceRanges[$priceNum] ?? '-') ?></span></div>
                </div>

                <?php if (!empty($restaurant['address1'])): ?>
                <div class="rv-section">
                    <h3 class="rv-section-title">위치정보</h3>
                    <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
                    <div class="rv-map-box">
                        <div id="naverMap"></div>
                    </div>
                    <?php else: ?>
                    <div class="rv-map-box">
                        <div class="rv-map-placeholder">
                            <span class="map-icon">🗺️</span>
                            <p>위치 정보가 없습니다</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="rv-address-row">
                        <span class="rv-address-text"><?= esc($restaurant['address1']) ?><?= !empty($restaurant['address2']) ? ' ' . esc($restaurant['address2']) : '' ?></span>
                        <button class="rv-copy-btn" id="btnCopyAddr">주소복사</button>
                    </div>
                    <a class="rv-map-link" href="https://map.kakao.com/link/search/<?= urlencode($restaurant['address1']) ?>" target="_blank" rel="noopener">카카오맵에서 보기 →</a>
                </div>
                <?php endif; ?>

                <div class="rv-section">
                    <h3 class="rv-section-title">주차정보</h3>
                    <div class="rv-info-row">
                        <span class="rv-info-key">주차 여부</span>
                        <?php if (!empty($restaurant['parking'])): ?>
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

            <!-- 메뉴 탭 (임시 비활성화)
            <div class="rv-tab-pane" id="tab-menu">
                <div class="rv-section"><div class="rv-empty-tab">등록된 메뉴 정보가 없습니다</div></div>
            </div>
            -->

            <!-- 리뷰 탭 (임시 비활성화)
            <div class="rv-tab-pane" id="tab-review">
                <div class="rv-section"><div class="rv-empty-tab">등록된 리뷰가 없습니다</div></div>
            </div>
            -->

        </div><!-- /rv-content -->
    </div><!-- /rv-panel -->
</div><!-- /rv-page -->

<?= view('modules/auth/login_modal') ?>

<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<?php if (!empty($kakaoMapJsKey) && !empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
<script src="https://dapi.kakao.com/v2/maps/sdk.js?appkey=<?= esc($kakaoMapJsKey) ?>"></script>
<?php endif; ?>
<script>
/* 이미지 슬라이더 (메인 배너와 동일한 페이드 방식) */
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

/* 카카오맵 초기화 */
<?php if (!empty($kakaoMapJsKey) && !empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
(function () {
    var lat    = <?= (float)$restaurant['latitude'] ?>;
    var lng    = <?= (float)$restaurant['longitude'] ?>;
    var name   = <?= json_encode($restaurant['name'], JSON_UNESCAPED_UNICODE) ?>;
    var mapEl  = document.getElementById('naverMap');
    if (!mapEl || typeof kakao === 'undefined' || !kakao.maps) return;

    var center = new kakao.maps.LatLng(lat, lng);
    var map    = new kakao.maps.Map(mapEl, {
        center: center,
        level: 3,
    });
    var marker = new kakao.maps.Marker({ position: center, map: map });

    // 마커 클릭 시 카카오맵 앱/웹으로 연결
    kakao.maps.event.addListener(marker, 'click', function () {
        window.open(
            'https://map.kakao.com/link/map/' + encodeURIComponent(name) + ',' + lat + ',' + lng,
            '_blank'
        );
    });

    // 탭 전환 후 지도 크기 재계산 (숨겨졌다 다시 보일 때 필요)
    document.querySelectorAll('.rv-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (tab.dataset.tab === 'home') {
                setTimeout(function () { map.relayout(); }, 50);
            }
        });
    });
})();
<?php endif; ?>

/* 추천/비추천 (DB 연동) */
(function () {
    var btnLike      = document.getElementById('btnLike');
    var btnDislike   = document.getElementById('btnDislike');
    var likeCount    = document.getElementById('likeCount');
    var dislikeCount = document.getElementById('dislikeCount');

    if (!btnLike || !btnDislike) return;

    var isLoggedIn   = <?= session()->get('user.idx') ? 'true' : 'false' ?>;
    var targetType   = 'restaurant';
    var targetIdx    = <?= (int)$restaurant['idx'] ?>;
    var userReaction = <?= json_encode($userReaction) ?>;

    /* 초기 활성 상태 반영 */
    if (userReaction === 'like')    btnLike.classList.add('active');
    if (userReaction === 'dislike') btnDislike.classList.add('active');

    /* 로그인 유도 토스트 */
    function showToast(msg) {
        var t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);'
            + 'background:rgba(0,0,0,.75);color:#fff;padding:10px 22px;border-radius:20px;'
            + 'font-size:14px;z-index:9999;opacity:0;transition:opacity .25s;white-space:nowrap;';
        document.body.appendChild(t);
        requestAnimationFrame(function () { t.style.opacity = '1'; });
        setTimeout(function () {
            t.style.opacity = '0';
            setTimeout(function () { t.remove(); }, 300);
        }, 2000);
    }

    function handleClick(type) {
        if (!isLoggedIn) { showToast('로그인이 필요합니다.'); return; }

        fetch('/api/reaction/toggle', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ target_type: targetType, target_idx: targetIdx, type: type }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) return;
            likeCount.textContent    = data.like_count;
            dislikeCount.textContent = data.dislike_count;
            userReaction             = data.user_reaction;
            btnLike.classList.toggle('active', userReaction === 'like');
            btnDislike.classList.toggle('active', userReaction === 'dislike');
        });
    }

    btnLike.addEventListener('click',    function () { handleClick('like'); });
    btnDislike.addEventListener('click', function () { handleClick('dislike'); });
})();

/* 주소 복사 */
(function () {
    var btn  = document.getElementById('btnCopyAddr');
    var addr = <?= json_encode(
        ($restaurant['address1'] ?? '') . (!empty($restaurant['address2']) ? ' ' . $restaurant['address2'] : ''),
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
