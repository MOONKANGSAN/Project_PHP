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
<script src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= esc($naverMapClientId) ?>"></script>
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
    var center = new naver.maps.LatLng(mapItems[0].lat, mapItems[0].lng);
    var map = new naver.maps.Map('courseMap', {
        center: center,
        zoom: 13,
    });

    // 전체 항목이 지도에 보이도록 bounds 조정
    var bounds = new naver.maps.LatLngBounds();
    var infoWindows = [];

    mapItems.forEach(function (item) {
        var pos = new naver.maps.LatLng(item.lat, item.lng);
        bounds.extend(pos);

        // 커스텀 마커 (순서 번호 표시)
        var marker = new naver.maps.Marker({
            position: pos,
            map: map,
            icon: {
                content: '<div style="' +
                    'width:32px;height:32px;border-radius:50%;' +
                    'background:#2563eb;color:#fff;' +
                    'font-size:14px;font-weight:800;' +
                    'display:flex;align-items:center;justify-content:center;' +
                    'box-shadow:0 2px 8px rgba(37,99,235,.45);' +
                    'border:2px solid #fff;' +
                    '">' + item.order + '</div>',
                anchor: new naver.maps.Point(16, 16),
            },
        });

        // 정보창
        var iw = new naver.maps.InfoWindow({
            content: '<div style="padding:10px 14px;min-width:160px;">' +
                     '<strong style="font-size:14px;color:#1e293b;">' + item.name + '</strong>' +
                     (item.address ? '<p style="font-size:12px;color:#64748b;margin:4px 0 0;">' + item.address + '</p>' : '') +
                     '</div>',
            borderWidth: 0,
            borderRadius: '8px',
            backgroundColor: '#fff',
            boxShadow: '0 4px 16px rgba(0,0,0,.15)',
            disableAnchor: false,
        });
        infoWindows.push(iw);

        (function (m, w) {
            naver.maps.Event.addListener(m, 'click', function () {
                infoWindows.forEach(function (x) { x.close(); });
                w.open(map, m);
            });
        })(marker, iw);
    });

    // 2개 이상일 때 전체 경로가 보이도록 맞춤
    if (mapItems.length > 1) {
        map.fitBounds(bounds, { top: 60, right: 40, bottom: 60, left: 40 });
    }

    // 항목 간 폴리라인 연결
    var path = mapItems.map(function (i) {
        return new naver.maps.LatLng(i.lat, i.lng);
    });
    new naver.maps.Polyline({
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
