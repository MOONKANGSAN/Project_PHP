<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <h1 class="bo-page-title">관리자 추가</h1>
    <p class="bo-page-desc">새 백오피스 관리자 계정을 생성합니다.</p>
</div>

<div class="bo-card bo-login-card">

    <?php if (!empty($form_errors)): ?>
        <div class="bo-alert bo-alert-error">
            <?php foreach ($form_errors as $err): ?>
                <p><?= esc($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/backoffice/add-admin" method="post" class="bo-form">
        <?= csrf_field() ?>

        <div class="bo-form-group">
            <label class="bo-form-label" for="new_id">아이디 <span class="bo-required">*</span></label>
            <input type="text" id="new_id" name="id"
                   class="bo-form-input" placeholder="영문·숫자 4자 이상"
                   value="<?= esc($old['id'] ?? '') ?>" required>
        </div>

        <div class="bo-form-group">
            <label class="bo-form-label" for="new_pw">비밀번호 <span class="bo-required">*</span></label>
            <input type="password" id="new_pw" name="password"
                   class="bo-form-input" placeholder="8자 이상" required>
        </div>

        <div class="bo-form-group">
            <label class="bo-form-label" for="new_pw_confirm">비밀번호 확인 <span class="bo-required">*</span></label>
            <input type="password" id="new_pw_confirm" name="password_confirm"
                   class="bo-form-input" placeholder="비밀번호 재입력" required>
        </div>

        <div class="bo-form-group">
            <label class="bo-form-label" for="new_level">권한 레벨 <span class="bo-required">*</span></label>
            <select id="new_level" name="level" class="bo-form-select" required>
                <option value="1" <?= ($old['level'] ?? '1') === '1' ? 'selected' : '' ?>>1 — 일반관리자</option>
                <option value="2" <?= ($old['level'] ?? '') === '2' ? 'selected' : '' ?>>2 — 슈퍼관리자</option>
            </select>
        </div>

        <div class="bo-form-actions">
            <a href="/backoffice/login" class="bo-btn bo-btn-ghost">취소</a>
            <button type="submit" class="bo-btn bo-btn-primary">계정 생성</button>
        </div>
    </form>
</div>

<?= view('backoffice/partials/footer') ?>
