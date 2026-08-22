<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마! 이게 진짜 국밥이다! | 부산온나 이벤트</title>
    <meta name="description" content="부산 국밥 맛집에 좋아요로 투표하세요! 3일 이상 참여하면 추첨을 통해 특별한 혜택을 드립니다.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="마! 이게 진짜 국밥이다! | 부산온나 이벤트">
    <meta property="og:description" content="부산 국밥 맛집에 좋아요로 투표하세요! 3일 이상 참여하면 추첨을 통해 특별한 혜택을 드립니다.">
    <meta property="og:url"         content="https://busanonna.com/events/<?= (int)$event['idx'] ?>">
    <meta property="og:site_name"   content="부산온나">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
    <style>
<?php include APPPATH . 'Views/service/event/views/css/view_2.css'; ?>
</style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'events']) ?>

<!-- ===================== 히어로 ===================== -->
<section class="m-hero">
    <div class="m-hero-inner container">
        <div class="m-hero-tag">🍲 국밥 맛집 좋아요 투표</div>
        <h1>마! <em>이게 진짜 국밥이다</em> 🍲</h1>
        <p class="m-hero-sub">
            부산 곳곳의 국밥 맛집, 진짜 1등은 어디일까요?<br>
            좋아요로 응원하고 추첨 이벤트에도 참여해보세요
        </p>
        <div class="m-period-row">
            <span class="m-period-pill">📅 2026.09.01 ~ 2026.09.18 (18일간)</span>
            <?php
            $status = $event['event_status'] ?? '';
            $statusLabel = ['ongoing' => '진행중', 'upcoming' => '예정', 'ended' => '종료'];
            if ($status): ?>
            <span class="m-period-pill"><span class="m-status-badge"><?= $statusLabel[$status] ?? '' ?></span></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===================== 이벤트 소개 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">🍲 이벤트 <span>소개</span></h2>
        <p class="m-section-desc">이번엔 오직 국밥 하나로 승부합니다</p>
        <div class="m-weekly">
            <div class="m-weekly-icon">🍲</div>
            <div class="m-weekly-body">
                <h3>부산 국밥 맛집, 좋아요로 뽑는다</h3>
                <p>
                    맛집 이름에 '국밥'이 들어간 부산온나 등록 맛집이라면 모두 투표 대상입니다.<br>
                    마음에 드는 국밥집에 매일 한 번씩 좋아요를 눌러 응원해주세요.
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
                <div class="m-vote-icon">🍲</div>
                <h3>국밥 맛집 확인</h3>
                <p>아래 목록에서 이름에 '국밥'이 들어간 부산온나 맛집을 확인하세요.</p>
            </div>
            <div class="m-vote-card">
                <div class="m-vote-icon">👍</div>
                <h3>좋아요 클릭</h3>
                <p>마음에 드는 국밥 맛집에 좋아요를 눌러주세요. 1인 1일 1표!</p>
            </div>
            <div class="m-vote-card">
                <div class="m-vote-icon">🎁</div>
                <h3>3일 이상 참여</h3>
                <p>서로 다른 날짜로 3일 이상 참여하면 자동으로 추첨 대상에 등록됩니다.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 국밥 맛집 전체보기 (투표 방법 바로 아래) ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">🍲 국밥 맛집 <span>전체보기</span></h2>
        <p class="m-section-desc">맛집 이름에 '국밥'이 들어간 모든 맛집이에요. 좋아요로 투표해보세요</p>

        <?php if (session()->get('user.idx')): ?>
            <p class="m-status-banner<?= (($myParticipationDays ?? 0) >= 3) ? ' is-eligible' : '' ?>" id="participationBanner">
                <?php if (($myParticipationDays ?? 0) > 0): ?>
                    현재 <?= (int) $myParticipationDays ?>일째 참여 중이에요<?= $myParticipationDays >= 3 ? ' — 추첨 대상에 등록되었습니다 🎉' : '! 3일 이상 참여하면 추첨 대상이 됩니다.' ?>
                <?php else: ?>
                    국밥 맛집에 좋아요를 눌러 투표를 시작해보세요! 3일 이상 참여하면 추첨 대상이 됩니다.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <p class="m-status-banner" id="participationBanner">로그인 후 좋아요를 누르면 투표에 참여할 수 있어요.</p>
        <?php endif; ?>
    </div>

    <?php if (empty($gukbapCards)): ?>
        <p class="m-spots-empty">아직 등록된 국밥 맛집이 없어요. 곧 새로운 맛집이 추가될 예정이에요!</p>
    <?php else: ?>
        <div class="m-spots-wrap">
            <div class="m-spots-track">
                <?php foreach (array_merge($gukbapCards, $gukbapCards) as $spot):
                    $voteCount    = (int) ($gukbapVoteCounts[$spot['idx']] ?? 0);
                    $votedTodayId = $myTodayVote['restaurant_idx'] ?? null;
                    $isThisVoted  = $votedTodayId !== null && (int) $votedTodayId === (int) $spot['idx'];
                    $isOtherVoted = $votedTodayId !== null && !$isThisVoted;
                ?>
                    <div class="m-spot-card" data-restaurant-idx="<?= (int) $spot['idx'] ?>">
                        <div class="m-spot-thumb">
                            <a href="<?= esc($spot['link']) ?>">
                                <img src="<?= esc($spot['thumbnail'] ?: '/img/no-image.svg') ?>" alt="<?= esc($spot['name']) ?>" loading="lazy">
                            </a>
                            <span class="m-spot-vote-count">🔥 <?= $voteCount ?>표</span>
                        </div>
                        <div class="m-spot-body">
                            <div class="m-spot-name"><a href="<?= esc($spot['link']) ?>"><?= esc($spot['name']) ?></a></div>
                            <div class="m-spot-cat"><?= esc($spot['category']) ?></div>
                            <?php if ($isThisVoted): ?>
                                <button type="button" class="m-vote-btn is-voted" data-restaurant-idx="<?= (int) $spot['idx'] ?>" disabled>✓ 오늘 투표완료</button>
                            <?php elseif ($isOtherVoted): ?>
                                <button type="button" class="m-vote-btn" data-restaurant-idx="<?= (int) $spot['idx'] ?>" disabled>오늘 투표 완료</button>
                            <?php else: ?>
                                <button type="button" class="m-vote-btn" data-restaurant-idx="<?= (int) $spot['idx'] ?>">❤️ 좋아요 투표</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php /*
<!-- ===================== 라운드 일정 (컨셉 변경으로 비활성화 — '국밥' 단일 주제로 전환, 필요 시 주석 해제) ===================== -->
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
*/ ?>

<!-- ===================== 경품 ===================== -->
<section class="m-section">
    <div class="container">
        <h2 class="m-section-title">🎁 <span>혜택</span> 안내</h2>
        <p class="m-section-desc">3일 이상 참여하면 추첨 대상이 됩니다</p>
        <div class="m-prize-banner">
            <div class="m-prize-left">
                <h3>참여만 해도 추첨 기회</h3>
                <p>
                    이벤트 기간 중 서로 다른 날짜로 3회 이상<br>
                    좋아요에 참여하면 자동으로 추첨 대상에 등록됩니다.<br><br>
                    많이 참여할수록, 그리고 인기 국밥집일수록<br>
                    당첨 확률과 이벤트의 재미가 커져요!
                </p>
            </div>
            <div class="m-prize-right">
                <div class="m-prize-item">
                    <span class="icon">🥇</span>
                    <span class="text">
                        <strong>국밥 맛집 상품권 5만원</strong>
                        추첨 3명
                    </span>
                </div>
                <div class="m-prize-item">
                    <span class="icon">🥈</span>
                    <span class="text">
                        <strong>국밥 맛집 상품권 3만원</strong>
                        추첨 5명
                    </span>
                </div>
                <div class="m-prize-item">
                    <span class="icon">🎀</span>
                    <span class="text">
                        <strong>부산온나 굿즈 세트</strong>
                        추첨 10명
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
                <li>좋아요는 로그인한 회원만 가능하며, 1인 1일 1회만 가능합니다.</li>
                <li>이벤트 기간 중 서로 다른 날짜로 3회 이상 참여한 회원은 자동으로 추첨 대상에 등록됩니다.</li>
                <li>비정상적인 다중 계정 참여는 무효 처리되며 이용이 제한될 수 있습니다.</li>
                <li>당첨자 발표 및 경품 안내는 이벤트 종료 후 공지사항을 통해 안내됩니다.</li>
                <li>이벤트 일정 및 내용은 운영 상황에 따라 변경될 수 있습니다.</li>
            </ul>
        </div>
    </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="m-cta">
    <h2>마! 이게 진짜 국밥이다! 🍲</h2>
    <p>지금 바로 좋아요를 누르고 추첨 이벤트에도 참여하세요.</p>
    <div class="m-cta-btns">
        <a href="/restaurants?q=국밥" class="m-btn m-btn-primary">🍲 국밥 맛집 보러가기</a>
        <a href="/events" class="m-btn m-btn-outline">← 이벤트 목록</a>
    </div>
</section>

<?= view('service/partials/footer') ?>
<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>
<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script>
<?php include APPPATH . 'Views/service/event/views/js/view_2.js'; ?>
</script>
</body>
</html>
