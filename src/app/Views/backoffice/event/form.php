<?= view('backoffice/partials/header', $this->data) ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit ? "/backoffice/festivals/{$item['idx']}/edit" : '/backoffice/festivals/register';
$oldVal = fn(string $key, $default = '') => old($key, $item[$key] ?? $default);

// 기존 이미지 & 남은 슬롯 수
$existingImages = $existing_images ?? [];
$imageSlots     = 8 - count($existingImages);
?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $isEdit ? '행사·축제 수정' : '행사·축제 등록' ?></h1>
            <p class="bo-page-desc"><?= $isEdit ? '행사·축제 정보를 수정합니다.' : '새 행사·축제를 등록합니다.' ?></p>
        </div>
        <a href="/backoffice/festivals" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<?php if (session()->getFlashdata('form_errors')): ?>
    <div class="bo-alert bo-alert-error">
        <?php foreach (session()->getFlashdata('form_errors') as $err): ?>
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
                <label class="bo-form-label">행사명 <span class="bo-required">*</span></label>
                <input type="text" name="name" class="bo-form-input"
                       value="<?= esc($oldVal('name')) ?>" required maxlength="150">
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">상태 <span class="bo-required">*</span></label>
                <select name="state" class="bo-form-select">
                    <option value="1" <?= $oldVal('state', 1) == 1 ? 'selected' : '' ?>>활성</option>
                    <option value="0" <?= $oldVal('state', 1) == 0 ? 'selected' : '' ?>>비활성</option>
                </select>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">카테고리</label>
                <select name="category_num" class="bo-form-select">
                    <option value="0">선택</option>
                    <?php foreach ($categories as $num => $label): ?>
                        <option value="<?= $num ?>" <?= $oldVal('category_num') == $num ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">무료 여부</label>
                <select name="is_free" class="bo-form-select">
                    <option value="0" <?= $oldVal('is_free', 0) == 0 ? 'selected' : '' ?>>유료</option>
                    <option value="1" <?= $oldVal('is_free', 0) == 1 ? 'selected' : '' ?>>무료</option>
                </select>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">가격대</label>
                <select name="price_range" class="bo-form-select">
                    <option value="1" <?= $oldVal('price_range', 1) == 1 ? 'selected' : '' ?>>₩ (1만원 미만)</option>
                    <option value="2" <?= $oldVal('price_range', 1) == 2 ? 'selected' : '' ?>>₩₩ (1~3만원)</option>
                    <option value="3" <?= $oldVal('price_range', 1) == 3 ? 'selected' : '' ?>>₩₩₩ (3만원 이상)</option>
                </select>
            </div>

            <div class="bo-form-group bo-col-full">
                <label class="bo-form-label">설명</label>
                <textarea name="info" class="bo-form-textarea" rows="4"
                          placeholder="행사·축제 소개 및 설명을 입력하세요."><?= esc($oldVal('info')) ?></textarea>
            </div>

        </div>
    </div>

    <!-- 일정 정보 -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">일정 정보</h3>
        <div class="bo-form-grid">

            <div class="bo-form-group">
                <label class="bo-form-label">시작일 <span class="bo-required">*</span></label>
                <input type="date" name="start_date" class="bo-form-input"
                       value="<?= esc($oldVal('start_date')) ?>" required>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">종료일 <span class="bo-required">*</span></label>
                <input type="date" name="end_date" class="bo-form-input"
                       value="<?= esc($oldVal('end_date')) ?>" required>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">주최자</label>
                <input type="text" name="host" class="bo-form-input"
                       value="<?= esc($oldVal('host')) ?>" placeholder="예) 부산광역시">
            </div>

            <div class="bo-form-group bo-col-full">
                <label class="bo-form-label">상세 URL</label>
                <input type="url" name="detail_url" class="bo-form-input"
                       value="<?= esc($oldVal('detail_url')) ?>" placeholder="https://...">
            </div>

        </div>
    </div>

    <!-- 위치 정보 -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">위치 정보</h3>
        <div class="bo-form-grid">

            <div class="bo-form-group bo-col-2">
                <label class="bo-form-label">도로명 주소</label>
                <input type="text" name="address1" class="bo-form-input"
                       value="<?= esc($oldVal('address1')) ?>" placeholder="예) 부산광역시 해운대구 해운대해변로 264">
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">상세 주소</label>
                <input type="text" name="address2" class="bo-form-input"
                       value="<?= esc($oldVal('address2')) ?>" placeholder="건물명, 층, 호수 등">
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">시/도</label>
                <input type="text" name="sido" class="bo-form-input"
                       value="<?= esc($oldVal('sido')) ?>" placeholder="예) 부산광역시">
            </div>

        </div>
    </div>

    <!-- 이미지 관리 -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">
            이미지 관리
            <span class="bo-img-count-badge" id="imgCountBadge">
                <?= count($existingImages) ?> / 8
            </span>
        </h3>

        <?php if (!empty($existingImages)): ?>
        <div class="bo-img-grid" id="existingGrid">
            <?php foreach ($existingImages as $img): ?>
            <div class="bo-img-card" id="imgCard-<?= $img['idx'] ?>">
                <span class="bo-img-order"><?= $img['img_order'] ?></span>
                <img src="<?= esc($img['img_url']) ?>" alt="이미지 <?= $img['img_order'] ?>">
                <input type="checkbox" name="delete_imgs[]"
                       value="<?= $img['idx'] ?>"
                       id="delCheck-<?= $img['idx'] ?>" hidden>
                <button type="button" class="bo-img-delete-btn"
                        onclick="toggleDeleteImg(<?= $img['idx'] ?>)">×</button>
                <div class="bo-img-delete-overlay" id="imgOverlay-<?= $img['idx'] ?>">삭제 예정</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bo-img-grid" id="previewGrid"></div>

        <div class="bo-upload-area <?= $imageSlots <= 0 ? 'bo-upload-area--full' : '' ?>"
             id="uploadArea">
            <div class="bo-upload-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
            </div>
            <p class="bo-upload-text">클릭하거나 이미지를 드래그하여 업로드</p>
            <p class="bo-upload-hint">JPG · PNG · WEBP · GIF &nbsp;|&nbsp; 개당 최대 5 MB &nbsp;|&nbsp; 최대 8개</p>
            <input type="file" name="images[]" id="imageInput"
                   multiple accept="image/jpeg,image/png,image/webp,image/gif" hidden>
        </div>
    </div>

    <!-- 부가 정보 -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">부가 정보</h3>
        <div class="bo-form-grid">

            <!-- 해시태그: 칩 방식 -->
            <div class="bo-form-group bo-col-full">
                <label class="bo-form-label">
                    해시태그
                    <span class="bo-tag-limit-hint">최대 5개</span>
                </label>
                <div class="bo-tag-input-wrap" id="tagInputWrap">
                    <div class="bo-tag-chips" id="tagChips"></div>
                    <input type="text" id="tagInput"
                           class="bo-tag-text-input"
                           placeholder="태그 입력 후 Enter"
                           autocomplete="off" maxlength="50">
                </div>
                <div class="bo-tag-suggestions" id="tagSuggestions"></div>
                <div id="tagHiddenInputs"></div>
            </div>

        </div>
    </div>

    <div class="bo-form-footer">
        <a href="/backoffice/festivals" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $isEdit ? '수정 저장' : '등록하기' ?>
        </button>
    </div>

</form>

<script>
(function () {
    // ---------------------------------------------------------------
    // 이미지 업로드 UI
    // ---------------------------------------------------------------
    const MAX_IMAGES  = 8;
    const uploadArea  = document.getElementById('uploadArea');
    const imageInput  = document.getElementById('imageInput');
    const previewGrid = document.getElementById('previewGrid');
    const countBadge  = document.getElementById('imgCountBadge');

    let existingCount = <?= count($existingImages) ?>;
    let deleteSet     = new Set();
    let selectedFiles = [];

    function getActiveExistingCount() { return existingCount - deleteSet.size; }
    function getTotalCount()          { return getActiveExistingCount() + selectedFiles.length; }

    function updateBadge() {
        countBadge.textContent = getTotalCount() + ' / 8';
        uploadArea.classList.toggle('bo-upload-area--full', getTotalCount() >= MAX_IMAGES);
    }

    uploadArea.addEventListener('click', function () {
        if (getTotalCount() < MAX_IMAGES) imageInput.click();
    });
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault(); this.classList.add('drag-over');
    });
    uploadArea.addEventListener('dragleave', function () {
        this.classList.remove('drag-over');
    });
    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault(); this.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });

    imageInput.addEventListener('change', function () {
        const copiedFiles = Array.from(this.files);
        this.value = '';
        handleFiles(copiedFiles);
    });

    function handleFiles(fileList) {
        const available = MAX_IMAGES - getTotalCount();
        Array.from(fileList).slice(0, available).forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            selectedFiles.push(file);
        });
        syncFileInput();
        renderPreviews();
        updateBadge();
    }

    function syncFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        imageInput.files = dt.files;
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        selectedFiles.forEach(function (file, idx) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const card = document.createElement('div');
                card.className = 'bo-img-card bo-img-card--new';

                const badge = document.createElement('span');
                badge.className   = 'bo-img-order';
                badge.textContent = getActiveExistingCount() + idx + 1;
                card.appendChild(badge);

                const img = document.createElement('img');
                img.src = e.target.result;
                card.appendChild(img);

                const btn = document.createElement('button');
                btn.type        = 'button';
                btn.className   = 'bo-img-delete-btn';
                btn.textContent = '×';
                btn.addEventListener('click', function () {
                    selectedFiles.splice(idx, 1);
                    syncFileInput();
                    renderPreviews();
                    updateBadge();
                });
                card.appendChild(btn);
                previewGrid.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    }

    window.toggleDeleteImg = function (imgId) {
        const card    = document.getElementById('imgCard-' + imgId);
        const overlay = document.getElementById('imgOverlay-' + imgId);
        const check   = document.getElementById('delCheck-' + imgId);

        if (deleteSet.has(imgId)) {
            deleteSet.delete(imgId);
            check.checked = false;
            card.classList.remove('bo-img-card--deleted');
            overlay.style.display = 'none';
        } else {
            deleteSet.add(imgId);
            check.checked = true;
            card.classList.add('bo-img-card--deleted');
            overlay.style.display = 'flex';
        }
        updateBadge();
    };

    updateBadge();

    // ---------------------------------------------------------------
    // 해시태그 칩 UI
    // ---------------------------------------------------------------
    const MAX_TAGS     = 5;
    const chipWrap     = document.getElementById('tagChips');
    const tagInput     = document.getElementById('tagInput');
    const suggestions  = document.getElementById('tagSuggestions');
    const hiddenInputs = document.getElementById('tagHiddenInputs');
    let tags           = [];
    let suggestTimer   = null;

    const initialTags = <?= json_encode(
        old('hashtag_names')
            ? array_values((array) old('hashtag_names'))
            : array_column($existing_hashtags ?? [], 'name')
    ) ?>;
    initialTags.forEach(addTag);

    function addTag(name) {
        name = name.trim();
        if (!name || tags.length >= MAX_TAGS || tags.includes(name)) return;
        tags.push(name);
        renderChips();
    }
    function removeTag(name) {
        tags = tags.filter(t => t !== name);
        renderChips();
    }
    function renderChips() {
        chipWrap.innerHTML     = '';
        hiddenInputs.innerHTML = '';
        tags.forEach(function (name) {
            const chip = document.createElement('span');
            chip.className   = 'bo-tag-chip';
            chip.textContent = '#' + name;
            const btn = document.createElement('button');
            btn.type        = 'button';
            btn.className   = 'bo-tag-chip-remove';
            btn.textContent = '×';
            btn.addEventListener('click', function () { removeTag(name); });
            chip.appendChild(btn);
            chipWrap.appendChild(chip);

            const hidden = document.createElement('input');
            hidden.type  = 'hidden';
            hidden.name  = 'hashtag_names[]';
            hidden.value = name;
            hiddenInputs.appendChild(hidden);
        });
        tagInput.style.display = tags.length >= MAX_TAGS ? 'none' : '';
    }

    tagInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = this.value.replace(/,/g, '').trim();
            if (val) { addTag(val); this.value = ''; }
            hideSuggestions();
        }
        if (e.key === 'Backspace' && this.value === '' && tags.length) {
            removeTag(tags[tags.length - 1]);
        }
    });
    tagInput.addEventListener('input', function () {
        clearTimeout(suggestTimer);
        const q = this.value.trim();
        if (!q) { hideSuggestions(); return; }
        suggestTimer = setTimeout(function () {
            fetch('/backoffice/hashtags/search?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(function (data) {
                    if (!data.length) { hideSuggestions(); return; }
                    suggestions.innerHTML = '';
                    data.forEach(function (tag) {
                        const item = document.createElement('div');
                        item.className   = 'bo-tag-suggestion-item';
                        item.textContent = '#' + tag.name + ' (' + tag.use_count + ')';
                        item.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            addTag(tag.name);
                            tagInput.value = '';
                            hideSuggestions();
                        });
                        suggestions.appendChild(item);
                    });
                    suggestions.style.display = 'block';
                });
        }, 250);
    });
    tagInput.addEventListener('blur', function () {
        setTimeout(hideSuggestions, 150);
    });
    function hideSuggestions() {
        suggestions.style.display = 'none';
        suggestions.innerHTML     = '';
    }

    document.querySelector('form').addEventListener('submit', function () {
        const val = tagInput.value.trim();
        if (val) { addTag(val); tagInput.value = ''; renderChips(); }
    });
})();
</script>

<?= view('backoffice/partials/footer') ?>
