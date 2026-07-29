<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($restaurant['name']) ?> - 부산온나</title>
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
                <h1 class="rv-name"><?= esc($restaurant['name']) ?></h1>

                <div class="rv-badge-row">
                    <span class="rv-cat-badge" style="background:<?= $color ?>;"><?= esc($categories[$catNum] ?? '기타') ?></span>
                    <span class="rv-price-badge"><?= esc($priceRanges[$priceNum] ?? '') ?></span>
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

            <!-- 탭 -->
            <div class="rv-tabs">
                <div class="rv-tab active" data-tab="home">홈</div>
                <div class="rv-tab" data-tab="menu">메뉴</div>
                <div class="rv-tab" data-tab="review">리뷰</div>
            </div>

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
                    <a class="rv-map-link" href="https://map.naver.com/search/<?= urlencode($restaurant['address1']) ?>" target="_blank" rel="noopener">네이버 지도에서 보기 →</a>
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

            <!-- 메뉴 탭 -->
            <div class="rv-tab-pane" id="tab-menu">
                <div class="rv-section"><div class="rv-empty-tab">등록된 메뉴 정보가 없습니다</div></div>
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
<?php if (!empty($naverMapClientId) && !empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
<script src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= esc($naverMapClientId) ?>"></script>
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

/* 네이버 지도 초기화 */
<?php if (!empty($naverMapClientId) && !empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
(function () {
    var lat  = <?= (float)$restaurant['latitude'] ?>;
    var lng  = <?= (float)$restaurant['longitude'] ?>;
    var name = <?= json_encode($restaurant['name'], JSON_UNESCAPED_UNICODE) ?>;

    var mapEl = document.getElementById('naverMap');
    if (!mapEl || typeof naver === 'undefined') return;

    var center = new naver.maps.LatLng(lat, lng);

    var map = new naver.maps.Map(mapEl, {
        center: center,
        zoom: 16,
        mapTypeControl: false,
        scaleControl: false,
        logoControl: true,
        mapDataControl: false,
    });

    /* 마커 */
    var marker = new naver.maps.Marker({
        position: center,
        map: map,
    });

    /* 마커 클릭 시 네이버 지도 앱/웹으로 연결 */
    naver.maps.Event.addListener(marker, 'click', function () {
        window.open(
            'https://map.naver.com/index.nhn?lat=' + lat + '&lng=' + lng + '&zoom=16&title=' + encodeURIComponent(name),
            '_blank'
        );
    });

    /* 탭 전환 후 지도 크기 재계산 */
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
