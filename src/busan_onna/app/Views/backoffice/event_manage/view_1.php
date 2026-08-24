<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= esc($event['title']) ?> 관리</h1>
            <p class="bo-page-desc">부산 골목 탐험단 이벤트의 운영 데이터를 관리합니다.</p>
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
        <button type="button" class="bo-tab-btn active" data-tab="reviews">
            💬 방문 후기 <span class="bo-tab-count"><?= count($reviews) ?></span>
        </button>
        <button type="button" class="bo-tab-btn" data-tab="hidden-spots">
            📍 숨은 명소 관리 <span class="bo-tab-count"><?= count($hiddenSpots) ?></span>
        </button>
    </div>

    <!-- ───────── 탭 1: 방문 후기 리스트 ───────── -->
    <div class="bo-tab-panel" id="tabReviews">
        <div class="bo-table-wrap">
            <table class="bo-table">
                <thead>
                    <tr>
                        <th style="width:60px">No.</th>
                        <th style="width:120px">작성자</th>
                        <th style="width:140px">방문 장소</th>
                        <th>내용</th>
                        <th style="width:70px">사진</th>
                        <th style="width:140px">등록일</th>
                        <th style="width:100px">관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reviews)): ?>
                    <tr><td colspan="7" class="bo-table-empty">등록된 방문 후기가 없습니다.</td></tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                    <tr>
                        <td class="text-center text-muted"><?= (int) $rv['idx'] ?></td>
                        <td><?= esc($rv['user_id']) ?></td>
                        <td><?= esc($rv['spot_name'] ?? '-') ?></td>
                        <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= esc($rv['content']) ?>
                        </td>
                        <td class="text-center"><?= !empty($rv['photo_url']) ? '📷' : '-' ?></td>
                        <td class="text-center text-muted text-sm"><?= esc(substr($rv['reg_date'], 0, 16)) ?></td>
                        <td class="text-center">
                            <button type="button" class="bo-btn-action edit js-review-detail"
                                    data-idx="<?= (int) $rv['idx'] ?>">상세보기</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ───────── 탭 2: 숨은 명소 관리 ───────── -->
    <div class="bo-tab-panel" id="tabHiddenSpots" style="display:none;">
        <div class="bo-table-wrap">
            <table class="bo-table">
                <thead>
                    <tr>
                        <th style="width:60px">No.</th>
                        <th style="width:70px">썸네일</th>
                        <th style="width:80px">구분</th>
                        <th>이름</th>
                        <th style="width:120px">카테고리</th>
                        <th style="width:70px">상태</th>
                        <th style="width:120px">관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($hiddenSpots)): ?>
                    <tr><td colspan="7" class="bo-table-empty">'숨은 명소' 태그가 연결된 항목이 없습니다.</td></tr>
                <?php else: ?>
                    <?php foreach ($hiddenSpots as $sp): ?>
                    <tr>
                        <td class="text-center text-muted"><?= (int) $sp['hn_idx'] ?></td>
                        <td>
                            <?php if (!empty($sp['thumbnail'])): ?>
                                <img src="<?= esc($sp['thumbnail']) ?>" alt=""
                                     style="width:44px;height:44px;object-fit:cover;border-radius:6px;">
                            <?php else: ?>
                                <div style="width:44px;height:44px;background:#f1f5f9;border-radius:6px;"></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= esc($sp['type_label']) ?></td>
                        <td><?= esc($sp['name']) ?></td>
                        <td class="text-center"><?= esc($sp['category_label']) ?></td>
                        <td class="text-center">
                            <span class="bo-badge <?= $sp['state'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $sp['state'] ? '활성' : '비활성' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <form method="post" action="/backoffice/event-manage/hidden-spots/<?= (int) $sp['hn_idx'] ?>/state" style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action <?= $sp['state'] ? 'deactivate' : 'activate' ?>">
                                    <?= $sp['state'] ? '비활성화' : '활성화' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===== 방문 후기 상세 모달 ===== -->
<div class="bo-modal-overlay" id="reviewModalOverlay">
    <div class="bo-modal-box">
        <div class="bo-modal-header">
            <h3>방문 후기 상세</h3>
            <button type="button" class="bo-modal-close" id="reviewModalClose">✕</button>
        </div>
        <div class="bo-modal-body" id="reviewModalBody">
            <p class="text-muted" style="text-align:center;padding:30px 0;">불러오는 중...</p>
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

/* 모달 */
.bo-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.bo-modal-overlay.is-open { display: flex; }
.bo-modal-box {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 480px;
    max-height: 84vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}
.bo-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid #e5e7eb;
}
.bo-modal-header h3 { font-size: 16px; font-weight: 700; color: #111827; }
.bo-modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #9ca3af;
    cursor: pointer;
    line-height: 1;
}
.bo-modal-close:hover { color: #374151; }
.bo-modal-body { padding: 22px; }
.bo-review-row { display: flex; gap: 8px; font-size: 13px; margin-bottom: 10px; }
.bo-review-row strong { flex-shrink: 0; width: 70px; color: #6b7280; }
.bo-review-photo { width: 100%; border-radius: 8px; margin-top: 8px; display: block; }
.bo-review-content {
    white-space: pre-wrap;
    font-size: 14px;
    color: #111827;
    line-height: 1.6;
    margin-top: 6px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
</style>

<script>
(function () {
    // ── 탭 전환 ──
    var tabBtns   = document.querySelectorAll('.bo-tab-btn');
    var tabPanels = {
        reviews:        document.getElementById('tabReviews'),
        'hidden-spots': document.getElementById('tabHiddenSpots'),
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

    // ── 방문 후기 상세 모달 ──
    var eventIdx     = <?= (int) $event['idx'] ?>;
    var modalOverlay = document.getElementById('reviewModalOverlay');
    var modalBody    = document.getElementById('reviewModalBody');
    var modalClose   = document.getElementById('reviewModalClose');

    function openModal() { modalOverlay.classList.add('is-open'); }
    function closeModal() { modalOverlay.classList.remove('is-open'); }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function (e) {
        if (e.target === modalOverlay) closeModal();
    });

    function esc(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    document.querySelectorAll('.js-review-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var reviewIdx = btn.dataset.idx;
            modalBody.innerHTML = '<p class="text-muted" style="text-align:center;padding:30px 0;">불러오는 중...</p>';
            openModal();

            fetch('/backoffice/event-manage/' + eventIdx + '/reviews/' + reviewIdx)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) {
                        modalBody.innerHTML = '<p class="text-muted" style="text-align:center;padding:30px 0;">' + esc(data.message) + '</p>';
                        return;
                    }
                    var rv = data.review;
                    var html = '';
                    html += '<div class="bo-review-row"><strong>작성자</strong><span>' + esc(rv.user_id) + '</span></div>';
                    html += '<div class="bo-review-row"><strong>방문 장소</strong><span>' + esc(rv.spot_name || '-') + '</span></div>';
                    html += '<div class="bo-review-row"><strong>등록일</strong><span>' + esc(rv.reg_date) + '</span></div>';
                    html += '<div class="bo-review-content">' + esc(rv.content).replace(/\n/g, '<br>') + '</div>';
                    if (rv.photo_url) {
                        html += '<img class="bo-review-photo" src="' + esc(rv.photo_url) + '" alt="후기 사진">';
                    }
                    modalBody.innerHTML = html;
                })
                .catch(function () {
                    modalBody.innerHTML = '<p class="text-muted" style="text-align:center;padding:30px 0;">불러오는 중 오류가 발생했습니다.</p>';
                });
        });
    });
}());
</script>

<?= view('backoffice/partials/footer') ?>
