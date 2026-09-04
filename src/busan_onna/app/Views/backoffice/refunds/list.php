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
    color: #495057; background: #fff; transition: all .15s;
}
.refund-tab:hover, .refund-tab.active { background: #343a40; color: #fff; border-color: #343a40; }
.refund-tab.tab-pending              { border-color: #ffc107; color: #856404; }
.refund-tab.tab-pending:hover,
.refund-tab.tab-pending.active       { background: #ffc107; color: #212529; border-color: #ffc107; }
.refund-tab.tab-approved             { border-color: #28a745; color: #155724; }
.refund-tab.tab-approved:hover,
.refund-tab.tab-approved.active      { background: #28a745; color: #fff; border-color: #28a745; }
.refund-tab.tab-rejected             { border-color: #dc3545; color: #721c24; }
.refund-tab.tab-rejected:hover,
.refund-tab.tab-rejected.active      { background: #dc3545; color: #fff; border-color: #dc3545; }

/* 상태 뱃지 */
.rs-badge {
    display: inline-block; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;
}
.rs-pending  { background: #fff3cd; color: #856404; }
.rs-approved { background: #d4edda; color: #155724; }
.rs-rejected { background: #f8d7da; color: #721c24; }

/* 상세 모달 */
.ro-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 9000;
    align-items: center; justify-content: center;
}
.ro-overlay.active { display: flex; }
.ro-modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 640px;
    max-height: 88vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,.3);
    margin: 16px;
    display: flex; flex-direction: column;
}
.ro-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 24px; border-bottom: 1px solid #e9ecef;
    position: sticky; top: 0; background: #fff; z-index: 1; flex-shrink: 0;
}
.ro-header h3 { margin: 0; font-size: 16px; font-weight: 700; color: #212529; }
.ro-header-right { display: flex; align-items: center; gap: 12px; }
.ro-close { font-size: 22px; color: #adb5bd; cursor: pointer; background: none; border: none; line-height: 1; }
.ro-body { padding: 22px 24px; flex: 1; }
.ro-footer {
    display: none; justify-content: flex-end; gap: 10px;
    padding: 14px 24px; border-top: 1px solid #e9ecef;
    background: #f8f9fa; position: sticky; bottom: 0; flex-shrink: 0;
}
.ro-section { margin-bottom: 20px; }
.ro-section:last-child { margin-bottom: 0; }
.ro-section h4 {
    font-size: 11px; font-weight: 700; color: #868e96;
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e9ecef;
}
.ro-row {
    display: flex; gap: 8px; font-size: 13px;
    padding: 6px 0; border-bottom: 1px solid #f8f9fa;
}
.ro-row:last-child { border-bottom: none; }
.ro-label { color: #868e96; white-space: nowrap; width: 90px; flex-shrink: 0; }
.ro-val   { font-weight: 600; color: #212529; word-break: break-all; }
.ro-item-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; background: #f8f9fa;
    border-radius: 8px; margin-bottom: 6px; font-size: 13px;
}
.ro-item-name  { flex: 1; font-weight: 600; color: #212529; }
.ro-item-opt   { font-size: 11px; color: #868e96; display: block; }
.ro-item-price { color: #e55039; font-weight: 700; white-space: nowrap; }
.ro-img-gallery { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
.ro-img-thumb {
    width: 80px; height: 80px; border-radius: 8px; overflow: hidden;
    border: 1.5px solid #dee2e6; cursor: pointer; flex-shrink: 0;
}
.ro-img-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ro-no-img { font-size: 13px; color: #adb5bd; }
.ro-memo {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #dee2e6; border-radius: 8px;
    font-size: 13px; font-family: inherit;
    resize: vertical; min-height: 64px;
    box-sizing: border-box; background: #fff;
}
.ro-memo:focus { outline: none; border-color: #495057; }
.btn-ro-close   { padding: 10px 22px; border: 1.5px solid #dee2e6; background: #fff; color: #495057; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.btn-ro-reject  { padding: 10px 22px; border: 1.5px solid #dc3545; background: #fff; color: #dc3545; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-ro-approve { padding: 10px 24px; border: none; background: #28a745; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-ro-close:hover   { background: #f8f9fa; }
.btn-ro-reject:hover  { background: #dc3545; color: #fff; }
.btn-ro-approve:hover { background: #218838; }
.btn-ro-approve:disabled,
.btn-ro-reject:disabled { opacity: .5; cursor: not-allowed; }

/* 이미지 라이트박스 */
.lb-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.85); z-index: 10000;
    align-items: center; justify-content: center;
}
.lb-overlay.active { display: flex; }
.lb-overlay img { max-width: 90vw; max-height: 90vh; border-radius: 8px; object-fit: contain; }
.lb-close {
    position: absolute; top: 16px; right: 20px;
    font-size: 32px; color: #fff; cursor: pointer; background: none; border: none; line-height: 1;
}

/* 검색 필터 폼 */
.refund-filter {
    background: #f8f9fa; border: 1px solid #e9ecef;
    border-radius: 8px; padding: 16px 20px; margin-bottom: 20px;
}
.rf-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
.rf-group { display: flex; flex-direction: column; gap: 4px; }
.rf-label { font-size: 12px; font-weight: 600; color: #495057; }
.rf-input {
    padding: 7px 11px; border: 1.5px solid #dee2e6; border-radius: 6px;
    font-size: 13px; background: #fff; color: #212529; height: 36px; box-sizing: border-box;
}
.rf-input:focus { outline: none; border-color: #495057; }
.rf-input-no  { width: 160px; }
.rf-input-usr { width: 130px; }
.rf-input-dt  { width: 140px; }
.rf-btns { display: flex; gap: 8px; }

/* 빈 상태 */
.refund-empty {
    text-align: center; padding: 48px 0; color: #adb5bd; font-size: 14px;
}

/* 테이블 컬럼 정렬·너비 */
.bo-table thead th              { text-align: center; white-space: nowrap; }
.bo-table thead th:nth-child(1) { width: 145px; }  /* 접수일시 */
.bo-table thead th:nth-child(2) { width: 170px; }  /* 주문번호 */
.bo-table thead th:nth-child(3) { width: 100px; }  /* 신청자   */
.bo-table thead th:nth-child(5) { width: 90px;  }  /* 상태     */
.bo-table thead th:nth-child(6) { width: 80px;  }  /* 처리     */

.bo-table tbody td              { text-align: center; white-space: nowrap; }
.bo-table tbody td:nth-child(2) { text-align: left; }   /* 주문번호만 좌측 */
.bo-table tbody td:nth-child(4) { white-space: nowrap; } /* 환불 사유 줄바꿈 방지 */
</style>

<!-- 상태 필터 탭 -->
<div class="refund-tabs">
    <?php
    $tabList  = ['' => '전체', 'pending' => '대기중', 'approved' => '승인', 'rejected' => '반려'];
    $tabExtra = ['' => '', 'pending' => 'tab-pending', 'approved' => 'tab-approved', 'rejected' => 'tab-rejected'];
    foreach ($tabList as $val => $label):
        $activeClass = ($status === $val) ? ' active' : '';
    ?>
    <a href="?<?= $val !== '' ? 'status=' . esc($val) : '' ?>"
       class="refund-tab <?= $tabExtra[$val] ?><?= $activeClass ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- 검색 필터 -->
<form class="refund-filter" method="get" action="">
    <input type="hidden" name="status" value="<?= esc($status) ?>">
    <div class="rf-row">
        <div class="rf-group">
            <label class="rf-label">주문번호</label>
            <input type="text" name="order_no" class="rf-input rf-input-no"
                   placeholder="주문번호 검색" value="<?= esc($orderNo ?? '') ?>">
        </div>
        <div class="rf-group">
            <label class="rf-label">주문자</label>
            <input type="text" name="user_name" class="rf-input rf-input-usr"
                   placeholder="이름 / 아이디" value="<?= esc($userName ?? '') ?>">
        </div>
        <div class="rf-group">
            <label class="rf-label">시작일자</label>
            <input type="date" name="date_from" class="rf-input rf-input-dt"
                   value="<?= esc($dateFrom ?? '') ?>">
        </div>
        <div class="rf-group">
            <label class="rf-label">종료일자</label>
            <input type="date" name="date_to" class="rf-input rf-input-dt"
                   value="<?= esc($dateTo ?? '') ?>">
        </div>
        <div class="rf-btns">
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="?<?= $status !== '' ? 'status=' . esc($status) : '' ?>"
               class="bo-btn">초기화</a>
        </div>
    </div>
</form>

<!-- 목록 테이블 -->
<?php if (empty($refunds)): ?>
    <div class="refund-empty">환불 요청이 없습니다.</div>
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
            $reasonLabel = \App\Models\RefundRequestModel::REASON_LABELS[$r['reason']] ?? $r['reason'];
        ?>
        <tr>
            <td><?= esc(substr($r['created_at'] ?? '', 0, 16)) ?></td>
            <td><?= esc($r['order_no'] ?? '-') ?></td>
            <td><?= esc($r['user_name'] ?? $r['user_id'] ?? '-') ?></td>
            <td><?= esc($reasonLabel) ?></td>
            <td><span class="rs-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
            <td>
                <button type="button" class="bo-btn bo-btn-sm btn-detail"
                        data-idx="<?= (int)$r['idx'] ?>">상세</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 페이지네이션 -->
<?php if ($pager): ?>
<div class="bo-pagination">
    <?= $pager->links('default', 'bo_pager') ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== 환불 요청 상세 모달 ===== -->
<div class="ro-overlay" id="roOverlay">
  <div class="ro-modal">
    <div class="ro-header">
      <h3>환불 요청 상세</h3>
      <div class="ro-header-right">
        <span id="roStatusBadge" class="rs-badge"></span>
        <button type="button" class="ro-close" id="roClose">✕</button>
      </div>
    </div>
    <div class="ro-body" id="roBody">
      <p style="color:#868e96;text-align:center;padding:40px 0;">불러오는 중...</p>
    </div>
    <div class="ro-footer" id="roFooter">
      <button type="button" class="btn-ro-close" id="roBtnClose2">닫기</button>
      <button type="button" class="btn-ro-reject"  id="roBtnReject">반려</button>
      <button type="button" class="btn-ro-approve" id="roBtnApprove">승인</button>
    </div>
  </div>
</div>

<!-- ===== 이미지 라이트박스 ===== -->
<div class="lb-overlay" id="lbOverlay">
  <button type="button" class="lb-close" id="lbClose">✕</button>
  <img id="lbImg" src="" alt="첨부 이미지">
</div>

<?= view('backoffice/partials/footer', $this->data) ?>

<script>
(function () {
  var currentIdx = null;
  var overlay    = document.getElementById('roOverlay');
  var roBody     = document.getElementById('roBody');
  var roFooter   = document.getElementById('roFooter');
  var roBadge    = document.getElementById('roStatusBadge');
  var btnApprove = document.getElementById('roBtnApprove');
  var btnReject  = document.getElementById('roBtnReject');
  var lbOverlay  = document.getElementById('lbOverlay');
  var lbImg      = document.getElementById('lbImg');

  var statusLabels = <?= json_encode(\App\Models\RefundRequestModel::STATUS_LABELS,  JSON_UNESCAPED_UNICODE) ?>;
  var reasonLabels = <?= json_encode(\App\Models\RefundRequestModel::REASON_LABELS,  JSON_UNESCAPED_UNICODE) ?>;
  var orderLabels  = <?= json_encode(\App\Models\OrderModel::STATUS_LABELS,          JSON_UNESCAPED_UNICODE) ?>;
  var badgeClasses = { pending: 'rs-pending', approved: 'rs-approved', rejected: 'rs-rejected' };

  /* 모달 열기·닫기 */
  function openModal() { overlay.classList.add('active'); }
  function closeModal() {
    overlay.classList.remove('active');
    currentIdx = null;
  }

  document.getElementById('roClose').addEventListener('click', closeModal);
  document.getElementById('roBtnClose2').addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });

  /* 라이트박스 */
  document.getElementById('lbClose').addEventListener('click', function () { lbOverlay.classList.remove('active'); });
  lbOverlay.addEventListener('click', function (e) { if (e.target === lbOverlay) lbOverlay.classList.remove('active'); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      lbOverlay.classList.remove('active');
      closeModal();
    }
  });

  /* 이미지 썸네일 클릭 — 위임 이벤트 */
  roBody.addEventListener('click', function (e) {
    var thumb = e.target.closest('.ro-img-thumb');
    if (!thumb) return;
    lbImg.src = thumb.dataset.src;
    lbOverlay.classList.add('active');
  });

  /* 상세 버튼 클릭 — 위임 이벤트 */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-detail');
    if (!btn) return;
    loadDetail(parseInt(btn.dataset.idx, 10));
  });

  /* 상세 로드 */
  function loadDetail(idx) {
    currentIdx = idx;
    openModal();
    roBody.innerHTML = '<p style="color:#868e96;text-align:center;padding:40px 0;">불러오는 중...</p>';
    roFooter.style.display = 'none';
    roBadge.textContent = '';
    roBadge.className   = 'rs-badge';

    fetch('/backoffice/refunds/' + idx + '/detail')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) {
          roBody.innerHTML = '<p style="color:#dc3545;padding:20px 0;text-align:center;">'
            + esc(data.message || '불러오기 실패') + '</p>';
          return;
        }
        renderDetail(data);
      })
      .catch(function (err) {
        roBody.innerHTML = '<p style="color:#dc3545;padding:20px 0;text-align:center;">네트워크 오류: '
          + esc(err && err.message ? err.message : '알 수 없는 오류') + '</p>';
      });
  }

  /* 상세 렌더링 */
  function renderDetail(data) {
    var r      = data.refund;
    var items  = data.items  || [];
    var images = data.images || [];

    roBadge.textContent = statusLabels[r.status] || r.status;
    roBadge.className   = 'rs-badge ' + (badgeClasses[r.status] || '');

    /* 환불 상품 목록 */
    var itemsHtml = items.length ? items.map(function (it) {
      var opt   = it.option_label
        ? '<span class="ro-item-opt">' + esc(it.option_label) + '</span>' : '';
      var price = (Number(it.unit_price) * Number(it.quantity)).toLocaleString();
      return '<div class="ro-item-row">'
           + '<span class="ro-item-name">' + esc(it.goods_name) + opt + '</span>'
           + '<span class="ro-item-price">' + price + '원</span>'
           + '</div>';
    }).join('') : '<p style="color:#adb5bd;font-size:13px;">상품 정보 없음</p>';

    /* 첨부 이미지 */
    var imgsHtml = images.length
      ? images.map(function (img) {
          return '<div class="ro-img-thumb" data-src="' + esc(img.file_path) + '">'
               + '<img src="' + esc(img.file_path) + '" alt="첨부이미지" loading="lazy"></div>';
        }).join('')
      : '<span class="ro-no-img">첨부 이미지 없음</span>';

    /* 환불 사유 */
    var reasonLabel  = reasonLabels[r.reason] || r.reason;
    var reasonDetail = (r.reason === 'direct' && r.reason_text)
      ? '<div class="ro-row"><span class="ro-label">직접 입력</span>'
        + '<span class="ro-val">' + esc(r.reason_text) + '</span></div>'
      : '';

    /* 처리 내역 (승인/반려된 경우) */
    var processedHtml = '';
    if (r.status !== 'pending') {
      processedHtml = '<div class="ro-section">'
        + '<h4>처리 내역</h4>'
        + '<div class="ro-row"><span class="ro-label">처리일시</span>'
        + '<span class="ro-val">' + esc((r.processed_at || '-').substring(0, 16)) + '</span></div>'
        + (r.admin_memo
          ? '<div class="ro-row"><span class="ro-label">관리자 메모</span>'
            + '<span class="ro-val">' + esc(r.admin_memo) + '</span></div>'
          : '')
        + '</div>';
    }

    roBody.innerHTML =
      '<div class="ro-section"><h4>주문 정보</h4>'
      + '<div class="ro-row"><span class="ro-label">주문번호</span><span class="ro-val">' + esc(r.order_no) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">신청자</span><span class="ro-val">' + esc(r.user_name || r.user_id) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">접수일시</span><span class="ro-val">' + esc((r.created_at || '').substring(0, 16)) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">주문 상태</span><span class="ro-val">' + esc(orderLabels[r.order_status] || r.order_status) + '</span></div>'
      + '</div>'
      + '<div class="ro-section"><h4>환불 요청 상품</h4>' + itemsHtml + '</div>'
      + '<div class="ro-section"><h4>환불 사유</h4>'
      + '<div class="ro-row"><span class="ro-label">선택 사유</span><span class="ro-val">' + esc(reasonLabel) + '</span></div>'
      + reasonDetail
      + '</div>'
      + '<div class="ro-section"><h4>고객 첨부 이미지</h4>'
      + '<div class="ro-img-gallery">' + imgsHtml + '</div></div>'
      + processedHtml
      + '<div class="ro-section"><h4>관리자 메모</h4>'
      + '<textarea class="ro-memo" id="roMemo" placeholder="승인 시 선택, 반려 시 필수 입력"></textarea>'
      + '</div>';

    roFooter.style.display = r.status === 'pending' ? 'flex' : 'none';
    btnApprove.disabled = false;
    btnReject.disabled  = false;
  }

  /* XSS 방지 이스케이프 */
  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /* 승인·반려 공통 처리 */
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
        alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
        btnApprove.disabled = false;
        btnReject.disabled  = false;
      });
  }

  btnApprove.addEventListener('click', function () {
    if (!confirm('이 환불 요청을 승인하시겠습니까?')) return;
    var memo = (document.getElementById('roMemo').value || '').trim();
    postAction('/backoffice/refunds/' + currentIdx + '/approve', memo, '환불 요청이 승인되었습니다.');
  });

  btnReject.addEventListener('click', function () {
    var memo = (document.getElementById('roMemo').value || '').trim();
    if (!memo) { alert('반려 사유를 입력해주세요.'); return; }
    if (!confirm('이 환불 요청을 반려하시겠습니까?')) return;
    postAction('/backoffice/refunds/' + currentIdx + '/reject', memo, '환불 요청이 반려되었습니다.');
  });
})();
</script>
