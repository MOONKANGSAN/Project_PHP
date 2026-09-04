<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>주문 상세 | 부산온나</title>
    <meta name="description" content="부산온나 마이페이지 - 주문 상세 내역을 확인하세요.">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 주문 상세 페이지 전용 스타일 ---- */
        .order-detail-section {
            padding: 116px 0 80px; /* 68px 고정 네비바 + 48px 여백 */
        }

        /* 뒤로 가기 링크 */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #868e96;
            text-decoration: none;
            margin-bottom: 28px;
            transition: color .15s;
        }
        .back-link:hover { color: #e55039; }

        /* 페이지 타이틀 */
        .order-detail-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 28px;
        }

        /* 정보 카드 공통 */
        .detail-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 20px;
        }
        .detail-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        /* 정보 목록 */
        .info-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .info-list li {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 9px 0;
            border-bottom: 1px solid #f1f3f5;
            gap: 12px;
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #868e96;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .info-value {
            font-weight: 600;
            color: #212529;
            text-align: right;
            word-break: break-all;
        }
        .info-value.accent {
            color: #e55039;
            font-size: 16px;
        }

        /* 상태 뱃지 */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending  { background: #fff3cd; color: #856404; }
        .status-paid     { background: #d1ecf1; color: #0c5460; }
        .status-shipping { background: #d4edda; color: #155724; }
        .status-done     { background: #e2e3e5; color: #383d41; }
        .status-cancel   { background: #f8d7da; color: #721c24; }
        .status-default  { background: #f8f9fa; color: #495057; }

        /* 주문 상품 테이블 */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .items-table thead th {
            padding: 10px 14px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        .items-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        .items-table tbody tr:last-child {
            border-bottom: none;
        }
        .items-table td {
            padding: 14px;
            vertical-align: middle;
        }
        .item-goods-name {
            font-weight: 600;
            margin: 0 0 4px;
        }
        .item-option {
            font-size: 12px;
            color: #868e96;
        }
        .item-price {
            font-weight: 700;
            color: #e55039;
            white-space: nowrap;
        }

        /* 총 결제금액 합계 행 */
        .items-total-row td {
            padding-top: 16px;
            border-top: 2px solid #dee2e6;
        }

        /* 목록으로 버튼 */
        .btn-back-list {
            display: inline-block;
            padding: 12px 28px;
            background: #fff;
            color: #495057;
            font-size: 15px;
            font-weight: 600;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            text-decoration: none;
            margin-top: 8px;
            transition: border-color .2s, color .2s;
        }
        .btn-back-list:hover {
            border-color: #e55039;
            color: #e55039;
        }

        /* 환불 요청 버튼 */
        .btn-refund-request {
            display: inline-block;
            padding: 12px 28px;
            background: #fff;
            color: #e55039;
            font-size: 15px;
            font-weight: 700;
            border: 2px solid #e55039;
            border-radius: 10px;
            cursor: pointer;
            transition: background .2s, color .2s;
        }
        .btn-refund-request:hover { background: #e55039; color: #fff; }

        /* 배송중 안내 텍스트 */
        .refund-ship-notice {
            font-size: 14px;
            color: #868e96;
            padding: 10px 0;
        }

        /* 환불 모달 */
        .refund-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9000;
            align-items: center;
            justify-content: center;
        }
        .refund-overlay.active { display: flex; }
        .refund-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            max-height: 88vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            margin: 16px;
        }
        .refund-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid #e9ecef;
            position: sticky; top: 0;
            background: #fff; z-index: 1;
        }
        .refund-modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; }
        .refund-modal-close { font-size: 24px; color: #adb5bd; cursor: pointer; background: none; border: none; line-height: 1; }
        .refund-modal-body { padding: 22px 24px; }
        .refund-modal-footer {
            display: flex; justify-content: flex-end; gap: 10px;
            padding: 14px 24px; border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            position: sticky; bottom: 0;
        }
        .rm-section { margin-bottom: 20px; }
        .rm-section h4 {
            font-size: 12px; font-weight: 700; color: #868e96;
            text-transform: uppercase; letter-spacing: .5px;
            margin: 0 0 10px; padding-bottom: 6px;
            border-bottom: 1px solid #e9ecef;
        }
        .rm-item-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            border: 1.5px solid #dee2e6; border-radius: 8px;
            margin-bottom: 8px;
        }
        .rm-item-row label { display: flex; align-items: center; gap: 10px; cursor: pointer; flex: 1; }
        .rm-item-row input[type=checkbox] { width: 16px; height: 16px; accent-color: #e55039; flex-shrink: 0; }
        .rm-item-name { font-size: 14px; font-weight: 600; }
        .rm-item-opt  { font-size: 12px; color: #868e96; }
        .rm-item-price { font-size: 14px; font-weight: 700; color: #e55039; white-space: nowrap; }
        .rm-warn {
            background: #fff3cd; border: 1.5px solid #ffc107;
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; color: #856404;
            display: flex; gap: 8px; align-items: flex-start;
            margin-bottom: 16px;
        }
        .rm-select, .rm-textarea, .rm-file {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid #dee2e6; border-radius: 8px;
            font-size: 14px; color: #343a40;
            background: #f8f9fa; box-sizing: border-box;
            font-family: inherit;
        }
        .rm-textarea { resize: vertical; min-height: 72px; margin-top: 8px; }
        .rm-file { background: #fff; padding: 6px 10px; }
        .rm-label { display: block; font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; }
        .rm-hint  { font-size: 12px; color: #868e96; margin-top: 4px; }
        .btn-rm-cancel  { padding: 10px 22px; border: 1.5px solid #dee2e6; background: #fff; color: #495057; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-rm-submit  { padding: 10px 24px; border: none; background: #e55039; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
        .btn-rm-submit:disabled { opacity: .5; cursor: not-allowed; }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'mypage']) ?>

<!-- ===================== 페이지 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>주문 상세</h1>
        <p>주문 상세 내역을 확인하세요</p>
    </div>
</section>

<!-- ===================== 주문 상세 본문 ===================== -->
<section class="order-detail-section">
    <div class="container">

        <!-- 목록으로 돌아가기 -->
        <a href="/mypage/orders" class="back-link">
            ← 주문 목록으로
        </a>

        <h2 class="order-detail-title">주문번호: <?= esc($order['order_no']) ?></h2>

        <?php
        /* 상태 CSS 클래스 결정 */
        $statusClass = match($order['status'] ?? '') {
            'pending'  => 'status-pending',
            'paid'     => 'status-paid',
            'shipping' => 'status-shipping',
            'done'     => 'status-done',
            'cancel'   => 'status-cancel',
            default    => 'status-default',
        };
        $statusLabel = $labels[$order['status'] ?? ''] ?? esc($order['status'] ?? '-');
        ?>

        <?php
        /* 환불 요청 가능 여부 계산 */
        // 상품 목록이 없으면 환불 버튼·모달 표시 불필요
        $canRefund  = !empty($items) && in_array($order['status'] ?? '', ['paid', 'preparing', 'delivered']);
        $isShipped  = ($order['status'] ?? '') === 'shipped';
        $isOver7Days = false;
        if (($order['status'] ?? '') === 'delivered' && !empty($order['delivered_at'])) {
            $diff = (new \DateTime())->diff(new \DateTime($order['delivered_at']))->days;
            $isOver7Days = ($diff >= 7);
        }
        ?>

        <!-- 주문 기본 정보 -->
        <div class="detail-card">
            <h3>주문 정보</h3>
            <ul class="info-list">
                <li>
                    <span class="info-label">주문번호</span>
                    <span class="info-value"><?= esc($order['order_no']) ?></span>
                </li>
                <li>
                    <span class="info-label">주문일시</span>
                    <span class="info-value"><?= esc($order['created_at'] ?? '-') ?></span>
                </li>
                <li>
                    <span class="info-label">결제금액</span>
                    <span class="info-value accent"><?= number_format((int)$order['total_price']) ?>원</span>
                </li>
                <li>
                    <span class="info-label">주문 상태</span>
                    <span class="info-value">
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </span>
                </li>
                <li>
                    <span class="info-label">배송 방법</span>
                    <span class="info-value">
                        <?= (int)($order['delivery_type'] ?? 1) === 1 ? '택배 배송' : '픽업' ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- 택배 배송지 정보 (택배 주문에만 표시) -->
        <?php if ((int)($order['delivery_type'] ?? 1) === 1): ?>
        <div class="detail-card">
            <h3>배송지 정보</h3>
            <ul class="info-list">
                <?php if (!empty($order['recipient_name'])): ?>
                <li>
                    <span class="info-label">수령인</span>
                    <span class="info-value"><?= esc($order['recipient_name']) ?></span>
                </li>
                <?php endif; ?>
                <?php if (!empty($order['recipient_phone'])): ?>
                <li>
                    <span class="info-label">연락처</span>
                    <span class="info-value"><?= esc($order['recipient_phone']) ?></span>
                </li>
                <?php endif; ?>
                <?php if (!empty($order['delivery_address'])): ?>
                <li>
                    <span class="info-label">배송지</span>
                    <span class="info-value"><?= esc($order['delivery_address']) ?></span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- 주문 상품 목록 -->
        <?php if (!empty($items)): ?>
        <div class="detail-card">
            <h3>주문 상품</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>상품명 / 옵션</th>
                        <th>단가</th>
                        <th>수량</th>
                        <th>금액</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <?php $itemTotal = (int)$item['unit_price'] * (int)$item['quantity']; ?>
                <tr>
                    <td>
                        <p class="item-goods-name"><?= esc($item['goods_name']) ?></p>
                        <?php if (!empty($item['option_label'])): ?>
                        <span class="item-option"><?= esc($item['option_label']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((int)$item['unit_price']) ?>원</td>
                    <td><?= (int)$item['quantity'] ?>개</td>
                    <td class="item-price"><?= number_format($itemTotal) ?>원</td>
                </tr>
                <?php endforeach; ?>
                <!-- 총 합계 행 -->
                <tr class="items-total-row">
                    <td colspan="3" style="text-align: right; font-weight: 700; color: #495057;">총 결제금액</td>
                    <td class="item-price" style="font-size: 16px;">
                        <?= number_format((int)$order['total_price']) ?>원
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- 하단 버튼 영역 -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
            <a href="/mypage/orders" class="btn-back-list">← 목록으로</a>
            <?php if ($canRefund): ?>
            <button type="button" class="btn-refund-request" id="btnOpenRefundModal">환불 요청</button>
            <?php elseif ($isShipped): ?>
            <span class="refund-ship-notice">배송 완료 후 환불 요청이 가능합니다</span>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php if ($canRefund): ?>
<!-- ===== 환불 요청 모달 ===== -->
<div class="refund-overlay" id="refundOverlay">
  <div class="refund-modal">
    <div class="refund-modal-header">
      <h3>환불 요청</h3>
      <button type="button" class="refund-modal-close" id="btnCloseRefundModal">✕</button>
    </div>
    <div class="refund-modal-body">

      <!-- 상품 선택 -->
      <div class="rm-section">
        <h4>환불할 상품 선택 <span style="color:#e55039">*</span></h4>
        <?php foreach ($items as $item): ?>
        <div class="rm-item-row">
          <label>
            <input type="checkbox" class="refund-item-check"
                   value="<?= (int)$item['idx'] ?>" checked>
            <div>
              <div class="rm-item-name"><?= esc($item['goods_name']) ?></div>
              <?php if (!empty($item['option_label'])): ?>
              <div class="rm-item-opt"><?= esc($item['option_label']) ?></div>
              <?php endif; ?>
            </div>
          </label>
          <span class="rm-item-price"><?= number_format((int)$item['unit_price'] * (int)$item['quantity']) ?>원</span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- 7일 경과 경고 -->
      <?php if ($isOver7Days): ?>
      <div class="rm-warn">
        <span style="font-size:16px">⚠️</span>
        <div>
          배송완료일로부터 <strong>7일 이상</strong> 경과한 상품이 포함되어 있습니다.<br>
          상품 확인 과정에서 <strong>패널티가 발생할 수 있습니다.</strong>
        </div>
      </div>
      <?php endif; ?>

      <!-- 환불 사유 -->
      <div class="rm-section">
        <h4>환불 사유 <span style="color:#e55039">*</span></h4>
        <select class="rm-select" id="refundReason">
          <option value="">-- 사유를 선택해주세요 --</option>
          <option value="change_of_mind">단순 변심</option>
          <option value="defective">상품 불량 / 파손</option>
          <option value="wrong_item">상품 오배송 (다른 상품 도착)</option>
          <option value="delay">배송 지연</option>
          <option value="not_as_described">상품 설명과 다름</option>
          <option value="duplicate">중복 주문</option>
          <option value="direct">직접 입력 ✏️</option>
        </select>
        <textarea class="rm-textarea" id="refundReasonText"
                  placeholder="환불 사유를 직접 입력해주세요 (최대 200자)"
                  maxlength="200"
                  style="display:none"></textarea>
        <p class="rm-hint" id="refundDirectHint" style="display:none">
          "직접 입력" 선택 시 이 입력란을 작성해주세요.
        </p>
      </div>

      <!-- 이미지 첨부 -->
      <div class="rm-section" style="margin-bottom:0">
        <h4>이미지 첨부 (선택)</h4>
        <input type="file" class="rm-file" id="refundImages"
               accept="image/jpeg,image/png,image/gif" multiple>
        <p class="rm-hint">최대 3장, jpg/png/gif, 파일당 10MB 이하</p>
      </div>

    </div>
    <div class="refund-modal-footer">
      <button type="button" class="btn-rm-cancel" id="btnCancelRefund">취소</button>
      <button type="button" class="btn-rm-submit" id="btnSubmitRefund">환불 요청 제출</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<?php if ($canRefund): ?>
<script>
(function () {
  var overlay   = document.getElementById('refundOverlay');
  var btnOpen   = document.getElementById('btnOpenRefundModal');
  var btnClose  = document.getElementById('btnCloseRefundModal');
  var btnCancel = document.getElementById('btnCancelRefund');
  var btnSubmit = document.getElementById('btnSubmitRefund');
  var selReason = document.getElementById('refundReason');
  var txtReason = document.getElementById('refundReasonText');
  var hintDirect= document.getElementById('refundDirectHint');
  var fileInput = document.getElementById('refundImages');
  var orderIdx  = <?= (int)$order['idx'] ?>;

  function openModal()  { overlay.classList.add('active'); }
  function closeModal() { overlay.classList.remove('active'); }

  if (btnOpen)   btnOpen.addEventListener('click', openModal);
  if (btnClose)  btnClose.addEventListener('click', closeModal);
  if (btnCancel) btnCancel.addEventListener('click', closeModal);
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) closeModal();
  });

  selReason.addEventListener('change', function () {
    var isDirect = this.value === 'direct';
    txtReason.style.display  = isDirect ? 'block' : 'none';
    hintDirect.style.display = isDirect ? 'block' : 'none';
  });

  fileInput.addEventListener('change', function () {
    if (this.files.length > 3) {
      alert('이미지는 최대 3장까지 첨부 가능합니다.');
      this.value = '';
    }
  });

  btnSubmit.addEventListener('click', function () {
    var checked = document.querySelectorAll('.refund-item-check:checked');
    if (checked.length === 0) {
      alert('환불할 상품을 1개 이상 선택해주세요.');
      return;
    }
    if (!selReason.value) {
      alert('환불 사유를 선택해주세요.');
      return;
    }
    if (selReason.value === 'direct' && !txtReason.value.trim()) {
      alert('직접 입력 사유를 작성해주세요.');
      return;
    }

    var fd = new FormData();
    checked.forEach(function (el) { fd.append('item_idxs[]', el.value); });
    fd.append('reason', selReason.value);
    if (selReason.value === 'direct') fd.append('reason_text', txtReason.value.trim());
    Array.from(fileInput.files).forEach(function (f) { fd.append('images[]', f); });

    btnSubmit.disabled = true;
    btnSubmit.textContent = '처리 중...';

    fetch('/mypage/orders/' + orderIdx + '/refund', {
      method: 'POST',
      body: fd,
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        alert('환불 요청이 접수되었습니다.');
        closeModal();
        location.reload();
      } else {
        alert(data.message || '오류가 발생했습니다.');
        btnSubmit.disabled = false;
        btnSubmit.textContent = '환불 요청 제출';
      }
    })
    .catch(function () {
      alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
      btnSubmit.disabled = false;
      btnSubmit.textContent = '환불 요청 제출';
    });
  });
})();
</script>
<?php endif; ?>

</body>
</html>
