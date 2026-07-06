<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">여행코스 관리</h1>
            <p class="bo-page-desc">등록된 여행코스 목록을 관리합니다.</p>
        </div>
        <a href="/backoffice/travel-courses/register" class="bo-btn bo-btn-primary">+ 여행코스 등록</a>
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
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="코스명 검색...">
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/travel-courses" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px">No.</th>
                    <th style="width:70px">상태</th>
                    <th>코스명</th>
                    <th style="width:100px">지역</th>
                    <th style="width:70px">항목수</th>
                    <th style="width:140px">등록일</th>
                    <th style="width:150px">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="bo-table-empty">등록된 여행코스가 없습니다.</td></tr>
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
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if ($row['thumb_url']): ?>
                                <img src="<?= esc($row['thumb_url']) ?>" alt=""
                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0;">
                            <?php endif; ?>
                            <a href="/backoffice/travel-courses/<?= $row['idx'] ?>/edit" class="bo-table-link">
                                <?= esc($row['title']) ?>
                            </a>
                        </div>
                    </td>
                    <td class="text-center"><?= esc($row['sido'] ?? '-') ?></td>
                    <td class="text-center">
                        <?php
                        // 항목 수 표시 (별도 쿼리 없이 서브쿼리 결과 활용 예정, 현재는 - 표시)
                        echo isset($row['item_count']) ? (int)$row['item_count'] : '-';
                        ?>
                    </td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>
                    <td class="text-center">
                        <div class="bo-action-btns">
                            <a href="/backoffice/travel-courses/<?= $row['idx'] ?>/edit" class="bo-btn-action edit">수정</a>
                            <form method="post" action="/backoffice/travel-courses/<?= $row['idx'] ?>/state" style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action <?= $row['state'] ? 'deactivate' : 'activate' ?>">
                                    <?= $row['state'] ? '비활성' : '활성화' ?>
                                </button>
                            </form>
                            <form method="post" action="/backoffice/travel-courses/<?= $row['idx'] ?>/delete" style="display:inline"
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

    <?php if ($pager): ?>
    <div class="bo-pagination">
        <?= $pager->links('default', 'bo_pager') ?>
    </div>
    <?php endif; ?>
</div>

<?= view('backoffice/partials/footer') ?>
