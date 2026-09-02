<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">주문 상세</h1>
            <p class="bo-page-desc">주문번호: <?= esc($order['order_no'] ?? $order['idx']) ?></p>
        </div>
        <!-- 목록으로 돌아가기 링크 -->
        <a href="/backoffice/orders" class="bo-btn bo-btn-ghost">&larr; 목록으로</a>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php
// 택배 주문 여부 판단 (delivery_type이 없거나 'parcel'이면 택배)
$isParcel = ($order['delivery_type'] ?? 'parcel') !== 'pickup';
?>

<!-- ===== 주문 기본 정보 ===== -->
<div class="bo-card" style="margin-bottom:20px;">
    <h2 style="font-size:15px;font-weight:600;margin-bottom:16px;color:#374151;">주문 정보</h2>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;">
        <!-- 주문번호 -->
        <div>
            <label class="bo-form-label">주문번호</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= esc($order['order_no'] ?? $order['idx']) ?>
            </div>
        </div>

        <!-- 현재 상태 -->
        <div>
            <label class="bo-form-label">상태</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= esc($labels[$order['status']] ?? $order['status']) ?>
            </div>
        </div>

        <!-- 결제 금액 -->
        <div>
            <label class="bo-form-label">결제금액</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= number_format($order['total_price'] ?? 0) ?>원
            </div>
        </div>

        <!-- 배송방법 -->
        <div>
            <label class="bo-form-label">배송방법</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= $isParcel ? '택배' : '픽업' ?>
            </div>
        </div>
    </div>

    <?php if ($isParcel): ?>
    <!-- 택배 주문 — 수취인 정보 표시 -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:16px;">
        <div>
            <label class="bo-form-label">받는 분</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= esc($order['receiver_name'] ?? '—') ?>
            </div>
        </div>
        <div>
            <label class="bo-form-label">연락처</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= esc($order['receiver_phone'] ?? '—') ?>
            </div>
        </div>
        <div>
            <label class="bo-form-label">배송지</label>
            <div class="bo-form-input" style="background:#f9fafb;color:#374151;">
                <?= esc($order['delivery_address'] ?? '—') ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- 픽업 주문 안내 -->
    <div style="margin-top:16px;padding:12px 16px;background:#fef3c7;border-radius:8px;color:#92400e;font-size:14px;">
        이 주문은 <strong>픽업</strong> 주문입니다. 별도 배송지가 없습니다.
    </div>
    <?php endif; ?>
</div>

<!-- ===== 주문 상품 목록 ===== -->
<div class="bo-card" style="margin-bottom:20px;">
    <h2 style="font-size:15px;font-weight:600;margin-bottom:16px;color:#374151;">주문 상품</h2>

    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="text-align:left;">상품명</th>
                    <th style="width:180px;text-align:center;">옵션</th>
                    <th style="width:80px;text-align:center;">수량</th>
                    <th style="width:110px;text-align:center;">단가</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="4" class="bo-table-empty">주문 상품 정보가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <!-- 상품명 -->
                    <td><?= esc($item['goods_name'] ?? '—') ?></td>
                    <!-- 옵션 레이블 -->
                    <td class="text-center text-muted"><?= esc($item['option_label'] ?? '—') ?></td>
                    <!-- 수량 -->
                    <td class="text-center"><?= (int) ($item['quantity'] ?? 0) ?>개</td>
                    <!-- 단가 -->
                    <td class="text-center"><?= number_format($item['unit_price'] ?? 0) ?>원</td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== 주문 상태 변경 폼 ===== -->
<div class="bo-card" style="margin-bottom:20px;">
    <h2 style="font-size:15px;font-weight:600;margin-bottom:16px;color:#374151;">주문 상태 변경</h2>

    <!-- POST /backoffice/orders/{idx}/status -->
    <form method="post" action="/backoffice/orders/<?= $order['idx'] ?>/status">
        <?= csrf_field() ?>
        <div style="display:flex;align-items:center;gap:12px;">
            <!-- 상태 선택 셀렉트 — STATUS_LABELS 상수 사용 -->
            <select name="status" class="bo-form-select" style="min-width:160px;">
                <?php foreach ($labels as $key => $label): ?>
                    <option value="<?= esc($key) ?>"
                        <?= ($order['status'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">상태 변경</button>
        </div>
    </form>
</div>

<?php if ($isParcel): ?>
<!-- ===== 송장 정보 입력 폼 (택배 주문 시에만 표시) ===== -->
<div class="bo-card" style="margin-bottom:20px;">
    <h2 style="font-size:15px;font-weight:600;margin-bottom:4px;color:#374151;">송장 정보</h2>
    <p style="font-size:13px;color:#9ca3af;margin-bottom:16px;">
        저장 시 주문 상태가 자동으로 <strong>배송중</strong>으로 변경됩니다.
    </p>

    <!-- 기존 배송 정보 있을 때 현재값 표시 -->
    <?php if (!empty($delivery)): ?>
    <div style="margin-bottom:14px;padding:10px 14px;background:#f0fdf4;border-radius:8px;font-size:13px;color:#166534;">
        현재 저장된 택배사: <strong><?= esc($couriers[$delivery['courier']] ?? $delivery['courier']) ?></strong>
        &nbsp;/&nbsp; 송장번호: <strong><?= esc($delivery['tracking_no']) ?></strong>
    </div>
    <?php endif; ?>

    <!-- POST /backoffice/orders/{idx}/delivery -->
    <form method="post" action="/backoffice/orders/<?= $order['idx'] ?>/delivery">
        <?= csrf_field() ?>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <!-- 택배사 선택 — COURIERS 상수 사용 -->
            <select name="courier" class="bo-form-select" style="min-width:160px;">
                <option value="">택배사 선택</option>
                <?php foreach ($couriers as $code => $name): ?>
                    <option value="<?= esc($code) ?>"
                        <?= ($delivery['courier'] ?? '') === $code ? 'selected' : '' ?>>
                        <?= esc($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- 송장번호 입력 -->
            <input type="text" name="tracking_no"
                   value="<?= esc($delivery['tracking_no'] ?? '') ?>"
                   class="bo-form-input"
                   style="min-width:200px;"
                   placeholder="송장번호를 입력하세요">

            <button type="submit" class="bo-btn bo-btn-primary">송장 저장</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?= view('backoffice/partials/footer') ?>
