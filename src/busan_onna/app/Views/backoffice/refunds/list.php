<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">환불 요청 관리</h1>
            <p class="bo-page-desc">고객의 환불 요청을 확인하고 승인·반려 처리합니다.</p>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<style>
/* 상태 필터 탭 */
.refund-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.refund-tab {
    padding: 6px 18px; border-radius: 20px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; border: 1.5px solid #dee2e6;
    color: #495057; background: #fff;
    transition: all .15s;
}
.refund-tab.active, .refund-tab:hover { background: #343a40; color: #fff; border-color: #343a40; }
.refund-tab.tab-pending  { border-color: #ffc107; color: #856404; }
.refund-tab.tab-pending.active, .refund-tab.tab-pending:hover { background: #ffc107; color: #fff; border-color: #ffc107; }
.refund-tab.tab-approved { border-color: #28a745; color: #155724; }
.refund-tab.tab-approved.active, .refund-tab.tab-approved:hover { background: #28a745; color: #fff; border-color: #28a745; }
.refund-tab.tab-rejected { border-color: #dc3545; color: #721c24; }
.refund-tab.tab-rejected.active, .refund-tab.tab-rejected:hover { background: #dc3545; color: #fff; border-color: #dc3545; }

/* 상태 뱃지 */
.rs-badge {
    display: inline-block; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
}
.rs-pending  { background: #fff3cd; color: #856404; }
.rs-approved { background: #d4edda; color: #155724; }
.rs-rejected { background: #f8d7da; color: #721c24; }

/* 상세 모달 */
.ro-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 9000;
    align-items: center; justify-content: center;
}
.ro-overlay.active { display: flex; }
.ro-modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 620px;
    max-height: 88vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,0.3);
    margin: 16px;
}
.ro-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 24px; border-bottom: 1px solid #e9ecef;
    position: sticky; top: 0; background: #fff; z-index: 1;
}
.ro-header h3 { margin: 0; font-size: 16px; font-weight: 700; color: #212529; }
.ro-close { font-size: 22px; color: #adb5bd; cursor: pointer; background: none; border: none; }
.ro-body { padding: 22px 24px; }
.ro-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 24px; border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    position: sticky; bottom: 0;
}
.ro-section { margin-bottom: 20px; }
.ro-section h4 {
    font-size: 12px; font-weight: 700; color: #868e96;
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e9ecef;
}
.ro-row {
    display: flex; gap: 8px; font-size: 13px;
    padding: 6px 0; border-bottom: 1px solid #f8f9fa;
}
.ro-row:last-child { border-bottom: none; }
.ro-label { color: #868e96; white-space: nowrap; width: 90px; flex-shrink: 0; }
.ro-val   { font-weight: 600; color: #212529; }
.ro-item-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; background: #f8f9fa;
    border-radius: 8px; margin-bottom: 6px; font-size: 13px;
}
.ro-item-name  { flex: 1; font-weight: 600; color: #212529; }
.ro-item-opt   { font-size: 11px; color: #868e96; display: block; }
.ro-item-price { color: #e55039; font-weight: 700; white-space: nowrap; }
.ro-img-gallery { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
.ro-img-thumb {
    width: 80px; height: 80px; border-radius: 8px; overflow: hidden;
    border: 1.5px solid #dee2e6; cursor: pointer;
}
.ro-img-thumb img { width: 100%; height: 100%; object-fit: cover; }
.ro-no-img { font-size: 13px; color: #adb5bd; }
.ro-memo {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #dee2e6; border-radius: 8px;
    font-size: 13px; font-family: inherit;
    resize: vertical; min-height: 64px;
    background: #f8f9fa; box-sizing: border-box;
}
.btn-ro-close   { padding: 10px 22px; border: 1.5px solid #dee2e6; background: #fff; color: #495057; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.btn-ro-reject  { padding: 10px 22px; border: 1.5px solid #dc3545; background: #fff; color: #dc3545; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-ro-approve { padding: 10px 24px; border: none; background: #28a745; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-ro-approve:disabled, .btn-ro-reject:disabled { opacity: .5; cursor: not-allowed; }

/* 이미지 라이트박스 */
.lightbox-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.85); z-index: 10000;
    align-items: center; justify-content: center;
}
.lightbox-overlay.active { display: flex; }
.lightbox-overlay img {
    max-width: 90vw; max-height: 90vh;
    border-radius: 8px; object-fit: contain;
}
.lightbox-close {
    position: absolute; top: 16px; right: 20px;
    font-size: 32px; color: #fff; cursor: pointer; background: none; border: none;
}
</style>

<!-- 상태 필터 탭 -->
<div class="refund-tabs">
    <?php
    $tabList  = ['' => '전체', 'pending' => '대기중', 'approved' => '승인', 'rejected' => '반려'];
    $tabClass = ['' => '', 'pending' => 'tab-pending', 'approved' => 'tab-approved', 'rejected' => 'tab-rejected'];
    foreach ($tabList as $val => $label):
        $isActive = ($status === $val) ? ' active' : '';
    ?>
    <a href="?status=<?= esc($val) ?>"
       class="refund-tab <?= $tabClass[$val] ?><?= $isActive ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- 목록 테이블 -->
<?php if (empty($refunds)): ?>
    <p style="color:#868e96; padding:20px 0;">환불 요청이 없습니다.</p>
<?php else: ?>
<div class="bo-table-wrap">
    <table class="bo-table">
        <thead>
            <tr>
                <th>접수일시</th>
                <th>주문번호</th>
                <th>신청자</th>
                <th>환불 사유</th>
                <th>상태</th>
                <th>처리</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($refunds as $r): ?>
        <?php
            $badgeClass  = match($r['status']) {
                'pending'  => 'rs-pending',
                'approved' => 'rs-approved',
                'rejected' => 'rs-rejected',
                default    => '',
            };
            $statusLabel = $statusLabels[$r['status']] ?? $r['status'];
        ?>
        <tr>
            <td><?= esc(substr($r['created_at'], 0, 16)) ?></td>
            <td><?= esc($r['order_no'] ?? '-') ?></td>
            <td><?= esc($r['user_name'] ?? $r['user_id'] ?? '-') ?></td>
            <td><?= esc(\App\Models\RefundRequestModel::REASON_LABELS[$r['reason']] ?? $r['reason']) ?></td>
            <td><span class="rs-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
            <td>
                <button type="button" class="bo-btn bo-btn-sm"
                        onclick="openRefundDetail(<?= (int)$r['idx'] ?>)">
                    상세
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 페이지네이션 -->
<?php if ($pager): ?>
<div class="bo-pager"><?= $pager->links() ?></div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== 환불 요청 상세 모달 ===== -->
<div class="ro-overlay" id="roOverlay">
  <div class="ro-modal">
    <div class="ro-header">
      <h3>환불 요청 상세</h3>
      <div style="display:flex;align-items:center;gap:12px;">
        <span id="roStatusBadge" class="rs-badge"></span>
        <button type="button" class="ro-close" id="roClose">✕</button>
      </div>
    </div>
    <div class="ro-body" id="roBody">
      <p style="color:#868e96;text-align:center;padding:30px 0;">불러오는 중...</p>
    </div>
    <div class="ro-footer" id="roFooter" style="display:none;">
      <button type="button" class="btn-ro-close" id="roCloseBtn">닫기</button>
      <button type="button" class="btn-ro-reject"  id="roBtnReject">반려</button>
      <button type="button" class="btn-ro-approve" id="roBtnApprove">승인</button>
    </div>
  </div>
</div>

<!-- ===== 이미지 라이트박스 ===== -->
<div class="lightbox-overlay" id="lightboxOverlay">
  <button type="button" class="lightbox-close" id="lightboxClose">✕</button>
  <img id="lightboxImg" src="" alt="첨부 이미지">
</div>

<?= view('backoffice/partials/footer', $this->data) ?>

<script>
(function () {
  var currentRefundIdx = null;
  var overlay   = document.getElementById('roOverlay');
  var body      = document.getElementById('roBody');
  var footer    = document.getElementById('roFooter');
  var badge     = document.getElementById('roStatusBadge');
  var btnApprove= document.getElementById('roBtnApprove');
  var btnReject = document.getElementById('roBtnReject');
  var lbOverlay = document.getElementById('lightboxOverlay');
  var lbImg     = document.getElementById('lightboxImg');

  var statusLabels = <?= json_encode(\App\Models\RefundRequestModel::STATUS_LABELS) ?>;
  var reasonLabels = <?= json_encode(\App\Models\RefundRequestModel::REASON_LABELS) ?>;

  function closeModal() { overlay.classList.remove('active'); }

  document.getElementById('roClose').addEventListener('click', closeModal);
  document.getElementById('roCloseBtn').addEventListener('click', closeModal);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });

  document.getElementById('lightboxClose').addEventListener('click', function () {
    lbOverlay.classList.remove('active');
  });
  lbOverlay.addEventListener('click', function(e) {
    if (e.target === lbOverlay) lbOverlay.classList.remove('active');
  });

  window.openRefundDetail = function (idx) {
    currentRefundIdx = idx;
    overlay.classList.add('active');
    body.innerHTML = '<p style="color:#868e96;text-align:center;padding:30px 0;">불러오는 중...</p>';
    footer.style.display = 'none';
    badge.textContent = '';
    badge.className = 'rs-badge';

    fetch('/backoffice/refunds/' + idx + '/detail')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) {
          body.innerHTML = '<p style="color:#dc3545;padding:20px 0;">불러오기 실패: ' + (data.message || '') + '</p>';
          return;
        }
        renderDetail(data);
      })
      .catch(function () {
        body.innerHTML = '<p style="color:#dc3545;padding:20px 0;">네트워크 오류</p>';
      });
  };

  function renderDetail(data) {
    var r      = data.refund;
    var items  = data.items  || [];
    var images = data.images || [];

    var badgeClasses = { pending: 'rs-pending', approved: 'rs-approved', rejected: 'rs-rejected' };
    badge.textContent = statusLabels[r.status] || r.status;
    badge.className   = 'rs-badge ' + (badgeClasses[r.status] || '');

    var itemsHtml = items.map(function (it) {
      var opt   = it.option_label ? '<span class="ro-item-opt">' + esc(it.option_label) + '</span>' : '';
      var price = Number(it.unit_price) * Number(it.quantity);
      return '<div class="ro-item-row"><span class="ro-item-name">' + esc(it.goods_name) + opt + '</span>'
           + '<span class="ro-item-price">' + price.toLocaleString() + '원</span></div>';
    }).join('');

    var imgsHtml = images.length
      ? images.map(function (img) {
          return '<div class="ro-img-thumb" data-src="' + esc(img.file_path) + '">'
               + '<img src="' + esc(img.file_path) + '" alt="첨부이미지"></div>';
        }).join('')
      : '<span class="ro-no-img">첨부 이미지 없음</span>';

    var reasonLabel  = reasonLabels[r.reason] || r.reason;
    var reasonDetail = (r.reason === 'direct' && r.reason_text)
      ? '<div class="ro-row"><span class="ro-label">상세 내용</span><span class="ro-val">' + esc(r.reason_text) + '</span></div>'
      : '';

    var processedHtml = '';
    if (r.status !== 'pending') {
      processedHtml = '<div class="ro-section">'
        + '<h4>처리 내역</h4>'
        + '<div class="ro-row"><span class="ro-label">처리일시</span><span class="ro-val">' + esc(r.processed_at || '-') + '</span></div>'
        + (r.admin_memo ? '<div class="ro-row"><span class="ro-label">관리자 메모</span><span class="ro-val">' + esc(r.admin_memo) + '</span></div>' : '')
        + '</div>';
    }

    body.innerHTML = ''
      + '<div class="ro-section"><h4>주문 정보</h4>'
      + '<div class="ro-row"><span class="ro-label">주문번호</span><span class="ro-val">' + esc(r.order_no) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">신청자</span><span class="ro-val">' + esc(r.user_name || r.user_id) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">접수일시</span><span class="ro-val">' + esc(r.created_at) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">주문 상태</span><span class="ro-val">' + esc(r.order_status) + '</span></div>'
      + '</div>'
      + '<div class="ro-section"><h4>환불 요청 상품</h4>' + itemsHtml + '</div>'
      + '<div class="ro-section"><h4>환불 사유</h4>'
      + '<div class="ro-row"><span class="ro-label">선택 사유</span><span class="ro-val">' + esc(reasonLabel) + '</span></div>'
      + reasonDetail
      + '</div>'
      + '<div class="ro-section"><h4>고객 첨부 이미지</h4><div class="ro-img-gallery">' + imgsHtml + '</div></div>'
      + processedHtml
      + '<div class="ro-section" style="margin-bottom:0"><h4>관리자 메모</h4>'
      + '<textarea class="ro-memo" id="roMemo" placeholder="승인 시 선택, 반려 시 필수입력"></textarea>'
      + '</div>';

    footer.style.display = 'flex';
    if (r.status === 'pending') {
      btnApprove.style.display = '';
      btnReject.style.display  = '';
    } else {
      btnApprove.style.display = 'none';
      btnReject.style.display  = 'none';
    }
  }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  document.getElementById('roBody').addEventListener('click', function (e) {
    var thumb = e.target.closest('.ro-img-thumb');
    if (thumb) {
      lbImg.src = thumb.dataset.src;
      lbOverlay.classList.add('active');
    }
  });

  function postAction(url, memo, successMsg) {
    var fd = new FormData();
    fd.append('admin_memo', memo);
    btnApprove.disabled = true;
    btnReject.disabled  = true;

    fetch(url, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          alert(successMsg);
          closeModal();
          location.reload();
        } else {
          alert(data.message || '처리 중 오류가 발생했습니다.');
          btnApprove.disabled = false;
          btnReject.disabled  = false;
        }
      })
      .catch(function () {
        alert('네트워크 오류가 발생했습니다.');
        btnApprove.disabled = false;
        btnReject.disabled  = false;
      });
  }

  btnApprove.addEventListener('click', function () {
    if (!confirm('이 환불 요청을 승인하시겠습니까?')) return;
    var memo = document.getElementById('roMemo').value.trim();
    postAction('/backoffice/refunds/' + currentRefundIdx + '/approve', memo, '환불 요청이 승인되었습니다.');
  });

  btnReject.addEventListener('click', function () {
    var memo = document.getElementById('roMemo').value.trim();
    if (!memo) { alert('반려 사유를 입력해주세요.'); return; }
    if (!confirm('이 환불 요청을 반려하시겠습니까?')) return;
    postAction('/backoffice/refunds/' + currentRefundIdx + '/reject', memo, '환불 요청이 반려되었습니다.');
  });
})();
</script>
