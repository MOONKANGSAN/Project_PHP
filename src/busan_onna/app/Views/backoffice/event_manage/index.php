<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">이벤트 관리</h1>
            <p class="bo-page-desc">진행 중인 이벤트를 선택해 방문 후기·참여 현황 등 운영 데이터를 관리합니다.</p>
        </div>
        <a href="/backoffice/site-events" class="bo-btn bo-btn-ghost">사이트 이벤트 기본 정보 관리 →</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (empty($items)): ?>
<div class="bo-card">
    <p class="bo-table-empty">전용 관리 화면이 있는 이벤트가 없습니다.</p>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;">
    <?php foreach ($items as $item): ?>
    <?php
        $emoji = \App\Models\SiteEventModel::TYPE_EMOJI[$item['event_type']] ?? '🎉';
        $color = \App\Models\SiteEventModel::TYPE_COLOR[$item['event_type']] ?? '#0984e3';
    ?>
    <a href="/backoffice/event-manage/<?= (int) $item['idx'] ?>" class="bo-card"
       style="display:block;text-decoration:none;color:inherit;overflow:hidden;padding:0;transition:box-shadow .15s,transform .15s;"
       onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)';this.style.transform='translateY(-2px)'"
       onmouseout="this.style.boxShadow='';this.style.transform='none'">
        <div style="height:100px;background:<?= esc($color) ?>;display:flex;align-items:center;justify-content:center;font-size:40px;
                    <?php if (!empty($item['thumb_url'])): ?>background-image:url('<?= esc($item['thumb_url']) ?>');background-size:cover;background-position:center;<?php endif; ?>">
            <?php if (empty($item['thumb_url'])): ?><?= $emoji ?><?php endif; ?>
        </div>
        <div style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <span class="bo-badge <?= $item['state'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $item['state'] ? '진행중' : '비활성' ?>
                </span>
                <span style="font-size:12px;color:#9ca3af;">
                    <?= $emoji ?> <?= esc(\App\Models\SiteEventModel::TYPES[$item['event_type']] ?? '기타') ?>
                </span>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:#111827;margin-bottom:4px;"><?= esc($item['title']) ?></h3>
            <?php if (!empty($item['sub_title'])): ?>
                <p style="font-size:13px;color:#6b7280;margin-bottom:10px;"><?= esc($item['sub_title']) ?></p>
            <?php endif; ?>
            <p style="font-size:12px;color:#9ca3af;">
                <?= esc(substr($item['start_date'], 0, 10)) ?> ~ <?= esc(substr($item['end_date'], 0, 10)) ?>
            </p>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?= view('backoffice/partials/footer') ?>
