<!-- ===================== 서비스 공용 헤더 ===================== -->
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">
            <a href="/" class="logo">
                <span class="logo-main">부산온나</span>
                <span class="logo-sub">BUSAN ONNA</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="/spots"<?= ($activeNav ?? '') === 'spots' ? ' class="active"' : '' ?>>관광지</a></li>
                    <li><a href="/restaurants"<?= ($activeNav ?? '') === 'restaurants' ? ' class="active"' : '' ?>>맛집</a></li>
                    <li><a href="/festivals"<?= ($activeNav ?? '') === 'festivals' ? ' class="active"' : '' ?>>축제</a></li>
                    <li><a href="/travel-courses"<?= ($activeNav ?? '') === 'travel-courses' ? ' class="active"' : '' ?>>여행코스</a></li>
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
