<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    $seoTitle = esc($event['title']) . ' | 부산온나 이벤트';
    $seoDesc  = !empty($event['sub_title'])
        ? esc($event['sub_title'])
        : esc(mb_substr(strip_tags($event['content'] ?? ''), 0, 120));

    $typeNum   = (int)($event['event_type'] ?? 4);
    $typeLabel = \App\Models\SiteEventModel::TYPES[$typeNum]  ?? '기타';
    $typeEmoji = \App\Models\SiteEventModel::TYPE_EMOJI[$typeNum] ?? '🎉';
    $typeColor = \App\Models\SiteEventModel::TYPE_COLOR[$typeNum] ?? '#fdcb6e';

    $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
    $statusColor = ['ongoing' => '#00b894', 'upcoming' => '#0984e3', 'ended' => '#b2bec3'];
    $evStatus    = $event['event_status'] ?? '';
    ?>

    <title><?= $seoTitle ?></title>
    <meta name="description" content="<?= $seoDesc ?>">
    <meta name="robots"      content="index, follow">
    <link rel="canonical"    href="https://busanonna.com/events/<?= (int)$event['idx'] ?>">

    <meta property="og:type"        content="website">
    <meta property="og:title"       content="<?= $seoTitle ?>">
    <meta property="og:description" content="<?= $seoDesc ?>">
    <meta property="og:url"         content="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:site_name"   content="부산온나">
    <?php if (!empty($event['thumb_url'])): ?>
    <meta property="og:image"       content="<?= esc($event['thumb_url']) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ===================== 기본 뷰 전용 스타일 ===================== */
        .dv-hero {
            background: linear-gradient(150deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            min-height: 420px;
            display: flex;
            align-items: center;
            padding: 80px 20px 70px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        /* 이벤트 유형 컬러로 상단 액센트 라인 */
        .dv-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: <?= $typeColor ?>;
        }
        .dv-hero-inner { position: relative; z-index: 1; width: 100%; }
        .dv-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: <?= $typeColor ?>33;
            border: 1px solid <?= $typeColor ?>88;
            color: <?= $typeColor ?>;
            border-radius: 20px;
            padding: 5px 18px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .dv-hero h1 {
            font-size: clamp(26px, 4.5vw, 48px);
            font-weight: 900;
            color: #fff;
            line-height: 1.25;
            margin: 0 0 14px;
        }
        .dv-hero-sub {
            font-size: 16px;
            color: rgba(255,255,255,0.75);
            margin-bottom: 28px;
            line-height: 1.65;
        }
        .dv-meta-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .dv-period-pill {
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 8px 18px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }
        .dv-status-badge {
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            background: <?= $evStatus ? ($statusColor[$evStatus] ?? '#888') : 'transparent' ?>;
        }

        /* 본문 래퍼 */
        .dv-body {
            max-width: 820px;
            margin: 0 auto;
            padding: 60px 20px 80px;
        }

        /* 대표 이미지 */
        .dv-thumb {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 48px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }
        .dv-thumb img { width: 100%; height: auto; display: block; }

        /* 이벤트 내용 */
        .dv-content-box {
            background: #fff;
            border-radius: 16px;
            padding: 44px 48px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            margin-bottom: 40px;
        }
        .dv-content-box .dv-content-body {
            font-size: 16px;
            line-height: 2;
            color: #333;
            white-space: pre-line;
        }

        /* 이벤트 기간 인포 박스 */
        .dv-info-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: <?= $typeColor ?>11;
            border-left: 4px solid <?= $typeColor ?>;
            border-radius: 0 10px 10px 0;
            padding: 16px 20px;
            margin-bottom: 32px;
            font-size: 15px;
            color: #333;
        }
        .dv-info-box .label {
            font-weight: 700;
            color: <?= $typeColor ?>;
            white-space: nowrap;
        }

        /* CTA 버튼 */
        .dv-cta {
            text-align: center;
            padding: 48px 20px;
            background: #f8faff;
            border-radius: 16px;
            margin-bottom: 32px;
        }
        .dv-cta-btn {
            display: inline-block;
            padding: 15px 40px;
            background: <?= $typeColor ?>;
            color: #fff;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 16px <?= $typeColor ?>66;
        }
        .dv-cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px <?= $typeColor ?>88;
        }
        /* CTA 버튼 글씨가 배지 색 대비 어두운 경우 대비 */
        .dv-cta-btn.dark-text { color: #1a1a1a; }

        .dv-back-link {
            display: inline-block;
            color: #6b7280;
            font-size: 14px;
            text-decoration: none;
        }
        .dv-back-link:hover { color: #374151; }

        @media (max-width: 600px) {
            .dv-content-box { padding: 28px 22px; }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'events']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="dv-hero">
    <div class="dv-hero-inner container">
        <div class="dv-type-badge"><?= $typeEmoji ?> <?= esc($typeLabel) ?></div>
        <h1><?= esc($event['title']) ?></h1>
        <?php if (!empty($event['sub_title'])): ?>
        <p class="dv-hero-sub"><?= esc($event['sub_title']) ?></p>
        <?php endif; ?>
        <div class="dv-meta-row">
            <?php if (!empty($event['start_date'])): ?>
            <span class="dv-period-pill">
                📅 <?= esc($event['start_date']) ?>
                <?= !empty($event['end_date']) ? ' ~ ' . esc($event['end_date']) : '' ?>
            </span>
            <?php endif; ?>
            <?php if ($evStatus): ?>
            <span class="dv-status-badge"><?= $statusLabel[$evStatus] ?? '' ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===================== 본문 ===================== -->
<div class="dv-body">

    <!-- 대표 이미지 -->
    <?php if (!empty($event['thumb_url'])): ?>
    <div class="dv-thumb">
        <img src="<?= esc($event['thumb_url']) ?>" alt="<?= esc($event['title']) ?>">
    </div>
    <?php endif; ?>

    <!-- 기간 인포 박스 -->
    <?php if (!empty($event['start_date'])): ?>
    <div class="dv-info-box">
        <span class="label">📅 이벤트 기간</span>
        <span>
            <?= esc($event['start_date']) ?>
            <?= !empty($event['end_date']) ? ' ~ ' . esc($event['end_date']) : '' ?>
            <?php if ($evStatus): ?>
            &nbsp;·&nbsp;
            <strong style="color:<?= $statusColor[$evStatus] ?? '#888' ?>;">
                <?= $statusLabel[$evStatus] ?>
            </strong>
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- 이벤트 내용 -->
    <?php if (!empty($event['content'])): ?>
    <div class="dv-content-box">
        <div class="dv-content-body"><?= esc($event['content']) ?></div>
    </div>
    <?php endif; ?>

    <!-- CTA 버튼 -->
    <?php if (!empty($event['cta_text']) && !empty($event['cta_url'])): ?>
    <div class="dv-cta">
        <a href="<?= esc($event['cta_url']) ?>" class="dv-cta-btn">
            <?= esc($event['cta_text']) ?>
        </a>
    </div>
    <?php endif; ?>

    <a href="/events" class="dv-back-link">← 이벤트 목록으로</a>

</div>

<?= view('service/partials/footer') ?>
<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>
<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
</body>
</html>
