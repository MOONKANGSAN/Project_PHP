<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">사이트 이벤트 관리</h1>
            <p class="bo-page-desc">부산온나 자체 이벤트를 등록·관리합니다.</p>
        </div>
        <a href="/backoffice/site-events/register" class="bo-btn bo-btn-primary">+ 이벤트 등록</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- 필터 -->
<form method="get" action="/backoffice/site-events" class="bo-filter-bar">
    <input type="text" name="q" class="bo-form-input" placeholder="이벤트명 검색"
           value="<?= esc($q) ?>" style="width:220px;">

    <select name="state" class="bo-form-select" style="width:110px;">
        <option value="">전체 상태</option>
        <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
        <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
    </select>

    <select name="type" class="bo-form-select" style="width:130px;">
        <option value="">전체 유형</option>
        <?php foreach (\App\Models\SiteEventModel::TYPES as $num => $label): ?>
        <option value="<?= $num ?>" <?= $type == $num ? 'selected' : '' ?>>
            <?= \App\Models\SiteEventModel::TYPE_EMOJI[$num] ?> <?= esc($label) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="bo-btn bo-btn-secondary">검색</button>
    <?php if ($q || $state !== '' || $type !== ''): ?>
    <a href="/backoffice/site-events" class="bo-btn bo-btn-ghost">초기화</a>
    <?php endif; ?>
</form>

<!-- 목록 테이블 -->
<div class="bo-table-wrap">
    <table class="bo-table">
        <thead>
            <tr>
                <th style="width:60px;">번호</th>
                <th style="width:80px;">상태</th>
                <th style="width:90px;">유형</th>
                <th>이벤트명</th>
                <th style="width:90px;">연결링크</th>
                <th style="width:180px;">이벤트 기간</th>
                <th style="width:120px;">등록일</th>
                <th style="width:140px;">관리</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="8" class="bo-table-empty">등록된 이벤트가 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($items as $ev): ?>
            <?php
            $typeNum   = (int)($ev['event_type'] ?? 4);
            $typeLabel = \App\Models\SiteEventModel::TYPES[$typeNum]  ?? '기타';
            $typeEmoji = \App\Models\SiteEventModel::TYPE_EMOJI[$typeNum] ?? '🎉';
            $typeColor = \App\Models\SiteEventModel::TYPE_COLOR[$typeNum] ?? '#fdcb6e';

            $today = date('Y-m-d');
            $evStatus = '';
            if (!empty($ev['start_date']) && !empty($ev['end_date'])) {
                if ($today < $ev['start_date'])      $evStatus = '예정';
                elseif ($today > $ev['end_date'])    $evStatus = '종료';
                else                                 $evStatus = '진행중';
            }
            ?>
            <tr>
                <td class="bo-table-center"><?= (int)$ev['idx'] ?></td>
                <td class="bo-table-center">
                    <?php if ((int)$ev['state'] === 1): ?>
                        <span class="bo-badge bo-badge-success">활성</span>
                    <?php else: ?>
                        <span class="bo-badge bo-badge-muted">비활성</span>
                    <?php endif; ?>
                </td>
                <td class="bo-table-center">
                    <span style="background:<?= $typeColor ?>22; color:<?= $typeColor ?>; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; white-space:nowrap;">
                        <?= $typeEmoji ?> <?= esc($typeLabel) ?>
                    </span>
                </td>
                <td>
                    <a href="/events/<?= (int)$ev['idx'] ?>" target="_blank"
                       style="font-weight:600; color:#1d4ed8;">
                        <?= esc($ev['title']) ?>
                    </a>
                    <?php if (!empty($ev['sub_title'])): ?>
                    <p style="font-size:12px; color:#9ca3af; margin:2px 0 0;"><?= esc($ev['sub_title']) ?></p>
                    <?php endif; ?>
                    <?php if ($evStatus): ?>
                    <span style="font-size:11px; padding:1px 7px; border-radius:10px; margin-left:4px;
                        <?= $evStatus === '진행중' ? 'background:#d1fae5;color:#065f46;' : ($evStatus === '예정' ? 'background:#dbeafe;color:#1e40af;' : 'background:#f3f4f6;color:#6b7280;') ?>">
                        <?= $evStatus ?>
                    </span>
                    <?php endif; ?>
                </td>
                <td class="bo-table-center">
                    <?php if ((int)$ev['use_view_file']): ?>
                        <span class="bo-badge bo-badge-info" title="<?= esc($ev['view_file']) ?>">
                            🔗 <?= esc($ev['view_file']) ?>
                        </span>
                    <?php else: ?>
                        <span class="bo-badge bo-badge-muted">기본뷰</span>
                    <?php endif; ?>
                </td>
                <td class="bo-table-center" style="font-size:13px; color:#555;">
                    <?php if (!empty($ev['start_date'])): ?>
                        <?= esc($ev['start_date']) ?><br>~ <?= esc($ev['end_date'] ?? '') ?>
                    <?php else: ?>
                        <span style="color:#ccc;">—</span>
                    <?php endif; ?>
                </td>
                <td class="bo-table-center" style="font-size:13px; color:#9ca3af;">
                    <?= esc(substr($ev['reg_date'] ?? '', 0, 10)) ?>
                </td>
                <td class="bo-table-center">
                    <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                        <a href="/backoffice/site-events/<?= (int)$ev['idx'] ?>/edit"
                           class="bo-btn bo-btn-sm bo-btn-secondary">수정</a>

                        <!-- 활성/비활성 토글 -->
                        <form method="post"
                              action="/backoffice/site-events/<?= (int)$ev['idx'] ?>/state"
                              style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="bo-btn bo-btn-sm <?= (int)$ev['state'] ? 'bo-btn-ghost' : 'bo-btn-success' ?>">
                                <?= (int)$ev['state'] ? '비활성화' : '활성화' ?>
                            </button>
                        </form>

                        <!-- 삭제 -->
                        <form method="post"
                              action="/backoffice/site-events/<?= (int)$ev['idx'] ?>/delete"
                              style="display:inline;"
                              onsubmit="return confirm('이벤트를 삭제하시겠습니까?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="bo-btn bo-btn-sm bo-btn-danger">삭제</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
<div class="pager-wrap" style="margin-top:24px;">
    <?= $pager->links('default', 'bo_pager') ?>
</div>
<?php endif; ?>

<?= view('backoffice/partials/footer') ?>
