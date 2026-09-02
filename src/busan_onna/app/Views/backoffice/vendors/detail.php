<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">판매자 상세</h1>
            <p class="bo-page-desc">판매자 정보를 확인하고 승인 또는 거절을 처리합니다.</p>
        </div>
        <!-- 목록으로 돌아가기 링크 -->
        <a href="/backoffice/vendors" class="bo-btn bo-btn-ghost">&#8592; 목록으로</a>
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
// 현재 판매자 상태값 (0=대기, 1=승인, 2=거절)
$currentState = (int) ($vendor['state'] ?? 0);
?>

<div class="bo-card">
    <!-- 판매자 기본 정보 -->
    <table class="bo-table" style="margin-bottom:24px;">
        <tbody>
            <tr>
                <th style="width:160px;background:#f9fafb;font-weight:600;padding:12px 16px;">IDX</th>
                <td style="padding:12px 16px;"><?= esc($vendor['idx'] ?? '-') ?></td>
            </tr>
            <tr>
                <th style="background:#f9fafb;font-weight:600;padding:12px 16px;">상점명</th>
                <td style="padding:12px 16px;"><?= esc($vendor['shop_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <th style="background:#f9fafb;font-weight:600;padding:12px 16px;">연락처</th>
                <td style="padding:12px 16px;"><?= esc($vendor['phone'] ?? '-') ?></td>
            </tr>
            <tr>
                <th style="background:#f9fafb;font-weight:600;padding:12px 16px;">메모</th>
                <td style="padding:12px 16px;"><?= esc($vendor['note'] ?? '-') ?></td>
            </tr>
            <tr>
                <th style="background:#f9fafb;font-weight:600;padding:12px 16px;">현재 상태</th>
                <td style="padding:12px 16px;">
                    <!-- 상태별 배지 표시 -->
                    <span class="bo-badge <?= $currentState === 1 ? 'badge-active' : ($currentState === 0 ? 'badge-pending' : 'badge-inactive') ?>">
                        <?= esc($labels[$currentState] ?? '알수없음') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="background:#f9fafb;font-weight:600;padding:12px 16px;">등록일</th>
                <td style="padding:12px 16px;"><?= esc(substr($vendor['reg_date'] ?? '', 0, 10)) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- 상태별 액션 영역 -->
    <div style="border-top:1px solid #e5e7eb;padding-top:20px;">

        <?php if ($currentState === 0): ?>
        <!-- 대기 상태일 때만 승인/거절 버튼 표시 -->
        <div style="display:flex;gap:12px;align-items:center;">
            <span style="font-size:14px;color:#6b7280;margin-right:8px;">처리:</span>

            <!-- 승인 버튼: POST /backoffice/vendors/{idx}/approve -->
            <form method="post" action="/backoffice/vendors/<?= esc($vendor['idx']) ?>/approve"
                  onsubmit="return confirm('이 판매자를 승인하시겠습니까?')">
                <?= csrf_field() ?>
                <button type="submit" class="bo-btn bo-btn-primary">승인</button>
            </form>

            <!-- 거절 버튼: POST /backoffice/vendors/{idx}/reject -->
            <form method="post" action="/backoffice/vendors/<?= esc($vendor['idx']) ?>/reject"
                  onsubmit="return confirm('이 판매자를 거절하시겠습니까?')">
                <?= csrf_field() ?>
                <button type="submit" class="bo-btn"
                        style="background:#6b7280;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;">
                    거절
                </button>
            </form>
        </div>

        <?php elseif ($currentState === 1): ?>
        <!-- 승인된 판매자 안내 -->
        <div class="bo-alert bo-alert-success" style="margin:0;">
            이미 승인된 판매자입니다.
        </div>

        <?php else: ?>
        <!-- 거절된 판매자 안내 -->
        <div class="bo-alert bo-alert-error" style="margin:0;">
            거절된 판매자입니다.
        </div>

        <?php endif; ?>
    </div>
</div>

<?= view('backoffice/partials/footer') ?>
