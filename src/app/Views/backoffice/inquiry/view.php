<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">문의 상세</h1>
            <p class="bo-page-desc">문의 내용을 확인하고 답변을 작성합니다.</p>
        </div>
        <a href="/backoffice/inquiries" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- ===== 문의 정보 카드 ===== -->
<div class="bo-card" style="margin-bottom:20px;">
    <div style="padding:4px 0 20px;border-bottom:1px solid #e5e7eb;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <!-- 유형 배지 -->
            <span style="font-size:12px;padding:3px 10px;border-radius:4px;background:#eff6ff;color:#2563eb;font-weight:600;">
                <?= esc($types[$item['inquiry_type']] ?? '기타') ?>
            </span>
            <!-- 공개/비공개 배지 -->
            <?php if ((int)$item['is_public']): ?>
                <span style="font-size:12px;padding:3px 10px;border-radius:4px;background:#f0fdf4;color:#16a34a;font-weight:600;">공개</span>
            <?php else: ?>
                <span style="font-size:12px;padding:3px 10px;border-radius:4px;background:#f9fafb;color:#6b7280;font-weight:600;">비공개</span>
            <?php endif; ?>
            <!-- 처리 상태 배지 -->
            <?php if ((int)$item['state'] === 2): ?>
                <span style="font-size:12px;padding:3px 10px;border-radius:4px;background:#f0fdf4;color:#16a34a;font-weight:600;">답변완료</span>
            <?php elseif ((int)$item['state'] === 1): ?>
                <span style="font-size:12px;padding:3px 10px;border-radius:4px;background:#fff7ed;color:#c2410c;font-weight:600;">접수 (미답변)</span>
            <?php else: ?>
                <span style="font-size:12px;padding:3px 10px;border-radius:4px;background:#f1f5f9;color:#94a3b8;font-weight:600;">숨김</span>
            <?php endif; ?>
        </div>
        <span style="font-size:12px;color:#9ca3af;">IDX: <?= $item['idx'] ?></span>
    </div>

    <!-- 제목 -->
    <h2 style="font-size:18px;font-weight:700;color:#111;margin:0 0 16px;"><?= esc($item['title']) ?></h2>

    <!-- 메타 정보 -->
    <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:20px;">
        <div style="display:flex;gap:6px;align-items:center;font-size:13px;color:#6b7280;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>작성자:</span>
            <strong style="color:#374151;"><?= esc($item['id']) ?></strong>
        </div>
        <div style="display:flex;gap:6px;align-items:center;font-size:13px;color:#6b7280;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>등록일:</span>
            <strong style="color:#374151;"><?= substr($item['reg_date'], 0, 16) ?></strong>
        </div>
    </div>

    <!-- 문의 내용 -->
    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px 22px;min-height:120px;font-size:14px;color:#374151;line-height:1.8;white-space:pre-wrap;word-break:break-all;">
        <?= esc($item['content']) ?>
    </div>
</div>

<!-- ===== 답변 영역 ===== -->
<div class="bo-card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
        <div style="width:28px;height:28px;background:#3b82f6;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
        <h3 style="font-size:15px;font-weight:700;color:#111;margin:0;">관리자 답변</h3>
    </div>

    <?php if ($item['answer']): ?>
    <!-- 기존 답변 표시 -->
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:20px 22px;margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <span style="font-size:12px;color:#2563eb;font-weight:600;">
                <?= esc(session()->get('backoffice.id') ?? '관리자') ?> 답변
                <?php if ($item['answer_date']): ?>
                    · <?= substr($item['answer_date'], 0, 16) ?>
                <?php endif; ?>
            </span>
            <form method="post" action="/backoffice/inquiries/<?= $item['idx'] ?>/answer/delete"
                  onsubmit="return confirm('답변을 삭제하시겠습니까? 상태가 [접수]로 되돌아갑니다.')">
                <?= csrf_field() ?>
                <button type="submit" style="background:none;border:none;font-size:12px;color:#ef4444;cursor:pointer;padding:0;">답변 삭제</button>
            </form>
        </div>
        <div style="font-size:14px;color:#1e3a5f;line-height:1.8;white-space:pre-wrap;word-break:break-all;">
            <?= esc($item['answer']) ?>
        </div>
    </div>
    <!-- 답변 수정 폼 -->
    <details style="margin-top:4px;">
        <summary style="font-size:13px;color:#6b7280;cursor:pointer;user-select:none;padding:4px 0;">답변 수정하기 ▾</summary>
        <div style="margin-top:14px;">
            <?= view('backoffice/inquiry/_answer_form', ['item' => $item]) ?>
        </div>
    </details>

    <?php else: ?>
    <!-- 미답변 상태: 바로 폼 노출 -->
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c2410c" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span style="font-size:13px;color:#c2410c;font-weight:500;">아직 답변이 등록되지 않았습니다.</span>
    </div>
    <?= view('backoffice/inquiry/_answer_form', ['item' => $item]) ?>
    <?php endif; ?>
</div>

<?= view('backoffice/partials/footer') ?>
