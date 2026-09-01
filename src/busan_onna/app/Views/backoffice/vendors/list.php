<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">판매자 관리</h1>
            <p class="bo-page-desc">입점 판매자 목록을 조회하고 승인/거절을 처리합니다.</p>
        </div>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="bo-card">
    <!-- 상태 필터 툴바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <!-- 상태 필터: 대기/승인/거절 -->
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>대기</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>승인</option>
                <option value="2" <?= $state === '2' ? 'selected' : '' ?>>거절</option>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/vendors" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 판매자 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="text-align:center">상점명</th>
                    <th style="width:160px;text-align:center">연락처</th>
                    <th style="width:90px;text-align:center">상태</th>
                    <th style="width:130px;text-align:center">등록일</th>
                    <th style="width:100px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($vendors)): ?>
                <!-- 결과 없음 안내 -->
                <tr><td colspan="6" class="bo-table-empty">등록된 판매자가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($vendors as $row): ?>
                <tr>
                    <td class="text-center text-muted"><?= esc($row['idx']) ?></td>

                    <!-- 상점명 -->
                    <td>
                        <a href="/backoffice/vendors/<?= $row['idx'] ?>" class="bo-table-link">
                            <?= esc($row['shop_name'] ?? '-') ?>
                        </a>
                    </td>

                    <!-- 연락처 -->
                    <td class="text-center text-muted"><?= esc($row['phone'] ?? '-') ?></td>

                    <!-- 상태 배지: 0=대기(노란색), 1=승인(초록색), 2=거절(회색) -->
                    <td class="text-center">
                        <?php $st = (int) $row['state']; ?>
                        <span class="bo-badge <?= $st === 1 ? 'badge-active' : ($st === 0 ? 'badge-pending' : 'badge-inactive') ?>">
                            <?= esc($labels[$st] ?? '알수없음') ?>
                        </span>
                    </td>

                    <!-- 등록일 -->
                    <td class="text-center text-muted text-sm">
                        <?= esc(substr($row['reg_date'] ?? '', 0, 10)) ?>
                    </td>

                    <!-- 상세 링크 -->
                    <td class="text-center">
                        <a href="/backoffice/vendors/<?= $row['idx'] ?>" class="bo-btn-action">상세</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 페이저 -->
    <?php if ($pager): ?>
    <div class="bo-pagination">
        <?= $pager->links('default', 'bo_pager') ?>
    </div>
    <?php endif; ?>
</div>

<?= view('backoffice/partials/footer') ?>
