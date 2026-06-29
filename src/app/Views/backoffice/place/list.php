<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">관광지 관리</h1>
            <p class="bo-page-desc">등록된 관광지 목록을 관리합니다.</p>
        </div>
        <a href="/backoffice/spots/register" class="bo-btn bo-btn-primary">+ 관광지 등록</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="bo-card">
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="관광지명 검색...">
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/spots" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px">No.</th>
                    <th style="width:70px">상태</th>
                    <th>관광지명</th>
                    <th style="width:110px">카테고리</th>
                    <th style="width:70px">별점</th>
                    <th style="width:70px">조회</th>
                    <th style="width:70px">좋아요</th>
                    <th style="width:140px">등록일</th>
                    <th style="width:120px">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="9" class="bo-table-empty">등록된 관광지가 없습니다.</td></tr>
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
                        <a href="/backoffice/spots/<?= $row['idx'] ?>/edit" class="bo-table-link">
                            <?= esc($row['name']) ?>
                        </a>
                    </td>
                    <td class="text-center"><?= esc($categories[$row['category_num']] ?? '-') ?></td>
                    <td class="text-center"><?= number_format((float)$row['star_point'], 1) ?></td>
                    <td class="text-center text-muted"><?= number_format($row['view_cnt']) ?></td>
                    <td class="text-center text-muted"><?= number_format($row['like_cnt']) ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>
                    <td class="text-center">
                        <div class="bo-action-btns">
                            <a href="/backoffice/spots/<?= $row['idx'] ?>/edit" class="bo-btn-action edit">수정</a>
                            <form method="post" action="/backoffice/spots/<?= $row['idx'] ?>/state" style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action <?= $row['state'] ? 'deactivate' : 'activate' ?>">
                                    <?= $row['state'] ? '비활성' : '활성화' ?>
                                </button>
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
