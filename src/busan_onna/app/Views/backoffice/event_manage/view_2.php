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

<!-- ===== 탭 영역 ===== -->
<div class="bo-card" style="padding:0;overflow:hidden;">

    <div class="bo-tab-nav">
        <button type="button" class="bo-tab-btn active" data-tab="summary">
            🍲 서비스 항목별 좋아요 집계 <span class="bo-tab-count"><?= count($gukbapItems) ?></span>
        </button>
        <button type="button" class="bo-tab-btn" data-tab="like-logs">
            📝 좋아요 로그 <span class="bo-tab-count"><?= count($likeLogs) ?></span>
        </button>
    </div>

    <!-- ───────── 탭 1: 서비스 항목별 좋아요 집계 ───────── -->
    <div class="bo-tab-panel" id="tabSummary">
        <div class="bo-table-wrap">
            <table class="bo-table">
                <thead>
                    <tr>
                        <th style="width:60px">순위</th>
                        <th>맛집명</th>
                        <th style="width:140px">카테고리</th>
                        <th style="width:120px">누적 좋아요</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($gukbapItems)): ?>
                    <tr><td colspan="4" class="bo-table-empty">이름에 '국밥'이 포함된 노출 상태 맛집이 없습니다.</td></tr>
                <?php else: ?>
                    <?php foreach ($gukbapItems as $i => $gi): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $i + 1 ?></td>
                        <td><?= esc($gi['name']) ?></td>
                        <td class="text-center"><?= esc($gi['category']) ?></td>
                        <td class="text-center">
                            <strong style="color:#3b82f6;">❤️ <?= number_format($gi['like_count']) ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ───────── 탭 2: 좋아요 로그 ───────── -->
    <div class="bo-tab-panel" id="tabLikeLogs" style="display:none;">
        <div class="bo-table-wrap">
            <table class="bo-table">
                <thead>
                    <tr>
                        <th style="width:60px">No.</th>
                        <th style="width:160px">회원 아이디</th>
                        <th>맛집명</th>
                        <th style="width:120px">참여일자</th>
                        <th style="width:150px">등록일시</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($likeLogs)): ?>
                    <tr><td colspan="5" class="bo-table-empty">등록된 좋아요 로그가 없습니다.</td></tr>
                <?php else: ?>
                    <?php foreach ($likeLogs as $log): ?>
                    <tr>
                        <td class="text-center text-muted"><?= (int) $log['idx'] ?></td>
                        <td><?= esc($log['user_id'] ?? '(탈퇴회원)') ?></td>
                        <td><?= esc($log['restaurant_name'] ?? '(삭제된 맛집)') ?></td>
                        <td class="text-center"><?= esc($log['like_date']) ?></td>
                        <td class="text-center text-muted text-sm"><?= esc(substr($log['reg_date'], 0, 16)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
/* 탭 UI */
.bo-tab-nav { display: flex; border-bottom: 1px solid #e5e7eb; }
.bo-tab-btn {
    flex: 1;
    padding: 16px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: color .15s, border-color .15s;
}
.bo-tab-btn:hover { color: #374151; }
.bo-tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }
.bo-tab-count {
    display: inline-block;
    background: #f1f5f9;
    color: #6b7280;
    font-size: 12px;
    font-weight: 700;
    padding: 1px 8px;
    border-radius: 10px;
    margin-left: 4px;
}
.bo-tab-btn.active .bo-tab-count { background: #dbeafe; color: #3b82f6; }
.bo-tab-panel { padding: 4px 0; }
</style>

<script>
(function () {
    // ── 탭 전환 ──
    var tabBtns   = document.querySelectorAll('.bo-tab-btn');
    var tabPanels = {
        summary:     document.getElementById('tabSummary'),
        'like-logs': document.getElementById('tabLikeLogs'),
    };

    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            Object.keys(tabPanels).forEach(function (key) {
                tabPanels[key].style.display = (key === btn.dataset.tab) ? '' : 'none';
            });
        });
    });
}());
</script>

<?= view('backoffice/partials/footer') ?>
