<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">배너 관리</h1>
            <p class="bo-page-desc">메인 페이지 상단 슬라이더에 노출되는 배너를 관리합니다.</p>
        </div>
        <a href="/backoffice/banners/register" class="bo-btn bo-btn-primary">+ 배너 등록</a>
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
    <!-- 필터 바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/banners" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 배너 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:55px;text-align:center">IDX</th>
                    <th style="width:130px;text-align:center">이미지</th>
                    <th style="text-align:center">제목 / 부제목</th>
                    <th style="width:200px;text-align:center">이미지 설명</th>
                    <th style="width:160px;text-align:center">링크 URL</th>
                    <th style="width:60px;text-align:center">순서</th>
                    <th style="width:80px;text-align:center">상태</th>
                    <th style="width:110px;text-align:center">등록일</th>
                    <th style="width:150px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="9" class="bo-table-empty">등록된 배너가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr>
                    <!-- IDX -->
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 이미지 미리보기 -->
                    <td class="text-center" style="padding:8px;">
                        <?php if ($row['image_url']): ?>
                            <img src="<?= esc($row['image_url']) ?>"
                                 alt="<?= esc($row['alt_text'] ?? '') ?>"
                                 style="width:110px;height:62px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;display:block;margin:0 auto;">
                        <?php else: ?>
                            <div style="width:110px;height:62px;background:#f1f5f9;border-radius:6px;border:1px solid #e5e7eb;
                                        display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                <span style="font-size:20px;opacity:.35;">🖼️</span>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- 제목 / 부제목 -->
                    <td>
                        <?php if ($row['title']): ?>
                            <div style="font-size:13px;font-weight:600;color:<?= $row['state'] ? '#111' : '#9ca3af' ?>;
                                        overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:260px;">
                                <?= esc($row['title']) ?>
                            </div>
                        <?php else: ?>
                            <span style="font-size:12px;color:#d1d5db;">제목 없음</span>
                        <?php endif; ?>
                        <?php if ($row['subtitle']): ?>
                            <div style="font-size:12px;color:#9ca3af;margin-top:2px;
                                        overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:260px;">
                                <?= esc($row['subtitle']) ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- 이미지 설명 (alt_text) -->
                    <td style="width:200px;">
                        <div style="font-size:12px;color:#6b7280;overflow:hidden;display:-webkit-box;
                                    -webkit-line-clamp:2;-webkit-box-orient:vertical;word-break:break-all;line-height:1.5;">
                            <?= esc($row['alt_text'] ?: '-') ?>
                        </div>
                    </td>

                    <!-- 링크 URL -->
                    <td style="width:160px;">
                        <?php if ($row['link_url']): ?>
                            <a href="<?= esc($row['link_url']) ?>" target="_blank"
                               style="font-size:12px;color:#3b82f6;overflow:hidden;display:block;
                                      white-space:nowrap;text-overflow:ellipsis;max-width:150px;"
                               title="<?= esc($row['link_url']) ?>">
                                <?= esc($row['link_url']) ?>
                            </a>
                        <?php else: ?>
                            <span style="font-size:12px;color:#d1d5db;">링크 없음</span>
                        <?php endif; ?>
                    </td>

                    <!-- 노출 순서 -->
                    <td class="text-center text-muted"><?= $row['sort_order'] ?></td>

                    <!-- 상태 배지 -->
                    <td class="text-center">
                        <span class="bo-badge <?= $row['state'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $row['state'] ? '활성' : '비활성' ?>
                        </span>
                    </td>

                    <!-- 등록일 -->
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>

                    <!-- 관리 버튼 -->
                    <td class="text-center">
                        <div class="bo-action-btns" style="flex-wrap:nowrap;justify-content:center;">
                            <a href="/backoffice/banners/<?= $row['idx'] ?>/edit"
                               class="bo-btn-action edit">수정</a>
                            <form method="post" action="/backoffice/banners/<?= $row['idx'] ?>/state"
                                  style="display:inline">
                                <?= csrf_field() ?>
                                <button type="submit"
                                        class="bo-btn-action <?= $row['state'] ? 'deactivate' : 'activate' ?>">
                                    <?= $row['state'] ? '비활성' : '활성화' ?>
                                </button>
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
