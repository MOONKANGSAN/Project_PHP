/* ===================== 나만의 코스 등록 폼 — 장소 검색/추가, 이미지 미리보기, AJAX 제출 ===================== */
(function initCourseSubmitForm() {
    var isLoggedIn = <?= session()->get('user.idx') ? 'true' : 'false' ?>;
    var eventIdx   = <?= (int) $event['idx'] ?>;

    var form = document.getElementById('courseSubmitForm');
    if (!form) return;

    var MIN_ITEMS = 3;
    var MAX_ITEMS = 8;

    var list      = document.getElementById('courseItemList');
    var btnAdd    = document.getElementById('btnAddCourseItem');
    var badge     = document.getElementById('courseItemBadge');
    var formMsg   = document.getElementById('courseFormMsg');
    var btnSubmit = document.getElementById('btnSubmitCourse');

    var thumbInput   = document.getElementById('courseThumbInput');
    var thumbPreview = document.getElementById('courseThumbPreview');

    var STAY_OPTIONS = [
        { value: '',           label: '선택 안함' },
        { value: '30분',       label: '30분' },
        { value: '1시간',      label: '1시간' },
        { value: '1시간 30분', label: '1시간 30분' },
        { value: '2시간',      label: '2시간' },
        { value: '3시간',      label: '3시간' },
        { value: '반나절',     label: '반나절' },
        { value: '하루',       label: '하루' },
    ];

    var itemCount = 0;
    var nextIdx   = 1;

    /* 대표 이미지 미리보기 */
    if (thumbInput) {
        thumbInput.addEventListener('change', function () {
            var file = thumbInput.files[0];
            if (!file) { thumbPreview.style.display = 'none'; return; }
            var reader = new FileReader();
            reader.onload = function (e) {
                thumbPreview.querySelector('img').src = e.target.result;
                thumbPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    function esc(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function buildRow(idx, removable) {
        var typeOpts = [
            { value: 'custom',     label: '직접 입력' },
            { value: 'restaurant', label: '맛집' },
            { value: 'place',      label: '관광지' },
            { value: 'event',      label: '축제' },
        ].map(function (o) {
            return '<option value="' + o.value + '">' + o.label + '</option>';
        }).join('');

        var stayOpts = STAY_OPTIONS.map(function (o) {
            return '<option value="' + esc(o.value) + '">' + esc(o.label) + '</option>';
        }).join('');

        var row = document.createElement('div');
        row.className = 'c-item-row';
        row.dataset.index = idx;
        row.innerHTML =
            '<div class="c-item-row-head">' +
                '<strong>장소 <span class="c-item-num">' + idx + '</span></strong>' +
                (removable ? '<button type="button" class="c-item-remove-btn">삭제</button>' : '') +
            '</div>' +
            '<div class="c-item-grid">' +
                '<div class="c-form-group">' +
                    '<label class="c-form-label">유형</label>' +
                    '<select name="items[' + idx + '][content_type]" class="c-form-select c-item-type-select">' + typeOpts + '</select>' +
                '</div>' +
                '<div class="c-form-group c-item-search-wrap" style="display:none;">' +
                    '<label class="c-form-label">검색 연결</label>' +
                    '<input type="text" class="c-form-input c-item-search-input" placeholder="이름으로 검색..." autocomplete="off">' +
                    '<div class="c-item-search-results"></div>' +
                    '<input type="hidden" name="items[' + idx + '][content_idx]" class="c-item-content-idx">' +
                '</div>' +
                '<div class="c-form-group c-form-group--wide">' +
                    '<label class="c-form-label">이름 <span class="c-required">*</span></label>' +
                    '<input type="text" name="items[' + idx + '][name]" class="c-form-input c-item-name" maxlength="100" placeholder="장소 이름">' +
                '</div>' +
                '<div class="c-form-group">' +
                    '<label class="c-form-label">체류 시간</label>' +
                    '<select name="items[' + idx + '][stay_time]" class="c-form-select">' + stayOpts + '</select>' +
                '</div>' +
                '<div class="c-form-group">' +
                    '<label class="c-form-label">주소</label>' +
                    '<input type="text" name="items[' + idx + '][address]" class="c-form-input c-item-address" maxlength="255" placeholder="검색 연결 시 자동 입력">' +
                '</div>' +
                '<div class="c-form-group c-form-group--wide">' +
                    '<label class="c-form-label">방문 이유 / 설명</label>' +
                    '<textarea name="items[' + idx + '][description]" class="c-form-textarea" rows="2" placeholder="왜 이 장소를 코스에 넣었는지 적어주세요"></textarea>' +
                '</div>' +
                '<input type="hidden" name="items[' + idx + '][latitude]" class="c-item-lat">' +
                '<input type="hidden" name="items[' + idx + '][longitude]" class="c-item-lng">' +
            '</div>';

        return row;
    }

    function bindRow(row) {
        var removeBtn = row.querySelector('.c-item-remove-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                row.remove();
                itemCount--;
                updateBadge();
                updateNumbers();
            });
        }

        var typeSelect = row.querySelector('.c-item-type-select');
        var searchWrap = row.querySelector('.c-item-search-wrap');
        typeSelect.addEventListener('change', function () {
            searchWrap.style.display = (this.value !== 'custom') ? '' : 'none';
            row.querySelector('.c-item-content-idx').value = '';
        });

        var searchInput = row.querySelector('.c-item-search-input');
        var contentIdx  = row.querySelector('.c-item-content-idx');
        var nameInput   = row.querySelector('.c-item-name');
        var addrInput   = row.querySelector('.c-item-address');
        var resultsBox  = row.querySelector('.c-item-search-results');
        var timer = null;

        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            var q = this.value.trim();
            if (!q || typeSelect.value === 'custom') {
                resultsBox.style.display = 'none';
                return;
            }
            timer = setTimeout(function () {
                fetch('/events/course-content-search?type=' + encodeURIComponent(typeSelect.value) + '&q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        resultsBox.innerHTML = '';
                        if (!data.length) { resultsBox.style.display = 'none'; return; }
                        data.forEach(function (item) {
                            var el = document.createElement('div');
                            el.className = 'c-item-search-result-item';
                            el.textContent = item.name + (item.address1 ? ' — ' + item.address1 : '');
                            el.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                contentIdx.value  = item.idx;
                                nameInput.value   = item.name;
                                addrInput.value   = item.address1 || '';
                                searchInput.value = item.name;
                                resultsBox.style.display = 'none';
                            });
                            resultsBox.appendChild(el);
                        });
                        resultsBox.style.display = 'block';
                    });
            }, 300);
        });

        searchInput.addEventListener('blur', function () {
            setTimeout(function () { resultsBox.style.display = 'none'; }, 150);
        });
    }

    function addItem(removable) {
        var idx = nextIdx++;
        var row = buildRow(idx, removable);
        list.appendChild(row);
        bindRow(row);
        itemCount++;
        updateBadge();
    }

    function updateBadge() {
        badge.textContent = itemCount + ' / ' + MAX_ITEMS;
        btnAdd.disabled = itemCount >= MAX_ITEMS;
    }

    function updateNumbers() {
        list.querySelectorAll('.c-item-row').forEach(function (row, i) {
            var numEl = row.querySelector('.c-item-num');
            if (numEl) numEl.textContent = i + 1;
        });
    }

    /* 기본 3개 행은 삭제 불가 (최소 등록 수 보장), 4개째부터 삭제 가능 */
    for (var i = 0; i < MIN_ITEMS; i++) addItem(false);

    btnAdd.addEventListener('click', function () {
        if (itemCount >= MAX_ITEMS) return;
        addItem(true);
    });

    function showMsg(msg, type) {
        formMsg.textContent = msg;
        formMsg.className = 'c-form-msg ' + type;
        formMsg.style.display = 'block';
    }
    function hideMsg() {
        formMsg.style.display = 'none';
        formMsg.textContent = '';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideMsg();

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

        var titleInput = document.getElementById('courseTitle');
        var titleErr   = document.getElementById('cerr-title');
        titleErr.textContent = '';
        if (!titleInput.value.trim()) {
            titleErr.textContent = '코스명을 입력해주세요.';
            return;
        }

        var nameFilled = 0;
        list.querySelectorAll('.c-item-name').forEach(function (inp) {
            if (inp.value.trim()) nameFilled++;
        });
        if (nameFilled < MIN_ITEMS) {
            showMsg('장소를 최소 ' + MIN_ITEMS + '곳 이상 입력해주세요. (현재 ' + nameFilled + '곳)', 'error');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.textContent = '제출 중...';

        fetch('/events/' + eventIdx + '/course-submit', {
            method: 'POST',
            body: new FormData(form),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showMsg(data.message, 'success');
                    form.reset();
                    list.innerHTML = '';
                    itemCount = 0;
                    nextIdx = 1;
                    for (var i = 0; i < MIN_ITEMS; i++) addItem(false);
                    thumbPreview.style.display = 'none';
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
                    btnSubmit.textContent = '🚀 코스 제출하기';
                }, 1000);
            });
    });
})();
