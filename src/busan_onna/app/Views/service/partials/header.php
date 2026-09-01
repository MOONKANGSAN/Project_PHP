<!-- ===================== 서비스 공용 헤더 ===================== -->
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">
            <a href="/" class="logo" title="부산온나 홈으로 이동" aria-label="부산온나 홈">
                <span class="logo-main">부산온나</span>
                <span class="logo-sub">BUSAN ONNA</span>
            </a>
            <nav class="main-nav" aria-label="주요 메뉴">
                <ul>
                    <li><a href="/spots"<?= ($activeNav ?? '') === 'spots' ? ' class="active"' : '' ?>>관광지</a></li>
                    <li><a href="/restaurants"<?= ($activeNav ?? '') === 'restaurants' ? ' class="active"' : '' ?>>맛집</a></li>
                    <li><a href="/festivals"<?= ($activeNav ?? '') === 'festivals' ? ' class="active"' : '' ?>>축제</a></li>
                    <li><a href="/travel-courses"<?= ($activeNav ?? '') === 'travel-courses' ? ' class="active"' : '' ?>>여행코스</a></li>
                    <li><a href="/events"<?= ($activeNav ?? '') === 'events' ? ' class="active"' : '' ?>>이벤트</a></li>
                    <li><a href="/goods"<?= ($activeNav ?? '') === 'goods' ? ' class="active"' : '' ?>>부산굿즈</a></li>
                </ul>
            </nav>
            <div class="header-auth">
                <a href="/cart" class="btn-cart-icon" aria-label="장바구니">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9"  cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </a>
                <?php if (session()->get('user.idx')): ?>
                    <a href="/auth/logout" class="btn-auth logout">로그아웃</a>
                <?php else: ?>
                    <button type="button" class="btn-auth login" id="btnOpenLogin">로그인</button>
                    <button type="button" class="btn-auth signup" id="btnOpenSignup">회원가입</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
