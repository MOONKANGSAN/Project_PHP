<?php
/**
 * 서비스(프론트) 페이지네이션 템플릿
 * - li 가로 배치, 배경·테두리 기본 none
 * - 호버 시 연한 회색 원형 이벤트
 * - prev/next: < > SVG 아이콘
 */
$pager->setSurroundCount(2);
$links = $pager->links();

// 현재 URL에서 page 파라미터만 제거하고 나머지 쿼리 유지
$currentUrl = current_url();
$baseUrl    = preg_replace('/([?&])page=\d+/', '', $currentUrl);
$baseUrl    = rtrim($baseUrl, '?&');
$sep        = str_contains($baseUrl, '?') ? '&' : '?';

$pageUrl = static fn ($page): string => $baseUrl . $sep . 'page=' . (int) $page;

$hasPrev  = $pager->hasPreviousPage();
$hasNext  = $pager->hasNextPage();
?>
<nav class="service-pager" aria-label="페이지 이동">
    <ul class="sp-list">

        <!-- 이전 < -->
        <li class="sp-item">
        <?php if ($hasPrev): ?>
            <a href="<?= $pageUrl($pager->getPreviousPageNumber()) ?>" class="sp-btn" aria-label="이전">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </a>
        <?php else: ?>
            <span class="sp-btn sp-disabled" aria-disabled="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </span>
        <?php endif ?>
        </li>

        <!-- 페이지 번호 -->
        <?php foreach ($links as $link): ?>
        <li class="sp-item">
            <?php if ($link['active']): ?>
                <span class="sp-btn sp-current" aria-current="page"><?= $link['title'] ?></span>
            <?php else: ?>
                <a href="<?= $link['uri'] ?>" class="sp-btn"><?= $link['title'] ?></a>
            <?php endif ?>
        </li>
        <?php endforeach ?>

        <!-- 다음 > -->
        <li class="sp-item">
        <?php if ($hasNext): ?>
            <a href="<?= $pageUrl($pager->getNextPageNumber()) ?>" class="sp-btn" aria-label="다음">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>
        <?php else: ?>
            <span class="sp-btn sp-disabled" aria-disabled="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </span>
        <?php endif ?>
        </li>

    </ul>
</nav>
