<?= view('backoffice/partials/header', $this->data) ?>

<?php
// 등록/수정 모드 공통 변수 세팅
$isEdit  = $mode === 'edit';
$action  = $isEdit ? "/backoffice/travel-courses/{$course['idx']}/edit" : '/backoffice/travel-courses/register';
$oldVal  = fn(string $key, $default = '') => old($key, $course[$key] ?? $default);
$regions = ['강서구','금정구','기장군','남구','동구','동래구','부산진구','북구','사상구','사하구','서구','수영구','연제구','영도구','중구','해운대구'];

// 수정 모드: 기존 항목을 JS로 전달
$initItems = $course_items ?? [];
?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $isEdit ? '여행코스 수정' : '여행코스 등록' ?></h1>
            <p class="bo-page-desc"><?= $isEdit ? '여행코스 정보를 수정합니다.' : '새 여행코스를 등록합니다.' ?></p>
        </div>
        <a href="/backoffice/travel-courses" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<?php if (session()->getFlashdata('form_errors')): ?>
    <div class="bo-alert bo-alert-error">
        <?php foreach ((array)session()->getFlashdata('form_errors') as $err): ?>
            <p><?= esc($err) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- 기본 정보 -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">기본 정보</h3>
        <div class="bo-form-grid">

            <div class="bo-form-group bo-col-2">
                <label class="bo-form-label">코스명 <span class="bo-required">*</span></label>
                <input type="text" name="title" class="bo-form-input"
                       value="<?= esc($oldVal('title')) ?>" required maxlength="100"
                       placeholder="예) 해운대 당일 여행 코스">
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">상태 <span class="bo-required">*</span></label>
                <select name="state" class="bo-form-select">
                    <option value="1" <?= $oldVal('state', 1) == 1 ? 'selected' : '' ?>>활성</option>
                    <option value="0" <?= $oldVal('state', 1) == 0 ? 'selected' : '' ?>>비활성</option>
                </select>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">대표 지역</label>
                <select name="sido" class="bo-form-select">
                    <option value="">-- 지역 선택 --</option>
                    <?php foreach ($regions as $gu): ?>
                        <option value="<?= esc($gu) ?>" <?= $oldVal('sido') === $gu ? 'selected' : '' ?>>
                            <?= esc($gu) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bo-form-group bo-col-full">
                <label class="bo-form-label">코스 소개</label>
                <textarea name="description" class="bo-form-textarea" rows="3"
                          placeholder="코스에 대한 간략한 소개를 입력하세요."><?= esc($oldVal('description')) ?></textarea>
            </div>

        </div>
    </div>

    <!-- 대표 이미지 -->
    <?php
    // 수정 모드에서 기존 대표 이미지 유무 확인
    $hasThumb = $isEdit && !empty($course['thumb_url']);
    ?>
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">
            대표 이미지
            <span class="bo-img-count-badge" id="thumbCountBadge">
                <?= $hasThumb ? '1' : '0' ?> / 1
            </span>
        </h3>

        <!-- 기존 대표 이미지 (수정 모드) -->
        <?php if ($hasThumb): ?>
        <div class="bo-img-grid" id="existingThumbGrid">
            <div class="bo-img-card" id="thumbCard-existing">
                <span class="bo-img-order">1</span>
                <img src="<?= esc($course['thumb_url']) ?>" alt="대표 이미지">
                <input type="checkbox" name="delete_thumb" value="1"
                       id="delThumbCheck" hidden>
                <button type="button" class="bo-img-delete-btn"
                        onclick="toggleDeleteThumb()">×</button>
                <div class="bo-img-delete-overlay" id="thumbOverlay">삭제 예정</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 새 이미지 미리보기 그리드 (JS가 동적으로 추가) -->
        <div class="bo-img-grid" id="thumbPreviewGrid"></div>

        <!-- 업로드 영역 -->
        <div class="bo-upload-area <?= $hasThumb ? 'bo-upload-area--full' : '' ?>"
             id="thumbUploadArea">
            <div class="bo-upload-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
            </div>
            <p class="bo-upload-text">클릭하거나 이미지를 드래그하여 업로드</p>
            <p class="bo-upload-hint">JPG · PNG · WEBP · GIF &nbsp;|&nbsp; 최대 5 MB &nbsp;|&nbsp; 1장</p>
            <input type="file" name="thumb_img" id="thumbInput"
                   accept="image/jpeg,image/png,image/webp,image/gif" hidden>
        </div>
    </div>

    <!-- 코스 항목 (최대 8개) -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">
            코스 항목
            <span class="bo-img-count-badge" id="itemCountBadge">0 / 8</span>
        </h3>
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
            각 장소를 방문 순서대로 추가하세요. 맛집·관광지·행사를 검색해 연결하거나 직접 입력할 수 있습니다.
        </p>

        <div id="courseItemList"></div>

        <button type="button" id="btnAddItem" class="bo-btn bo-btn-ghost" style="margin-top:12px;">
            + 항목 추가
        </button>
    </div>

    <div class="bo-form-footer">
        <a href="/backoffice/travel-courses" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $isEdit ? '수정 저장' : '등록하기' ?>
        </button>
    </div>
</form>

<style>
/* 코스 항목 행 스타일 */
.course-item-row {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    background: #fafafa;
}
.course-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.course-item-num {
    font-size: 13px;
    color: #374151;
}
.course-item-num strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    font-size: 12px;
    margin-left: 4px;
}
.item-search-wrap { position: relative; }
.item-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 100;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    min-width: 280px;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.item-search-result-item {
    padding: 8px 12px;
    font-size: 13px;
    cursor: pointer;
}
.item-search-result-item:hover { background: #f3f4f6; }
</style>

<script>
// ---------------------------------------------------------------
// 대표 이미지 업로드 UI (맛집 관리와 동일한 패턴, MAX=1)
// ---------------------------------------------------------------
(function () {
    var MAX_THUMB    = 1;
    var uploadArea   = document.getElementById('thumbUploadArea');
    var thumbInput   = document.getElementById('thumbInput');
    var previewGrid  = document.getElementById('thumbPreviewGrid');
    var badge        = document.getElementById('thumbCountBadge');

    // 수정 모드에서 기존 이미지 유무
    var hasExisting  = <?= $hasThumb ? 'true' : 'false' ?>;
    var existDeleted = false;
    var selectedFile = null;

    function getActiveCount() {
        var existing = (hasExisting && !existDeleted) ? 1 : 0;
        return existing + (selectedFile ? 1 : 0);
    }
    function updateBadge() {
        badge.textContent = getActiveCount() + ' / 1';
        uploadArea.classList.toggle('bo-upload-area--full', getActiveCount() >= MAX_THUMB);
    }

    uploadArea.addEventListener('click', function () {
        if (getActiveCount() < MAX_THUMB) thumbInput.click();
    });
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    uploadArea.addEventListener('dragleave', function () {
        this.classList.remove('drag-over');
    });
    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });

    thumbInput.addEventListener('change', function () {
        var copied = Array.from(this.files);
        this.value = '';
        handleFiles(copied);
    });

    function handleFiles(fileList) {
        if (getActiveCount() >= MAX_THUMB) return;
        var file = Array.from(fileList).find(function (f) { return f.type.startsWith('image/'); });
        if (!file) return;
        selectedFile = file;
        syncInput();
        renderPreview();
        updateBadge();
    }

    function syncInput() {
        var dt = new DataTransfer();
        if (selectedFile) dt.items.add(selectedFile);
        thumbInput.files = dt.files;
    }

    function renderPreview() {
        previewGrid.innerHTML = '';
        if (!selectedFile) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            var card = document.createElement('div');
            card.className = 'bo-img-card bo-img-card--new';

            var order = document.createElement('span');
            order.className = 'bo-img-order';
            order.textContent = '1';
            card.appendChild(order);

            var img = document.createElement('img');
            img.src = e.target.result;
            card.appendChild(img);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'bo-img-delete-btn';
            btn.textContent = '×';
            btn.addEventListener('click', function () {
                selectedFile = null;
                syncInput();
                renderPreview();
                updateBadge();
            });
            card.appendChild(btn);
            previewGrid.appendChild(card);
        };
        reader.readAsDataURL(selectedFile);
    }

    // 기존 이미지 삭제 토글
    window.toggleDeleteThumb = function () {
        var card    = document.getElementById('thumbCard-existing');
        var overlay = document.getElementById('thumbOverlay');
        var check   = document.getElementById('delThumbCheck');

        if (existDeleted) {
            existDeleted  = false;
            check.checked = false;
            card.classList.remove('bo-img-card--deleted');
            overlay.style.display = 'none';
        } else {
            existDeleted  = true;
            check.checked = true;
            card.classList.add('bo-img-card--deleted');
            overlay.style.display = 'flex';
        }
        updateBadge();
    };

    updateBadge();
})();
</script>

<script>
(function () {
    const MAX_ITEMS  = 8;
    const list       = document.getElementById('courseItemList');
    const btnAdd     = document.getElementById('btnAddItem');
    const badge      = document.getElementById('itemCountBadge');

    // 수정 모드: 서버에서 내려온 기존 항목 데이터
    const INIT_ITEMS = <?= json_encode(array_values($initItems), JSON_UNESCAPED_UNICODE) ?>;

    // STAY_TIME 선택지
    const STAY_OPTIONS = [
        { value: '',        label: '선택 안함' },
        { value: '30분',    label: '30분' },
        { value: '1시간',   label: '1시간' },
        { value: '1시간 30분', label: '1시간 30분' },
        { value: '2시간',   label: '2시간' },
        { value: '3시간',   label: '3시간' },
        { value: '반나절',  label: '반나절' },
        { value: '하루',    label: '하루' },
    ];

    let itemCount = 0;
    // 고유 인덱스: timestamp 기반
    let nextIdx = Date.now();

    // 페이지 로드 시 기존 항목 렌더링
    INIT_ITEMS.forEach(function (it) {
        addItem(it);
    });
    updateBadge();

    btnAdd.addEventListener('click', function () {
        if (itemCount >= MAX_ITEMS) {
            alert('코스 항목은 최대 8개까지 등록할 수 있습니다.');
            return;
        }
        addItem(null);
    });

    /**
     * 항목 행 추가
     * @param {object|null} data  기존 데이터 (수정 모드) or null (신규)
     */
    function addItem(data) {
        const idx = nextIdx++;
        const row = buildRow(idx, data);
        list.appendChild(row);
        bindRowEvents(row, idx);
        itemCount++;
        updateBadge();
        updateOrderLabels();
    }

    /**
     * 항목 행 DOM 생성
     */
    function buildRow(idx, data) {
        data = data || {};

        const contentType = data.content_type || 'custom';
        const stayTime    = data.stay_time    || '';

        // stay_time <select> 옵션 HTML
        const stayOpts = STAY_OPTIONS.map(function (o) {
            const sel = o.value === stayTime ? ' selected' : '';
            return '<option value="' + esc(o.value) + '"' + sel + '>' + esc(o.label) + '</option>';
        }).join('');

        // content_type <select> 옵션
        const typeOpts = [
            { value: 'custom',     label: '직접 입력' },
            { value: 'restaurant', label: '맛집' },
            { value: 'place',      label: '관광지' },
            { value: 'event',      label: '행사·축제' },
        ].map(function (o) {
            const sel = o.value === contentType ? ' selected' : '';
            return '<option value="' + o.value + '"' + sel + '>' + o.label + '</option>';
        }).join('');

        const showSearch = contentType !== 'custom' ? '' : 'display:none;';

        const div = document.createElement('div');
        div.className        = 'course-item-row';
        div.dataset.index    = idx;
        div.innerHTML = `
<div class="course-item-header">
    <span class="course-item-num">항목 <strong>?</strong></span>
    <button type="button" class="bo-btn-action delete btn-remove-item">삭제</button>
</div>
<div class="bo-form-grid" style="margin-top:8px;">

    <div class="bo-form-group">
        <label class="bo-form-label">유형</label>
        <select name="items[${idx}][content_type]" class="bo-form-select item-type-select">
            ${typeOpts}
        </select>
    </div>

    <div class="bo-form-group item-search-wrap" style="${showSearch}">
        <label class="bo-form-label">검색 연결</label>
        <div style="position:relative;">
            <input type="text" class="bo-form-input item-search-input"
                   value="${esc(data.name || '')}"
                   placeholder="이름으로 검색..." autocomplete="off">
            <div class="item-search-results"></div>
        </div>
        <input type="hidden" name="items[${idx}][content_idx]"
               class="item-content-idx" value="${esc(data.content_idx || '')}">
    </div>

    <div class="bo-form-group bo-col-2">
        <label class="bo-form-label">이름 <span class="bo-required">*</span></label>
        <input type="text" name="items[${idx}][name]" class="bo-form-input item-name"
               value="${esc(data.name || '')}"
               required maxlength="100" placeholder="장소 이름">
    </div>

    <div class="bo-form-group">
        <label class="bo-form-label">체류 시간</label>
        <select name="items[${idx}][stay_time]" class="bo-form-select">
            ${stayOpts}
        </select>
    </div>

    <div class="bo-form-group bo-col-full">
        <label class="bo-form-label">주소</label>
        <input type="text" name="items[${idx}][address]" class="bo-form-input item-address"
               value="${esc(data.address || '')}"
               maxlength="255" placeholder="주소 (검색 연결 시 자동 입력)">
    </div>

    <div class="bo-form-group bo-col-full">
        <label class="bo-form-label">설명</label>
        <textarea name="items[${idx}][description]" class="bo-form-textarea"
                  rows="2" placeholder="이 장소에 대한 간략한 설명">${esc(data.description || '')}</textarea>
    </div>

    <input type="hidden" name="items[${idx}][latitude]"  class="item-lat"  value="${esc(data.latitude  || '')}">
    <input type="hidden" name="items[${idx}][longitude]" class="item-lng"  value="${esc(data.longitude || '')}">
</div>`;
        return div;
    }

    /**
     * 항목 행 이벤트 바인딩
     */
    function bindRowEvents(row) {
        // 삭제 버튼
        row.querySelector('.btn-remove-item').addEventListener('click', function () {
            row.remove();
            itemCount--;
            updateBadge();
            updateOrderLabels();
        });

        // 유형 변경 → 검색 영역 토글
        const typeSelect = row.querySelector('.item-type-select');
        const searchWrap = row.querySelector('.item-search-wrap');

        typeSelect.addEventListener('change', function () {
            searchWrap.style.display = (this.value !== 'custom') ? '' : 'none';
            // 유형 변경 시 연결 idx 초기화
            row.querySelector('.item-content-idx').value = '';
        });

        // 콘텐츠 검색 Ajax
        const searchInput = row.querySelector('.item-search-input');
        const contentIdx  = row.querySelector('.item-content-idx');
        const nameInput   = row.querySelector('.item-name');
        const addrInput   = row.querySelector('.item-address');
        const resultsBox  = row.querySelector('.item-search-results');

        if (!searchInput) return;

        let timer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            if (!q || typeSelect.value === 'custom') {
                resultsBox.style.display = 'none';
                return;
            }
            timer = setTimeout(function () {
                fetch('/backoffice/travel-courses/content-search?type=' +
                      encodeURIComponent(typeSelect.value) + '&q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        resultsBox.innerHTML = '';
                        if (!data.length) { resultsBox.style.display = 'none'; return; }
                        data.forEach(function (item) {
                            const el = document.createElement('div');
                            el.className   = 'item-search-result-item';
                            el.textContent = item.name + (item.address1 ? ' — ' + item.address1 : '');
                            el.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                contentIdx.value   = item.idx;
                                nameInput.value    = item.name;
                                addrInput.value    = item.address1 || '';
                                searchInput.value  = item.name;
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

    function updateBadge() {
        badge.textContent = itemCount + ' / 8';
        btnAdd.disabled   = itemCount >= MAX_ITEMS;
    }

    function updateOrderLabels() {
        list.querySelectorAll('.course-item-row').forEach(function (row, i) {
            const strong = row.querySelector('.course-item-num strong');
            if (strong) strong.textContent = i + 1;
        });
    }

    // XSS 방지용 간단 escape
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
})();
</script>

<?= view('backoffice/partials/footer') ?>
