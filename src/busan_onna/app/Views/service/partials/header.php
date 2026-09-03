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
                    <!-- 프로필 아이콘: 이미지가 있으면 사진, 없으면 기본 SVG 아이콘 -->
                    <a href="/mypage"
                       class="btn-profile-icon<?= ($activeNav ?? '') === 'mypage' ? ' active' : '' ?>"
                       aria-label="마이페이지">
                        <?php if (session()->get('user.profile_image')): ?>
                            <img src="/uploads/profile/<?= esc(session()->get('user.profile_image')) ?>"
                                 alt="프로필" class="profile-icon-img">
                        <?php else: ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        <?php endif; ?>
                    </a>
                    <a href="/auth/logout" class="btn-auth logout">로그아웃</a>
                <?php else: ?>
                    <button type="button" class="btn-auth login" id="btnOpenLogin">로그인</button>
                    <button type="button" class="btn-auth signup" id="btnOpenSignup">회원가입</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<style>
    /* ---- 헤더 프로필 아이콘 ---- */
    .btn-profile-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #dee2e6;
        background: #f8f9fa;
        color: #868e96;
        text-decoration: none;
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
        flex-shrink: 0;
    }
    .btn-profile-icon:hover,
    .btn-profile-icon.active {
        border-color: #e55039;
        box-shadow: 0 0 0 3px rgba(229, 80, 57, 0.15);
        color: #e55039;
    }

    /* 프로필 사진이 있을 때: 이미지가 원형 영역을 꽉 채우도록 */
    .profile-icon-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
</style>
