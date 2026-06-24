<?= view('backoffice/partials/header', $this->data) ?>

<!-- 페이지 타이틀 -->
<div class="bo-page-header">
    <h1 class="bo-page-title">관리자 로그인</h1>
    <p class="bo-page-desc">부산온나 백오피스에 오신 것을 환영합니다.</p>
</div>

<!-- 로그인 폼 -->
<div class="bo-card bo-login-card">

    <?php if (!empty($login_error)): ?>
        <div class="bo-alert bo-alert-error"><?= esc($login_error) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form action="/backoffice/login" method="post" class="bo-form">
        <?= csrf_field() ?>

        <div class="bo-form-group">
            <label class="bo-form-label" for="admin_id">아이디</label>
            <input type="text" id="admin_id" name="id"
                   class="bo-form-input" placeholder="관리자 아이디"
                   autocomplete="username" required>
        </div>

        <div class="bo-form-group">
            <label class="bo-form-label" for="admin_pw">비밀번호</label>
            <input type="password" id="admin_pw" name="password"
                   class="bo-form-input" placeholder="비밀번호"
                   autocomplete="current-password" required>
        </div>

        <button type="submit" class="bo-btn bo-btn-primary bo-btn-full">로그인</button>
    </form>
</div>

<?= view('backoffice/partials/footer') ?>
