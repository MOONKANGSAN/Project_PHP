<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> — 부산온나 관리자</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/backoffice.css">
    <?php foreach ((array) ($extra_css ?? []) as $css): ?>
    <link rel="stylesheet" href="<?= esc($css) ?>">
    <?php endforeach; ?>
</head>
<body>
<div class="bo-wrapper">

    <!-- ===== 상단 내비게이션 ===== -->
    <header class="bo-topnav">
        <div class="bo-topnav-left">
            <a href="/backoffice" class="bo-logo">
                <span class="bo-logo-text">부산온나</span>
                <span class="bo-logo-badge">ADMIN</span>
            </a>
            <nav class="bo-topnav-menu">
                <a href="/backoffice/dashboard"
                   class="bo-topnav-link <?= $current_uri === '/backoffice/dashboard' ? 'active' : '' ?>">
                    대시보드
                </a>
                <a href="/backoffice/dashboard"
                   class="bo-topnav-link">
                    통계
                </a>
            </nav>
        </div>
        <div class="bo-topnav-right">
            <?php if ($admin['idx']): ?>
                <span class="bo-admin-info">
                    <span class="bo-admin-badge level-<?= $admin['level'] ?>">
                        <?= $admin['level'] === 2 ? '슈퍼관리자' : '일반관리자' ?>
                    </span>
                    <span class="bo-admin-id"><?= esc($admin['id']) ?></span>
                </span>
                <a href="/backoffice/logout" class="bo-btn-logout">로그아웃</a>
            <?php else: ?>
                <span class="bo-topnav-hint">관리자 로그인이 필요합니다</span>
            <?php endif; ?>
        </div>
    </header>

    <!-- ===== 좌측 사이드바 ===== -->
    <aside class="bo-sidebar">
        <div class="bo-sidebar-inner">

        <?php if (!$admin['idx']): ?>
            <!-- 로그인 전 메뉴 -->
            <ul class="bo-nav-list">
                <li>
                    <a href="/backoffice/login"
                       class="bo-nav-item <?= in_array($current_uri, ['/backoffice/login', '/backoffice']) ? 'active' : '' ?>">
                        <span class="bo-nav-icon">🔐</span>
                        <span>로그인</span>
                    </a>
                </li>
                <li>
                    <a href="/backoffice/add-admin"
                       class="bo-nav-item <?= $current_uri === '/backoffice/add-admin' ? 'active' : '' ?>">
                        <span class="bo-nav-icon">👤</span>
                        <span>관리자 추가</span>
                    </a>
                </li>
            </ul>

        <?php else: ?>
            <!-- 로그인 후 메뉴 -->

            <div class="bo-nav-group">
                <p class="bo-nav-group-title">주요서비스</p>
                <ul class="bo-nav-list">
                    <li>
                        <a href="/backoffice/restaurants"
                           class="bo-nav-item <?= $current_uri === '/backoffice/restaurants' ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🍽️</span>
                            <span>맛집 관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/spots"
                           class="bo-nav-item <?= $current_uri === '/backoffice/spots' ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🗺️</span>
                            <span>관광지 관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/festivals"
                           class="bo-nav-item <?= $current_uri === '/backoffice/festivals' ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🎉</span>
                            <span>주요행사 및 축제관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/region-explore"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/region-explore') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">📍</span>
                            <span>지역별 탐색</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/travel-courses"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/travel-courses') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🗓️</span>
                            <span>여행코스 관리</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bo-nav-group">
                <p class="bo-nav-group-title">회원관리</p>
                <ul class="bo-nav-list">
                    <li>
                        <a href="/backoffice/members"
                           class="bo-nav-item <?= $current_uri === '/backoffice/members' ? 'active' : '' ?>">
                            <span class="bo-nav-icon">👥</span>
                            <span>회원 정보 관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/withdrawn-members"
                           class="bo-nav-item <?= $current_uri === '/backoffice/withdrawn-members' ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🚪</span>
                            <span>탈퇴회원 관리</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bo-nav-group">
                <p class="bo-nav-group-title">고객서비스</p>
                <ul class="bo-nav-list">
                    <li>
                        <a href="/backoffice/notices"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/notices') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">📢</span>
                            <span>공지사항 관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/inquiries"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/inquiries') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">💬</span>
                            <span>고객문의</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/faqs"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/faqs') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">❓</span>
                            <span>FAQs 관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/trash"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/trash') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🗑️</span>
                            <span>휴지통</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bo-nav-group">
                <p class="bo-nav-group-title">사이트관리</p>
                <ul class="bo-nav-list">
                    <li>
                        <a href="/backoffice/banners"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/banners') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">🖼️</span>
                            <span>배너 관리</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/error-logs"
                           class="bo-nav-item <?= str_starts_with($current_uri, '/backoffice/error-logs') ? 'active' : '' ?>">
                            <span class="bo-nav-icon">⚠️</span>
                            <span>에러 로그</span>
                        </a>
                    </li>
                    <li>
                        <a href="/backoffice/site-config"
                           class="bo-nav-item <?= $current_uri === '/backoffice/site-config' ? 'active' : '' ?>">
                            <span class="bo-nav-icon">⚙️</span>
                            <span>헤더 및 Footer 수정</span>
                        </a>
                    </li>
                </ul>
            </div>

        <?php endif; ?>
        </div>
    </aside>

    <!-- ===== 메인 콘텐츠 시작 ===== -->
    <main class="bo-content">
        <div class="bo-content-inner">
