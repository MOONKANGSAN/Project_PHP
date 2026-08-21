<?= view('backoffice/partials/header', $this->data) ?>

<?php
$isEdit  = $mode === 'edit';
$action  = $isEdit
    ? "/backoffice/site-events/{$item['idx']}/edit"
    : '/backoffice/site-events/register';
$oldVal  = fn(string $key, $default = '') => old($key, $item[$key] ?? $default);

// 연결 링크 사용 여부 초기값 (등록 시: 0, 수정 시: DB값, 폼 오류 시: old 값 우선)
$useViewFile = (int) old('use_view_file', $item['use_view_file'] ?? 0);
?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $isEdit ? '사이트 이벤트 수정' : '사이트 이벤트 등록' ?></h1>
            <p class="bo-page-desc">
                <?= $isEdit ? '이벤트 정보를 수정합니다.' : '새 이벤트를 등록합니다.' ?>
            </p>
        </div>
        <a href="/backoffice/site-events" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<?php if (session()->getFlashdata('form_errors')): ?>
<div class="bo-alert bo-alert-error">
    <?php foreach (session()->getFlashdata('form_errors') as $err): ?>
        <p><?= esc($err) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data" novalidate id="siteEventForm">
    <?= csrf_field() ?>
    <!-- use_view_file 값은 JS가 체크박스 상태에 따라 갱신 -->
    <input type="hidden" name="use_view_file" id="useViewFileInput" value="<?= $useViewFile ?>">

    <!-- ============================= 공통 기본 정보 ============================= -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">기본 정보</h3>
        <div class="bo-form-grid">

            <div class="bo-form-group">
                <label class="bo-form-label">상태 <span class="bo-required">*</span></label>
                <select name="state" class="bo-form-select">
                    <option value="1" <?= $oldVal('state', 1) == 1 ? 'selected' : '' ?>>활성</option>
                    <option value="0" <?= $oldVal('state', 1) == 0 ? 'selected' : '' ?>>비활성</option>
                </select>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">이벤트 유형 <span class="bo-required">*</span></label>
                <select name="event_type" class="bo-form-select">
                    <?php foreach (\App\Models\SiteEventModel::TYPES as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $oldVal('event_type', 4) == $num ? 'selected' : '' ?>>
                        <?= \App\Models\SiteEventModel::TYPE_EMOJI[$num] ?> <?= esc($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bo-form-group bo-col-2">
                <label class="bo-form-label">이벤트 제목 <span class="bo-required">*</span></label>
                <input type="text" name="title" class="bo-form-input"
                       value="<?= esc($oldVal('title')) ?>"
                       placeholder="예) 부산 골목 탐험단" required maxlength="200">
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">이벤트 시작일 <span class="bo-required">*</span></label>
                <input type="date" name="start_date" class="bo-form-input"
                       value="<?= esc($oldVal('start_date')) ?>" required>
            </div>

            <div class="bo-form-group">
                <label class="bo-form-label">이벤트 종료일 <span class="bo-required">*</span></label>
                <input type="date" name="end_date" class="bo-form-input"
                       value="<?= esc($oldVal('end_date')) ?>" required>
            </div>

        </div>
    </div>

    <!-- ============================= 연결 링크 사용 토글 ============================= -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">뷰 페이지 설정</h3>

        <!-- 체크박스 -->
        <label class="bo-toggle-row" id="viewFileSwitchLabel">
            <div class="bo-toggle-switch">
                <input type="checkbox" id="useViewFileChk" <?= $useViewFile ? 'checked' : '' ?>>
                <span class="bo-toggle-slider"></span>
            </div>
            <div class="bo-toggle-text">
                <strong>연결 링크 사용</strong>
                <span>체크하면 별도 제작된 이벤트 전용 뷰 파일(view_1.php 등)로 연결됩니다.</span>
            </div>
        </label>

        <!-- 연결 링크 ON: view_file 입력 -->
        <div id="sectionViewFile" style="<?= $useViewFile ? '' : 'display:none;' ?> margin-top:20px;">
            <div class="bo-form-grid">
                <div class="bo-form-group bo-col-2">
                    <label class="bo-form-label">뷰 파일명 <span class="bo-required">*</span></label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="color:#9ca3af; font-size:14px; white-space:nowrap;">
                            service/event/views/
                        </span>
                        <input type="text" name="view_file" id="viewFileInput" class="bo-form-input"
                               value="<?= esc($oldVal('view_file')) ?>"
                               placeholder="view_1"
                               maxlength="50"
                               pattern="[a-zA-Z0-9_]+"
                               style="flex:1;">
                        <span style="color:#9ca3af; font-size:14px; white-space:nowrap;">.php</span>
                    </div>
                    <p style="font-size:12px; color:#9ca3af; margin-top:6px;">
                        영문·숫자·언더스코어만 사용 가능합니다. 예) view_1, view_2, view_3
                    </p>
                </div>
            </div>
        </div>

        <!-- 연결 링크 OFF: 기본 뷰 콘텐츠 입력 -->
        <div id="sectionDefaultView" style="<?= $useViewFile ? 'display:none;' : '' ?> margin-top:20px;">
            <div class="bo-form-grid">

                <div class="bo-form-group bo-col-full">
                    <label class="bo-form-label">한줄 소개</label>
                    <input type="text" name="sub_title" class="bo-form-input"
                           value="<?= esc($oldVal('sub_title')) ?>"
                           placeholder="이벤트를 한 문장으로 설명해주세요."
                           maxlength="300">
                </div>

                <div class="bo-form-group bo-col-full">
                    <label class="bo-form-label">이벤트 내용</label>
                    <textarea name="content" class="bo-form-textarea" rows="10"
                              placeholder="이벤트 참여 방법, 경품 안내, 유의사항 등을 자유롭게 입력하세요."><?= esc($oldVal('content')) ?></textarea>
                    <p style="font-size:12px; color:#9ca3af; margin-top:4px;">줄바꿈은 그대로 반영됩니다.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- ============================= 대표 이미지 (공통) ============================= -->
    <div class="bo-form-card">
        <h3 class="bo-form-section-title">대표 이미지</h3>
        <div class="bo-form-grid">

            <?php if (!empty($oldVal('thumb_url'))): ?>
            <div class="bo-form-group bo-col-full">
                <label class="bo-form-label">현재 대표 이미지</label>
                <div style="display:flex; align-items:flex-start; gap:16px;">
                    <img src="<?= esc($oldVal('thumb_url')) ?>" alt="현재 대표 이미지"
                         style="width:180px; height:120px; object-fit:cover; border-radius:8px;
                                border:1px solid #e5e7eb;">
                    <div>
                        <p style="font-size:13px; color:#6b7280; margin-bottom:8px;">
                            새 파일을 업로드하면 교체됩니다.
                        </p>
                        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="remove_thumb" value="1" id="removeThumbChk">
                            현재 이미지 삭제 (업로드 없이 제거)
                        </label>
                    </div>
                </div>
                <!-- 삭제 체크 시 thumb_url을 빈값으로 전송 -->
                <input type="hidden" name="thumb_url" id="thumbUrlHidden"
                       value="<?= esc($oldVal('thumb_url')) ?>">
            </div>
            <?php else: ?>
            <input type="hidden" name="thumb_url" value="">
            <?php endif; ?>

            <div class="bo-form-group bo-col-full">
                <label class="bo-form-label">이미지 업로드</label>
                <div class="bo-upload-area" id="thumbUploadArea">
                    <div class="bo-upload-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <p class="bo-upload-text">클릭하거나 이미지를 드래그하여 업로드</p>
                    <p class="bo-upload-hint">JPG · PNG · WEBP &nbsp;|&nbsp; 최대 5 MB</p>
                    <input type="file" name="thumb_file" id="thumbFileInput"
                           accept="image/jpeg,image/png,image/webp" hidden>
                </div>
                <!-- 업로드 미리보기 -->
                <div id="thumbPreview" style="display:none; margin-top:12px;">
                    <img id="thumbPreviewImg"
                         style="width:180px; height:120px; object-fit:cover; border-radius:8px;
                                border:1px solid #e5e7eb;">
                    <button type="button" id="thumbClearBtn"
                            style="display:block; margin-top:6px; font-size:12px; color:#ef4444;
                                   background:none; border:none; cursor:pointer; padding:0;">
                        ✕ 취소
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================= CTA 버튼 (기본뷰 전용) ============================= -->
    <div id="sectionCta" style="<?= $useViewFile ? 'display:none;' : '' ?>">
        <div class="bo-form-card">
            <h3 class="bo-form-section-title">
                CTA 버튼
                <span style="font-size:13px; font-weight:400; color:#9ca3af; margin-left:8px;">
                    — 이벤트 페이지 하단에 표시될 행동 유도 버튼 (선택)
                </span>
            </h3>
            <div class="bo-form-grid">

                <div class="bo-form-group">
                    <label class="bo-form-label">버튼 텍스트</label>
                    <input type="text" name="cta_text" class="bo-form-input"
                           value="<?= esc($oldVal('cta_text')) ?>"
                           placeholder="예) 지금 참여하기" maxlength="100">
                </div>

                <div class="bo-form-group">
                    <label class="bo-form-label">버튼 링크 URL</label>
                    <input type="text" name="cta_url" class="bo-form-input"
                           value="<?= esc($oldVal('cta_url')) ?>"
                           placeholder="예) /spots 또는 https://..." maxlength="500">
                </div>

            </div>
        </div>
    </div>

    <!-- ============================= 폼 하단 버튼 ============================= -->
    <div class="bo-form-footer">
        <a href="/backoffice/site-events" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $isEdit ? '수정 저장' : '등록하기' ?>
        </button>
    </div>

</form>

<style>
/* 연결링크 토글 스위치 */
.bo-toggle-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    cursor: pointer;
    user-select: none;
}
.bo-toggle-switch {
    position: relative;
    flex-shrink: 0;
    width: 46px;
    height: 26px;
    margin-top: 2px;
}
.bo-toggle-switch input { opacity: 0; width: 0; height: 0; }
.bo-toggle-slider {
    position: absolute;
    inset: 0;
    background: #d1d5db;
    border-radius: 26px;
    transition: background 0.2s;
}
.bo-toggle-slider::before {
    content: '';
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.bo-toggle-switch input:checked + .bo-toggle-slider { background: #3b82f6; }
.bo-toggle-switch input:checked + .bo-toggle-slider::before { transform: translateX(20px); }
.bo-toggle-text strong { display: block; font-size: 14px; color: #111827; margin-bottom: 2px; }
.bo-toggle-text span   { font-size: 13px; color: #6b7280; }
</style>

<script>
(function () {
    const chk           = document.getElementById('useViewFileChk');
    const hiddenInput   = document.getElementById('useViewFileInput');
    const secViewFile   = document.getElementById('sectionViewFile');
    const secDefault    = document.getElementById('sectionDefaultView');
    const secCta        = document.getElementById('sectionCta');
    const viewFileInput = document.getElementById('viewFileInput');

    function toggle() {
        const on = chk.checked;
        hiddenInput.value = on ? '1' : '0';
        secViewFile.style.display  = on ? '' : 'none';
        secDefault.style.display   = on ? 'none' : '';
        secCta.style.display       = on ? 'none' : '';
        viewFileInput.required     = on;
    }

    chk.addEventListener('change', toggle);
    toggle(); // 초기화

    // ── 대표 이미지 업로드 UI ──
    const uploadArea  = document.getElementById('thumbUploadArea');
    const fileInput   = document.getElementById('thumbFileInput');
    const preview     = document.getElementById('thumbPreview');
    const previewImg  = document.getElementById('thumbPreviewImg');
    const clearBtn    = document.getElementById('thumbClearBtn');

    uploadArea.addEventListener('click', function () { fileInput.click(); });
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault(); this.classList.add('drag-over');
    });
    uploadArea.addEventListener('dragleave', function () {
        this.classList.remove('drag-over');
    });
    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault(); this.classList.remove('drag-over');
        if (e.dataTransfer.files[0]) showPreview(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', function () {
        if (this.files[0]) showPreview(this.files[0]);
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            uploadArea.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            fileInput.value = '';
            preview.style.display = 'none';
            uploadArea.style.display = '';
        });
    }

    // ── 기존 이미지 삭제 체크박스 ──
    const removeThumbChk  = document.getElementById('removeThumbChk');
    const thumbUrlHidden  = document.getElementById('thumbUrlHidden');
    if (removeThumbChk && thumbUrlHidden) {
        removeThumbChk.addEventListener('change', function () {
            thumbUrlHidden.value = this.checked ? '' : <?= json_encode($oldVal('thumb_url')) ?>;
        });
    }

    // ── 폼 제출 전 필수값 검증 ──
    document.getElementById('siteEventForm').addEventListener('submit', function (e) {
        const missing = [];
        if (!document.querySelector('[name="title"]').value.trim()) {
            missing.push('이벤트 제목');
        }
        if (!document.querySelector('[name="start_date"]').value) {
            missing.push('이벤트 시작일');
        }
        if (!document.querySelector('[name="end_date"]').value) {
            missing.push('이벤트 종료일');
        }
        if (chk.checked && !viewFileInput.value.trim()) {
            missing.push('뷰 파일명 (연결 링크 사용 시 필수)');
        }
        if (!missing.length) return;

        e.preventDefault();
        alert('⚠ 필수 항목을 입력해주세요.\n\n' + missing.map(m => '  • ' + m).join('\n'));
    });
}());
</script>

<?= view('backoffice/partials/footer') ?>
