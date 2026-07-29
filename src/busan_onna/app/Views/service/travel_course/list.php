<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>여행코스 - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
    <style>
        /* 여행코스 카드 전용 스타일 */
        .course-card {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            transition: transform .2s, box-shadow .2s;
            text-decoration: none;
            color: inherit;
        }
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0,0,0,.13);
        }
        .course-card-thumb {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: #f1f5f9;
        }
        .course-card-thumb img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform .3s;
        }
        .course-card:hover .course-card-thumb img {
            transform: scale(1.04);
        }
        .course-card-thumb-default {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-size: 48px;
            background: linear-gradient(135deg, #667eea22, #764ba222);
        }
        .course-card-sido {
            position: absolute;
            top: 12px; left: 12px;
            background: rgba(37,99,235,.85);
            color: #fff;
            font-size: 12px; font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }
        .course-card-count {
            position: absolute;
            top: 12px; right: 12px;
            background: rgba(0,0,0,.55);
            color: #fff;
            font-size: 12px; font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }
        .course-card-body {
            padding: 18px 20px 20px;
            display: flex; flex-direction: column; gap: 8px;
            flex: 1;
        }
        .course-card-title {
            font-size: 16px; font-weight: 700;
            color: #1e293b;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .course-card-desc {
            font-size: 13px; color: #64748b;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .course-card-footer {
            display: flex; align-items: center; gap: 6px;
            flex-wrap: wrap;
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
        }
        .course-step-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px; font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* 필터 지역 칩 */
        .sido-chips {
            display: flex; gap: 8px; flex-wrap: wrap;
            margin-top: 12px;
        }
        .sido-chip {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 13px; font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            background: #f1f5f9;
            color: #475569;
            transition: background .15s, color .15s;
        }
        .sido-chip:hover, .sido-chip.active {
            background: #2563eb;
            color: #fff;
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'travel-courses']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero page-hero--course" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);">
    <div class="container">
        <h1>🗓️ 부산 여행코스</h1>
        <p>전문가가 큐레이션한 부산 맞춤 코스로 완벽한 여행을 계획하세요</p>
    </div>
</section>

<!-- ===================== 필터 ===================== -->
<div class="filter-section">
    <div class="container">
        <form class="filter-bar" method="get" action="/travel-courses" id="filterForm">
            <div class="filter-search">
                <span class="search-icon">🔍</span>
                <input type="text" name="q"
                       placeholder="코스명으로 검색"
                       value="<?= esc($activeSearch) ?>"
                       autocomplete="off">
            </div>
            <button type="submit" class="filter-submit-btn">검색</button>
            <?php if ($activeSearch || $activeSido): ?>
            <a href="/travel-courses" class="filter-reset-btn">초기화</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($sidoList)): ?>
        <div class="sido-chips">
            <a href="/travel-courses<?= $activeSearch ? '?q='.urlencode($activeSearch) : '' ?>"
               class="sido-chip <?= $activeSido === '' ? 'active' : '' ?>">전체</a>
            <?php foreach ($sidoList as $s): ?>
            <a href="/travel-courses?sido=<?= urlencode($s) ?><?= $activeSearch ? '&q='.urlencode($activeSearch) : '' ?>"
               class="sido-chip <?= $activeSido === $s ? 'active' : '' ?>">
                <?= esc($s) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================== 목록 ===================== -->
<section class="restaurant-section">
    <div class="container">
        <div class="view-controls">
            <p class="result-count">
                총 <strong><?= $totalCount ?></strong>개의 여행코스
                <?php if ($totalCount > 0):
                    $perPage  = 9;
                    $currPage = (int)(service('request')->getGet('page') ?? 1);
                    $from     = ($currPage - 1) * $perPage + 1;
                    $to       = min($currPage * $perPage, $totalCount);
                ?>
                <span class="result-range">(<?= $from ?>–<?= $to ?>번째)</span>
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($courses)): ?>
        <div class="empty-result">
            <div class="empty-result-icon">🗓️</div>
            <h3>등록된 여행코스가 없습니다</h3>
            <p>다른 검색어나 지역을 선택해보세요</p>
        </div>
        <?php else: ?>

        <div class="restaurant-grid">
            <?php foreach ($courses as $c): ?>
            <a class="course-card" href="/travel-courses/<?= (int)$c['idx'] ?>">
                <div class="course-card-thumb">
                    <?php if (!empty($c['thumb_url'])): ?>
                        <img src="<?= esc($c['thumb_url']) ?>" alt="<?= esc($c['title']) ?>"
                             onerror="this.onerror=null; this.src='/img/no-image.svg';">
                    <?php else: ?>
                        <div class="course-card-thumb-default">🗓️</div>
                    <?php endif; ?>
                    <?php if (!empty($c['sido'])): ?>
                    <span class="course-card-sido">📍 <?= esc($c['sido']) ?></span>
                    <?php endif; ?>
                    <span class="course-card-count">📋 <?= (int)$c['item_count'] ?>곳</span>
                </div>
                <div class="course-card-body">
                    <h3 class="course-card-title"><?= esc($c['title']) ?></h3>
                    <?php if (!empty($c['description'])): ?>
                    <p class="course-card-desc"><?= esc($c['description']) ?></p>
                    <?php endif; ?>
                    <div class="course-card-footer">
                        <span class="course-step-badge">
                            🚩 <?= (int)$c['item_count'] ?>개 장소
                        </span>
                        <span style="font-size:12px;color:#94a3b8;margin-left:auto;">
                            <?= substr($c['reg_date'], 0, 10) ?>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($pager->getPageCount() > 1): ?>
        <div class="pager-wrap">
            <?= $pager->links('default', 'service_pager') ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>

<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>
</body>
</html>
