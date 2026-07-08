<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($course['title']) ?> - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <style>
        /* ======== 코스 상세 레이아웃 ======== */
        .course-detail-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        /* 히어로 이미지 */
        .course-hero-img {
            width: 100%;
            aspect-ratio: 16/7;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 32px;
        }
        .course-hero-placeholder {
            width: 100%;
            aspect-ratio: 16/7;
            border-radius: 16px;
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 64px;
            margin-bottom: 32px;
        }

        /* 코스 기본 정보 */
        .course-info-header {
            margin-bottom: 28px;
        }
        .course-info-meta {
            display: flex; align-items: center; gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .course-sido-badge {
            background: #eff6ff;
            color: #2563eb;
            font-size: 13px; font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
        }
        .course-count-badge {
            background: #f0fdf4;
            color: #16a34a;
            font-size: 13px; font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
        }
        .course-info-title {
            font-size: 28px; font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
            margin: 0 0 14px;
        }
        .course-info-desc {
            font-size: 15px; color: #475569;
            line-height: 1.8;
            padding: 16px 20px;
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            border-radius: 0 8px 8px 0;
        }

        /* ======== 타임라인 ======== */
        .course-timeline {
            margin-top: 40px;
        }
        .course-timeline-title {
            font-size: 18px; font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
            display: flex; align-items: center; gap: 8px;
        }
        .timeline-list {
            position: relative;
            padding-left: 0;
            list-style: none;
            margin: 0;
        }

        /* 연결선 */
        .timeline-list::before {
            content: '';
            position: absolute;
            left: 24px;
            top: 28px;
            bottom: 28px;
            width: 2px;
            background: linear-gradient(to bottom, #2563eb, #93c5fd);
        }

        .timeline-item {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
            position: relative;
        }
        .timeline-item:last-child { margin-bottom: 0; }

        /* 순서 번호 원 */
        .timeline-num {
            flex-shrink: 0;
            width: 48px; height: 48px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            font-size: 16px; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 0 4px #eff6ff;
            position: relative;
            z-index: 1;
        }

        /* 항목 카드 */
        .timeline-card {
            flex: 1;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,.05);
        }
        .timeline-card-header {
            display: flex; align-items: flex-start; gap: 10px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .timeline-card-name {
            font-size: 16px; font-weight: 700;
            color: #1e293b;
            flex: 1;
        }
        .timeline-card-type {
            font-size: 11px; font-weight: 600;
            padding: 2px 9px;
            border-radius: 12px;
            flex-shrink: 0;
        }
        .type-restaurant { background: #fef3c7; color: #b45309; }
        .type-place      { background: #dbeafe; color: #1d4ed8; }
        .type-event      { background: #fce7f3; color: #be185d; }
        .type-custom     { background: #f3f4f6; color: #4b5563; }

        .timeline-stay {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 12px; font-weight: 600;
            color: #2563eb;
            background: #eff6ff;
            padding: 2px 10px;
            border-radius: 12px;
        }

        .timeline-card-desc {
            font-size: 14px; color: #64748b;
            line-height: 1.7;
            margin: 8px 0 0;
        }
        .timeline-card-addr {
            margin-top: 10px;
            font-size: 13px; color: #94a3b8;
            display: flex; align-items: center; gap: 4px;
        }

        /* 지도 섹션 */
        .course-map-section {
            margin-top: 48px;
        }
        .course-map-title {
            font-size: 18px; font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        #courseMap {
            width: 100%;
            height: 420px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* 뒤로가기 버튼 */
        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            margin-bottom: 24px;
            font-size: 14px; font-weight: 500;
            color: #64748b;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            transition: all .15s;
        }
        .back-btn:hover {
            background: #f8fafc;
            color: #1e293b;
        }
    </style>
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
                    <li><a href="/travel-courses" class="active">여행코스</a></li>
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

<!-- ===================== 본문 ===================== -->
<main>
    <div class="course-detail-wrap">

        <a href="/travel-courses" class="back-btn">← 목록으로</a>

        <!-- 대표 이미지 -->
        <?php if (!empty($course['thumb_url'])): ?>
            <img src="<?= esc($course['thumb_url']) ?>" alt="<?= esc($course['title']) ?>"
                 class="course-hero-img">
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
