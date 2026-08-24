<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= esc($event['title']) ?> 관리</h1>
            <p class="bo-page-desc">마! 이게 진짜 국밥이다 이벤트의 운영 데이터를 관리합니다.</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="/backoffice/event-manage" class="bo-btn bo-btn-ghost">← 이벤트 관리</a>
            <a href="/events/<?= (int) $event['idx'] ?>" target="_blank" class="bo-btn bo-btn-ghost">공개 페이지 보기 ↗</a>
            <a href="/backoffice/site-events/<?= (int) $event['idx'] ?>/edit" class="bo-btn bo-btn-primary">기본 정보 수정</a>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- ===== 이벤트 요약 ===== -->
<div class="bo-card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <?php if (!empty($event['thumb_url'])): ?>
        <img src="<?= esc($event['thumb_url']) ?>" alt=""
             style="width:96px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
        <?php endif; ?>
        <div style="flex:1;min-width:200px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <span class="bo-badge <?= $event['state'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $event['state'] ? '진행중' : '비활성' ?>
                </span>
                <span style="font-size:12px;color:#9ca3af;">
                    <?= esc(substr($event['start_date'], 0, 10)) ?> ~ <?= esc(substr($event['end_date'], 0, 10)) ?>
                </span>
            </div>
            <?php if (!empty($event['sub_title'])): ?>
                <p style="font-size:14px;color:#374151;"><?= esc($event['sub_title']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== 운영 관리 (준비 중) ===== -->
<div class="bo-card">
    <h3 class="bo-form-section-title" style="margin-bottom:12px;">운영 관리</h3>
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                padding:60px 20px;color:#9ca3af;text-align:center;">
        <div style="font-size:40px;margin-bottom:14px;">🛠️</div>
        <p style="font-size:14px;">이 이벤트의 운영 관리 기능은 준비 중입니다.</p>
    </div>
</div>

<?= view('backoffice/partials/footer') ?>
