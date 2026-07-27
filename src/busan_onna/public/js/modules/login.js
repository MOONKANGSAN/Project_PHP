/* =============================================
   로그인 모달 — 열기/닫기, 유효성 검사, AJAX 제출
   ============================================= */
(function initLoginModal() {
    const overlay    = document.getElementById('loginModal');
    const btnOpen    = document.getElementById('btnOpenLogin');
    const btnClose   = document.getElementById('btnCloseLogin');
    const form       = document.getElementById('loginForm');
    const formMsg    = document.getElementById('loginFormMsg');
    const btnSubmit  = document.getElementById('btnSubmitLogin');
    const btnSwitch  = document.getElementById('btnSwitchToSignup');
    if (!overlay) return;

    /* 모달 열기 */
    function openModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        /* 아이디 저장 쿠키가 없으면 아이디 필드 포커스, 있으면 비밀번호 필드 포커스 */
        const loginId = document.getElementById('loginId');
        const target  = loginId.value ? document.getElementById('loginPw') : loginId;
        target.focus();
    }

    /* 모달 닫기 + 폼 초기화 */
    function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        /* 아이디 저장 쿠키 값은 유지, 비밀번호만 초기화 */
        document.getElementById('loginPw').value = '';
        clearAllErrors();
        hideMsg();
    }

    if (btnOpen)  btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });

    /* 로그인 → 회원가입 모달 전환 */
    if (btnSwitch) {
        btnSwitch.addEventListener('click', () => {
            closeModal();
            setTimeout(() => {
                const signupModal = document.getElementById('signupModal');
                if (signupModal) {
                    signupModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }
            }, 200);
        });
    }

    /* ---- 에러 표시 / 제거 ---- */
    function setError(key, msg) {
        const errEl = document.getElementById(`lerr-${key}`);
        const fgEl  = document.getElementById(`lfg-${key}`);
        if (errEl) errEl.textContent = msg;
        if (fgEl)  fgEl.querySelectorAll('.form-input').forEach(el => el.classList.add('is-error'));
    }
    function clearError(key) {
        const errEl = document.getElementById(`lerr-${key}`);
        const fgEl  = document.getElementById(`lfg-${key}`);
        if (errEl) errEl.textContent = '';
        if (fgEl)  fgEl.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-error'));
    }
    function clearAllErrors() { ['id', 'password'].forEach(clearError); }

    /* ---- 메시지 ---- */
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
        if (!document.getElementById('loginId').value.trim()) {
            setError('id', '아이디를 입력해주세요.'); valid = false;
        }
        if (!document.getElementById('loginPw').value) {
            setError('password', '비밀번호를 입력해주세요.'); valid = false;
        }
        return valid;
    }

    /* ---- AJAX 폼 제출 ---- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMsg();
        if (!validateClient()) return;

        btnSubmit.disabled     = true;
        btnSubmit.textContent  = '로그인 중...';

        try {
            const res  = await fetch('/auth/login', { method: 'POST', body: new FormData(form) });
            const data = await res.json();

            if (data.success) {
                showMsg(data.message, 'success');
                /* 세션 상태를 서버에서 다시 렌더링하기 위해 페이지 새로고침 */
                setTimeout(() => window.location.reload(), 800);
            } else {
                if (data.errors) {
                    Object.entries(data.errors).forEach(([k, v]) => setError(k, v));
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
                btnSubmit.textContent = '로그인';
            }, 3000);
        }
    });
})();
