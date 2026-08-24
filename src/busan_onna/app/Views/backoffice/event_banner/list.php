<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">이벤트 배너 관리</h1>
            <p class="bo-page-desc">메인 페이지 이벤트 배너 영역에 노출할 배너를 관리합니다. (권장 이미지 비율 1905 × 600)</p>
        </div>
        <a href="/backoffice/event-banners/register" class="bo-btn bo-btn-primary">+ 이벤트 배너 등록</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="bo-card">
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/event-banners" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px">No.</th>
                    <th style="width:70px">상태</th>
                    <th style="width:160px">배너 이미지</th>
                    <th>연결된 이벤트</th>
                    <th style="width:90px">순서</th>
                    <th style="width:140px">등록일</th>
                    <th style="width:200px">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="bo-table-empty">등록된 이벤트 배너가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>
                    <td class="text-center">
                        <span class="bo-badge <?= $row['state'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $row['state'] ? '활성' : '비활성' ?>
                        </span>
                    </td>
                    <td>
                        <img src="<?= esc($row['image_url']) ?>" alt=""
                             style="width:140px;aspect-ratio:1905/600;object-fit:cover;object-position:<?= esc(str_replace(' ', '% ', $row['image_position'] ?: '50 50')) ?>%;border-radius:6px;display:block;">
                    </td>
                    <td>
                        <?php if (!empty($row['event_title'])): ?>
                            <a href="/backoffice/site-events/<?= (int) $row['event_idx'] ?>/edit" class="bo-table-link">
                                <?= esc($row['event_title']) ?>
                            </a>
                            <?php if (isset($row['event_state']) && !$row['event_state']): ?>
                                <span class="bo-badge badge-inactive" style="margin-left:6px;">이벤트 비활성</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">(삭제된 이벤트 #<?= (int) $row['event_idx'] ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-muted"><?= (int) $row['sort_order'] ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>
                    <td class="text-center" style="white-space:nowrap;">
                        <div class="bo-action-btns">
                            <a href="/backoffice/event-banners/<?= $row['idx'] ?>/edit" class="bo-btn-action edit">수정</a>
                            <form method="post" action="/backoffice/event-banners/<?= $row['idx'] ?>/state" style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action <?= $row['state'] ? 'deactivate' : 'activate' ?>">
                                    <?= $row['state'] ? '비활성' : '활성화' ?>
                                </button>
                            </form>
                            <form method="post" action="/backoffice/event-banners/<?= $row['idx'] ?>/delete" style="display:inline"
                                  onsubmit="return confirm('정말 삭제하시겠습니까?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action delete">삭제</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= view('backoffice/partials/footer') ?>
