<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $mode === 'register' ? '공지사항 등록' : '공지사항 수정' ?></h1>
            <p class="bo-page-desc"><?= $mode === 'register' ? '새로운 공지사항을 등록합니다.' : '공지사항 내용을 수정합니다.' ?></p>
        </div>
        <a href="/backoffice/notices" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<!-- 유효성 에러 -->
<?php if (session()->getFlashdata('form_errors')): ?>
    <div class="bo-alert bo-alert-error">
        <?php foreach ((array) session()->getFlashdata('form_errors') as $err): ?>
            <div><?= esc($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$action = $mode === 'register'
    ? '/backoffice/notices/register'
    : '/backoffice/notices/' . $item['idx'] . '/edit';

$old = fn(string $field, mixed $default = '') =>
    old($field) ?? ($item[$field] ?? $default);
?>

<form id="noticeForm" method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="bo-card" style="margin-bottom:20px;">

        <!-- 상단 옵션 -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:20px;">

            <div>
                <label class="bo-form-label">노출 상태 <span style="color:#ef4444">*</span></label>
                <select name="state" class="bo-form-select" style="width:100%">
                    <option value="1" <?= (string)$old('state', 1) === '1' ? 'selected' : '' ?>>활성 (공개)</option>
                    <option value="0" <?= (string)$old('state', 1) === '0' ? 'selected' : '' ?>>비활성 (숨김)</option>
                </select>
            </div>

            <div>
                <label class="bo-form-label">상단 고정</label>
                <select name="is_pinned" class="bo-form-select" style="width:100%">
                    <option value="0" <?= (string)$old('is_pinned', 0) === '0' ? 'selected' : '' ?>>일반 공지</option>
                    <option value="1" <?= (string)$old('is_pinned', 0) === '1' ? 'selected' : '' ?>>📌 상단 고정</option>
                </select>
            </div>

            <div>
                <label class="bo-form-label">등록자</label>
                <input type="text"
                       value="<?= esc($item['reg_id'] ?? session()->get('backoffice.id')) ?>"
                       class="bo-form-input" style="width:100%;background:#f9fafb;color:#9ca3af" readonly>
            </div>

            <?php if ($mode === 'edit' && !empty($item['reg_date'])): ?>
            <div>
                <label class="bo-form-label">등록일</label>
                <input type="text" value="<?= esc(substr($item['reg_date'], 0, 10)) ?>"
                       class="bo-form-input" style="width:100%;background:#f9fafb;color:#9ca3af" readonly>
            </div>
            <?php endif; ?>
        </div>

        <!-- 제목 -->
        <div style="margin-bottom:24px;">
            <label class="bo-form-label">제목 <span style="color:#ef4444">*</span></label>
            <input type="text" name="title"
                   value="<?= esc($old('title')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="공지사항 제목을 입력하세요. (최대 200자)">
        </div>

        <!-- 본문 — Toast UI Editor -->
        <div>
            <label class="bo-form-label" style="margin-bottom:10px;display:block;">
                내용 <span style="color:#ef4444">*</span>
            </label>

            <!-- 에디터 마운트 컨테이너 -->
            <div id="toastEditor"
                 style="border:1px solid #d1d5db;border-radius:8px;overflow:hidden;"></div>

            <!-- 폼 전송용 hidden textarea -->
            <textarea name="content" id="noticeContent" style="display:none"><?= $old('content') ?></textarea>

            <p style="margin:8px 0 0;font-size:12px;color:#9ca3af;">
                툴바의 <strong>Markdown</strong> / <strong>WYSIWYG</strong> 버튼으로 편집 모드를 전환할 수 있습니다.
            </p>
        </div>
    </div>

    <!-- 저장 버튼 -->
    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="/backoffice/notices" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $mode === 'register' ? '등록하기' : '저장하기' ?>
        </button>
    </div>
</form>

<!-- Toast UI Editor JS -->
<script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
<script src="https://uicdn.toast.com/editor/latest/i18n/ko-kr.js"></script>

<script>
(function () {
    var savedContent = document.getElementById('noticeContent').value;

    var editor = new toastui.Editor({
        el             : document.getElementById('toastEditor'),
        initialEditType: 'wysiwyg',
        previewStyle   : 'vertical',
        height         : '520px',
        language       : 'ko-KR',
        toolbarItems   : [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task'],
            ['table', 'link'],
            ['code', 'codeblock'],
            ['scrollSync'],
        ],
        hooks: {
            addImageBlobHook: function (blob, callback) {
                alert('이미지 업로드 기능은 준비 중입니다.\n이미지 URL을 직접 입력해 주세요.');
                callback('', '이미지 설명');
            }
        },
    });

    if (savedContent) {
        editor.setHTML(savedContent);
    }

    // 폼 제출 전 에디터 HTML을 hidden textarea에 동기화
    document.getElementById('noticeForm').addEventListener('submit', function () {
        document.getElementById('noticeContent').value = editor.getHTML();
    });
}());
</script>

<?= view('backoffice/partials/footer') ?>
