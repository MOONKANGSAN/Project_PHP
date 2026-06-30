<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">고객문의</h1>
            <p class="bo-page-desc">회원이 등록한 문의를 조회하고 답변을 관리합니다.</p>
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
    <!-- 검색/필터 바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="제목 / 아이디 검색...">

            <select name="type" class="bo-form-select bo-filter-select">
                <option value="">전체 유형</option>
                <?php foreach ($types as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $type === (string) $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <?php foreach ($states as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $state === (string) $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/inquiries" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 문의 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="width:110px;text-align:center">유형</th>
                    <th style="text-align:center">제목</th>
                    <th style="width:110px;text-align:center">작성자</th>
                    <th style="width:80px;text-align:center">공개</th>
                    <th style="width:90px;text-align:center">상태</th>
                    <th style="width:130px;text-align:center">등록일</th>
                    <th style="width:160px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="8" class="bo-table-empty">등록된 문의가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr>
                    <!-- IDX -->
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 문의 유형 -->
                    <td class="text-center">
                        <span style="font-size:12px;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#475569;">
                            <?= esc($types[$row['inquiry_type']] ?? '기타') ?>
                        </span>
                    </td>

                    <!-- 제목 (상세 링크) -->
                    <td>
                        <a href="/backoffice/inquiries/<?= $row['idx'] ?>" class="bo-table-link"
                           style="<?= (int)$row['state'] === 0 ? 'color:#9ca3af;' : '' ?>">
                            <?= esc($row['title']) ?>
                            <?php if (!(int)$row['is_public']): ?>
                                <span style="font-size:11px;color:#9ca3af;margin-left:4px">[비공개]</span>
                            <?php endif; ?>
                        </a>
                    </td>

                    <!-- 작성자 -->
                    <td class="text-center text-muted"><?= esc($row['id']) ?></td>

                    <!-- 공개/비공개 -->
                    <td class="text-center">
                        <?php if ((int)$row['is_public']): ?>
                            <span style="font-size:12px;color:#16a34a;">공개</span>
                        <?php else: ?>
                            <span style="font-size:12px;color:#9ca3af;">비공개</span>
                        <?php endif; ?>
                    </td>

                    <!-- 처리 상태 -->
                    <td class="text-center">
                        <?php if ((int)$row['state'] === 2): ?>
                            <span class="bo-badge badge-active" style="font-size:11px">답변완료</span>
                        <?php elseif ((int)$row['state'] === 1): ?>
                            <span class="bo-badge badge-inactive" style="font-size:11px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa">접수</span>
                        <?php else: ?>
                            <span class="bo-badge" style="font-size:11px;background:#f1f5f9;color:#94a3b8">숨김</span>
                        <?php endif; ?>
                    </td>

                    <!-- 등록일 -->
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 16) ?></td>

                    <!-- 관리 버튼 -->
                    <td class="text-center">
                        <div class="bo-action-btns" style="flex-wrap:nowrap;">
                            <a href="/backoffice/inquiries/<?= $row['idx'] ?>" class="bo-btn-action edit">상세</a>
                            <?php if ((int)$row['state'] !== 2): ?>
                            <form method="post" action="/backoffice/inquiries/<?= $row['idx'] ?>/state" style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action <?= (int)$row['state'] === 0 ? 'activate' : 'deactivate' ?>">
                                    <?= (int)$row['state'] === 0 ? '접수' : '숨김' ?>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="/backoffice/inquiries/<?= $row['idx'] ?>/delete" style="display:inline"
                                  onsubmit="return confirm('이 문의를 휴지통으로 이동하시겠습니까?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action" style="background:#6b7280;color:#fff;white-space:nowrap" title="삭제">🗑</button>
                            </form>
                        </div>
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

<?= view('backoffice/partials/footer') ?>
