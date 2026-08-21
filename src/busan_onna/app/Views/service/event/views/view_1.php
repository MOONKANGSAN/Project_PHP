<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>부산 골목 탐험단 | 부산온나 이벤트</title>
    <meta name="description" content="부산의 숨겨진 골목을 직접 탐험하고 후기를 남기면 추첨을 통해 부산 로컬 식당 상품권을 드립니다.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="부산 골목 탐험단 | 부산온나 이벤트">
    <meta property="og:description" content="부산의 숨겨진 골목을 직접 탐험하고 후기를 남기면 추첨을 통해 부산 로컬 식당 상품권을 드립니다.">
    <meta property="og:url"         content="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:site_name"   content="부산온나">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
    <style>
        /* ===================== 부산 골목 탐험단 전용 스타일 ===================== */

        /* 히어로 */
        .g-hero {
            position: relative;
            min-height: 520px;
            background: linear-gradient(145deg, #1a2a4a 0%, #0d3b6e 50%, #1565c0 100%);
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 80px 0 60px;
        }
        .g-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .g-hero-inner {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
            padding: 0 20px;
            width: 100%;
        }
        .g-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,165,0,0.25);
            border: 1px solid rgba(255,165,0,0.6);
            color: #ffd080;
            border-radius: 24px;
            padding: 5px 18px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 22px;
        }
        .g-hero h1 {
            font-size: clamp(28px, 5vw, 52px);
            font-weight: 900;
            line-height: 1.2;
            margin: 0 0 16px;
            text-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        .g-hero h1 span { color: #ffc107; }
        .g-hero-sub {
            font-size: clamp(14px, 2vw, 17px);
            opacity: 0.85;
            margin-bottom: 28px;
            line-height: 1.6;
        }
        .g-hero-period {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 15px;
            font-weight: 600;
        }
        .g-status-badge {
            display: inline-block;
            margin-left: 10px;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            background: #00b894;
        }

        /* 공통 섹션 */
        .g-section { padding: 72px 0; }
        .g-section:nth-child(even) { background: #f8f9ff; }
        .g-section-title {
            text-align: center;
            font-size: clamp(20px, 3vw, 28px);
            font-weight: 900;
            color: #1a2a4a;
            margin-bottom: 12px;
        }
        .g-section-title span { color: #1565c0; }
        .g-section-desc {
            text-align: center;
            color: #666;
            font-size: 15px;
            margin-bottom: 52px;
            line-height: 1.7;
        }

        /* 이벤트 소개 */
        .g-intro-box {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            padding: 44px 48px;
            box-shadow: 0 4px 24px rgba(21,101,192,0.1);
            border-left: 5px solid #1565c0;
            font-size: 16px;
            line-height: 1.9;
            color: #333;
        }
        .g-hashtag {
            display: inline-block;
            margin-top: 20px;
            background: #e3f2fd;
            color: #1565c0;
            border-radius: 6px;
            padding: 4px 12px;
            font-weight: 700;
            font-size: 15px;
        }

        /* 참여 방법 스텝 */
        .g-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            max-width: 900px;
            margin: 0 auto;
        }
        .g-step {
            background: #fff;
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            text-align: center;
            position: relative;
        }
        .g-step-num {
            width: 44px;
            height: 44px;
            background: #1565c0;
            color: #fff;
            border-radius: 50%;
            font-size: 18px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .g-step-icon { font-size: 36px; margin-bottom: 12px; }
        .g-step h3 { font-size: 16px; font-weight: 700; color: #1a2a4a; margin-bottom: 8px; }
        .g-step p  { font-size: 14px; color: #666; line-height: 1.6; }

        /* 경품 */
        .g-prizes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 860px;
            margin: 0 auto;
        }
        .g-prize {
            background: #fff;
            border-radius: 16px;
            padding: 28px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            border-top: 4px solid #1565c0;
        }
        .g-prize:first-child { border-top-color: #ffc107; }
        .g-prize-icon { font-size: 40px; margin-bottom: 12px; }
        .g-prize h4 { font-size: 14px; color: #888; margin-bottom: 6px; }
        .g-prize p  { font-size: 18px; font-weight: 800; color: #1a2a4a; }
        .g-prize small { font-size: 13px; color: #999; display: block; margin-top: 4px; }

        /* 주의사항 */
        .g-notes {
            max-width: 720px;
            margin: 0 auto;
            background: #fff8e1;
            border-radius: 12px;
            padding: 28px 32px;
            border-left: 4px solid #ffc107;
        }
        .g-notes h4 { font-size: 15px; font-weight: 700; color: #f57f17; margin-bottom: 12px; }
        .g-notes ul { margin: 0; padding-left: 18px; }
        .g-notes li { font-size: 14px; color: #555; line-height: 1.8; }

        /* CTA */
        .g-cta {
            text-align: center;
            padding: 72px 20px;
            background: linear-gradient(135deg, #1565c0, #1a237e);
        }
        .g-cta h2 { font-size: clamp(20px, 3vw, 30px); font-weight: 900; color: #fff; margin-bottom: 12px; }
        .g-cta p  { color: rgba(255,255,255,0.8); margin-bottom: 32px; font-size: 15px; }
        .g-cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .g-btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .g-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
        .g-btn-primary { background: #ffc107; color: #1a2a4a; }
        .g-btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,0.6); }

        @media (max-width: 600px) {
            .g-intro-box { padding: 28px 24px; }
            .g-notes { padding: 20px 20px; }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'events']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="g-hero">
    <div class="g-hero-inner container">
        <div class="g-hero-tag">📍 방문 인증 이벤트</div>
        <h1>부산 <span>골목 탐험단</span></h1>
        <p class="g-hero-sub">
            부산의 숨겨진 골목을 직접 걷고, 후기를 남기면<br>
            추첨을 통해 특별한 선물을 드립니다
        </p>
        <div class="g-hero-period">
            📅 2026.08.21 ~ 2026.09.20
            <?php
            $today = date('Y-m-d');
            $status = $event['event_status'] ?? '';
            $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
            if ($status): ?>
            <span class="g-status-badge"><?= $statusLabel[$status] ?? '' ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===================== 이벤트 소개 ===================== -->
<section class="g-section">
    <div class="container">
        <h2 class="g-section-title">🗺️ <span>이벤트</span> 소개</h2>
        <p class="g-section-desc">부산에는 아직 알려지지 않은 보석 같은 골목이 가득합니다</p>
        <div class="g-intro-box">
            부산온나에 등록된 관광지·맛집 중 <strong>'숨은 명소'</strong> 태그가 붙은 장소를 직접 방문하고,
            진솔한 후기와 함께 지정 해시태그를 달아 좋아요를 5개 이상 받으면 자동으로 경품 추첨에 응모됩니다.<br><br>
            부산의 좁은 골목, 오래된 계단, 동네 카페… 당신만이 아는 부산의 이야기를 부산온나와 함께 기록해보세요.
            <br>
            <span class="g-hashtag">#부산골목탐험</span>
        </div>
    </div>
</section>

<!-- ===================== 참여 방법 ===================== -->
<section class="g-section">
    <div class="container">
        <h2 class="g-section-title">✅ 참여 <span>방법</span></h2>
        <p class="g-section-desc">3단계로 간단하게 참여할 수 있어요</p>
        <div class="g-steps">
            <div class="g-step">
                <div class="g-step-num">1</div>
                <div class="g-step-icon">🔍</div>
                <h3>숨은 명소 찾기</h3>
                <p>부산온나 관광지·맛집 페이지에서 '숨은 명소' 태그가 붙은 장소를 골라보세요.</p>
            </div>
            <div class="g-step">
                <div class="g-step-num">2</div>
                <div class="g-step-icon">📸</div>
                <h3>방문 후 후기 작성</h3>
                <p>직접 방문하고 사진과 함께 솔직한 후기를 남겨주세요. 반드시 <strong>#부산골목탐험</strong> 포함!</p>
            </div>
            <div class="g-step">
                <div class="g-step-num">3</div>
                <div class="g-step-icon">❤️</div>
                <h3>좋아요 5개 달성</h3>
                <p>작성한 후기에 좋아요가 5개 이상 쌓이면 자동으로 추첨 명단에 등록됩니다.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 경품 ===================== -->
<section class="g-section">
    <div class="container">
        <h2 class="g-section-title">🎁 <span>경품</span> 안내</h2>
        <p class="g-section-desc">참여만 해도 추첨 기회가 주어집니다</p>
        <div class="g-prizes">
            <div class="g-prize">
                <div class="g-prize-icon">🥇</div>
                <h4>당첨 경품</h4>
                <p>부산 로컬 식당<br>상품권 5만원</p>
                <small>2명 추첨</small>
            </div>
            <div class="g-prize">
                <div class="g-prize-icon">🥈</div>
                <h4>당첨 경품</h4>
                <p>부산 로컬 식당<br>상품권 3만원</p>
                <small>3명 추첨</small>
            </div>
            <div class="g-prize">
                <div class="g-prize-icon">🎀</div>
                <h4>참여 감사</h4>
                <p>부산온나<br>굿즈 세트</p>
                <small>10명 추첨</small>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 주의사항 ===================== -->
<section class="g-section">
    <div class="container">
        <h2 class="g-section-title">⚠️ 유의 <span>사항</span></h2>
        <p class="g-section-desc">이벤트 참여 전 꼭 확인해주세요</p>
        <div class="g-notes">
            <h4>📌 유의사항</h4>
            <ul>
                <li>이벤트 기간: 2026년 8월 21일 ~ 9월 20일 (30일간)</li>
                <li>당첨자 발표: 2026년 9월 말 (부산온나 공지사항 게시)</li>
                <li>1인 최대 3건까지 응모 가능합니다.</li>
                <li>허위 방문 및 도용 후기는 응모 취소 및 이후 이벤트 참여가 제한됩니다.</li>
                <li>경품 배송은 국내에 한하며, 당첨 후 7일 이내 정보 미제공 시 당첨이 취소됩니다.</li>
                <li>이벤트 내용은 사정에 따라 변경될 수 있습니다.</li>
            </ul>
        </div>
    </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="g-cta">
    <h2>지금 바로 골목을 탐험해보세요! 🗺️</h2>
    <p>부산의 숨겨진 매력을 발견하고, 특별한 경품까지 받아가세요.</p>
    <div class="g-cta-btns">
        <a href="/spots" class="g-btn g-btn-primary">🔍 숨은 명소 찾기</a>
        <a href="/events" class="g-btn g-btn-outline">← 이벤트 목록</a>
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
