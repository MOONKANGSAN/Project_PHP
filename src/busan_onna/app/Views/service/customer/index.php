<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>고객센터 - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
</head>
<body>

<!-- ===================== 헤더 ===================== -->
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
                    <li><a href="/travel-courses">여행코스</a></li>
                </ul>
            </nav>
            <div class="header-auth">
                <?php if (session()->get('user.idx')): ?>
                    <span class="user-greeting">안녕하세요, <?= esc(session()->get('user.id')) ?>님</span>
                    <a href="/auth/logout" class="btn-auth logout">로그아웃</a>
                <?php else: ?>
                    <button type="button" class="btn-auth login" id="btnOpenLogin">로그인</button>
                    <button type="button" class="btn-auth signup" id="btnOpenSignup">회원가입</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- ===================== 히어로 ===================== -->
<section class="page-hero page-hero--customer">
    <div class="container">
        <h1>고객센터</h1>
        <p>부산온나 이용에 관한 궁금한 점을 해결해 드립니다</p>
    </div>
</section>

<!-- ===================== 탭 네비게이션 ===================== -->
<div class="customer-tab-bar">
    <div class="container">
        <div class="customer-tabs">
            <button class="customer-tab <?= $activeTab === 'notice'  ? 'active' : '' ?>"
                    data-tab="notice">공지사항</button>
            <button class="customer-tab <?= $activeTab === 'faq'     ? 'active' : '' ?>"
                    data-tab="faq">FAQs</button>
            <button class="customer-tab <?= $activeTab === 'inquiry' ? 'active' : '' ?>"
                    data-tab="inquiry">고객문의</button>
        </div>
    </div>
</div>

<!-- ===================== 탭 콘텐츠 영역 ===================== -->
<section class="customer-content">
    <div class="container">
        <div id="tabContent" class="customer-tab-content">
            <!-- AJAX로 탭 내용이 로드됩니다 -->
            <div class="tab-loading">
                <div class="loading-spinner"></div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== 푸터 ===================== -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="footer-logo">부산온나</span>
                <p>부산 여행의 시작과 끝<br>설레는 부산 여행을 부산온나와 함께하세요</p>
            </div>
            <div class="footer-nav">
                <h4>바로가기</h4>
                <ul>
                    <li><a href="/spots">관광지</a></li>
                    <li><a href="/restaurants">맛집</a></li>
                    <li><a href="/festivals">축제·행사</a></li>
                    <li><a href="/travel-courses">여행코스</a></li>
                </ul>
            </div>
            <div class="footer-nav">
                <h4>이용안내</h4>
                <ul>
                    <li><a href="/customer?tab=notice">공지사항</a></li>
                    <li><a href="/customer?tab=faq">FAQs</a></li>
                    <li><a href="/customer?tab=inquiry">고객문의</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>고객센터</h4>
                <p>운영시간 평일 09:00 ~ 18:00</p>
                <p>이메일 contact@busanonna.kr</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2024 부산온나. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- ===================== 로그인 모달 ===================== -->
<div class="modal-overlay" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
    <div class="modal-box modal-box--sm">
        <div class="modal-header">
            <h2 class="modal-title" id="loginModalTitle">로그인</h2>
            <button type="button" class="modal-close" id="btnCloseLogin" aria-label="닫기">&times;</button>
        </div>
        <form class="signup-form" id="loginForm" novalidate>
            <?= csrf_field() ?>
            <div class="form-group" id="lfg-id">
                <label class="form-label" for="loginId">아이디 <span class="required">*</span></label>
                <input type="text" id="loginId" name="id" class="form-input" placeholder="아이디 입력"
                       autocomplete="username" maxlength="50" value="<?= esc($saved_id) ?>">
                <span class="form-error" id="lerr-id"></span>
            </div>
            <div class="form-group" id="lfg-password">
                <label class="form-label" for="loginPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="loginPw" name="password" class="form-input" placeholder="비밀번호 입력"
                       autocomplete="current-password" maxlength="100">
                <span class="form-error" id="lerr-password"></span>
            </div>
            <div class="login-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="save_id" id="chkSaveId" value="1" <?= $saved_id ? 'checked' : '' ?>>
                    <span class="checkbox-text">아이디 저장</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="keep_login" id="chkKeepLogin" value="1">
                    <span class="checkbox-text">상시 로그인</span>
                </label>
            </div>
            <div class="form-msg" id="loginFormMsg" style="display:none"></div>
            <button type="submit" class="btn-submit" id="btnSubmitLogin">로그인</button>
            <div class="login-footer">
                <span>아직 회원이 아니신가요?</span>
                <button type="button" class="link-btn" id="btnSwitchToSignup">회원가입</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== 회원가입 모달 ===================== -->
<div class="modal-overlay" id="signupModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">회원가입</h2>
            <button type="button" class="modal-close" id="btnCloseSignup" aria-label="닫기">&times;</button>
        </div>
        <form class="signup-form" id="signupForm" novalidate>
            <?= csrf_field() ?>
            <div class="form-group" id="fg-id">
                <label class="form-label" for="signupId">아이디 <span class="required">*</span></label>
                <input type="text" id="signupId" name="id" class="form-input" placeholder="영문·숫자 4자 이상"
                       autocomplete="username" maxlength="50">
                <span class="form-error" id="err-id"></span>
            </div>
            <div class="form-group" id="fg-password">
                <label class="form-label" for="signupPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="signupPw" name="password" class="form-input" placeholder="8자 이상 입력"
                       autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password"></span>
            </div>
            <div class="form-group" id="fg-password_confirm">
                <label class="form-label" for="signupPwConfirm">비밀번호 확인 <span class="required">*</span></label>
                <input type="password" id="signupPwConfirm" name="password_confirm" class="form-input"
                       placeholder="비밀번호를 다시 입력" autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password_confirm"></span>
            </div>
            <div class="form-group" id="fg-email">
                <label class="form-label" for="emailLocal">이메일 <span class="required">*</span></label>
                <div class="email-wrap">
                    <input type="text" id="emailLocal" class="form-input email-local" placeholder="이메일 아이디" autocomplete="email">
                    <span class="at-sign">@</span>
                    <select id="emailDomainSelect" class="form-select email-domain-select">
                        <option value="naver.com">naver.com</option>
                        <option value="gmail.com">gmail.com</option>
                        <option value="daum.net">daum.net</option>
                        <option value="kakao.com">kakao.com</option>
                        <option value="nate.com">nate.com</option>
                        <option value="direct">직접입력</option>
                    </select>
                    <input type="text" id="emailDomainDirect" class="form-input email-domain-direct"
                           placeholder="도메인 직접 입력" style="display:none">
                </div>
                <input type="hidden" name="email" id="emailFull">
                <span class="form-error" id="err-email"></span>
            </div>
            <div class="form-group" id="fg-phone">
                <label class="form-label" for="signupPhone">휴대폰 번호</label>
                <input type="tel" id="signupPhone" name="phone" class="form-input" placeholder="010-0000-0000" maxlength="13">
                <span class="form-error" id="err-phone"></span>
            </div>
            <div class="form-msg" id="formMsg" style="display:none"></div>
            <button type="submit" class="btn-submit" id="btnSubmitSignup">가입하기</button>
        </form>
    </div>
</div>

<script>
// 초기 활성 탭
var INITIAL_TAB = '<?= esc($activeTab) ?>';

// CSRF 토큰 (AJAX 요청에 사용)
var CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
var CSRF_HASH      = '<?= csrf_hash() ?>';

/**
 * 탭 콘텐츠 AJAX 로드
 */
function loadTab(tab) {
    var tabContent = document.getElementById('tabContent');
    tabContent.innerHTML = '<div class="tab-loading"><div class="loading-spinner"></div></div>';

    // 탭 버튼 active 상태 갱신
    document.querySelectorAll('.customer-tab').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });

    // URL 히스토리 업데이트
    history.replaceState(null, '', '/customer?tab=' + tab);

    // 공지사항은 다른 탭과 동일하게 AJAX 처리


    fetch('/customer/ajax/' + tab, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.text(); })
    .then(function(html) {
        tabContent.innerHTML = html;
        bindTabEvents(tab);
    })
    .catch(function() {
        tabContent.innerHTML = '<p class="tab-error">데이터를 불러오지 못했습니다. 다시 시도해 주세요.</p>';
    });
}

/**
 * 탭별 이벤트 바인딩 (AJAX 로드 후 호출)
 */
function bindTabEvents(tab) {
    if (tab === 'notice') {
        bindNoticeEvents();
    } else if (tab === 'faq') {
        bindFaqEvents();
    } else if (tab === 'inquiry') {
        bindInquiryEvents();
    }
}

/**
 * 공지사항 아코디언 이벤트 (클릭 시 조회수 증가 포함)
 */
function bindNoticeEvents() {
    document.querySelectorAll('.notice-item-header').forEach(function(header) {
        header.addEventListener('click', function() {
            var item    = this.closest('.notice-item');
            var wasOpen = item.classList.contains('open');

            document.querySelectorAll('.notice-item.open').forEach(function(el) {
                el.classList.remove('open');
            });

            if (!wasOpen) {
                item.classList.add('open');

                // 조회수 증가 (열 때 1회만)
                if (!item.dataset.viewed) {
                    item.dataset.viewed = '1';
                    var idx = item.dataset.idx;
                    var fd  = new FormData();
                    fd.append(CSRF_TOKEN_NAME, CSRF_HASH);
                    fetch('/customer/notice/' + idx + '/view', {
                        method : 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body   : fd,
                    }).catch(function() {});
                }
            }
        });
    });
}

/**
 * FAQ 아코디언 이벤트
 */
function bindFaqEvents() {
    // 카테고리 필터 버튼
    document.querySelectorAll('.faq-type-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var type = this.dataset.type;
            document.querySelectorAll('.faq-type-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');

            var tabContent = document.getElementById('tabContent');
            tabContent.innerHTML = '<div class="tab-loading"><div class="loading-spinner"></div></div>';

            fetch('/customer/ajax/faq?type=' + encodeURIComponent(type), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.text(); })
            .then(function(html) {
                tabContent.innerHTML = html;
                bindFaqEvents();
            });
        });
    });

    // FAQ 아코디언 토글
    document.querySelectorAll('.faq-question').forEach(function(q) {
        q.addEventListener('click', function() {
            var item = this.closest('.faq-item');
            var isOpen = item.classList.contains('open');

            // 다른 모든 아코디언 닫기
            document.querySelectorAll('.faq-item.open').forEach(function(el) {
                el.classList.remove('open');
            });

            if (!isOpen) {
                item.classList.add('open');
            }
        });
    });
}

/**
 * 고객문의 이벤트 (등록 폼, 토글 등)
 */
function bindInquiryEvents() {
    // 문의 등록 폼 토글 버튼
    var btnToggle = document.getElementById('btnToggleInquiryForm');
    var formWrap  = document.getElementById('inquiryFormWrap');

    if (btnToggle && formWrap) {
        btnToggle.addEventListener('click', function() {
            var isHidden = formWrap.style.display === 'none' || formWrap.style.display === '';
            formWrap.style.display = isHidden ? 'block' : 'none';
            this.textContent = isHidden ? '작성 취소' : '문의 작성';
        });
    }

    // 문의 등록 폼 제출
    var inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var title   = this.querySelector('[name="title"]').value.trim();
            var content = this.querySelector('[name="content"]').value.trim();

            if (!title) {
                alert('제목을 입력해주세요.');
                return;
            }
            if (!content) {
                alert('내용을 입력해주세요.');
                return;
            }

            var formData = new FormData(this);
            formData.append(CSRF_TOKEN_NAME, CSRF_HASH);

            var btnSubmit = this.querySelector('[type="submit"]');
            btnSubmit.disabled = true;
            btnSubmit.textContent = '등록 중...';

            fetch('/customer/inquiry/store', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    // 등록 성공 → 목록 새로고침
                    loadTab('inquiry');
                } else {
                    alert(data.message || '등록에 실패했습니다.');
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = '등록하기';
                }
            })
            .catch(function() {
                alert('오류가 발생했습니다. 다시 시도해 주세요.');
                btnSubmit.disabled = false;
                btnSubmit.textContent = '등록하기';
            });
        });
    }

    // 문의 상세 토글
    document.querySelectorAll('.inquiry-item-header').forEach(function(header) {
        header.addEventListener('click', function() {
            var item = this.closest('.inquiry-item');
            item.classList.toggle('open');
        });
    });
}

// 탭 버튼 클릭 이벤트 등록
document.querySelectorAll('.customer-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        loadTab(this.dataset.tab);
    });
});

// 초기 탭 로드
loadTab(INITIAL_TAB);
</script>
<script src="/js/busan.js"></script>
</body>
</html>
