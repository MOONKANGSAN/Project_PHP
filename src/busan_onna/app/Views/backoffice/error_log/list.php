<?= view('backoffice/partials/header', $this->data) ?>

<!-- CSRF 메타 (AJAX 상태 토글용) -->
<meta name="csrf-name" content="<?= csrf_token() ?>">
<meta name="csrf-hash" content="<?= csrf_hash() ?>">

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">에러 로그</h1>
            <p class="bo-page-desc">사이트 운영 중 발생한 에러를 조회하고 처리 상태를 관리합니다.</p>
        </div>
        <?php if ($unresolvedCount > 0): ?>
        <div style="display:flex;align-items:center;gap:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 16px;">
            <span style="font-size:22px;font-weight:800;color:#dc2626;"><?= $unresolvedCount ?></span>
            <span style="font-size:13px;color:#dc2626;font-weight:500;">건 미해결</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<div id="ajaxMsg" style="display:none"></div>

<div class="bo-card">
    <!-- 검색/필터 바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="메시지 / URL / IP 검색...">

            <select name="type" class="bo-form-select bo-filter-select">
                <option value="">전체 유형</option>
                <?php foreach ($types as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $type === (string) $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>미해결</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>해결됨</option>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/error-logs" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 에러 로그 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:55px;text-align:center">IDX</th>
                    <th style="width:110px;text-align:center">유형</th>
                    <th style="width:70px;text-align:center">코드</th>
                    <th style="text-align:center">에러 내용</th>
                    <th style="width:100px;text-align:center">IP</th>
                    <th style="width:130px;text-align:center">발생일시</th>
                    <th style="width:80px;text-align:center">상태</th>
                    <th style="width:220px;text-align:center">해결내용 (피드백)</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="8" class="bo-table-empty">기록된 에러 로그가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <?php
                    $typeColor = $typeColors[$row['error_type']] ?? $typeColors[5];
                    $isResolved = (int)$row['state'] === 1;
                ?>
                <tr id="elog-row-<?= $row['idx'] ?>" style="<?= $isResolved ? 'opacity:.65;' : '' ?>">

                    <!-- IDX -->
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 에러 유형 배지 -->
                    <td class="text-center">
                        <span style="font-size:11px;padding:3px 7px;border-radius:4px;font-weight:600;white-space:nowrap;
                                     background:<?= $typeColor['bg'] ?>;color:<?= $typeColor['color'] ?>;">
                            <?= esc($types[$row['error_type']] ?? '기타') ?>
                        </span>
                    </td>

                    <!-- 에러 코드 -->
                    <td class="text-center">
                        <span style="font-size:12px;font-weight:700;color:#374151;">
                            <?= esc($row['error_code'] ?: '-') ?>
                        </span>
                    </td>

                    <!-- 에러 내용 + 발생 위치 -->
                    <td>
                        <div style="font-size:13px;color:<?= $isResolved ? '#9ca3af' : '#111' ?>;
                                    overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;
                                    word-break:break-all;line-height:1.5;">
                            <?= esc($row['message']) ?>
                        </div>
                        <?php if ($row['url'] || $row['file']): ?>
                        <div style="margin-top:3px;font-size:11px;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?php if ($row['url']): ?>
                                <span title="<?= esc($row['url']) ?>">
                                    [<?= esc($row['method'] ?: 'GET') ?>] <?= esc(mb_strimwidth($row['url'], 0, 60, '...')) ?>
                                </span>
                            <?php elseif ($row['file']): ?>
                                <?= esc(basename($row['file'])) ?>:<?= (int)$row['line'] ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <!-- IP -->
                    <td class="text-center text-muted" style="font-size:12px;"><?= esc($row['ip'] ?: '-') ?></td>

                    <!-- 발생일시 -->
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 16) ?></td>

                    <!-- 상태 토글 버튼 (AJAX) -->
                    <td class="text-center">
                        <button type="button"
                                id="elog-state-btn-<?= $row['idx'] ?>"
                                onclick="toggleElogState(<?= $row['idx'] ?>, this)"
                                style="padding:4px 10px;border-radius:5px;border:none;cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap;transition:all .15s;
                                       <?= $isResolved
                                            ? 'background:#dcfce7;color:#16a34a;'
                                            : 'background:#fef2f2;color:#dc2626;' ?>">
                            <?= $isResolved ? '✓ 해결됨' : '● 미해결' ?>
                        </button>
                        <?php if ($isResolved && $row['resolved_at']): ?>
                        <div style="font-size:10px;color:#9ca3af;margin-top:2px;"><?= substr($row['resolved_at'], 0, 10) ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- 피드백 입력 폼 -->
                    <td>
                        <form method="post" action="/backoffice/error-logs/<?= $row['idx'] ?>/feedback"
                              style="display:flex;gap:5px;align-items:center;">
                            <?= csrf_field() ?>
                            <input type="text"
                                   name="feedback"
                                   value="<?= esc($row['feedback'] ?? '') ?>"
                                   maxlength="50"
                                   placeholder="해결 내용 입력 (50자)"
                                   style="flex:1;min-width:0;padding:4px 8px;border:1px solid #d1d5db;border-radius:5px;
                                          font-size:12px;color:#374151;outline:none;
                                          <?= $isResolved ? 'background:#f9fafb;' : '' ?>"
                                   onfocus="this.style.borderColor='#3b82f6'"
                                   onblur="this.style.borderColor='#d1d5db'">
                            <button type="submit"
                                    style="padding:4px 10px;border:none;border-radius:5px;background:#3b82f6;
                                           color:#fff;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;flex-shrink:0;">
                                저장
                            </button>
                        </form>
                        <?php if ($row['feedback']): ?>
                        <div style="margin-top:3px;font-size:11px;color:#6b7280;">
                            현재: <?= esc($row['feedback']) ?>
                        </div>
                        <?php endif; ?>
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

<script>
(function () {
    function getCsrf() {
        return {
            name: document.querySelector('meta[name="csrf-name"]').getAttribute('content'),
            hash: document.querySelector('meta[name="csrf-hash"]').getAttribute('content'),
        };
    }
    function updateCsrf(name, hash) {
        document.querySelector('meta[name="csrf-name"]').setAttribute('content', name);
        document.querySelector('meta[name="csrf-hash"]').setAttribute('content', hash);
    }
    function showMsg(msg, type) {
        var el = document.getElementById('ajaxMsg');
        el.className = 'bo-alert bo-alert-' + (type === 'error' ? 'error' : 'success');
        el.textContent = msg;
        el.style.display = 'block';
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.style.display = 'none'; }, 3000);
    }

    window.toggleElogState = function (idx, btn) {
        btn.disabled = true;

        var csrf = getCsrf();
        var body = new FormData();
        body.append(csrf.name, csrf.hash);

        fetch('/backoffice/error-logs/' + idx + '/state', {
            method : 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body   : body,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                showMsg(data.message || '오류가 발생했습니다.', 'error');
                btn.disabled = false;
                return;
            }

            updateCsrf(data.csrf_name, data.csrf_hash);

            var resolved = data.state === 1;
            var row = document.getElementById('elog-row-' + idx);

            // 버튼 상태 업데이트
            btn.style.background = resolved ? '#dcfce7' : '#fef2f2';
            btn.style.color      = resolved ? '#16a34a' : '#dc2626';
            btn.textContent      = resolved ? '✓ 해결됨' : '● 미해결';
            btn.disabled         = false;

            // 행 투명도 조정
            row.style.opacity = resolved ? '0.65' : '1';

            // 피드백 input 배경 조정
            var input = row.querySelector('input[name="feedback"]');
            if (input) input.style.background = resolved ? '#f9fafb' : '';

            showMsg(resolved ? '해결됨으로 변경되었습니다.' : '미해결로 변경되었습니다.', 'success');
        })
        .catch(function () {
            showMsg('서버 연결에 실패했습니다.', 'error');
            btn.disabled = false;
        });
    };
}());
</script>

<?= view('backoffice/partials/footer') ?>
