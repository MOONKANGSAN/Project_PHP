<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">결제내역</h1>
            <p class="bo-page-desc">전체 주문·결제 내역을 조회하고 관리합니다.</p>
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
    <!-- 검색·상태 필터 툴바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <!-- 주문번호 텍스트 검색 -->
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="주문번호 검색...">

            <!-- 상태 필터 드롭다운 — PAYMENT_STATUS_FILTER 상수 사용 -->
            <select name="status" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <?php foreach ($filterLabels as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= $status === $key ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/payments" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 결제내역 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="text-align:left;min-width:140px;">상품명</th>
                    <th style="width:160px;text-align:center;">주문번호</th>
                    <th style="width:100px;text-align:center;">주문자</th>
                    <th style="width:100px;text-align:center;">수령인</th>
                    <th style="text-align:left;min-width:160px;">배송지</th>
                    <th style="width:130px;text-align:center;">결제일시</th>
                    <th style="width:90px;text-align:center;">결제분류</th>
                    <th style="width:80px;text-align:center;">상태</th>
                    <th style="width:80px;text-align:center;">주문취소</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="9" class="bo-table-empty">결제 내역이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $row): ?>
                <?php
                    // 취소 불가 상태: 이미 취소됐거나 배송완료인 경우
                    $isCancelDisabled = in_array($row['status'], ['cancelled', 'delivered'], true);
                    // 배송지: 주소 + 상세주소 합산
                    $address  = $row['delivery_address']  ?? '';
                    $address2 = $row['delivery_address2'] ?? '';
                    $fullAddress = $address !== ''
                        ? esc($address) . ($address2 !== '' ? ' ' . esc($address2) : '')
                        : '—';
                    // 결제일시: paid_at이 있으면 날짜+시간, 없으면 '—'
                    $paidAt = $row['paid_at'] ?? '';
                    $paidAtText = $paidAt !== '' ? esc(substr($paidAt, 0, 16)) : '—';
                ?>
                <tr>
                    <!-- 상품명 (복수 상품 시 콤마 구분) -->
                    <td class="text-sm">
                        <?= esc($row['goods_names'] ?? '—') ?>
                    </td>

                    <!-- 주문번호 — orders 상세로 연결 -->
                    <td class="text-center">
                        <a href="/backoffice/orders/<?= $row['idx'] ?>" class="bo-table-link">
                            <?= esc($row['order_no'] ?? $row['idx']) ?>
                        </a>
                    </td>

                    <!-- 주문자 (이름 우선, 없으면 아이디) -->
                    <td class="text-center text-sm">
                        <?= esc($row['orderer_name'] ?: ($row['orderer_id'] ?? '—')) ?>
                    </td>

                    <!-- 수령인 -->
                    <td class="text-center text-sm">
                        <?= ($row['delivery_type'] ?? '') === 'pickup'
                            ? '<span class="text-muted">픽업</span>'
                            : esc($row['recipient_name'] ?? '—') ?>
                    </td>

                    <!-- 배송지 (주소 + 상세주소) -->
                    <td class="text-sm">
                        <?php if (($row['delivery_type'] ?? '') === 'pickup'): ?>
                            <span class="text-muted">픽업 주문</span>
                        <?php else: ?>
                            <?= $fullAddress ?>
                        <?php endif; ?>
                    </td>

                    <!-- 결제일시 — paid_at (분 단위까지 표시) -->
                    <td class="text-center text-sm text-muted">
                        <?= $paidAtText ?>
                    </td>

                    <!-- 결제분류 배지 — PAY_KIND_LABELS 기준 -->
                    <td class="text-center">
                        <?php $payKind = $row['pay_kind'] ?? ''; ?>
                        <?php if ($payKind !== ''): ?>
                            <span class="bo-badge <?= $payKind === 'kakao' ? 'badge-kakao' : 'badge-inicis' ?>">
                                <?= esc($payKindLabels[$payKind] ?? $payKind) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- 상태 배지 — STATUS_LABELS 기준 -->
                    <td class="text-center">
                        <?php
                            $statusKey  = $row['status'] ?? '';
                            $statusText = $filterLabels[$statusKey]
                                ?? ($statusLabels[$statusKey] ?? $statusKey);
                        ?>
                        <span class="bo-badge <?= $statusKey === 'cancelled' ? 'badge-inactive' : '' ?>">
                            <?= esc($statusText) ?>
                        </span>
                    </td>

                    <!-- 주문취소 버튼 — 이미 취소/배송완료면 비활성화 -->
                    <td class="text-center">
                        <?php if ($isCancelDisabled): ?>
                            <button type="button" class="bo-btn-action" disabled
                                    style="opacity:.4;cursor:not-allowed;">취소</button>
                        <?php else: ?>
                            <form method="post"
                                  action="/backoffice/payments/<?= $row['idx'] ?>/cancel"
                                  onsubmit="return confirm('주문번호 <?= esc($row['order_no'] ?? $row['idx']) ?>을(를) 취소하시겠습니까?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action delete">취소</button>
                            </form>
                        <?php endif; ?>
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
