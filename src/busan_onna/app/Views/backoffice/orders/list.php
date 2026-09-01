<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">주문 관리</h1>
            <p class="bo-page-desc">전체 주문 내역을 조회하고 관리합니다.</p>
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
    <!-- 검색·필터 툴바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <!-- 주문번호 텍스트 검색 -->
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="주문번호 검색...">

            <!-- 주문 상태 필터 — STATUS_LABELS 상수 사용 -->
            <select name="status" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <?php foreach ($labels as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= $status === $key ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/orders" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 주문 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="text-align:center">주문번호</th>
                    <th style="width:120px;text-align:center">일자</th>
                    <th style="width:110px;text-align:center">금액</th>
                    <th style="width:90px;text-align:center">배송방법</th>
                    <th style="width:90px;text-align:center">상태</th>
                    <th style="width:80px;text-align:center">상세</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <!-- 검색 결과 없을 때 메시지 -->
                <tr><td colspan="7" class="bo-table-empty">주문 내역이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $row): ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 주문번호 -->
                    <td class="text-center">
                        <a href="/backoffice/orders/<?= $row['idx'] ?>" class="bo-table-link">
                            <?= esc($row['order_no'] ?? $row['idx']) ?>
                        </a>
                    </td>

                    <!-- 등록일 (날짜 10자리만 표시) -->
                    <td class="text-center text-muted text-sm">
                        <?= esc(substr($row['reg_date'] ?? '', 0, 10)) ?>
                    </td>

                    <!-- 결제 금액 (원 단위 천 단위 구분) -->
                    <td class="text-center">
                        <?= number_format($row['total_price'] ?? 0) ?>원
                    </td>

                    <!-- 배송방법 배지 -->
                    <td class="text-center">
                        <?php if (($row['delivery_type'] ?? '') === 'pickup'): ?>
                            <span class="bo-badge badge-inactive">픽업</span>
                        <?php else: ?>
                            <span class="bo-badge badge-active">택배</span>
                        <?php endif; ?>
                    </td>

                    <!-- 주문 상태 배지 -->
                    <td class="text-center">
                        <span class="bo-badge">
                            <?= esc($labels[$row['status']] ?? $row['status']) ?>
                        </span>
                    </td>

                    <!-- 상세 링크 -->
                    <td class="text-center">
                        <a href="/backoffice/orders/<?= $row['idx'] ?>"
                           class="bo-btn-action activate">상세</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($pager): ?>
    <div class="bo-pagination">
        <?= $pager->links('default', 'bo_pager') ?>
    </div>
    <?php endif; ?>
</div>

<?= view('backoffice/partials/footer') ?>
