<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로필 수정 | 부산온나</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 프로필 수정 페이지 레이아웃 (마이페이지와 동일한 50% 중앙) ---- */
        .mypage-body {
            background: #f4f6f9;
            min-height: calc(100vh - 68px);
            padding: 100px 0 80px;
        }
        .mypage-wrap {
            width: 50%;
            min-width: 600px;
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .mypage-card {
            background: #fff;
            border-radius: 16px;
            padding: 36px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        /* ---- 페이지 제목 & 뒤로가기 ---- */
        .page-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .btn-back {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #868e96;
            text-decoration: none;
            transition: color .15s;
        }
        .btn-back:hover { color: #e55039; }
        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
        }

        /* ---- 플래시 메시지 ---- */
        .flash-msg {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .flash-msg.success { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; }
        .flash-msg.error   { background: #fdf2f2; color: #c0392b; border: 1px solid #f1aeb5; }

        /* ---- 프로필 이미지 업로드 영역 ---- */
        .avatar-upload-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        /* 원형 미리보기 */
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #dee2e6;
            position: relative;
            cursor: pointer;
            flex-shrink: 0;
        }
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-preview svg {
            width: 52px;
            height: 52px;
            color: #adb5bd;
        }

        /* 이미지 위 변경 오버레이 */
        .avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s;
        }
        .avatar-preview:hover .avatar-overlay { opacity: 1; }
        .avatar-overlay span {
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        /* 숨겨진 실제 파일 input */
        #profileImageInput { display: none; }

        .avatar-hint {
            font-size: 12px;
            color: #adb5bd;
            text-align: center;
            line-height: 1.6;
        }

        /* ---- 폼 공통 ---- */
        .section-divider {
            border: none;
            border-top: 1px solid #f1f3f5;
            margin: 24px 0;
        }
        .section-label {
            font-size: 13px;
            font-weight: 700;
            color: #868e96;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 16px;
        }
        .field-group {
            margin-bottom: 20px;
        }
        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }
        .field-input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #212529;
            background: #fff;
            outline: none;
            transition: border-color .15s;
            box-sizing: border-box;
        }
        .field-input:focus { border-color: #e55039; }
        .field-input:read-only {
            background: #f8f9fa;
            color: #868e96;
            cursor: not-allowed;
        }
        .field-hint {
            font-size: 12px;
            color: #adb5bd;
            margin-top: 4px;
        }

        /* ---- 저장 버튼 ---- */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }
        .btn-save {
            flex: 1;
            padding: 13px;
            background: #e55039;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-save:hover { background: #c0392b; }

        /* ---- 비밀번호 변경 버튼 (카드 하단) ---- */
        .btn-pw-change {
            width: 100%;
            padding: 13px;
            background: #fff;
            color: #495057;
            font-size: 15px;
            font-weight: 600;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: border-color .2s, color .2s;
        }
        .btn-pw-change:hover {
            border-color: #e55039;
            color: #e55039;
        }

        /* ============================================================
           비밀번호 변경 모달
           ============================================================ */
        /* 모달 박스 넓이 조정 */
        #pwChangeModal .modal-box {
            max-width: 420px;
        }

        /* 스텝 인디케이터 */
        .pw-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }
        .pw-step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .pw-step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e9ecef;
            color: #adb5bd;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s, color .2s;
        }
        .pw-step-dot.active {
            background: #e55039;
            color: #fff;
        }
        .pw-step-dot.done {
            background: #2ecc71;
            color: #fff;
        }
        .pw-step-label {
            font-size: 11px;
            color: #adb5bd;
            white-space: nowrap;
        }
        .pw-step-label.active { color: #e55039; font-weight: 600; }
        .pw-step-label.done   { color: #2ecc71; }
        .pw-step-line {
            width: 48px;
            height: 2px;
            background: #e9ecef;
            margin-bottom: 16px;
            transition: background .2s;
        }
        .pw-step-line.done { background: #2ecc71; }

        /* 각 스텝 패널 */
        .pw-panel { display: none; }
        .pw-panel.active { display: block; }

        /* 스텝 내 폼 그룹 재활용 (auth-common 오버라이드 없이) */
        .pw-form-group {
            margin-bottom: 18px;
        }
        .pw-form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }
        .pw-form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #212529;
            background: #fff;
            outline: none;
            transition: border-color .15s;
            box-sizing: border-box;
        }
        .pw-form-input:focus { border-color: #e55039; }
        .pw-form-input.is-error { border-color: #e74c3c; }
        .pw-form-error {
            font-size: 12px;
            color: #e74c3c;
            margin-top: 4px;
            display: none;
        }
        .pw-form-error.show { display: block; }

        /* 모달 내 메시지 */
        .pw-modal-msg {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 16px;
            display: none;
        }
        .pw-modal-msg.error   { background: #fdf2f2; color: #c0392b; border: 1px solid #f1aeb5; display: block; }
        .pw-modal-msg.success { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; display: block; }

        /* 완료 스텝 (체크 아이콘) */
        .pw-done-icon {
            text-align: center;
            padding: 20px 0 8px;
        }
        .pw-done-icon svg {
            width: 56px;
            height: 56px;
            color: #2ecc71;
        }
        .pw-done-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #212529;
            margin: 12px 0 6px;
        }
        .pw-done-desc {
            text-align: center;
            font-size: 14px;
            color: #868e96;
            margin-bottom: 24px;
        }

        /* 버튼 공통 */
        .pw-btn-primary {
            width: 100%;
            padding: 12px;
            background: #e55039;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s;
        }
        .pw-btn-primary:hover    { background: #c0392b; }
        .pw-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

        /* 반응형 */
        @media (max-width: 768px) {
            .mypage-wrap {
                width: 100%;
                min-width: 0;
                padding: 0 16px;
            }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'mypage']) ?>

<!-- ===================== 프로필 수정 본문 ===================== -->
<div class="mypage-body">
    <div class="mypage-wrap">

        <!-- 플래시 메시지 출력 -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="flash-msg success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="flash-msg error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <!-- 프로필 정보 수정 카드 -->
        <div class="mypage-card">

            <!-- 상단: 뒤로가기 + 타이틀 -->
            <div class="page-top">
                <a href="/mypage" class="btn-back">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    마이페이지
                </a>
                <span style="color:#dee2e6">|</span>
                <h1 class="page-title">프로필 수정</h1>
            </div>

            <!-- 프로필 수정 폼 -->
            <form action="/mypage/profile" method="post" enctype="multipart/form-data" id="profileForm">
                <?= csrf_field() ?>

                <!-- 프로필 이미지 업로드 -->
                <div class="avatar-upload-wrap">
                    <!-- 원형 미리보기: 이미지 있으면 img, 없으면 기본 아이콘 -->
                    <div class="avatar-preview" id="avatarPreview" title="클릭하여 이미지 변경">
                        <?php if (! empty($user['profile_image'])): ?>
                            <img src="/uploads/profile/<?= esc($user['profile_image']) ?>"
                                 alt="프로필 이미지" id="avatarImg">
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" id="avatarDefaultIcon">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            <!-- JS로 미리보기 img 태그 동적 생성 -->
                            <img src="" alt="프로필 이미지" id="avatarImg" style="display:none">
                        <?php endif; ?>
                        <div class="avatar-overlay">
                            <span>변경</span>
                        </div>
                    </div>

                    <!-- 실제 파일 선택 input (숨김) -->
                    <input type="file" id="profileImageInput" name="profile_image"
                           accept=".jpg,.jpeg,.png,.webp">

                    <p class="avatar-hint">클릭하여 이미지 변경<br>JPG · PNG · WEBP / 최대 6MB</p>
                </div>

                <hr class="section-divider">

                <!-- 기본 정보 섹션 -->
                <p class="section-label">기본 정보</p>

                <!-- 아이디 (읽기 전용) -->
                <div class="field-group">
                    <label class="field-label" for="fieldId">아이디</label>
                    <input type="text" id="fieldId" class="field-input" readonly
                           value="<?= esc($user['id'] ?? '') ?>">
                    <p class="field-hint">아이디는 변경할 수 없습니다.</p>
                </div>

                <!-- 닉네임 / 이름 -->
                <div class="field-group">
                    <label class="field-label" for="fieldName">이름 / 닉네임</label>
                    <input type="text" id="fieldName" name="name" class="field-input"
                           placeholder="표시될 이름을 입력하세요"
                           maxlength="50"
                           value="<?= esc($user['name'] ?? '') ?>">
                </div>

                <!-- 이메일 -->
                <div class="field-group">
                    <label class="field-label" for="fieldEmail">이메일 <span style="color:#e74c3c">*</span></label>
                    <input type="email" id="fieldEmail" name="email" class="field-input"
                           placeholder="이메일 주소"
                           maxlength="100"
                           value="<?= esc($user['email'] ?? '') ?>"
                           required>
                </div>

                <!-- 저장 버튼 -->
                <div class="form-actions">
                    <button type="submit" class="btn-save">변경사항 저장</button>
                </div>

            </form>
        </div>

        <!-- 비밀번호 변경 카드 -->
        <div class="mypage-card">
            <button type="button" class="btn-pw-change" id="btnOpenPwChange">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                비밀번호 변경
            </button>
        </div>

    </div><!-- /.mypage-wrap -->
</div><!-- /.mypage-body -->

<!-- ============================================================
     비밀번호 변경 모달 (3단계: 현재 비밀번호 → 새 비밀번호 → 완료)
     ============================================================ -->
<div class="modal-overlay" id="pwChangeModal" role="dialog" aria-modal="true"
     aria-labelledby="pwModalTitle">
    <div class="modal-box">

        <div class="modal-header">
            <h2 class="modal-title" id="pwModalTitle">비밀번호 변경</h2>
            <button type="button" class="modal-close" id="btnClosePwModal" aria-label="닫기">&times;</button>
        </div>

        <!-- 스텝 인디케이터 -->
        <div class="pw-steps">
            <!-- 스텝 1 -->
            <div class="pw-step-item">
                <div class="pw-step-dot active" id="stepDot1">1</div>
                <span class="pw-step-label active" id="stepLabel1">현재 비밀번호</span>
            </div>
            <div class="pw-step-line" id="stepLine1"></div>
            <!-- 스텝 2 -->
            <div class="pw-step-item">
                <div class="pw-step-dot" id="stepDot2">2</div>
                <span class="pw-step-label" id="stepLabel2">새 비밀번호</span>
            </div>
            <div class="pw-step-line" id="stepLine2"></div>
            <!-- 스텝 3 -->
            <div class="pw-step-item">
                <div class="pw-step-dot" id="stepDot3">3</div>
                <span class="pw-step-label" id="stepLabel3">완료</span>
            </div>
        </div>

        <!-- 오류 메시지 -->
        <div class="pw-modal-msg" id="pwModalMsg"></div>

        <!-- 스텝 1: 현재 비밀번호 확인 -->
        <div class="pw-panel active" id="pwPanel1">
            <div class="pw-form-group">
                <label class="pw-form-label" for="currentPw">현재 비밀번호</label>
                <input type="password" id="currentPw" class="pw-form-input"
                       placeholder="현재 비밀번호를 입력하세요" autocomplete="current-password" maxlength="100">
                <span class="pw-form-error" id="errCurrentPw">현재 비밀번호를 입력해주세요.</span>
            </div>
            <button type="button" class="pw-btn-primary" id="btnStep1Next">다음</button>
        </div>

        <!-- 스텝 2: 새 비밀번호 입력 -->
        <div class="pw-panel" id="pwPanel2">
            <div class="pw-form-group">
                <label class="pw-form-label" for="newPw">새 비밀번호</label>
                <input type="password" id="newPw" class="pw-form-input"
                       placeholder="8자 이상 새 비밀번호" autocomplete="new-password" maxlength="100">
                <span class="pw-form-error" id="errNewPw">새 비밀번호를 8자 이상 입력해주세요.</span>
            </div>
            <div class="pw-form-group">
                <label class="pw-form-label" for="confirmPw">새 비밀번호 확인</label>
                <input type="password" id="confirmPw" class="pw-form-input"
                       placeholder="새 비밀번호 재입력" autocomplete="new-password" maxlength="100">
                <span class="pw-form-error" id="errConfirmPw">비밀번호가 일치하지 않습니다.</span>
            </div>
            <button type="button" class="pw-btn-primary" id="btnStep2Apply">적용</button>
        </div>

        <!-- 스텝 3: 완료 -->
        <div class="pw-panel" id="pwPanel3">
            <div class="pw-done-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <p class="pw-done-title">비밀번호가 변경되었습니다!</p>
            <p class="pw-done-desc">새 비밀번호로 로그인해주세요.</p>
            <button type="button" class="pw-btn-primary" id="btnPwDoneClose">확인</button>
        </div>

    </div>
</div>

<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<script>
(function () {
    'use strict';

    /* ================================================================
       프로필 이미지 미리보기
       ================================================================ */
    var avatarPreview  = document.getElementById('avatarPreview');
    var avatarInput    = document.getElementById('profileImageInput');
    var avatarImg      = document.getElementById('avatarImg');
    var avatarDefault  = document.getElementById('avatarDefaultIcon');

    /* 원형 미리보기 클릭 시 파일 선택 창 열기 */
    avatarPreview.addEventListener('click', function () {
        avatarInput.click();
    });

    /* 파일 선택 후 미리보기 갱신 */
    avatarInput.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;

        /* 확장자 검증 */
        var allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        var ext = file.name.split('.').pop().toLowerCase();
        if (allowedExt.indexOf(ext) === -1) {
            alert('JPG, PNG, WEBP 형식의 파일만 업로드할 수 있습니다.');
            this.value = '';
            return;
        }

        /* 용량 검증 (6MB) */
        if (file.size > 6 * 1024 * 1024) {
            alert('파일 크기는 최대 6MB까지 허용됩니다.');
            this.value = '';
            return;
        }

        /* FileReader로 미리보기 */
        var reader = new FileReader();
        reader.onload = function (e) {
            avatarImg.src = e.target.result;
            avatarImg.style.display = 'block';
            if (avatarDefault) avatarDefault.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    /* ================================================================
       비밀번호 변경 모달
       ================================================================ */
    var modal       = document.getElementById('pwChangeModal');
    var btnOpen     = document.getElementById('btnOpenPwChange');
    var btnClose    = document.getElementById('btnClosePwModal');
    var btnDoneClose = document.getElementById('btnPwDoneClose');

    /* 현재 스텝 (1~3) */
    var currentStep = 1;

    /* 모달 열기 */
    btnOpen.addEventListener('click', function () {
        resetModal();
        modal.classList.add('is-open');
    });

    /* 모달 닫기 */
    function closeModal() {
        modal.classList.remove('is-open');
        resetModal();
    }
    btnClose.addEventListener('click', closeModal);
    btnDoneClose.addEventListener('click', closeModal);

    /* 오버레이 클릭 시 닫기 */
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    /* ---- 스텝 전환 ---- */
    function goToStep(step) {
        /* 패널 전환 */
        document.querySelectorAll('.pw-panel').forEach(function (p) {
            p.classList.remove('active');
        });
        document.getElementById('pwPanel' + step).classList.add('active');

        /* 스텝 인디케이터 갱신 */
        for (var i = 1; i <= 3; i++) {
            var dot   = document.getElementById('stepDot' + i);
            var label = document.getElementById('stepLabel' + i);
            dot.classList.remove('active', 'done');
            label.classList.remove('active', 'done');

            if (i < step) {
                dot.classList.add('done');
                dot.innerHTML = '✓';
                label.classList.add('done');
            } else if (i === step) {
                dot.classList.add('active');
                label.classList.add('active');
            } else {
                dot.innerHTML = i;
            }
        }

        /* 연결선 갱신 */
        for (var j = 1; j <= 2; j++) {
            var line = document.getElementById('stepLine' + j);
            if (j < step) {
                line.classList.add('done');
            } else {
                line.classList.remove('done');
            }
        }

        currentStep = step;
        clearMsg();
    }

    /* ---- 모달 초기화 ---- */
    function resetModal() {
        document.getElementById('currentPw').value  = '';
        document.getElementById('newPw').value      = '';
        document.getElementById('confirmPw').value  = '';
        clearAllErrors();
        clearMsg();
        goToStep(1);
    }

    /* ---- 오류 표시 / 해제 ---- */
    function showError(inputEl, errEl, msg) {
        inputEl.classList.add('is-error');
        errEl.textContent = msg;
        errEl.classList.add('show');
    }
    function clearError(inputEl, errEl) {
        inputEl.classList.remove('is-error');
        errEl.classList.remove('show');
    }
    function clearAllErrors() {
        clearError(document.getElementById('currentPw'),  document.getElementById('errCurrentPw'));
        clearError(document.getElementById('newPw'),      document.getElementById('errNewPw'));
        clearError(document.getElementById('confirmPw'),  document.getElementById('errConfirmPw'));
    }

    /* ---- 모달 메시지 ---- */
    var msgEl = document.getElementById('pwModalMsg');
    function showMsg(text, type) {
        msgEl.textContent = text;
        msgEl.className   = 'pw-modal-msg ' + type;
    }
    function clearMsg() {
        msgEl.textContent = '';
        msgEl.className   = 'pw-modal-msg';
    }

    /* ---- 스텝 1: 다음 버튼 ---- */
    document.getElementById('btnStep1Next').addEventListener('click', function () {
        var currentPwEl  = document.getElementById('currentPw');
        var errCurrentEl = document.getElementById('errCurrentPw');

        clearAllErrors();
        clearMsg();

        if (!currentPwEl.value.trim()) {
            showError(currentPwEl, errCurrentEl, '현재 비밀번호를 입력해주세요.');
            return;
        }

        /* 서버에 현재 비밀번호 검증 요청 (1단계만 검증) */
        this.disabled = true;
        this.textContent = '확인 중...';

        var self = this;
        /* AJAX로 비밀번호 검증 전용 엔드포인트 대신,
           step=1 파라미터로 전체 API를 재사용 — 새 비밀번호 없이 current_password만 전송하면
           컨트롤러에서 current 검증 후 실패 응답이 오므로 통과 여부를 확인한다.
           단, step 구분 없이 바로 최종 API를 호출하면 새 비밀번호가 없어 에러가 나므로
           별도의 검증 파라미터(verify_only=1)를 사용한다. */
        fetch('/mypage/password/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                current_password: currentPwEl.value
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            self.disabled = false;
            self.textContent = '다음';
            if (data.success) {
                goToStep(2);
            } else {
                showError(currentPwEl, errCurrentEl, data.message || '현재 비밀번호가 올바르지 않습니다.');
            }
        })
        .catch(function () {
            self.disabled = false;
            self.textContent = '다음';
            showMsg('오류가 발생했습니다. 다시 시도해주세요.', 'error');
        });
    });

    /* ---- 스텝 2: 적용 버튼 ---- */
    document.getElementById('btnStep2Apply').addEventListener('click', function () {
        var currentPwEl  = document.getElementById('currentPw');
        var newPwEl      = document.getElementById('newPw');
        var confirmPwEl  = document.getElementById('confirmPw');
        var errNewEl     = document.getElementById('errNewPw');
        var errConfirmEl = document.getElementById('errConfirmPw');

        clearAllErrors();
        clearMsg();

        var valid = true;

        if (newPwEl.value.length < 8) {
            showError(newPwEl, errNewEl, '새 비밀번호는 8자 이상이어야 합니다.');
            valid = false;
        }
        if (newPwEl.value !== confirmPwEl.value) {
            showError(confirmPwEl, errConfirmEl, '비밀번호가 일치하지 않습니다.');
            valid = false;
        }
        if (!valid) return;

        this.disabled = true;
        this.textContent = '변경 중...';

        var self = this;
        fetch('/mypage/password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                current_password: currentPwEl.value,
                new_password:     newPwEl.value,
                confirm_password: confirmPwEl.value
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            self.disabled = false;
            self.textContent = '적용';
            if (data.success) {
                goToStep(3);
            } else {
                showMsg(data.message || '비밀번호 변경에 실패했습니다.', 'error');
            }
        })
        .catch(function () {
            self.disabled = false;
            self.textContent = '적용';
            showMsg('오류가 발생했습니다. 다시 시도해주세요.', 'error');
        });
    });

})();
</script>

</body>
</html>
