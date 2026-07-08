<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $district ? esc($district) . ' 핫플레이스' : '지역별 핫플레이스' ?> — 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <style>
        .coming-wrap {
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 80px 20px;
            text-align: center;
        }
        .coming-icon { font-size: 64px; }
        .coming-title {
            font-size: 28px;
            font-weight: 800;
            color: #111;
        }
        .coming-district {
            display: inline-block;
            background: #eff6ff;
            color: #3b82f6;
            font-size: 16px;
            font-weight: 700;
            padding: 6px 20px;
            border-radius: 999px;
            margin-bottom: 4px;
        }
        .coming-desc {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.7;
        }
        .coming-back {
            margin-top: 10px;
            padding: 12px 32px;
            background: #111;
            color: #fff;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }
        .coming-back:hover { background: #374151; }
    </style>
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
