<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($course['title']) ?> - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'travel-courses']) ?>

<!-- ===================== 본문 ===================== -->
<main>
    <div class="course-detail-wrap">

        <a href="/travel-courses" class="back-btn">← 목록으로</a>

        <!-- 대표 이미지 -->
        <?php if (!empty($course['thumb_url'])): ?>
            <img src="<?= esc($course['thumb_url']) ?>" alt="<?= esc($course['title']) ?>"
                 class="course-hero-img"
                 onerror="this.onerror=null; this.src='/img/no-image.svg';">
        <?php else: ?>
            <div class="course-hero-placeholder">🗓️</div>
        <?php endif; ?>

        <!-- 코스 기본 정보 -->
        <div class="course-info-header">
            <div class="course-info-meta">
                <?php if (!empty($course['sido'])): ?>
                <span class="course-sido-badge">📍 <?= esc($course['sido']) ?></span>
                <?php endif; ?>
                <span class="course-count-badge">🚩 <?= count($items) ?>개 장소</span>
            </div>
            <h1 class="course-info-title"><?= esc($course['title']) ?></h1>
            <?php if (!empty($course['description'])): ?>
            <p class="course-info-desc"><?= esc($course['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- 코스 항목 타임라인 -->
        <?php if (!empty($items)): ?>
        <div class="course-timeline">
            <h2 class="course-timeline-title">📋 코스 일정</h2>
            <ul class="timeline-list">
                <?php foreach ($items as $item):
                    $typeMap = [
                        'restaurant' => ['label' => '맛집',     'class' => 'type-restaurant'],
                        'place'      => ['label' => '관광지',   'class' => 'type-place'],
                        'event'      => ['label' => '행사·축제','class' => 'type-event'],
                        'custom'     => ['label' => '장소',     'class' => 'type-custom'],
                    ];
                    $typeInfo = $typeMap[$item['content_type']] ?? $typeMap['custom'];
                ?>
                <li class="timeline-item">
                    <div class="timeline-num"><?= (int)$item['item_order'] ?></div>
                    <div class="timeline-card">
                        <div class="timeline-card-header">
                            <span class="timeline-card-name"><?= esc($item['name']) ?></span>
                            <span class="timeline-card-type <?= $typeInfo['class'] ?>">
                                <?= $typeInfo['label'] ?>
                            </span>
                            <?php if (!empty($item['stay_time'])): ?>
                            <span class="timeline-stay">⏱ <?= esc($item['stay_time']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                        <p class="timeline-card-desc"><?= esc($item['description']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['address'])): ?>
                        <p class="timeline-card-addr">📌 <?= esc($item['address']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- 지도 (좌표가 있는 항목이 1개 이상일 때만 표시) -->
        <?php
        $mapItems = array_filter($items, fn($i) => !empty($i['latitude']) && !empty($i['longitude']));
        ?>
        <?php if (!empty($mapItems)): ?>
        <div class="course-map-section">
            <h2 class="course-map-title">🗺️ 코스 지도</h2>
            <div id="courseMap"></div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>

<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<?php if (!empty($mapItems)): ?>
<script src="https://dapi.kakao.com/v2/maps/sdk.js?appkey=<?= esc($kakaoMapJsKey) ?>"></script>
<script>
(function () {
    // 좌표가 있는 항목만 추출
    var mapItems = <?= json_encode(
        array_values(array_map(fn($i) => [
            'order'   => (int)   $i['item_order'],
            'name'    => $i['name'],
            'address' => $i['address'] ?? '',
            'lat'     => (float) $i['latitude'],
            'lng'     => (float) $i['longitude'],
        ], $mapItems)),
        JSON_UNESCAPED_UNICODE
    ) ?>;

    if (!mapItems.length) return;

    // 첫 번째 항목을 지도 중심으로
    var center = new kakao.maps.LatLng(mapItems[0].lat, mapItems[0].lng);
    var map = new kakao.maps.Map(document.getElementById('courseMap'), {
        center: center,
        level: 7,
    });

    // 전체 항목이 지도에 보이도록 bounds 조정
    var bounds = new kakao.maps.LatLngBounds();
    var infoWindows = [];

    mapItems.forEach(function (item) {
        var pos = new kakao.maps.LatLng(item.lat, item.lng);
        bounds.extend(pos);

        // 순서 번호가 표시되는 커스텀 마커 — 클릭 이벤트를 직접 붙이기 위해 실제 DOM 엘리먼트로 생성
        var pinEl = document.createElement('div');
        pinEl.style.cssText =
            'width:32px;height:32px;border-radius:50%;' +
            'background:#2563eb;color:#fff;' +
            'font-size:14px;font-weight:800;' +
            'display:flex;align-items:center;justify-content:center;' +
            'box-shadow:0 2px 8px rgba(37,99,235,.45);' +
            'border:2px solid #fff;cursor:pointer;';
        pinEl.textContent = item.order;

        new kakao.maps.CustomOverlay({
            position: pos,
            content: pinEl,
            map: map,
            yAnchor: 0.5,
        });

        // 정보창 — CustomOverlay는 marker가 아니므로 position을 직접 지정해서 연다
        var iw = new kakao.maps.InfoWindow({
            position: pos,
            content: '<div style="padding:10px 14px;min-width:160px;">' +
                     '<strong style="font-size:14px;color:#1e293b;">' + item.name + '</strong>' +
                     (item.address ? '<p style="font-size:12px;color:#64748b;margin:4px 0 0;">' + item.address + '</p>' : '') +
                     '</div>',
            removable: true,
        });
        infoWindows.push(iw);

        pinEl.addEventListener('click', function () {
            infoWindows.forEach(function (x) { x.close(); });
            iw.open(map);
        });
    });

    // 2개 이상일 때 전체 경로가 보이도록 맞춤
    if (mapItems.length > 1) {
        map.setBounds(bounds, 60, 40, 60, 40);
    }

    // 항목 간 폴리라인 연결
    var path = mapItems.map(function (i) {
        return new kakao.maps.LatLng(i.lat, i.lng);
    });
    new kakao.maps.Polyline({
        path: path,
        map: map,
        strokeColor: '#2563eb',
        strokeWeight: 3,
        strokeOpacity: 0.6,
        strokeStyle: 'solid',
    });
})();
</script>
<?php endif; ?>

</body>
</html>
