<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이 맛이 부산이다 | 부산온나 이벤트</title>
    <meta name="description" content="부산 대표 음식 카테고리별 맛집 투표! 좋아요로 최애 맛집을 1위로 만들고 특별 혜택을 받아가세요.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="이 맛이 부산이다 | 부산온나 이벤트">
    <meta property="og:description" content="부산 대표 음식 카테고리별 맛집 투표! 좋아요로 최애 맛집을 1위로 만들고 특별 혜택을 받아가세요.">
    <meta property="og:url"         content="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:site_name"   content="부산온나">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
    <style>
        /* ===================== 이 맛이 부산이다 전용 스타일 ===================== */

        /* 히어로 */
        .m-hero {
            background: linear-gradient(160deg, #c0392b 0%, #e74c3c 40%, #e67e22 100%);
            min-height: 480px;
            display: flex;
            align-items: center;
            text-align: center;
            padding: 80px 20px 70px;
            position: relative;
            overflow: hidden;
        }
        .m-hero::after {
            content: '🍜🍖🍡🐟🍢🦀';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 60px;
            opacity: 0.12;
            letter-spacing: 8px;
            white-space: nowrap;
        }
        .m-hero-inner { position: relative; z-index: 1; width: 100%; }
        .m-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.5);
            color: #fff;
            border-radius: 24px;
            padding: 5px 18px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .m-hero h1 {
            font-size: clamp(30px, 5vw, 54px);
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            margin: 0 0 14px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.25);
        }
        .m-hero h1 em { font-style: normal; color: #ffe082; }
        .m-hero-sub {
            color: rgba(255,255,255,0.88);
            font-size: 16px;
            margin-bottom: 28px;
            line-height: 1.65;
        }
        .m-period-row {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .m-period-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.18);
            border-radius: 8px;
            padding: 9px 20px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }
        .m-status-badge {
            background: #00b894;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        /* 공통 */
        .m-section { padding: 72px 0; }
        .m-section:nth-child(even) { background: #fff9f5; }
        .m-section-title {
            text-align: center;
            font-size: clamp(20px, 3vw, 28px);
            font-weight: 900;
            color: #2d1a0e;
            margin-bottom: 10px;
        }
        .m-section-title span { color: #c0392b; }
        .m-section-desc {
            text-align: center;
            color: #777;
            font-size: 15px;
            margin-bottom: 48px;
            line-height: 1.7;
        }

        /* 이번 주 카테고리 배너 */
        .m-weekly {
            max-width: 760px;
            margin: 0 auto;
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            border-radius: 20px;
            padding: 40px 44px;
            display: flex;
            align-items: center;
            gap: 32px;
            box-shadow: 0 6px 28px rgba(224,87,45,0.15);
        }
        .m-weekly-icon { font-size: 72px; flex-shrink: 0; }
        .m-weekly-body {}
        .m-weekly-badge {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            border-radius: 6px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .m-weekly-body h3 {
            font-size: clamp(20px, 3vw, 28px);
            font-weight: 900;
            color: #2d1a0e;
            margin-bottom: 8px;
        }
        .m-weekly-body p { font-size: 14px; color: #666; line-height: 1.6; }

        /* 투표 방식 */
        .m-vote-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
            max-width: 920px;
            margin: 0 auto;
        }
        .m-vote-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px 24px;
            text-align: center;
            box-shadow: 0 4px 18px rgba(0,0,0,0.07);
            border-top: 4px solid #e74c3c;
        }
        .m-vote-card:nth-child(2) { border-top-color: #e67e22; }
        .m-vote-card:nth-child(3) { border-top-color: #f39c12; }
        .m-vote-card:nth-child(4) { border-top-color: #c0392b; }
        .m-vote-icon { font-size: 38px; margin-bottom: 12px; }
        .m-vote-card h3 { font-size: 16px; font-weight: 700; color: #2d1a0e; margin-bottom: 8px; }
        .m-vote-card p  { font-size: 13px; color: #777; line-height: 1.6; }

        /* 라운드 일정 */
        .m-schedule {
            max-width: 760px;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .m-schedule-row {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            align-items: center;
            padding: 18px 28px;
            gap: 16px;
            background: #fff;
            border-bottom: 1px solid #f0e8e0;
        }
        .m-schedule-row:first-child {
            background: #e74c3c;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }
        .m-schedule-row:last-child { border-bottom: none; }
        .m-round-badge {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .m-round-badge.r2 { background: #e67e22; }
        .m-round-badge.r3 { background: #c0392b; }
        .m-schedule-period { font-size: 14px; color: #555; }
        .m-schedule-cat {
            font-size: 14px;
            font-weight: 700;
            color: #2d1a0e;
            background: #fff3e0;
            border-radius: 20px;
            padding: 4px 14px;
        }

        /* 경품 */
        .m-prize-banner {
            max-width: 820px;
            margin: 0 auto;
            background: linear-gradient(120deg, #2d1a0e, #4a2000);
            border-radius: 20px;
            padding: 44px 40px;
            color: #fff;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }
        .m-prize-left h3 { font-size: 22px; font-weight: 900; margin-bottom: 10px; color: #ffe082; }
        .m-prize-left p { font-size: 14px; color: rgba(255,255,255,0.7); line-height: 1.7; }
        .m-prize-right { display: flex; flex-direction: column; gap: 14px; }
        .m-prize-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 12px 16px;
        }
        .m-prize-item .icon { font-size: 24px; }
        .m-prize-item .text { font-size: 14px; line-height: 1.4; }
        .m-prize-item .text strong { display: block; font-size: 16px; color: #ffe082; }

        /* 주의사항 */
        .m-notes {
            max-width: 720px;
            margin: 0 auto;
            background: #fff5f5;
            border-radius: 12px;
            padding: 28px 32px;
            border-left: 4px solid #e74c3c;
        }
        .m-notes h4 { font-size: 15px; font-weight: 700; color: #c0392b; margin-bottom: 12px; }
        .m-notes ul { margin: 0; padding-left: 18px; }
        .m-notes li  { font-size: 14px; color: #555; line-height: 1.8; }

        /* CTA */
        .m-cta {
            background: linear-gradient(135deg, #c0392b, #e67e22);
            padding: 72px 20px;
            text-align: center;
        }
        .m-cta h2 { font-size: clamp(20px, 3vw, 30px); font-weight: 900; color: #fff; margin-bottom: 10px; }
        .m-cta p  { color: rgba(255,255,255,0.85); margin-bottom: 30px; }
        .m-cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .m-btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: transform 0.15s;
        }
        .m-btn:hover { transform: translateY(-2px); }
        .m-btn-primary { background: #fff; color: #c0392b; }
        .m-btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,0.6); }

        @media (max-width: 680px) {
            .m-weekly { flex-direction: column; text-align: center; padding: 28px 24px; }
            .m-prize-banner { grid-template-columns: 1fr; }
            .m-schedule-row { grid-template-columns: 1fr; gap: 6px; }
            .m-schedule-row:first-child { display: none; }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'events']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="m-hero">
    <div class="m-hero-inner container">
        <div class="m-hero-tag">🗳️ 맛집 투표 이벤트</div>
        <h1>이 맛이 <em>부산이다</em> 🍜</h1>
        <p class="m-hero-sub">
            부산 대표 음식 카테고리별 최고 맛집을 직접 뽑아보세요<br>
            2주마다 카테고리가 바뀌는 실시간 순위전!
        </p>
        <div class="m-period-row">
            <span class="m-period-pill">📅 2026.09.01 ~ 2026.09.28 (4주)</span>
            <?php
            $status = $event['event_status'] ?? '';
            $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
            if ($status): ?>
            <span class="m-period-pill"><span class="m-status-badge"><?= $statusLabel[$status] ?? '' ?></span></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===================== 이번 주 카테고리 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">🔥 현재 <span>진행 중</span>인 라운드</h2>
        <p class="m-section-desc">이번 2주는 이 카테고리로 투표가 진행됩니다</p>
        <div class="m-weekly">
            <div class="m-weekly-icon">🍖</div>
            <div class="m-weekly-body">
                <span class="m-weekly-badge">1라운드 진행중</span>
                <h3>돼지국밥 주간</h3>
                <p>
                    부산의 소울푸드 돼지국밥! 부산온나 맛집 중 최고의 돼지국밥집을 좋아요로 뽑아보세요.<br>
                    2주 후 1위 맛집은 메인 배너에 등극합니다.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 투표 방식 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">❤️ 투표 <span>방법</span></h2>
        <p class="m-section-desc">로그인 후 좋아요 한 번이면 투표 완료!</p>
        <div class="m-vote-grid">
            <div class="m-vote-card">
                <div class="m-vote-icon">🔑</div>
                <h3>로그인</h3>
                <p>부산온나 회원이라면 누구나 투표에 참여할 수 있어요.</p>
            </div>
            <div class="m-vote-card">
                <div class="m-vote-icon">🍽️</div>
                <h3>맛집 확인</h3>
                <p>이번 라운드 카테고리에 해당하는 부산온나 맛집을 찾아보세요.</p>
            </div>
            <div class="m-vote-card">
                <div class="m-vote-icon">👍</div>
                <h3>좋아요 클릭</h3>
                <p>마음에 드는 맛집 상세 페이지에서 좋아요를 눌러주세요. 1인 1일 1표!</p>
            </div>
            <div class="m-vote-card">
                <div class="m-vote-icon">👑</div>
                <h3>결과 확인</h3>
                <p>라운드 종료 후 1위 맛집은 메인 배너에 2주간 노출됩니다.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 라운드 일정 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">📅 라운드 <span>일정</span></h2>
        <p class="m-section-desc">총 4라운드, 각 2주씩 진행됩니다</p>
        <div class="m-schedule">
            <div class="m-schedule-row">
                <span>라운드</span>
                <span>기간</span>
                <span>카테고리</span>
            </div>
            <div class="m-schedule-row">
                <span class="m-round-badge">1라운드</span>
                <span class="m-schedule-period">09.01 ~ 09.14</span>
                <span class="m-schedule-cat">🍖 돼지국밥</span>
            </div>
            <div class="m-schedule-row">
                <span class="m-round-badge r2">2라운드</span>
                <span class="m-schedule-period">09.15 ~ 09.28</span>
                <span class="m-schedule-cat">🍜 밀면</span>
            </div>
            <div class="m-schedule-row">
                <span class="m-round-badge r3">3라운드</span>
                <span class="m-schedule-period">10.01 ~ 10.14</span>
                <span class="m-schedule-cat">🌰 씨앗호떡</span>
            </div>
            <div class="m-schedule-row">
                <span class="m-round-badge" style="background:#7f8c8d;">4라운드</span>
                <span class="m-schedule-period">10.15 ~ 10.28</span>
                <span class="m-schedule-cat">🐟 회·초밥</span>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 경품 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">🎁 <span>혜택</span> 안내</h2>
        <p class="m-section-desc">투표에 참여하면 특별 혜택이 따라옵니다</p>
        <div class="m-prize-banner">
            <div class="m-prize-left">
                <h3>라운드별 특별 혜택</h3>
                <p>
                    각 라운드에서 1위를 차지한 맛집은<br>
                    부산온나 메인 배너에 2주간 무료 노출됩니다.<br><br>
                    투표에 참여한 회원 중 추첨을 통해<br>
                    해당 맛집 할인 쿠폰을 지급해드립니다.
                </p>
            </div>
            <div class="m-prize-right">
                <div class="m-prize-item">
                    <span class="icon">👑</span>
                    <span class="text">
                        <strong>1위 맛집 특전</strong>
                        메인 배너 2주 무료 노출
                    </span>
                </div>
                <div class="m-prize-item">
                    <span class="icon">🎫</span>
                    <span class="text">
                        <strong>투표 참여자 경품</strong>
                        해당 맛집 30% 할인 쿠폰 (선착순 100명)
                    </span>
                </div>
                <div class="m-prize-item">
                    <span class="icon">🏆</span>
                    <span class="text">
                        <strong>전라운드 참여 보너스</strong>
                        4라운드 모두 참여 시 추첨 2회 기회
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 주의사항 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">⚠️ 유의 <span>사항</span></h2>
        <p class="m-section-desc"></p>
        <div class="m-notes">
            <h4>📌 유의사항</h4>
            <ul>
                <li>투표는 로그인한 회원만 가능하며, 1인 1일 1표입니다.</li>
                <li>비정상적인 다중 계정 투표는 무효 처리되며 이용이 제한될 수 있습니다.</li>
                <li>할인 쿠폰은 해당 맛집 방문 시 제시해야 하며, 타인 양도가 불가합니다.</li>
                <li>이벤트 일정 및 카테고리는 운영 상황에 따라 변경될 수 있습니다.</li>
                <li>쿠폰 유효기간은 발급일로부터 30일입니다.</li>
            </ul>
        </div>
    </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="m-cta">
    <h2>내가 뽑는 부산 최고의 맛집! 🍽️</h2>
    <p>지금 바로 투표에 참여하고 할인 쿠폰도 받아가세요.</p>
    <div class="m-cta-btns">
        <a href="/restaurants" class="m-btn m-btn-primary">🍖 맛집 보러 가기</a>
        <a href="/events" class="m-btn m-btn-outline">← 이벤트 목록</a>
    </div>
</section>

<?= view('service/partials/footer') ?>
<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>
<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
</body>
</html>
