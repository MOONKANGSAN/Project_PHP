/* =============================================
   회원가입 모달 — 열기/닫기, 이메일 조합, 유효성 검사, AJAX 제출
   ============================================= */
(function initSignupModal() {
    const overlay       = document.getElementById('signupModal');
    const btnOpen       = document.getElementById('btnOpenSignup');
    const btnClose      = document.getElementById('btnCloseSignup');
    const form          = document.getElementById('signupForm');
    const domainSelect  = document.getElementById('emailDomainSelect');
    const domainDirect  = document.getElementById('emailDomainDirect');
    const emailFull     = document.getElementById('emailFull');
    const formMsg       = document.getElementById('formMsg');
    const btnSubmit     = document.getElementById('btnSubmitSignup');
    if (!overlay || !btnOpen) return;

    /* 모달 열기 */
    function openModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('signupId').focus();
    }

    /* 모달 닫기 + 폼 초기화 */
    function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        form.reset();
        domainDirect.style.display = 'none';
        domainSelect.style.display = '';
        clearAllErrors();
        hideMsg();
    }

    btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);

    /* 오버레이 배경 클릭 시 닫기 */
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    /* ESC 키 닫기 */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });

    /* ---- 이메일 도메인 셀렉트 처리 ---- */
    domainSelect.addEventListener('change', () => {
        if (domainSelect.value === 'direct') {
            domainSelect.style.display = 'none';
            domainDirect.style.display = '';
            domainDirect.value = '';
            domainDirect.focus();
        }
    });

    /* 직접입력 필드에서 빈값이 되면 셀렉트로 복귀 */
    domainDirect.addEventListener('input', () => {
        if (domainDirect.value === '') {
            domainDirect.style.display = 'none';
            domainSelect.style.display = '';
            domainSelect.value = 'naver.com';
        }
    });

    /* ---- 이메일 완성값 조합 ---- */
    function buildEmail() {
        const local  = document.getElementById('emailLocal').value.trim();
        const domain = domainSelect.style.display === 'none'
            ? domainDirect.value.trim()
            : domainSelect.value;
        return local && domain ? `${local}@${domain}` : '';
    }

    /* ---- 에러 표시 / 제거 ---- */
    function setError(fieldKey, msg) {
        const errEl  = document.getElementById(`err-${fieldKey}`);
        const fgEl   = document.getElementById(`fg-${fieldKey}`);
        if (errEl) errEl.textContent = msg;
        if (fgEl)  fgEl.querySelectorAll('.form-input, .form-select').forEach(el => el.classList.add('is-error'));
    }
    function clearError(fieldKey) {
        const errEl = document.getElementById(`err-${fieldKey}`);
        const fgEl  = document.getElementById(`fg-${fieldKey}`);
        if (errEl) errEl.textContent = '';
        if (fgEl)  fgEl.querySelectorAll('.form-input, .form-select').forEach(el => el.classList.remove('is-error'));
    }
    function clearAllErrors() {
        ['id', 'password', 'password_confirm', 'email', 'phone'].forEach(clearError);
    }

    /* ---- 전송 메시지 ---- */
    function showMsg(msg, type) {
        formMsg.textContent = msg;
        formMsg.className   = `form-msg ${type}`;
        formMsg.style.display = 'block';
    }
    function hideMsg() {
        formMsg.style.display = 'none';
        formMsg.textContent   = '';
    }

    /* ---- 클라이언트 사전 검증 ---- */
    function validateClient() {
        let valid = true;
        clearAllErrors();

        const id     = document.getElementById('signupId').value.trim();
        const pw     = document.getElementById('signupPw').value;
        const pwc    = document.getElementById('signupPwConfirm').value;
        const emailL = document.getElementById('emailLocal').value.trim();

        if (id.length < 4)                    { setError('id', '아이디는 4자 이상 입력해주세요.'); valid = false; }
        if (pw.length < 8)                    { setError('password', '비밀번호는 8자 이상 입력해주세요.'); valid = false; }
        if (pw !== pwc)                       { setError('password_confirm', '비밀번호가 일치하지 않습니다.'); valid = false; }
        if (!emailL)                          { setError('email', '이메일을 입력해주세요.'); valid = false; }
        else if (!/^[^\s@]+$/.test(emailL))  { setError('email', '이메일 아이디에 잘못된 문자가 포함되어 있습니다.'); valid = false; }

        return valid;
    }

    /* ---- 폼 AJAX 제출 ---- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMsg();
        if (!validateClient()) return;

        /* 숨겨진 email 필드에 완성된 이메일 값 설정 */
        emailFull.value = buildEmail();

        const formData = new FormData(form);
        btnSubmit.disabled = true;
        btnSubmit.textContent = '처리 중...';

        try {
            const res  = await fetch('/auth/register', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                showMsg(data.message, 'success');
                form.reset();
                domainDirect.style.display = 'none';
                domainSelect.style.display = '';
                /* 2초 후 자동으로 모달 닫기 */
                setTimeout(closeModal, 2000);
            } else {
                /* 서버 에러를 각 필드에 표시 */
                if (data.errors) {
                    Object.entries(data.errors).forEach(([key, msg]) => setError(key, msg));
                } else {
                    showMsg('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            }
        } catch {
            showMsg('서버 연결에 실패했습니다. 잠시 후 다시 시도해주세요.', 'error');
        } finally {
            /* 3초 후 버튼 복원 (연속 클릭 방지) */
            setTimeout(function () {
                btnSubmit.disabled    = false;
                btnSubmit.textContent = '가입하기';
            }, 3000);
        }
    });
})();

/* =============================================
   휴대폰 번호 자동 하이픈 (010-XXXX-XXXX)
   ============================================= */
(function initPhoneFormat() {
    const input = document.getElementById('signupPhone');
    if (!input) return;

    input.addEventListener('input', function () {
        /* 숫자만 추출 후 최대 11자리 제한 */
        const digits = this.value.replace(/\D/g, '').slice(0, 11);

        let formatted;
        if (digits.length <= 3) {
            formatted = digits;
        } else if (digits.length <= 7) {
            formatted = digits.slice(0, 3) + '-' + digits.slice(3);
        } else {
            formatted = digits.slice(0, 3) + '-' + digits.slice(3, 7) + '-' + digits.slice(7);
        }

        this.value = formatted;
    });
})();
