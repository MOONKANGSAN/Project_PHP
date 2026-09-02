<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">굿즈 관리</h1>
            <p class="bo-page-desc">굿즈 상품을 등록하고 판매 상태를 관리합니다.</p>
        </div>
        <a href="/backoffice/goods/register" class="bo-btn bo-btn-primary">+ 상품 등록</a>
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
            <!-- 상품명 검색 입력 -->
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="상품명 검색...">

            <!-- 판매 상태 필터 -->
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>판매중</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>판매중지</option>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/goods" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 상품 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="text-align:center">상품명</th>
                    <th style="width:100px;text-align:center">가격</th>
                    <th style="width:80px;text-align:center">재고</th>
                    <th style="width:90px;text-align:center">배송 유형</th>
                    <th style="width:80px;text-align:center">상태</th>
                    <th style="width:200px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="bo-table-empty">등록된 상품이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <?php
                    /* 배송 유형: 1=택배, 2=픽업 */
                    $deliveryLabel = (int)$row['delivery_type'] === 2 ? '픽업' : '택배';
                    /* 상태: 1=판매중, 0=중지, 9=삭제 */
                    $isActive = (int)$row['state'] === 1;
                ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 상품명 — 수정 페이지 링크 -->
                    <td>
                        <a href="/backoffice/goods/<?= $row['idx'] ?>/edit" class="bo-table-link"
                           style="<?= !$isActive ? 'color:#9ca3af;' : '' ?>">
                            <?= esc($row['name']) ?>
                        </a>
                    </td>

                    <td class="text-center"><?= number_format((int)$row['price']) ?>원</td>
                    <td class="text-center"><?= number_format((int)$row['stock']) ?></td>

                    <!-- 배송 유형 배지 -->
                    <td class="text-center">
                        <span class="bo-badge <?= (int)$row['delivery_type'] === 2 ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $deliveryLabel ?>
                        </span>
                    </td>

                    <!-- 판매 상태 배지 -->
                    <td class="text-center">
                        <span class="bo-badge <?= $isActive ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $isActive ? '판매중' : '중지' ?>
                        </span>
                    </td>

                    <!-- 관리 버튼 -->
                    <td class="text-center">
                        <div class="bo-action-btns" style="flex-wrap:nowrap;justify-content:center;">

                            <!-- 판매 상태 토글 (POST 폼) -->
                            <form method="post" action="/backoffice/goods/<?= $row['idx'] ?>/state"
                                  style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit"
                                        class="bo-btn-action <?= $isActive ? 'deactivate' : 'activate' ?>">
                                    <?= $isActive ? '판매중지' : '판매재개' ?>
                                </button>
                            </form>

                            <!-- 수정 링크 -->
                            <a href="/backoffice/goods/<?= $row['idx'] ?>/edit"
                               class="bo-btn-action">수정</a>

                            <!-- 논리 삭제 (confirm 확인 후 POST) -->
                            <form method="post" action="/backoffice/goods/<?= $row['idx'] ?>/delete"
                                  style="display:inline"
                                  onsubmit="return confirm('상품 [<?= esc($row['name'], 'js') ?>]을 삭제하시겠습니까?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action"
                                        style="background:#6b7280;color:#fff" title="삭제">삭제</button>
                            </form>

                        </div>
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
