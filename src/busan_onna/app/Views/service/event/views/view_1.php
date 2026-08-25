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
<?php include APPPATH . 'Views/service/event/views/css/view_1.css'; ?>
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
            진솔한 후기를 작성하시면 자동으로 경품 추첨에 응모됩니다.<br><br>
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
                <p>직접 방문하고 사진과 함께 솔직한 후기를 남겨주세요!</p>
            </div>
            <!-- <div class="g-step">
                <div class="g-step-num">3</div>
                <div class="g-step-icon">❤️</div>
                <h3>좋아요 5개 달성</h3>
                <p>작성한 후기에 좋아요가 5개 이상 쌓이면 자동으로 추첨 명단에 등록됩니다.</p>
            </div> -->
        </div>
    </div>
</section>

<!-- ===================== 숨은 명소 (참여 방법 바로 아래) ===================== -->
<section class="g-section">
    <div class="container">
        <h2 class="g-section-title">🔍 <span>숨은 명소</span> 둘러보기</h2>
        <p class="g-section-desc">'숨은 명소' 태그가 붙은 맛집·관광지예요. 방문하고 후기를 남겨보세요</p>
    </div>

    <?php if (empty($hiddenSpotCards)): ?>
        <p class="g-spots-empty">아직 등록된 '숨은 명소'가 없어요. 곧 새로운 장소가 추가될 예정이에요!</p>
    <?php else: ?>
        <div class="g-spots-wrap">
            <div class="g-spots-track">
                <?php foreach (array_merge($hiddenSpotCards, $hiddenSpotCards) as $spot): ?>
                    <a class="g-spot-card" href="<?= esc($spot['link']) ?>">
                        <div class="g-spot-thumb">
                            <img src="<?= esc($spot['thumbnail'] ?: '/img/no-image.svg') ?>" alt="<?= esc($spot['name']) ?>" loading="lazy">
                            <span class="g-spot-type-badge"><?= esc($spot['type_label']) ?></span>
                            <span class="g-spot-hidden-badge">숨은 명소</span>
                        </div>
                        <div class="g-spot-body">
                            <div class="g-spot-name"><?= esc($spot['name']) ?></div>
                            <div class="g-spot-cat"><?= esc($spot['category']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- ===================== 방문 후기 (숨은 명소 캐러셀 바로 아래) ===================== -->
<section class="g-section">
    <div class="container">
        <div class="g-review-head">
            <div>
                <h2 class="g-section-title">📝 방문 <span>후기</span></h2>
                <p class="g-section-desc">먼저 다녀온 탐험가들의 생생한 골목 이야기예요</p>
            </div>
            <button type="button" class="g-btn g-btn-primary g-btn-review" id="btnOpenReview">✍️ 후기 등록</button>
        </div>

        <div class="g-review-list" id="reviewList">
            <?php if (empty($eventReviews)): ?>
                <p class="g-review-empty" id="reviewEmptyMsg">아직 등록된 후기가 없어요. 첫 번째 탐험 후기를 남겨보세요!</p>
            <?php else: foreach ($eventReviews as $rv): ?>
                <div class="g-review-card">
                    <div class="g-review-top">
                        <span class="g-review-user"><?= esc($rv['user_id']) ?></span>
                        <span class="g-review-date"><?= esc(date('Y.m.d', strtotime($rv['reg_date']))) ?></span>
                    </div>
                    <?php if (!empty($rv['spot_name'])): ?>
                        <span class="g-review-spot">📍 <?= esc($rv['spot_name']) ?></span>
                    <?php endif; ?>
                    <div class="g-review-content"><?= nl2br(esc($rv['content'])) ?></div>
                    <?php if (!empty($rv['photo_url'])): ?>
                        <div class="g-review-photo"><img src="<?= esc($rv['photo_url']) ?>" alt="후기 사진" loading="lazy"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
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

<!-- ===================== 방문 후기 작성 모달 ===================== -->
<div class="modal-overlay" id="reviewModal" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
    <div class="modal-box">

        <div class="modal-header">
            <h2 class="modal-title" id="reviewModalTitle">방문 후기 작성</h2>
            <button type="button" class="modal-close" id="btnCloseReview" aria-label="닫기">&times;</button>
        </div>

        <form id="reviewForm" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <!-- 방문한 숨은 명소 -->
            <div class="form-group" id="rfg-spot_name">
                <label class="form-label" for="reviewSpotName">방문한 숨은 명소</label>
                <input type="text" id="reviewSpotName" name="spot_name"
                       class="form-input" placeholder="예) OO계단마을, OO골목카페" maxlength="100">
            </div>

            <!-- 후기 내용 -->
            <div class="form-group" id="rfg-content">
                <label class="form-label" for="reviewContent">후기 내용 <span class="required">*</span></label>
                <textarea id="reviewContent" name="content" class="form-input" rows="5" maxlength="1000"
                          placeholder="어떤 골목을 다녀오셨나요? #부산골목탐험 태그와 함께 솔직한 후기를 남겨주세요"></textarea>
                <span class="form-error" id="rerr-content"></span>
            </div>

            <!-- 사진 첨부 -->
            <div class="form-group" id="rfg-photo">
                <label class="form-label" for="reviewPhoto">사진 첨부 (선택)</label>
                <input type="file" id="reviewPhoto" name="photo" accept="image/*">
                <div class="g-review-photo-preview" id="reviewPhotoPreview"><img src="" alt="미리보기"></div>
                <span class="form-error" id="rerr-photo"></span>
            </div>

            <!-- 전송 메시지 -->
            <div class="form-msg" id="reviewFormMsg" style="display:none"></div>

            <button type="submit" class="btn-submit" id="btnSubmitReview">후기 등록하기</button>
        </form>

    </div>
</div>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script>
<?php include APPPATH . 'Views/service/event/views/js/view_1.js'; ?>
</script>
</body>
</html>
