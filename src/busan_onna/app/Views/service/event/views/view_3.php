<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>나만의 부산 코스 공모전 | 부산온나 이벤트</title>
    <meta name="description" content="나만의 부산 여행 코스를 기획해 제출하세요. 선정된 코스는 부산온나 공식 여행코스로 등록됩니다.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="나만의 부산 코스 공모전 | 부산온나 이벤트">
    <meta property="og:description" content="나만의 부산 여행 코스를 기획해 제출하세요. 선정된 코스는 부산온나 공식 여행코스로 등록됩니다.">
    <meta property="og:url"         content="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:site_name"   content="부산온나">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
    <style>
        /* ===================== 나만의 부산 코스 공모전 전용 스타일 ===================== */

        /* 히어로 */
        .c-hero {
            background: linear-gradient(150deg, #0a3d2e 0%, #1b5e20 50%, #2e7d32 100%);
            min-height: 500px;
            display: flex;
            align-items: center;
            text-align: center;
            padding: 80px 20px 80px;
            position: relative;
            overflow: hidden;
        }
        .c-hero-deco {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(76,175,80,0.18) 0%, transparent 50%),
                radial-gradient(circle at 80% 30%, rgba(139,195,74,0.12) 0%, transparent 40%);
        }
        .c-hero-inner { position: relative; z-index: 1; width: 100%; }
        .c-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.4);
            color: #a5d6a7;
            border-radius: 24px;
            padding: 5px 20px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 22px;
        }
        .c-hero h1 {
            font-size: clamp(26px, 4.5vw, 50px);
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
            margin: 0 0 16px;
            text-shadow: 0 2px 14px rgba(0,0,0,0.3);
        }
        .c-hero h1 em { font-style: normal; color: #a5d6a7; }
        .c-hero-sub {
            color: rgba(255,255,255,0.82);
            font-size: 16px;
            margin-bottom: 32px;
            line-height: 1.7;
        }
        .c-period-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .c-period-pill {
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 9px 20px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }
        .c-status-badge {
            background: #00b894;
            border-radius: 6px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        /* 공통 */
        .c-section { padding: 72px 0; }
        .c-section:nth-child(even) { background: #f1f8e9; }
        .c-section-title {
            text-align: center;
            font-size: clamp(20px, 3vw, 28px);
            font-weight: 900;
            color: #1b3a1f;
            margin-bottom: 10px;
        }
        .c-section-title span { color: #2e7d32; }
        .c-section-desc {
            text-align: center;
            color: #666;
            font-size: 15px;
            margin-bottom: 48px;
            line-height: 1.7;
        }

        /* 이벤트 소개 */
        .c-intro {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            max-width: 900px;
            margin: 0 auto;
        }
        .c-intro-card {
            background: #fff;
            border-radius: 18px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(46,125,50,0.1);
        }
        .c-intro-card .icon { font-size: 44px; margin-bottom: 14px; }
        .c-intro-card h3 { font-size: 18px; font-weight: 800; color: #1b3a1f; margin-bottom: 10px; }
        .c-intro-card p { font-size: 14px; color: #555; line-height: 1.7; }

        /* 제출 조건 */
        .c-conditions {
            max-width: 720px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .c-cond-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: #fff;
            border-radius: 14px;
            padding: 22px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 4px solid #4caf50;
        }
        .c-cond-item:nth-child(2) { border-left-color: #8bc34a; }
        .c-cond-item:nth-child(3) { border-left-color: #cddc39; }
        .c-cond-num {
            width: 36px;
            height: 36px;
            background: #2e7d32;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 900;
            flex-shrink: 0;
        }
        .c-cond-body h4 { font-size: 16px; font-weight: 700; color: #1b3a1f; margin-bottom: 4px; }
        .c-cond-body p  { font-size: 13px; color: #666; line-height: 1.6; }

        /* 참여 방법 스텝 */
        .c-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 960px;
            margin: 0 auto;
            position: relative;
        }
        .c-step {
            background: #fff;
            border-radius: 16px;
            padding: 30px 22px;
            text-align: center;
            box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        }
        .c-step-num {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2e7d32, #66bb6a);
            color: #fff;
            border-radius: 50%;
            font-size: 16px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
        }
        .c-step-icon { font-size: 36px; margin-bottom: 12px; }
        .c-step h3 { font-size: 15px; font-weight: 700; color: #1b3a1f; margin-bottom: 8px; }
        .c-step p  { font-size: 13px; color: #666; line-height: 1.6; }

        /* 심사 기준 */
        .c-criteria {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            max-width: 860px;
            margin: 0 auto;
        }
        .c-criterion {
            text-align: center;
            background: #fff;
            border-radius: 14px;
            padding: 26px 18px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.06);
        }
        .c-criterion .bar-wrap {
            height: 6px;
            background: #e8f5e9;
            border-radius: 3px;
            margin: 10px 0;
            overflow: hidden;
        }
        .c-criterion .bar { height: 100%; background: linear-gradient(90deg, #2e7d32, #81c784); border-radius: 3px; }
        .c-criterion .icon { font-size: 32px; }
        .c-criterion h4 { font-size: 14px; font-weight: 700; color: #1b3a1f; margin: 8px 0 2px; }
        .c-criterion small { font-size: 12px; color: #888; }

        /* 경품 */
        .c-prizes {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            max-width: 840px;
            margin: 0 auto;
        }
        .c-prize {
            text-align: center;
            border-radius: 20px;
            padding: 36px 20px;
            position: relative;
            overflow: hidden;
        }
        .c-prize:nth-child(1) {
            background: linear-gradient(145deg, #f9a825, #f57f17);
            color: #fff;
            transform: scale(1.04);
            box-shadow: 0 10px 36px rgba(245,127,23,0.35);
        }
        .c-prize:nth-child(2) {
            background: linear-gradient(145deg, #78909c, #546e7a);
            color: #fff;
            box-shadow: 0 6px 24px rgba(84,110,122,0.25);
        }
        .c-prize:nth-child(3) {
            background: linear-gradient(145deg, #a0522d, #8b4513);
            color: #fff;
            box-shadow: 0 6px 24px rgba(139,69,19,0.25);
        }
        .c-prize-crown { font-size: 40px; margin-bottom: 10px; }
        .c-prize-rank { font-size: 13px; opacity: 0.85; margin-bottom: 6px; font-weight: 600; }
        .c-prize-title { font-size: clamp(16px, 2.5vw, 22px); font-weight: 900; margin-bottom: 8px; }
        .c-prize-desc { font-size: 13px; opacity: 0.85; line-height: 1.6; }

        /* 일정 */
        .c-timeline {
            max-width: 680px;
            margin: 0 auto;
            position: relative;
        }
        .c-timeline::before {
            content: '';
            position: absolute;
            left: 22px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #c8e6c9;
        }
        .c-tl-item {
            display: flex;
            gap: 20px;
            padding-bottom: 32px;
            position: relative;
        }
        .c-tl-item:last-child { padding-bottom: 0; }
        .c-tl-dot {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #2e7d32;
            color: #fff;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            box-shadow: 0 0 0 4px #e8f5e9;
        }
        .c-tl-body { padding-top: 8px; }
        .c-tl-date { font-size: 13px; color: #4caf50; font-weight: 700; margin-bottom: 4px; }
        .c-tl-title { font-size: 16px; font-weight: 800; color: #1b3a1f; margin-bottom: 4px; }
        .c-tl-desc { font-size: 13px; color: #666; line-height: 1.6; }

        /* 주의사항 */
        .c-notes {
            max-width: 720px;
            margin: 0 auto;
            background: #f1f8e9;
            border-radius: 12px;
            padding: 28px 32px;
            border-left: 4px solid #4caf50;
        }
        .c-notes h4 { font-size: 15px; font-weight: 700; color: #2e7d32; margin-bottom: 12px; }
        .c-notes ul { margin: 0; padding-left: 18px; }
        .c-notes li  { font-size: 14px; color: #444; line-height: 1.9; }

        /* CTA */
        .c-cta {
            background: linear-gradient(150deg, #1b5e20, #388e3c);
            padding: 80px 20px;
            text-align: center;
        }
        .c-cta h2 { font-size: clamp(20px, 3vw, 32px); font-weight: 900; color: #fff; margin-bottom: 12px; }
        .c-cta p  { color: rgba(255,255,255,0.82); margin-bottom: 32px; font-size: 15px; line-height: 1.7; }
        .c-cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .c-btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .c-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
        .c-btn-primary { background: #a5d6a7; color: #1b3a1f; }
        .c-btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,0.55); }

        @media (max-width: 680px) {
            .c-intro { grid-template-columns: 1fr; }
            .c-prizes { grid-template-columns: 1fr; }
            .c-prize:nth-child(1) { transform: none; }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'events']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="c-hero">
    <div class="c-hero-deco"></div>
    <div class="c-hero-inner container">
        <div class="c-hero-tag">🏆 여행코스 공모전</div>
        <h1><em>나만의</em> 부산 코스<br>공모전</h1>
        <p class="c-hero-sub">
            당신이 직접 기획한 부산 여행 코스를 제출해주세요<br>
            선정된 코스는 부산온나 <strong>공식 여행코스</strong>로 등록됩니다
        </p>
        <div class="c-period-row">
            <span class="c-period-pill">📅 2026.09.01 ~ 2026.09.30</span>
            <?php
            $status = $event['event_status'] ?? '';
            $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
            if ($status): ?>
            <span class="c-status-badge"><?= $statusLabel[$status] ?? '' ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===================== 이벤트 소개 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">🗺️ 공모전 <span>소개</span></h2>
        <p class="c-section-desc">여행 고수가 짠 나만의 코스, 이제 부산온나에서 모두와 나눠보세요</p>
        <div class="c-intro">
            <div class="c-intro-card">
                <div class="icon">🌟</div>
                <h3>공식 코스로 등록</h3>
                <p>선정된 코스는 작성자 이름과 함께 부산온나 공식 여행코스로 영구 등록됩니다. 내가 만든 코스로 더 많은 여행자를 부산으로!</p>
            </div>
            <div class="c-intro-card">
                <div class="icon">🎁</div>
                <h3>풍성한 경품</h3>
                <p>최우수작에는 부산 숙박권이, 우수작에는 체험 상품권이 증정됩니다. 입선작 작성자 전원에게도 기념 굿즈를 드려요.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 코스 제출 조건 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">📋 코스 제출 <span>조건</span></h2>
        <p class="c-section-desc">아래 3가지 조건을 충족한 코스를 제출해주세요</p>
        <div class="c-conditions">
            <div class="c-cond-item">
                <div class="c-cond-num">1</div>
                <div class="c-cond-body">
                    <h4>장소 3곳 이상 포함</h4>
                    <p>부산온나에 등록된 관광지·맛집·축제 중 최소 3곳 이상을 코스에 포함해야 합니다.</p>
                </div>
            </div>
            <div class="c-cond-item">
                <div class="c-cond-num">2</div>
                <div class="c-cond-body">
                    <h4>각 장소별 방문 이유 설명</h4>
                    <p>단순 나열이 아닌, 왜 이 장소를 선택했는지 여행자 관점의 설명을 각 장소마다 작성해주세요.</p>
                </div>
            </div>
            <div class="c-cond-item">
                <div class="c-cond-num">3</div>
                <div class="c-cond-body">
                    <h4>이동 동선 및 테마 명시</h4>
                    <p>반나절·하루·1박2일 등 소요 시간과 코스의 테마(미식/역사/자연/힐링 등)를 함께 적어주세요.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 참여 방법 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">✅ 참여 <span>방법</span></h2>
        <p class="c-section-desc">4단계로 나만의 코스를 제출하세요</p>
        <div class="c-steps">
            <div class="c-step">
                <div class="c-step-num">1</div>
                <div class="c-step-icon">🔍</div>
                <h3>장소 탐색</h3>
                <p>부산온나에서 마음에 드는 관광지·맛집·축제를 미리 찾아두세요.</p>
            </div>
            <div class="c-step">
                <div class="c-step-num">2</div>
                <div class="c-step-icon">✏️</div>
                <h3>코스 기획</h3>
                <p>장소 3곳 이상과 테마·소요시간·각 장소 설명을 포함한 코스를 작성하세요.</p>
            </div>
            <div class="c-step">
                <div class="c-step-num">3</div>
                <div class="c-step-icon">📤</div>
                <h3>고객센터 제출</h3>
                <p>부산온나 고객센터 문의 양식에 [코스 공모전] 제목으로 코스를 제출해주세요.</p>
            </div>
            <div class="c-step">
                <div class="c-step-num">4</div>
                <div class="c-step-icon">🏆</div>
                <h3>심사 및 발표</h3>
                <p>10월 중 심사 후 당선작 발표 및 공식 코스 등록이 진행됩니다.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 심사 기준 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">🔎 심사 <span>기준</span></h2>
        <p class="c-section-desc">아래 4가지 기준으로 심사가 진행됩니다</p>
        <div class="c-criteria">
            <div class="c-criterion">
                <div class="icon">🗺️</div>
                <h4>동선 효율성</h4>
                <div class="bar-wrap"><div class="bar" style="width:90%;"></div></div>
                <small>이동 거리와 순서의 합리성</small>
            </div>
            <div class="c-criterion">
                <div class="icon">🌈</div>
                <h4>다양성</h4>
                <div class="bar-wrap"><div class="bar" style="width:80%;"></div></div>
                <small>장소 카테고리의 다양한 조합</small>
            </div>
            <div class="c-criterion">
                <div class="icon">✍️</div>
                <h4>설명의 질</h4>
                <div class="bar-wrap"><div class="bar" style="width:85%;"></div></div>
                <small>각 장소 설명의 구체성·진정성</small>
            </div>
            <div class="c-criterion">
                <div class="icon">💡</div>
                <h4>독창성</h4>
                <div class="bar-wrap"><div class="bar" style="width:75%;"></div></div>
                <small>기존에 없는 새로운 관점의 코스</small>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 경품 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">🎁 <span>경품</span> 안내</h2>
        <p class="c-section-desc">창의적인 코스에 걸맞은 특별한 경품을 드립니다</p>
        <div class="c-prizes">
            <div class="c-prize">
                <div class="c-prize-crown">🥇</div>
                <div class="c-prize-rank">최우수상 (1명)</div>
                <div class="c-prize-title">부산 호텔<br>1박 숙박권</div>
                <div class="c-prize-desc">부산 시내 4성급 호텔<br>2인 1박 숙박권<br>+ 공식 코스 영구 등록</div>
            </div>
            <div class="c-prize">
                <div class="c-prize-crown">🥈</div>
                <div class="c-prize-rank">우수상 (2명)</div>
                <div class="c-prize-title">부산 체험<br>상품권 5만원</div>
                <div class="c-prize-desc">제휴 체험 프로그램<br>이용 상품권<br>+ 공식 코스 등록</div>
            </div>
            <div class="c-prize">
                <div class="c-prize-crown">🥉</div>
                <div class="c-prize-rank">입선 (5명)</div>
                <div class="c-prize-title">부산온나<br>굿즈 세트</div>
                <div class="c-prize-desc">부산온나 공식 굿즈<br>+ 공식 코스 등록<br>+ 명예의 전당 게시</div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 일정 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">📅 진행 <span>일정</span></h2>
        <p class="c-section-desc"></p>
        <div class="c-timeline">
            <div class="c-tl-item">
                <div class="c-tl-dot">📢</div>
                <div class="c-tl-body">
                    <div class="c-tl-date">2026.09.01</div>
                    <div class="c-tl-title">공모전 접수 시작</div>
                    <div class="c-tl-desc">고객센터 문의를 통해 코스 제출이 가능합니다.</div>
                </div>
            </div>
            <div class="c-tl-item">
                <div class="c-tl-dot">📮</div>
                <div class="c-tl-body">
                    <div class="c-tl-date">2026.09.30</div>
                    <div class="c-tl-title">접수 마감</div>
                    <div class="c-tl-desc">마감일 23:59까지 제출된 작품만 심사에 포함됩니다.</div>
                </div>
            </div>
            <div class="c-tl-item">
                <div class="c-tl-dot">🔍</div>
                <div class="c-tl-body">
                    <div class="c-tl-date">2026.10.01 ~ 10.14</div>
                    <div class="c-tl-title">내부 심사</div>
                    <div class="c-tl-desc">부산온나 운영진이 4가지 기준으로 심사를 진행합니다.</div>
                </div>
            </div>
            <div class="c-tl-item">
                <div class="c-tl-dot">🎉</div>
                <div class="c-tl-body">
                    <div class="c-tl-date">2026.10.20</div>
                    <div class="c-tl-title">당선작 발표 및 코스 등록</div>
                    <div class="c-tl-desc">공지사항을 통해 발표되며, 선정 코스가 즉시 등록됩니다.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 주의사항 ===================== -->
<section class="c-section">
    <div class="container">
        <h2 class="c-section-title">⚠️ 유의 <span>사항</span></h2>
        <p class="c-section-desc"></p>
        <div class="c-notes">
            <h4>📌 유의사항</h4>
            <ul>
                <li>공모전은 부산온나 회원 누구나 참여 가능하며, 1인 최대 2편까지 제출 가능합니다.</li>
                <li>부산온나에 등록되지 않은 장소는 코스에 포함할 수 없습니다.</li>
                <li>타인의 코스를 도용·표절한 경우 즉시 실격 처리됩니다.</li>
                <li>당선작의 저작권은 부산온나에 귀속되며, 일부 수정 후 등록될 수 있습니다.</li>
                <li>경품은 당선자 개인 정보 확인 후 30일 이내 지급됩니다.</li>
                <li>이벤트 내용은 운영 사정에 따라 변경될 수 있으며 공지사항을 통해 안내됩니다.</li>
            </ul>
        </div>
    </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="c-cta">
    <h2>당신의 부산이 공식 코스가 됩니다 🗺️</h2>
    <p>
        직접 걷고, 먹고, 느낀 부산의 이야기를 코스로 만들어 제출해보세요.<br>
        부산온나가 더 많은 여행자에게 당신의 코스를 소개합니다.
    </p>
    <div class="c-cta-btns">
        <a href="/customer" class="c-btn c-btn-primary">📤 고객센터에서 제출하기</a>
        <a href="/travel-courses" class="c-btn c-btn-outline">🗺️ 기존 여행코스 보기</a>
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
