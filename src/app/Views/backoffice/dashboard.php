<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <h1 class="bo-page-title">대시보드</h1>
    <p class="bo-page-desc"><?= date('Y년 m월 d일') ?> 기준 서비스 현황입니다.</p>
</div>

<!-- 통계 카드 -->
<div class="bo-stats-grid">

    <div class="bo-stat-card">
        <div class="bo-stat-icon" style="background:#eff6ff; color:#3b82f6;">👥</div>
        <div class="bo-stat-body">
            <p class="bo-stat-label">총 회원 수</p>
            <p class="bo-stat-value"><?= number_format($stats['total_members']) ?><span class="bo-stat-unit">명</span></p>
        </div>
    </div>

    <div class="bo-stat-card">
        <div class="bo-stat-icon" style="background:#f0fdf4; color:#22c55e;">🔐</div>
        <div class="bo-stat-body">
            <p class="bo-stat-label">관리자 계정</p>
            <p class="bo-stat-value"><?= number_format($stats['total_admins']) ?><span class="bo-stat-unit">명</span></p>
        </div>
    </div>

    <div class="bo-stat-card">
        <div class="bo-stat-icon" style="background:#fff7ed; color:#f97316;">🗺️</div>
        <div class="bo-stat-body">
            <p class="bo-stat-label">등록 관광지</p>
            <p class="bo-stat-value">—<span class="bo-stat-unit"></span></p>
        </div>
    </div>

    <div class="bo-stat-card">
        <div class="bo-stat-icon" style="background:#fdf4ff; color:#a855f7;">🍽️</div>
        <div class="bo-stat-body">
            <p class="bo-stat-label">등록 맛집</p>
            <p class="bo-stat-value">—<span class="bo-stat-unit"></span></p>
        </div>
    </div>

</div>

<!-- 빠른 이동 -->
<div class="bo-card" style="margin-top: 28px;">
    <h2 class="bo-card-title">빠른 이동</h2>
    <div class="bo-quick-links">
        <a href="/backoffice/restaurants" class="bo-quick-item">
            <span>🍽️</span><span>맛집 관리</span>
        </a>
        <a href="/backoffice/spots" class="bo-quick-item">
            <span>🗺️</span><span>관광지 관리</span>
        </a>
        <a href="/backoffice/festivals" class="bo-quick-item">
            <span>🎉</span><span>행사·축제</span>
        </a>
        <a href="/backoffice/members" class="bo-quick-item">
            <span>👥</span><span>회원 관리</span>
        </a>
        <a href="/backoffice/inquiries" class="bo-quick-item">
            <span>💬</span><span>고객문의</span>
        </a>
        <a href="/backoffice/error-logs" class="bo-quick-item">
            <span>⚠️</span><span>에러 로그</span>
        </a>
    </div>
</div>

<?= view('backoffice/partials/footer') ?>
