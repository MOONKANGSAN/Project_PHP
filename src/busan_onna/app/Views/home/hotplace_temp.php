<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $district ? esc($district) . ' 핫플레이스' : '지역별 핫플레이스' ?> — 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
</head>
<body>

<!-- 헤더 -->
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
                    <li><a href="#">여행코스</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- 임시 준비 중 콘텐츠 -->
<main>
    <div class="container">
        <div class="coming-wrap">
            <span class="coming-icon">📍</span>
            <?php if ($district): ?>
                <span class="coming-district"><?= esc($district) ?></span>
            <?php endif; ?>
            <h1 class="coming-title">지역별 핫플레이스</h1>
            <p class="coming-desc">
                <?= $district ? esc($district) . ' 지역의' : '각 지역별' ?> 핫플레이스 페이지를<br>
                현재 준비 중입니다. 곧 만나보실 수 있습니다!
            </p>
            <a href="/" class="coming-back">← 메인으로 돌아가기</a>
        </div>
    </div>
</main>

<script src="/js/busan.js"></script>
</body>
</html>
