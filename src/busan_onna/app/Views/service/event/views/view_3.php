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
<?php include APPPATH . 'Views/service/event/views/css/view_3.css'; ?>
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
                <h3>온라인 제출</h3>
                <p>아래 <a href="#courseSubmitSection">코스 등록 폼</a>에서 바로 작성해 제출하세요.</p>
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

<!-- ===================== 코스 등록 (참여 방법 바로 아래) ===================== -->
<section class="c-section" id="courseSubmitSection">
    <div class="container">
        <h2 class="c-section-title">✏️ 나만의 코스 <span>등록하기</span></h2>
        <p class="c-section-desc">아래 폼에서 바로 코스를 작성해 제출할 수 있어요. 장소는 최소 3곳 이상 등록해주세요.</p>

        <div class="c-submit-card">
            <div class="c-form-msg" id="courseFormMsg" style="display:none"></div>

            <form id="courseSubmitForm" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>

                <div class="c-form-row">
                    <div class="c-form-group c-form-group--wide">
                        <label class="c-form-label">코스명 <span class="c-required">*</span></label>
                        <input type="text" name="title" id="courseTitle" class="c-form-input" maxlength="100"
                               placeholder="예) 해운대에서 즐기는 미식 반나절 코스">
                        <span class="c-form-error" id="cerr-title"></span>
                    </div>
                    <div class="c-form-group">
                        <label class="c-form-label">대표 지역</label>
                        <select name="sido" class="c-form-select">
                            <option value="">-- 지역 선택 --</option>
                            <?php foreach (['강서구','금정구','기장군','남구','동구','동래구','부산진구','북구','사상구','사하구','서구','수영구','연제구','영도구','중구','해운대구'] as $gu): ?>
                                <option value="<?= esc($gu) ?>"><?= esc($gu) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="c-form-group">
                    <label class="c-form-label">코스 소개</label>
                    <textarea name="description" class="c-form-textarea" rows="3"
                              placeholder="이 코스를 한마디로 소개해주세요. 테마와 총 소요시간을 함께 적어주시면 좋아요."></textarea>
                </div>

                <div class="c-form-group">
                    <label class="c-form-label">대표 이미지 (선택)</label>
                    <input type="file" name="thumb_img" id="courseThumbInput" accept="image/jpeg,image/png,image/webp,image/gif">
                    <div class="c-thumb-preview" id="courseThumbPreview"><img src="" alt="미리보기"></div>
                </div>

                <div class="c-items-head">
                    <h3>코스 장소 <span class="c-required">*</span><span class="c-item-count-badge" id="courseItemBadge">3 / 8</span></h3>
                    <p>방문 순서대로 등록해주세요. 최소 3곳 이상 필요합니다.</p>
                </div>

                <div id="courseItemList"></div>

                <button type="button" id="btnAddCourseItem" class="c-btn-add-item">+ 장소 추가</button>

                <div class="c-form-footer">
                    <button type="submit" class="c-btn c-btn-submit" id="btnSubmitCourse">🚀 코스 제출하기</button>
                </div>
            </form>
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
                    <div class="c-tl-desc">이벤트 페이지의 코스 등록 폼을 통해 코스 제출이 가능합니다.</div>
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
                <li>장소는 부산온나에 등록된 관광지·맛집·축제를 검색해 연결하거나, 아직 등록되지 않은 장소는 직접 입력할 수 있습니다.</li>
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
        <a href="#courseSubmitSection" class="c-btn c-btn-primary">📤 코스 등록하러 가기</a>
        <a href="/travel-courses" class="c-btn c-btn-outline">🗺️ 기존 여행코스 보기</a>
    </div>
</section>

<?= view('service/partials/footer') ?>
<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>
<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script>
<?php include APPPATH . 'Views/service/event/views/js/view_3.js'; ?>
</script>
</body>
</html>
