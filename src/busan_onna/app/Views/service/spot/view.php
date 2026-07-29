<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($spot['name']) ?> - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'spots']) ?>

<!-- 페이지 본문 -->
<div class="rv-page rv-page--spot">
    <div class="rv-panel">

        <!-- 이미지 슬라이더 -->
        <?php
        $catColor = [1=>'#0984e3', 2=>'#00b894', 3=>'#6c5ce7', 4=>'#e17055', 5=>'#fd79a8', 6=>'#fdcb6e', 7=>'#a29bfe', 8=>'#b2bec3'];
        $catEmoji = [1=>'🏖️', 2=>'🌲', 3=>'🏛️', 4=>'🖼️', 5=>'🎡', 6=>'🌃', 7=>'🛍️', 8=>'📍'];
        $catNum   = (int)($spot['category_num'] ?? 8);
        $starVal  = (float)($spot['star_point'] ?? 0);
        $color    = $catColor[$catNum] ?? '#b2bec3';
        $emoji    = $catEmoji[$catNum] ?? '📍';
        $imgCount = count($thumbnails);
        ?>
        <div class="rv-slider" id="rvSlider">
            <?php if (empty($thumbnails)): ?>
            <div class="rv-slide rv-slide-default active"><span><?= $emoji ?></span></div>
            <?php else: ?>
            <?php foreach ($thumbnails as $i => $thumb): ?>
            <div class="rv-slide <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= esc($thumb['img_url']) ?>" alt="<?= esc($spot['name']) ?> 이미지 <?= $i + 1 ?>"
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
                    <h1 class="rv-name"><?= esc($spot['name']) ?></h1>

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
                            <span class="rv-reaction-count" id="likeCount"><?= (int)$likeCount ?></span>
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
                    <span class="rv-cat-badge" style="background:<?= $color ?>;">
                        <?= esc($categories[$catNum] ?? '기타') ?>
                    </span>
                    <?php if (!empty($spot['admission_fee'])): ?>
                    <span class="rv-fee-badge">🎫 <?= esc($spot['admission_fee']) ?></span>
                    <?php else: ?>
                    <span class="rv-fee-badge free">🎫 무료</span>
                    <?php endif; ?>
                </div>

                <div class="rv-meta-list">
                    <?php if (!empty($spot['address1'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">📍</span>
                        <span><?= esc($spot['address1']) ?><?= !empty($spot['address2']) ? ' ' . esc($spot['address2']) : '' ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($spot['open_time'])): ?>
                    <div class="rv-meta-item">
                        <span class="rv-icon">🕐</span>
                        <span>오늘 <?= esc($spot['open_time']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($spot['parking'])): ?>
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
                <div class="rv-tab" data-tab="info">정보</div>
                <div class="rv-tab" data-tab="review">리뷰</div>
            </div>
            -->

            <!-- 홈 탭 -->
            <div class="rv-tab-pane active" id="tab-home">

                <?php if (!empty($spot['info'])): ?>
                <div class="rv-section">
                    <h3 class="rv-section-title">관광지 소개</h3>
                    <p class="rv-intro-text"><?= esc($spot['info']) ?></p>
                </div>
                <?php endif; ?>

                <div class="rv-section">
                    <h3 class="rv-section-title">이용정보</h3>
                    <?php if (!empty($spot['open_time'])): ?>
                    <div class="rv-info-row"><span class="rv-info-key">운영시간</span><span class="rv-info-val"><?= esc($spot['open_time']) ?></span></div>
                    <?php endif; ?>
                    <div class="rv-info-row">
                        <span class="rv-info-key">입장료</span>
                        <span class="rv-info-val"><?= !empty($spot['admission_fee']) ? esc($spot['admission_fee']) : '무료' ?></span>
                    </div>
                    <div class="rv-info-row"><span class="rv-info-key">카테고리</span><span class="rv-info-val"><?= esc($categories[$catNum] ?? '기타') ?></span></div>
                </div>

                <?php if (!empty($spot['address1'])): ?>
                <div class="rv-section">
                    <h3 class="rv-section-title">위치정보</h3>
                    <?php if (!empty($spot['latitude']) && !empty($spot['longitude'])): ?>
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
                        <span class="rv-address-text"><?= esc($spot['address1']) ?><?= !empty($spot['address2']) ? ' ' . esc($spot['address2']) : '' ?></span>
                        <button class="rv-copy-btn" id="btnCopyAddr">주소복사</button>
                    </div>
                    <a class="rv-map-link" href="https://map.naver.com/search/<?= urlencode($spot['address1']) ?>" target="_blank" rel="noopener">네이버 지도에서 보기 →</a>
                </div>
                <?php endif; ?>

                <div class="rv-section">
                    <h3 class="rv-section-title">주차정보</h3>
                    <div class="rv-info-row">
                        <span class="rv-info-key">주차 여부</span>
                        <?php if (!empty($spot['parking'])): ?>
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

            <!-- 정보 탭 -->
            <div class="rv-tab-pane" id="tab-info">
                <div class="rv-section"><div class="rv-empty-tab">추가 정보가 없습니다</div></div>
            </div>

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
<?php if (!empty($naverMapClientId) && !empty($spot['latitude']) && !empty($spot['longitude'])): ?>
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
<?php if (!empty($naverMapClientId) && !empty($spot['latitude']) && !empty($spot['longitude'])): ?>
(function () {
    var lat    = <?= (float)$spot['latitude'] ?>;
    var lng    = <?= (float)$spot['longitude'] ?>;
    var name   = <?= json_encode($spot['name'], JSON_UNESCAPED_UNICODE) ?>;
    var mapEl  = document.getElementById('naverMap');
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

/* 추천/비추천 (DB 연동) */
(function () {
    var btnLike      = document.getElementById('btnLike');
    var btnDislike   = document.getElementById('btnDislike');
    var likeCount    = document.getElementById('likeCount');
    var dislikeCount = document.getElementById('dislikeCount');

    if (!btnLike || !btnDislike) return;

    var isLoggedIn   = <?= session()->get('user.idx') ? 'true' : 'false' ?>;
    var targetType   = 'spot';
    var targetIdx    = <?= (int)$spot['idx'] ?>;
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
        ($spot['address1'] ?? '') . (!empty($spot['address2']) ? ' ' . $spot['address2'] : ''),
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
