<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">행사·축제 관리</h1>
            <p class="bo-page-desc">등록된 행사 및 축제 목록을 관리합니다.</p>
        </div>
        <a href="/backoffice/festivals/register" class="bo-btn bo-btn-primary">+ 행사·축제 등록</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="bo-card">
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="행사명 검색...">
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/festivals" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px">No.</th>
                    <th style="width:70px">상태</th>
                    <th>행사명</th>
                    <th style="width:100px">카테고리</th>
                    <th style="width:70px">무료</th>
                    <th style="width:110px">시작일</th>
                    <th style="width:110px">종료일</th>
                    <th style="width:70px">조회</th>
                    <th style="width:140px">등록일</th>
                    <th style="width:120px">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="10" class="bo-table-empty">등록된 행사·축제가 없습니다.</td></tr>
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
                        <a href="/backoffice/festivals/<?= $row['idx'] ?>/edit" class="bo-table-link">
                            <?= esc($row['name']) ?>
                        </a>
                    </td>
                    <td class="text-center"><?= esc($categories[$row['category_num']] ?? '-') ?></td>
                    <td class="text-center">
                        <span class="bo-badge <?= $row['is_free'] ? 'badge-free' : 'badge-paid' ?>">
                            <?= $row['is_free'] ? '무료' : '유료' ?>
                        </span>
                    </td>
                    <td class="text-center text-muted text-sm"><?= $row['start_date'] ?? '-' ?></td>
                    <td class="text-center text-muted text-sm"><?= $row['end_date'] ?? '-' ?></td>
                    <td class="text-center text-muted"><?= number_format($row['view_cnt']) ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>
                    <td class="text-center">
                        <div class="bo-action-btns">
                            <a href="/backoffice/festivals/<?= $row['idx'] ?>/edit" class="bo-btn-action edit">수정</a>
                            <form method="post" action="/backoffice/festivals/<?= $row['idx'] ?>/state" style="display:inline">
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
