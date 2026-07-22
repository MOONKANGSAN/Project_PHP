<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>고객센터 - 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">
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

<?= view('modules/auth/login_modal') ?>

<?= view('modules/auth/signup_modal') ?>

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
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
</body>
</html>
