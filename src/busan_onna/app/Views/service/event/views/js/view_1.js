(function initReviewModal() {
    /* ===================== 방문 후기 모달 — 열기/닫기, AJAX 등록 ===================== */
    var isLoggedIn = <?= session()->get('user.idx') ? 'true' : 'false' ?>;
    var eventIdx   = <?= (int) $event['idx'] ?>;

    var overlay      = document.getElementById('reviewModal');
    var btnOpen      = document.getElementById('btnOpenReview');
    var btnClose     = document.getElementById('btnCloseReview');
    var form         = document.getElementById('reviewForm');
    var formMsg      = document.getElementById('reviewFormMsg');
    var btnSubmit    = document.getElementById('btnSubmitReview');
    var photoInput   = document.getElementById('reviewPhoto');
    var photoPreview = document.getElementById('reviewPhotoPreview');
    var reviewList   = document.getElementById('reviewList');
    if (!overlay || !btnOpen || !form) return;

    /* 모달 열기 — 비로그인 시 로그인 모달로 유도 */
    function openModal() {
        if (!isLoggedIn) {
            var loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            } else {
                alert('로그인이 필요합니다.');
            }
            return;
        }
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('reviewContent').focus();
    }

    /* 모달 닫기 + 폼 초기화 */
    function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        form.reset();
        if (photoPreview) photoPreview.style.display = 'none';
        clearErrors();
        hideMsg();
    }

    btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });

    /* 사진 미리보기 */
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            var file = photoInput.files[0];
            if (!file) { photoPreview.style.display = 'none'; return; }
            var reader = new FileReader();
            reader.onload = function (e) {
                photoPreview.querySelector('img').src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    /* ---- 에러 표시 / 제거 ---- */
    function setError(key, msg) {
        var errEl = document.getElementById('rerr-' + key);
        var fgEl  = document.getElementById('rfg-' + key);
        if (errEl) errEl.textContent = msg;
        if (fgEl)  fgEl.querySelectorAll('.form-input').forEach(function (el) { el.classList.add('is-error'); });
    }
    function clearErrors() {
        ['content', 'photo'].forEach(function (key) {
            var errEl = document.getElementById('rerr-' + key);
            var fgEl  = document.getElementById('rfg-' + key);
            if (errEl) errEl.textContent = '';
            if (fgEl)  fgEl.querySelectorAll('.form-input').forEach(function (el) { el.classList.remove('is-error'); });
        });
    }

    /* ---- 메시지 ---- */
    function showMsg(msg, type) {
        formMsg.textContent = msg;
        formMsg.className = 'form-msg ' + type;
        formMsg.style.display = 'block';
    }
    function hideMsg() {
        formMsg.style.display = 'none';
        formMsg.textContent = '';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    /* 등록 성공 시 목록 맨 위에 새 후기 카드 삽입 (새로고침 없이) */
    function prependReviewCard(review) {
        var emptyMsg = document.getElementById('reviewEmptyMsg');
        if (emptyMsg) emptyMsg.remove();

        var card = document.createElement('div');
        card.className = 'g-review-card';

        var spotHtml = review.spot_name
            ? '<span class="g-review-spot">📍 ' + escapeHtml(review.spot_name) + '</span>'
            : '';
        var photoHtml = review.photo_url
            ? '<div class="g-review-photo"><img src="' + review.photo_url + '" alt="후기 사진" loading="lazy"></div>'
            : '';

        card.innerHTML =
            '<div class="g-review-top">' +
                '<span class="g-review-user">' + escapeHtml(review.user_id) + '</span>' +
                '<span class="g-review-date">' + escapeHtml(review.reg_date) + '</span>' +
            '</div>' +
            spotHtml +
            '<div class="g-review-content">' + escapeHtml(review.content).replace(/\n/g, '<br>') + '</div>' +
            photoHtml;

        reviewList.insertBefore(card, reviewList.firstChild);
    }

    /* ---- AJAX 폼 제출 ---- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideMsg();
        clearErrors();

        if (!document.getElementById('reviewContent').value.trim()) {
            setError('content', '후기 내용을 입력해주세요.');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.textContent = '등록 중...';

        fetch('/events/' + eventIdx + '/reviews', {
            method: 'POST',
            body: new FormData(form),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                prependReviewCard(data.review);
                showMsg(data.message, 'success');
                setTimeout(closeModal, 900);
            } else {
                showMsg(data.message || '오류가 발생했습니다. 다시 시도해주세요.', 'error');
            }
        })
        .catch(function () {
            showMsg('서버 연결에 실패했습니다. 잠시 후 다시 시도해주세요.', 'error');
        })
        .finally(function () {
            setTimeout(function () {
                btnSubmit.disabled = false;
                btnSubmit.textContent = '후기 등록하기';
            }, 1000);
        });
    });
})();
