<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <h1 class="bo-page-title"><?= esc($page_title) ?></h1>
</div>

<div class="bo-card bo-placeholder-card">
    <div class="bo-placeholder-icon">🚧</div>
    <h2 class="bo-placeholder-title">준비 중입니다</h2>
    <p class="bo-placeholder-desc">
        <strong><?= esc($page_title) ?></strong> 페이지는 현재 개발 중입니다.<br>
        빠른 시일 내에 완성될 예정입니다.
    </p>
    <a href="/backoffice/dashboard" class="bo-btn bo-btn-primary">대시보드로 돌아가기</a>
</div>

<?= view('backoffice/partials/footer') ?>
