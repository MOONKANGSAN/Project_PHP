/**
 * 백오피스 공통 스크립트
 * - POST 폼의 bo-btn-primary submit 버튼에 3초 중복 클릭 방지 쿨다운 적용
 * - GET 검색 폼은 method 체크로 자동 제외
 */
(function () {
    'use strict';

    var COOLDOWN_MS  = 3000;
    var LOADING_TEXT = '처리 중...';
    // POST 폼 안의 bo-btn-primary submit 버튼만 대상
    var FORM_SEL     = 'form[method="post"], form[method="POST"]';
    var BTN_SEL      = 'button[type="submit"].bo-btn-primary';

    function lockButton(btn) {
        if (btn.dataset.locked === '1') return false;

        btn.dataset.locked       = '1';
        btn.dataset.originalText = btn.textContent.trim();
        btn.disabled             = true;
        btn.textContent          = LOADING_TEXT;
        btn.style.opacity        = '0.65';
        btn.style.cursor         = 'not-allowed';

        setTimeout(function () { unlockButton(btn); }, COOLDOWN_MS);
        return true;
    }

    function unlockButton(btn) {
        btn.dataset.locked = '0';
        btn.disabled       = false;
        btn.textContent    = btn.dataset.originalText || btn.textContent;
        btn.style.opacity  = '';
        btn.style.cursor   = '';
    }

    function bindForms() {
        document.querySelectorAll(FORM_SEL).forEach(function (form) {
            var btn = form.querySelector(BTN_SEL);
            if (!btn) return;

            form.addEventListener('submit', function (e) {
                var ok = lockButton(btn);
                if (!ok) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindForms);
    } else {
        bindForms();
    }
}());
