<?php
/**
 * FAQ 탭 AJAX 부분 뷰
 * $faqs       : FAQ 레코드 배열
 * $types      : FaqModel::TYPES 카테고리 목록
 * $activeType : 현재 선택된 카테고리 ('' = 전체)
 */
?>

<!-- FAQ 카테고리 필터 -->
<div class="faq-type-filter">
    <button class="faq-type-btn <?= $activeType === '' ? 'active' : '' ?>" data-type="">전체</button>
    <?php foreach ($types as $num => $label): ?>
    <button class="faq-type-btn <?= $activeType === (string)$num ? 'active' : '' ?>"
            data-type="<?= (int)$num ?>"><?= esc($label) ?></button>
    <?php endforeach; ?>
</div>

<!-- FAQ 아코디언 목록 -->
<?php if (empty($faqs)): ?>
<div class="empty-result">
    <div class="empty-result-icon">💬</div>
    <p>등록된 FAQ가 없습니다.</p>
</div>
<?php else: ?>
<div class="faq-list">
    <?php foreach ($faqs as $faq): ?>
    <div class="faq-item">
        <button class="faq-question" type="button">
            <span class="faq-q-badge">Q</span>
            <span class="faq-q-text"><?= esc($faq['title']) ?></span>
            <span class="faq-category-badge"><?= esc($types[$faq['faq_type']] ?? '') ?></span>
            <span class="faq-arrow">▼</span>
        </button>
        <div class="faq-answer">
            <span class="faq-a-badge">A</span>
            <div class="faq-a-text"><?= nl2br(esc($faq['content'])) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
