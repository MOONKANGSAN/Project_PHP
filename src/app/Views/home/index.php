<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>부산온나 - 부산 여행의 시작</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
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
                    <li><a href="#">관광지</a></li>
                    <li><a href="#">맛집</a></li>
                    <li><a href="#">축제</a></li>
                    <li><a href="#">여행코스</a></li>
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
        <?php foreach ($banners as $i => $banner): ?>
        <div class="banner-slide <?= $banner['bg'] ?> <?= $i === 0 ? 'active' : '' ?>">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <span class="banner-location">📍 <?= $banner['location'] ?></span>
                <h1 class="banner-title"><?= $banner['title'] ?></h1>
                <p class="banner-subtitle"><?= $banner['subtitle'] ?></p>
                <a href="#" class="btn-banner">지금 탐험하기 →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="banner-controls">
        <button class="banner-arrow prev" id="bannerPrev">&#8249;</button>
        <div class="banner-dots">
            <?php foreach ($banners as $i => $banner): ?>
            <span class="dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
            <?php endforeach; ?>
        </div>
        <button class="banner-arrow next" id="bannerNext">&#8250;</button>
    </div>
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
            <!-- 인터랙티브 SVG 지도 -->
            <div class="busan-map-wrap">
                <svg viewBox="0 0 700 580" xmlns="http://www.w3.org/2000/svg" class="map-svg" id="busanMap">
                    <defs>
                        <linearGradient id="seaGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#b3d9f5;stop-opacity:1"/>
                            <stop offset="100%" style="stop-color:#7ec8e3;stop-opacity:1"/>
                        </linearGradient>
                        <filter id="landShadow">
                            <feDropShadow dx="2" dy="3" stdDeviation="4" flood-opacity="0.15"/>
                        </filter>
                    </defs>

                    <!-- 바다 배경 -->
                    <rect width="700" height="580" fill="url(#seaGrad)" rx="14"/>

                    <!-- 파도 느낌 선 -->
                    <path d="M0,520 Q175,500 350,520 Q525,540 700,520" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.4"/>
                    <path d="M0,545 Q175,525 350,545 Q525,565 700,545" fill="none" stroke="white" stroke-width="1" stroke-opacity="0.3"/>

                    <!-- 부산 육지 윤곽 (단순화) -->
                    <path d="
                        M 68,82
                        L 658,60
                        L 678,165
                        L 648,262
                        L 682,368
                        L 608,438
                        L 505,498
                        L 382,522
                        L 260,502
                        L 158,460
                        L 78,382
                        L 52,255
                        L 62,132
                        Z
                    " fill="#dce9c8" stroke="#9eb87a" stroke-width="2" filter="url(#landShadow)"/>

                    <!-- 낙동강 (서쪽 경계) -->
                    <path d="M 62,132 Q 48,195 52,255 Q 58,318 78,382" fill="none" stroke="#74b9ff" stroke-width="7" stroke-linecap="round" opacity="0.7"/>

                    <!-- 영도구 (섬) -->
                    <ellipse cx="305" cy="513" rx="76" ry="30" fill="#dce9c8" stroke="#9eb87a" stroke-width="2"/>

                    <!-- 지역 마커는 JS로 동적 생성 -->
                </svg>
                <div class="map-legend">
                    <span class="legend-sea">■ 바다</span>
                    <span class="legend-land">■ 육지</span>
                    <span class="legend-river">■ 낙동강</span>
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
        <div class="section-header fade-in">
            <h2>추천 관광지</h2>
            <p>부산에서 꼭 가봐야 할 대표 명소들</p>
        </div>
        <div class="card-grid">
            <?php foreach ($spots as $i => $spot): ?>
            <div class="spot-card fade-in" style="--card-color: <?= $spot['color'] ?>; --delay: <?= $i * 80 ?>ms">
                <div class="card-thumb">
                    <span class="card-emoji"><?= $spot['emoji'] ?></span>
                    <span class="card-badge"><?= $spot['category'] ?></span>
                </div>
                <div class="card-body">
                    <span class="card-district">📍 <?= $spot['district'] ?></span>
                    <h3 class="card-title"><?= $spot['name'] ?></h3>
                    <p class="card-desc"><?= $spot['desc'] ?></p>
                    <a href="#" class="card-link">자세히 보기 →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== 인기 맛집 ===================== -->
<section class="food-section">
    <div class="container">
        <div class="section-header fade-in">
            <h2>부산 대표 먹거리</h2>
            <p>부산에서 꼭 먹어봐야 할 향토 음식들</p>
        </div>
        <div class="card-grid">
            <?php foreach ($restaurants as $i => $r): ?>
            <div class="food-card fade-in" style="--card-color: <?= $r['color'] ?>; --delay: <?= $i * 80 ?>ms">
                <div class="food-top" style="background: <?= $r['color'] ?>22;">
                    <span class="food-emoji"><?= $r['emoji'] ?></span>
                </div>
                <div class="food-body">
                    <h3 class="food-name"><?= $r['name'] ?></h3>
                    <span class="food-area">📍 <?= $r['area'] ?></span>
                    <p class="food-desc"><?= $r['desc'] ?></p>
                    <div class="food-bottom">
                        <span class="food-price" style="background: <?= $r['color'] ?>"><?= $r['price'] ?></span>
                        <a href="#" class="food-link">더보기 →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== 추천 여행 코스 ===================== -->
<section class="courses-section">
    <div class="container">
        <div class="section-header fade-in">
            <h2>추천 여행 코스</h2>
            <p>테마별로 즐기는 부산 여행</p>
        </div>
        <div class="courses-grid">
            <?php foreach ($courses as $i => $course): ?>
            <div class="course-card fade-in" style="--course-color: <?= $course['color'] ?>; --delay: <?= $i * 120 ?>ms">
                <div class="course-top">
                    <span class="course-theme"><?= $course['theme'] ?></span>
                    <span class="course-duration"><?= $course['duration'] ?></span>
                </div>
                <h3 class="course-title"><?= $course['title'] ?></h3>
                <p class="course-desc"><?= $course['desc'] ?></p>
                <div class="course-route">
                    <?php foreach ($course['spots'] as $j => $spot): ?>
                    <?php if ($j > 0): ?><span class="route-arrow">→</span><?php endif; ?>
                    <span class="route-stop"><?= $spot ?></span>
                    <?php endforeach; ?>
                </div>
                <a href="#" class="btn-course" style="background: <?= $course['color'] ?>">코스 보기</a>
            </div>
            <?php endforeach; ?>
        </div>
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
                    <li><a href="#">관광지</a></li>
                    <li><a href="#">맛집</a></li>
                    <li><a href="#">축제·행사</a></li>
                    <li><a href="#">여행코스</a></li>
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

            <!-- 아이디 -->
            <div class="form-group" id="lfg-id">
                <label class="form-label" for="loginId">아이디 <span class="required">*</span></label>
                <input type="text" id="loginId" name="id"
                       class="form-input" placeholder="아이디 입력"
                       autocomplete="username" maxlength="50"
                       value="<?= esc($saved_id) ?>">
                <span class="form-error" id="lerr-id"></span>
            </div>

            <!-- 비밀번호 -->
            <div class="form-group" id="lfg-password">
                <label class="form-label" for="loginPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="loginPw" name="password"
                       class="form-input" placeholder="비밀번호 입력"
                       autocomplete="current-password" maxlength="100">
                <span class="form-error" id="lerr-password"></span>
            </div>

            <!-- 체크박스 옵션 -->
            <div class="login-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="save_id" id="chkSaveId" value="1"
                           <?= $saved_id ? 'checked' : '' ?>>
                    <span class="checkbox-text">아이디 저장</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="keep_login" id="chkKeepLogin" value="1">
                    <span class="checkbox-text">상시 로그인</span>
                </label>
            </div>

            <!-- 전송 메시지 -->
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

            <!-- 아이디 -->
            <div class="form-group" id="fg-id">
                <label class="form-label" for="signupId">아이디 <span class="required">*</span></label>
                <input type="text" id="signupId" name="id"
                       class="form-input" placeholder="영문·숫자 4자 이상" autocomplete="username" maxlength="50">
                <span class="form-error" id="err-id"></span>
            </div>

            <!-- 비밀번호 -->
            <div class="form-group" id="fg-password">
                <label class="form-label" for="signupPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="signupPw" name="password"
                       class="form-input" placeholder="8자 이상 입력" autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password"></span>
            </div>

            <!-- 비밀번호 확인 -->
            <div class="form-group" id="fg-password_confirm">
                <label class="form-label" for="signupPwConfirm">비밀번호 확인 <span class="required">*</span></label>
                <input type="password" id="signupPwConfirm" name="password_confirm"
                       class="form-input" placeholder="비밀번호를 다시 입력" autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password_confirm"></span>
            </div>

            <!-- 이메일 -->
            <div class="form-group" id="fg-email">
                <label class="form-label" for="emailLocal">이메일 <span class="required">*</span></label>
                <div class="email-wrap">
                    <input type="text" id="emailLocal" class="form-input email-local"
                           placeholder="이메일 아이디" autocomplete="email">
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
                <!-- 실제 서버로 전송되는 완성된 이메일 값 -->
                <input type="hidden" name="email" id="emailFull">
                <span class="form-error" id="err-email"></span>
            </div>

            <!-- 휴대폰 -->
            <div class="form-group" id="fg-phone">
                <label class="form-label" for="signupPhone">휴대폰 번호</label>
                <input type="tel" id="signupPhone" name="phone"
                       class="form-input" placeholder="010-0000-0000" maxlength="13">
                <span class="form-error" id="err-phone"></span>
            </div>

            <!-- 전송 메시지 영역 -->
            <div class="form-msg" id="formMsg" style="display:none"></div>

            <button type="submit" class="btn-submit" id="btnSubmitSignup">가입하기</button>
        </form>

    </div>
</div>

<script src="/js/busan.js"></script>
</body>
</html>
