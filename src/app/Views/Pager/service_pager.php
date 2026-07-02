<?php
/**
 * 서비스(프론트) 페이지 전용 페이지네이션 템플릿
 * bo_pager.php 와 동일한 URL 구성 방식 사용 (기존 필터 파라미터 유지)
 */
$pager->setSurroundCount(2);
$links = $pager->links();

// 현재 URL에서 page 파라미터만 제거하고 나머지 쿼리(q, district, category 등) 유지
$currentUrl = current_url();
$baseUrl    = preg_replace('/([?&])page=\d+/', '', $currentUrl);
$baseUrl    = rtrim($baseUrl, '?&');
$sep        = str_contains($baseUrl, '?') ? '&' : '?';

$pageUrl = static fn ($page): string => $baseUrl . $sep . 'page=' . (int) $page;

$hasPrev  = $pager->hasPreviousPage();
$hasNext  = $pager->hasNextPage();
$lastPage = $pager->getLastPageNumber();
?>
<nav class="service-pager" aria-label="페이지 이동">

    <!-- 처음 / 이전 -->
    <?php if ($hasPrev): ?>
        <a href="<?= $pageUrl(1) ?>"                                  class="sp-btn" title="처음">«</a>
        <a href="<?= $pageUrl($pager->getPreviousPageNumber()) ?>"     class="sp-btn" title="이전">‹</a>
    <?php else: ?>
        <span class="sp-btn sp-disabled">«</span>
        <span class="sp-btn sp-disabled">‹</span>
    <?php endif ?>

    <!-- 페이지 번호 -->
    <?php foreach ($links as $link): ?>
        <?php if ($link['active']): ?>
            <span class="sp-btn sp-current"><?= $link['title'] ?></span>
        <?php else: ?>
            <a href="<?= $link['uri'] ?>" class="sp-btn"><?= $link['title'] ?></a>
        <?php endif ?>
    <?php endforeach ?>

    <!-- 다음 / 마지막 -->
    <?php if ($hasNext): ?>
        <a href="<?= $pageUrl($pager->getNextPageNumber()) ?>"         class="sp-btn" title="다음">›</a>
        <a href="<?= $pageUrl($lastPage) ?>"                           class="sp-btn" title="마지막">»</a>
    <?php else: ?>
        <span class="sp-btn sp-disabled">›</span>
        <span class="sp-btn sp-disabled">»</span>
    <?php endif ?>

</nav>
