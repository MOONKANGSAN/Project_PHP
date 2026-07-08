<?php
/**
 * 공지사항 탭 AJAX 부분 뷰
 * $notices : 공지사항 레코드 배열 (state=1, 고정 우선 정렬)
 */
?>

<?php if (empty($notices)): ?>
<div class="empty-result">
    <div class="empty-result-icon">📢</div>
    <p>등록된 공지사항이 없습니다.</p>
</div>
<?php else: ?>
<div class="notice-list">
    <?php foreach ($notices as $notice): ?>
    <div class="notice-item" data-idx="<?= (int)$notice['idx'] ?>">
        <div class="notice-item-header">
            <?php if ($notice['is_pinned']): ?>
            <span class="notice-pin-label">📌 공지</span>
            <?php endif; ?>
            <span class="notice-title"><?= esc($notice['title']) ?></span>
            <span class="notice-date"><?= esc(substr($notice['reg_date'], 0, 10)) ?></span>
            <span class="notice-toggle-arrow">▼</span>
        </div>
        <div class="notice-item-body">
            <div class="notice-content">
                <?= $notice['content'] /* 에디터 HTML, 신뢰된 관리자 입력이므로 이스케이프 안 함 */ ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
