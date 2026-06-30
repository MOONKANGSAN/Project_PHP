<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">회원 정보 관리</h1>
            <p class="bo-page-desc">가입된 회원 목록을 조회하고 관리합니다.</p>
        </div>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="bo-card">
    <!-- 검색/필터 바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="아이디 / 이메일 / 전화번호 검색...">
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/members" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 회원 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="text-align:center">이메일</th>
                    <th style="width:130px;text-align:center">아이디</th>
                    <th style="width:130px;text-align:center">전화번호</th>
                    <th style="width:140px;text-align:center">가입일</th>
                    <th style="width:100px;text-align:center">로그인</th>
                    <th style="width:90px;text-align:center">상태</th>
                    <th style="width:120px;text-align:center">비밀번호 초기화</th>
                    <th style="width:80px;text-align:center">탈퇴</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="9" class="bo-table-empty">가입된 회원이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>
                    <td><?= esc($row['email']) ?></td>
                    <td class="text-center"><?= esc($row['id']) ?></td>
                    <td class="text-center text-muted"><?= esc($row['phone'] ?: '-') ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>

                    <!-- 로그인 버튼 -->
                    <td class="text-center">
                        <form method="post" action="/backoffice/members/<?= $row['idx'] ?>/login-as"
                              onsubmit="return confirm('[<?= esc($row['id']) ?>] 계정으로 로그인하시겠습니까?\n메인 서비스로 이동합니다.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="bo-btn-action edit" style="min-width:64px">로그인</button>
                        </form>
                    </td>

                    <!-- 상태 토글 배지 -->
                    <td class="text-center">
                        <form method="post" action="/backoffice/members/<?= $row['idx'] ?>/state" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit"
                                    class="bo-badge <?= $row['state'] ? 'badge-active' : 'badge-inactive' ?>"
                                    style="cursor:pointer;border:none;padding:3px 10px"
                                    title="클릭하여 상태 변경">
                                <?= $row['state'] ? '활성' : '비활성' ?>
                            </button>
                        </form>
                    </td>

                    <!-- 비밀번호 초기화 -->
                    <td class="text-center">
                        <form method="post" action="/backoffice/members/<?= $row['idx'] ?>/reset-password"
                              onsubmit="return confirm('[<?= esc($row['id']) ?>] 비밀번호를 0000으로 초기화하시겠습니까?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="bo-btn-action deactivate" style="min-width:80px">초기화</button>
                        </form>
                    </td>

                    <!-- 탈퇴 버튼: 모달 트리거 -->
                    <td class="text-center">
                        <button type="button"
                                class="bo-btn-action"
                                style="min-width:60px;background:#6b7280;color:#fff"
                                onclick="openWithdrawModal(<?= $row['idx'] ?>, '<?= esc($row['id'], 'js') ?>')">
                            탈퇴
                        </button>
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

<!-- ===== 탈퇴 처리 모달 ===== -->
<div id="withdrawModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:480px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">

        <!-- 모달 헤더 -->
        <div style="background:#fef2f2;padding:20px 24px 16px;border-bottom:1px solid #fecaca;display:flex;align-items:flex-start;gap:12px;">
            <div style="width:36px;height:36px;background:#ef4444;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;color:#b91c1c">회원 탈퇴 처리</h3>
                <p style="margin:0;font-size:13px;color:#dc2626">탈퇴 후 해당 회원은 서비스를 이용할 수 없습니다.</p>
            </div>
            <button onclick="closeWithdrawModal()" style="margin-left:auto;background:none;border:none;cursor:pointer;padding:2px;color:#9ca3af;flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- 모달 바디 -->
        <div style="padding:24px;">

            <!-- 대상 회원 정보 박스 -->
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:#e5e7eb;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.2">탈퇴 대상 회원</p>
                    <p id="withdrawTargetId" style="margin:0;font-size:14px;font-weight:700;color:#111;line-height:1.4"></p>
                </div>
            </div>

            <form id="withdrawForm" method="post" action="">
                <?= csrf_field() ?>

                <!-- 탈퇴 사유 입력 -->
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <label for="withdrawReason" style="font-size:13px;font-weight:600;color:#374151;">
                            탈퇴 사유
                            <span style="color:#9ca3af;font-weight:400;font-size:12px;margin-left:4px">(선택)</span>
                        </label>
                        <span id="withdrawReasonCount" style="font-size:12px;color:#9ca3af;">0 / 30</span>
                    </div>
                    <textarea id="withdrawReason" name="reason"
                              maxlength="30"
                              rows="3"
                              placeholder="탈퇴 사유를 입력하세요. (최대 30자)"
                              oninput="updateReasonCount()"
                              style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:13px;resize:none;font-family:inherit;color:#111;outline:none;transition:border-color .15s;line-height:1.6;"
                              onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.1)'"
                              onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'"></textarea>
                    <p style="margin:6px 0 0;font-size:11px;color:#9ca3af;">미입력 시 공란으로 저장됩니다.</p>
                </div>

                <!-- 버튼 영역 -->
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:24px;">
                    <button type="button" onclick="closeWithdrawModal()"
                            style="padding:9px 22px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:500;cursor:pointer;color:#374151;transition:background .15s;"
                            onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                        취소
                    </button>
                    <button type="submit"
                            style="padding:9px 22px;border:none;border-radius:8px;background:#ef4444;color:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;"
                            onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        탈퇴 처리
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openWithdrawModal(idx, userId) {
    document.getElementById('withdrawTargetId').textContent = userId;
    document.getElementById('withdrawForm').action = '/backoffice/members/' + idx + '/withdraw';
    document.getElementById('withdrawReason').value = '';
    document.getElementById('withdrawReasonCount').textContent = '0 / 30';
    document.getElementById('withdrawReasonCount').style.color = '#9ca3af';

    var modal = document.getElementById('withdrawModal');
    modal.style.display = 'flex';
    // 열릴 때 textarea에 포커스
    setTimeout(function() { document.getElementById('withdrawReason').focus(); }, 50);
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'none';
}

function updateReasonCount() {
    var ta      = document.getElementById('withdrawReason');
    var counter = document.getElementById('withdrawReasonCount');
    var len     = ta.value.length;
    counter.textContent = len + ' / 30';
    // 30자 근접 시 붉은색으로 강조
    counter.style.color = len >= 28 ? '#ef4444' : (len >= 20 ? '#f59e0b' : '#9ca3af');
}

// 배경 클릭 시 닫기
document.getElementById('withdrawModal').addEventListener('click', function(e) {
    if (e.target === this) closeWithdrawModal();
});

// ESC 키로 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeWithdrawModal();
});
</script>

<?= view('backoffice/partials/footer') ?>
