<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">휴지통</h1>
            <p class="bo-page-desc">삭제된 고객문의와 FAQ를 조회하고 복원합니다.</p>
        </div>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="bo-card">

    <!-- ===== 탭 헤더 ===== -->
    <div style="display:flex;border-bottom:2px solid #e5e7eb;margin-bottom:20px;gap:0;">
        <a href="/backoffice/trash?tab=inquiry"
           style="display:flex;align-items:center;gap:8px;padding:12px 24px;font-size:14px;font-weight:600;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;
                  <?= $tab === 'inquiry' ? 'color:#3b82f6;border-bottom-color:#3b82f6;' : 'color:#6b7280;' ?>">
            💬 고객문의
            <?php if ($inquiryCount > 0): ?>
                <span style="background:<?= $tab === 'inquiry' ? '#3b82f6' : '#9ca3af' ?>;color:#fff;font-size:11px;font-weight:700;padding:1px 7px;border-radius:999px;min-width:20px;text-align:center;">
                    <?= $inquiryCount ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="/backoffice/trash?tab=faq"
           style="display:flex;align-items:center;gap:8px;padding:12px 24px;font-size:14px;font-weight:600;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;
                  <?= $tab === 'faq' ? 'color:#3b82f6;border-bottom-color:#3b82f6;' : 'color:#6b7280;' ?>">
            ❓ FAQs
            <?php if ($faqCount > 0): ?>
                <span style="background:<?= $tab === 'faq' ? '#3b82f6' : '#9ca3af' ?>;color:#fff;font-size:11px;font-weight:700;padding:1px 7px;border-radius:999px;min-width:20px;text-align:center;">
                    <?= $faqCount ?>
                </span>
            <?php endif; ?>
        </a>
    </div>

    <!-- ===== 검색 바 ===== -->
    <form method="get" class="bo-list-toolbar" style="margin-bottom:16px;">
        <input type="hidden" name="tab" value="<?= esc($tab) ?>">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input"
                   placeholder="<?= $tab === 'inquiry' ? '제목 / 아이디 검색...' : '제목 검색...' ?>">
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/trash?tab=<?= esc($tab) ?>" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- ===== 고객문의 탭 ===== -->
    <?php if ($tab === 'inquiry'): ?>
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="width:110px;text-align:center">유형</th>
                    <th style="text-align:center">제목</th>
                    <th style="width:110px;text-align:center">작성자</th>
                    <th style="width:130px;text-align:center">등록일</th>
                    <th style="width:80px;text-align:center">복원</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6" class="bo-table-empty">휴지통에 고객문의가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>
                    <td class="text-center">
                        <span style="font-size:12px;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#475569;">
                            <?= esc($inquiryTypes[$row['inquiry_type']] ?? '기타') ?>
                        </span>
                    </td>
                    <td style="color:#9ca3af;"><?= esc($row['title']) ?></td>
                    <td class="text-center text-muted"><?= esc($row['id']) ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 16) ?></td>
                    <td class="text-center">
                        <form method="post" action="/backoffice/trash/inquiry/<?= $row['idx'] ?>/restore"
                              onsubmit="return confirm('이 문의를 복원하시겠습니까?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="bo-btn-action activate" style="min-width:56px">복원</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== FAQs 탭 ===== -->
    <?php else: ?>
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="width:110px;text-align:center">카테고리</th>
                    <th style="text-align:center">제목</th>
                    <th style="width:70px;text-align:center">조회수</th>
                    <th style="width:130px;text-align:center">등록일</th>
                    <th style="width:80px;text-align:center">복원</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6" class="bo-table-empty">휴지통에 FAQ가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>
                    <td class="text-center">
                        <span style="font-size:12px;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#475569;">
                            <?= esc($faqTypes[$row['faq_type']] ?? '기타') ?>
                        </span>
                    </td>
                    <td style="color:#9ca3af;"><?= esc($row['title']) ?></td>
                    <td class="text-center text-muted"><?= number_format($row['view_cnt']) ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>
                    <td class="text-center">
                        <form method="post" action="/backoffice/trash/faq/<?= $row['idx'] ?>/restore"
                              onsubmit="return confirm('이 FAQ를 복원하시겠습니까?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="bo-btn-action activate" style="min-width:56px">복원</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- 페이지네이션 -->
    <?php if ($pager): ?>
    <div class="bo-pagination">
        <?= $pager->links('default', 'bo_pager') ?>
    </div>
    <?php endif; ?>
</div>

<?= view('backoffice/partials/footer') ?>
