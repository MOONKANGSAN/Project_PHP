<?php
/**
 * 백오피스 전용 페이지네이션 템플릿
 *
 * 실제 사용 가능한 PagerRenderer 메서드만 사용:
 *   hasPreviousPage() / hasNextPage()
 *   getPreviousPageNumber() / getNextPageNumber() / getLastPageNumber()
 *   links() → ['uri', 'title', 'active']
 */
$pager->setSurroundCount(2);
$links = $pager->links();

// page 파라미터를 제거한 기본 URL (검색어·필터 등 기존 쿼리 유지)
$currentUrl = current_url();
$baseUrl    = preg_replace('/([?&])page=\d+/', '', $currentUrl);
$baseUrl    = rtrim($baseUrl, '?&');
$sep        = str_contains($baseUrl, '?') ? '&' : '?';

// 페이지 번호 → URL (string·int 모두 수용)
$pageUrl = static fn ($page): string => $baseUrl . $sep . 'page=' . (int) $page;

$hasPrev = $pager->hasPreviousPage();
$hasNext = $pager->hasNextPage();
?>
<nav class="bo-pager-nav" aria-label="페이지 이동">

    <!-- 처음 / 이전 -->
    <?php if ($hasPrev): ?>
        <a href="<?= $pageUrl(1) ?>"                                          class="bo-pager-btn" title="처음">«</a>
        <a href="<?= $pageUrl($pager->getPreviousPageNumber()) ?>"            class="bo-pager-btn" title="이전">‹</a>
    <?php else: ?>
        <span class="bo-pager-btn bo-pager-disabled">«</span>
        <span class="bo-pager-btn bo-pager-disabled">‹</span>
    <?php endif ?>

    <!-- 페이지 번호 목록 -->
    <?php foreach ($links as $link): ?>
        <?php if ($link['active']): ?>
            <span class="bo-pager-btn bo-pager-current"><?= $link['title'] ?></span>
        <?php else: ?>
            <a href="<?= $link['uri'] ?>" class="bo-pager-btn"><?= $link['title'] ?></a>
        <?php endif ?>
    <?php endforeach ?>

    <!-- 다음 / 마지막 -->
    <?php if ($hasNext): ?>
        <a href="<?= $pageUrl($pager->getNextPageNumber()) ?>"                class="bo-pager-btn" title="다음">›</a>
        <a href="<?= $pageUrl($pager->getLastPageNumber()) ?>"                class="bo-pager-btn" title="마지막">»</a>
    <?php else: ?>
        <span class="bo-pager-btn bo-pager-disabled">›</span>
        <span class="bo-pager-btn bo-pager-disabled">»</span>
    <?php endif ?>

</nav>
