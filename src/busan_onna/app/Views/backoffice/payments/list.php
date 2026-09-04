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

<style>
/* ── 상태 순환 버튼 색상 ── */
.status-btn {
    display: inline-block;
    padding: 4px 10px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: opacity .15s, transform .1s;
    line-height: 1.5;
}
.status-btn:hover  { opacity: .85; }
.status-btn:active { transform: scale(.96); }
.status-btn:disabled { opacity: .45; cursor: not-allowed; }

/* 상태별 색상 */
.status-btn[data-status="paid"]      { background: #3b82f6; color: #fff; } /* 파랑 — 결제완료 */
.status-btn[data-status="preparing"] { background: #f59e0b; color: #fff; } /* 주황 — 상품준비중 */
.status-btn[data-status="shipped"]   { background: #8b5cf6; color: #fff; } /* 보라 — 배송중 */
.status-btn[data-status="delivered"] { background: #10b981; color: #fff; } /* 초록 — 배송완료 */
.status-btn[data-status="cancelled"] { background: #ef4444; color: #fff; cursor: not-allowed; }
.status-btn[data-status="pending"]   { background: #9ca3af; color: #fff; cursor: not-allowed; }

/* 날짜 필터 input */
.bo-date-input {
    height: 36px;
    padding: 0 10px;
    border: 1.5px solid var(--bo-border);
    border-radius: var(--bo-radius-sm);
    font-size: 13px;
    font-family: inherit;
    color: var(--bo-text);
    background: #fff;
    outline: none;
}
.bo-date-input:focus { border-color: var(--bo-accent); }
.date-range-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}
.date-range-sep { font-size: 13px; color: var(--bo-text-muted); }
</style>

<div class="bo-card">
    <!-- 검색·상태·날짜 필터 툴바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap" style="flex-wrap:wrap;gap:8px;">

            <!-- 주문번호 텍스트 검색 -->
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="주문번호 검색...">

            <!-- 상태 필터 드롭다운 -->
            <select name="status" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <?php foreach ($filterLabels as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= $status === $key ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- 결제일 날짜 범위 필터 -->
            <div class="date-range-wrap">
                <input type="date" name="date_from" value="<?= esc($dateFrom ?? '') ?>"
                       class="bo-date-input" title="시작일자">
                <span class="date-range-sep">~</span>
                <input type="date" name="date_to" value="<?= esc($dateTo ?? '') ?>"
                       class="bo-date-input" title="종료일자">
            </div>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/payments" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 결제내역 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center;">IDX</th>
                    <th style="width:160px;text-align:center;">주문번호</th>
                    <th style="width:100px;text-align:center;">주문자</th>
                    <th style="width:100px;text-align:center;">수령인</th>
                    <th style="text-align:left;min-width:160px;">배송지</th>
                    <th style="width:130px;text-align:center;">결제일시</th>
                    <th style="width:90px;text-align:center;">결제분류</th>
                    <th style="width:110px;text-align:center;">상태</th>
                    <th style="width:80px;text-align:center;">주문취소</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="9" class="bo-table-empty">결제 내역이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $row): ?>
                <?php
                    $isCancelDisabled = in_array($row['status'], ['cancelled', 'delivered'], true);
                    $address   = $row['delivery_address']  ?? '';
                    $address2  = $row['delivery_address2'] ?? '';
                    $fullAddress = $address !== ''
                        ? esc($address) . ($address2 !== '' ? ' ' . esc($address2) : '')
                        : '—';
                    $paidAt     = $row['paid_at'] ?? '';
                    $paidAtText = $paidAt !== '' ? esc(substr($paidAt, 0, 16)) : '—';
                    $statusKey  = $row['status'] ?? '';
                    $statusLabel = $statusLabels[$statusKey] ?? $statusKey;
                    /* 순환 가능한 상태: paid, preparing, shipped, delivered */
                    $isCyclable = in_array($statusKey, ['paid', 'preparing', 'shipped', 'delivered'], true);
                ?>
                <tr>
                    <!-- IDX -->
                    <td class="text-center text-sm text-muted">
                        <?= (int)$row['idx'] ?>
                    </td>

                    <!-- 주문번호 — orders 상세로 연결 -->
                    <td class="text-center">
                        <a href="/backoffice/orders/<?= $row['idx'] ?>" class="bo-table-link">
                            <?= esc($row['order_no'] ?? $row['idx']) ?>
                        </a>
                    </td>

                    <!-- 주문자 -->
                    <td class="text-center text-sm">
                        <?= esc($row['orderer_name'] ?: ($row['orderer_id'] ?? '—')) ?>
                    </td>

                    <!-- 수령인 -->
                    <td class="text-center text-sm">
                        <?= ($row['delivery_type'] ?? '') === 'pickup'
                            ? '<span class="text-muted">픽업</span>'
                            : esc($row['recipient_name'] ?? '—') ?>
                    </td>

                    <!-- 배송지 -->
                    <td class="text-sm">
                        <?php if (($row['delivery_type'] ?? '') === 'pickup'): ?>
                            <span class="text-muted">픽업 주문</span>
                        <?php else: ?>
                            <?= $fullAddress ?>
                        <?php endif; ?>
                    </td>

                    <!-- 결제일시 -->
                    <td class="text-center text-sm text-muted">
                        <?= $paidAtText ?>
                    </td>

                    <!-- 결제분류 배지 -->
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

                    <!-- 상태 순환 버튼 -->
                    <td class="text-center">
                        <button type="button"
                                class="status-btn"
                                data-idx="<?= (int)$row['idx'] ?>"
                                data-status="<?= esc($statusKey) ?>"
                                <?= $isCyclable ? '' : 'disabled' ?>>
                            <?= esc($statusLabel) ?>
                        </button>
                    </td>

                    <!-- 주문취소 버튼 -->
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

<script>
/* ===== 상태 순환 버튼 AJAX ===== */
(function () {
    /* 상태 → 다음 상태 순서 */
    const CYCLE = {
        paid     : 'preparing',
        preparing: 'shipped',
        shipped  : 'delivered',
        delivered: 'paid',
    };

    /* 상태 → 레이블 */
    const LABELS = {
        paid     : '결제완료',
        preparing: '상품준비중',
        shipped  : '배송중',
        delivered: '배송완료',
    };

    const CSRF_TOKEN = '<?= csrf_hash() ?>';
    const CSRF_NAME  = '<?= csrf_token() ?>';

    document.querySelectorAll('.status-btn:not([disabled])').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const idx    = btn.dataset.idx;
            const status = btn.dataset.status;
            const next   = CYCLE[status];
            if (!next) return;

            if (!confirm(LABELS[status] + ' → ' + LABELS[next] + ' 으로 변경하시겠습니까?')) return;

            btn.disabled = true;

            fetch('/backoffice/payments/' + idx + '/status', {
                method : 'POST',
                headers: {
                    'Content-Type'    : 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: CSRF_NAME + '=' + CSRF_TOKEN,
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    btn.dataset.status   = data.nextStatus;
                    btn.textContent      = data.nextLabel;
                    btn.disabled         = false;
                } else {
                    alert(data.message || '상태 변경에 실패했습니다.');
                    btn.disabled = false;
                }
            })
            .catch(function () {
                alert('네트워크 오류가 발생했습니다.');
                btn.disabled = false;
            });
        });
    });
}());
</script>
