<?= view('backoffice/partials/header', $this->data) ?>

<?php
/* 등록 모드: $goods === null, 수정 모드: $goods = 기존 데이터 배열 */
$isEdit = !empty($goods);
$action = $isEdit
    ? '/backoffice/goods/' . $goods['idx'] . '/edit'
    : '/backoffice/goods/register';

$val = fn(string $field, mixed $default = '') =>
    old($field) ?? ($goods[$field] ?? $default);

// 기존 이미지 & 남은 슬롯 수
$existingImages = $existing_images ?? [];
$imageSlots     = 3 - count($existingImages);
?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $isEdit ? '상품 수정' : '상품 등록' ?></h1>
            <p class="bo-page-desc"><?= $isEdit ? '굿즈 상품 정보를 수정합니다.' : '새로운 굿즈 상품을 등록합니다.' ?></p>
        </div>
        <a href="/backoffice/goods" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<!-- 유효성 오류 메시지 -->
<?php if (session()->getFlashdata('form_errors')): ?>
    <div class="bo-alert bo-alert-error">
        <?php foreach ((array) session()->getFlashdata('form_errors') as $err): ?>
            <div><?= esc($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- enctype="multipart/form-data" — 이미지 파일 업로드 필수 -->
<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- 기본 정보 -->
    <div class="bo-card" style="margin-bottom:20px;">
        <h3 style="font-size:15px;font-weight:600;color:#1e293b;margin:0 0 18px;">기본 정보</h3>

        <!-- 상품명 -->
        <div style="margin-bottom:20px;">
            <label class="bo-form-label">
                상품명 <span style="color:#ef4444">*</span>
            </label>
            <input type="text" name="name"
                   value="<?= esc($val('name')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="굿즈 상품명을 입력하세요." required>
        </div>

        <!-- 가격 / 재고 / 배송 유형 -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
            <div>
                <label class="bo-form-label">가격 (원) <span style="color:#ef4444">*</span></label>
                <input type="number" name="price" min="0"
                       value="<?= esc($val('price', 0)) ?>"
                       class="bo-form-input" style="width:100%"
                       placeholder="0" required>
            </div>
            <div>
                <label class="bo-form-label">재고 <span style="color:#ef4444">*</span></label>
                <input type="number" name="stock" min="0"
                       value="<?= esc($val('stock', 0)) ?>"
                       class="bo-form-input" style="width:100%"
                       placeholder="0" required>
            </div>
            <div>
                <label class="bo-form-label">배송 유형</label>
                <select name="delivery_type" class="bo-form-select" style="width:100%">
                    <option value="1" <?= (string)$val('delivery_type', 1) === '1' ? 'selected' : '' ?>>택배</option>
                    <option value="2" <?= (string)$val('delivery_type', 1) === '2' ? 'selected' : '' ?>>픽업</option>
                </select>
            </div>
        </div>

        <!-- 픽업 장소 안내 -->
        <?php if (!empty($pickups)): ?>
        <div style="margin-bottom:20px;padding:14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#374151;">
                등록된 픽업 장소 (배송 유형을 "픽업"으로 선택 시 참고)
            </p>
            <ul style="margin:0;padding-left:18px;font-size:13px;color:#6b7280;">
                <?php foreach ($pickups as $p): ?>
                <li><?= esc($p['name']) ?> — <?= esc($p['address']) ?></li>
                <?php endforeach; ?>
            </ul>
            <p style="margin:8px 0 0;font-size:12px;color:#9ca3af;">
                픽업 장소 추가·관리는
                <a href="/backoffice/pickup-locations" style="color:#6366f1;">픽업 장소 관리</a> 페이지에서 할 수 있습니다.
            </p>
        </div>
        <?php endif; ?>

        <!-- 상품 설명 -->
        <div>
            <label class="bo-form-label">상품 설명</label>
            <textarea name="description" rows="6"
                      class="bo-form-input" style="width:100%;resize:vertical;"
                      placeholder="상품 상세 설명을 입력하세요."><?= esc($val('description')) ?></textarea>
        </div>
    </div>

    <!-- 이미지 관리 -->
    <div class="bo-card" style="margin-bottom:20px;">
        <h3 style="font-size:15px;font-weight:600;color:#1e293b;margin:0 0 4px;display:flex;align-items:center;gap:10px;">
            이미지 관리
            <span id="imgCountBadge"
                  style="font-size:12px;font-weight:500;color:#6366f1;
                         background:#eef2ff;border-radius:12px;padding:2px 10px;">
                <?= count($existingImages) ?> / 3
            </span>
        </h3>
        <p style="margin:0 0 16px;font-size:12px;color:#9ca3af;">
            대표 이미지(썸네일)는 첫 번째 이미지로 자동 지정됩니다.
        </p>

        <!-- 등록된 이미지 그리드 (수정 모드) -->
        <?php if (!empty($existingImages)): ?>
        <div class="bo-img-grid" id="existingGrid">
            <?php foreach ($existingImages as $img): ?>
            <div class="bo-img-card" id="imgCard-<?= $img['idx'] ?>">
                <span class="bo-img-order"><?= $img['sort_order'] ?></span>
                <img src="<?= esc($img['image_path']) ?>" alt="이미지 <?= $img['sort_order'] ?>">
                <!-- 삭제 체크박스 (JS가 토글) -->
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

        <!-- 새 이미지 미리보기 그리드 (JS가 동적으로 추가) -->
        <div class="bo-img-grid" id="previewGrid"></div>

        <!-- 업로드 영역 -->
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
            <p class="bo-upload-hint">JPG · PNG · WEBP · GIF &nbsp;|&nbsp; 개당 최대 5 MB &nbsp;|&nbsp; 최대 3개</p>
            <input type="file" name="images[]" id="imageInput"
                   multiple accept="image/jpeg,image/png,image/webp,image/gif" hidden>
        </div>
    </div>

    <!-- 저장·취소 버튼 -->
    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="/backoffice/goods" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $isEdit ? '저장하기' : '등록하기' ?>
        </button>
    </div>
</form>

<script>
(function () {
    const MAX_IMAGES  = 3;
    const uploadArea  = document.getElementById('uploadArea');
    const imageInput  = document.getElementById('imageInput');
    const previewGrid = document.getElementById('previewGrid');
    const countBadge  = document.getElementById('imgCountBadge');

    // 서버에서 전달된 기존 이미지 수
    let existingCount = <?= count($existingImages) ?>;
    // 삭제 예정 기존 이미지 idx 셋
    let deleteSet     = new Set();
    // 새로 선택한 File 객체 배열
    let selectedFiles = [];

    function getActiveExistingCount() {
        return existingCount - deleteSet.size;
    }
    function getTotalCount() {
        return getActiveExistingCount() + selectedFiles.length;
    }
    function updateBadge() {
        countBadge.textContent = getTotalCount() + ' / 3';
        uploadArea.classList.toggle('bo-upload-area--full', getTotalCount() >= MAX_IMAGES);
    }

    uploadArea.addEventListener('click', function () {
        if (getTotalCount() < MAX_IMAGES) imageInput.click();
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

    imageInput.addEventListener('change', function () {
        const copiedFiles = Array.from(this.files);
        this.value = '';
        handleFiles(copiedFiles);
    });

    function handleFiles(fileList) {
        const available = MAX_IMAGES - getTotalCount();
        Array.from(fileList).slice(0, available).forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            // 5 MB 용량 제한
            if (file.size > 5 * 1024 * 1024) {
                alert(file.name + ' 파일이 5 MB를 초과합니다.');
                return;
            }
            selectedFiles.push(file);
        });
        syncFileInput();
        renderPreviews();
        updateBadge();
    }

    // DataTransfer로 file input과 selectedFiles 배열 동기화
    function syncFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(function (f) { dt.items.add(f); });
        imageInput.files = dt.files;
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        selectedFiles.forEach(function (file, idx) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const card = document.createElement('div');
                card.className = 'bo-img-card bo-img-card--new';

                const orderBadge = document.createElement('span');
                orderBadge.className   = 'bo-img-order';
                orderBadge.textContent = getActiveExistingCount() + idx + 1;
                card.appendChild(orderBadge);

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

    // 기존 이미지 삭제 토글
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
}());
</script>

<?= view('backoffice/partials/footer') ?>
