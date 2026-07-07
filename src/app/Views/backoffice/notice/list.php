<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">공지사항 관리</h1>
            <p class="bo-page-desc">공지사항을 등록하고 관리합니다.</p>
        </div>
        <a href="/backoffice/notices/register" class="bo-btn bo-btn-primary">+ 공지사항 등록</a>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- AJAX 결과 메시지 -->
<div id="ajaxMsg" style="display:none"></div>

<!-- CSRF 메타 -->
<meta name="csrf-name" content="<?= csrf_token() ?>">
<meta name="csrf-hash" content="<?= csrf_hash() ?>">

<div class="bo-card">
    <!-- 검색·필터 툴바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="제목 검색...">

            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>

            <select name="pinned" class="bo-form-select bo-filter-select">
                <option value="">전체</option>
                <option value="1" <?= $pinned === '1' ? 'selected' : '' ?>>고정 공지만</option>
                <option value="0" <?= $pinned === '0' ? 'selected' : '' ?>>일반 공지만</option>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/notices" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="width:70px;text-align:center">고정</th>
                    <th style="text-align:center">제목</th>
                    <th style="width:70px;text-align:center">조회수</th>
                    <th style="width:80px;text-align:center">상태</th>
                    <th style="width:130px;text-align:center">등록일</th>
                    <th style="width:180px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="bo-table-empty">등록된 공지사항이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr id="notice-row-<?= $row['idx'] ?>">
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 상단 고정 배지 -->
                    <td class="text-center">
                        <span class="notice-pin-badge" id="notice-pin-badge-<?= $row['idx'] ?>"
                              style="font-size:18px;cursor:default;"
                              title="<?= $row['is_pinned'] ? '고정 공지' : '일반 공지' ?>">
                            <?= $row['is_pinned'] ? '📌' : '—' ?>
                        </span>
                    </td>

                    <td>
                        <a href="/backoffice/notices/<?= $row['idx'] ?>/edit" class="bo-table-link"
                           style="<?= !(int)$row['state'] ? 'color:#9ca3af;' : '' ?>">
                            <?= esc($row['title']) ?>
                        </a>
                        <?php if ($row['edit_date']): ?>
                            <span style="font-size:11px;color:#9ca3af;margin-left:6px">수정됨</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center text-muted"><?= number_format($row['view_cnt']) ?></td>

                    <!-- 상태 배지 -->
                    <td class="text-center">
                        <span class="bo-badge <?= $row['state'] ? 'badge-active' : 'badge-inactive' ?>"
                              id="notice-badge-<?= $row['idx'] ?>">
                            <?= $row['state'] ? '활성' : '비활성' ?>
                        </span>
                    </td>

                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>

                    <!-- 관리 버튼 -->
                    <td class="text-center">
                        <div class="bo-action-btns" style="flex-wrap:nowrap;justify-content:center;">
                            <!-- 고정 토글 -->
                            <button type="button"
                                    class="bo-btn-action <?= $row['is_pinned'] ? 'deactivate' : 'activate' ?>"
                                    id="notice-pin-btn-<?= $row['idx'] ?>"
                                    onclick="toggleNoticePin(<?= $row['idx'] ?>, this)">
                                <?= $row['is_pinned'] ? '고정해제' : '고정' ?>
                            </button>

                            <!-- 상태 토글 -->
                            <button type="button"
                                    class="bo-btn-action <?= $row['state'] ? 'deactivate' : 'activate' ?>"
                                    id="notice-toggle-<?= $row['idx'] ?>"
                                    onclick="toggleNoticeState(<?= $row['idx'] ?>, this)">
                                <?= $row['state'] ? '비활성' : '활성화' ?>
                            </button>

                            <!-- 삭제 -->
                            <form method="post" action="/backoffice/notices/<?= $row['idx'] ?>/delete"
                                  style="display:inline"
                                  onsubmit="return confirm('이 공지사항을 삭제하시겠습니까?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action"
                                        style="background:#6b7280;color:#fff" title="삭제">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

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
        el.innerHTML = msg;
        el.style.display = 'block';
        clearTimeout(el._timer);
        el._timer = setTimeout(function () { el.style.display = 'none'; }, 3000);
    }

    // 공지사항 상태 토글
    window.toggleNoticeState = function (idx, btn) {
        btn.disabled = true;
        var csrf = getCsrf();
        var body = new FormData();
        body.append(csrf.name, csrf.hash);

        fetch('/backoffice/notices/' + idx + '/state', {
            method : 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body   : body,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) { showMsg(data.message || '오류 발생', 'error'); btn.disabled = false; return; }
            updateCsrf(data.csrf_name, data.csrf_hash);

            var isActive = data.state === 1;
            var badge = document.getElementById('notice-badge-' + idx);
            badge.className = 'bo-badge ' + (isActive ? 'badge-active' : 'badge-inactive');
            badge.textContent = isActive ? '활성' : '비활성';

            btn.className   = 'bo-btn-action ' + (isActive ? 'deactivate' : 'activate');
            btn.textContent = isActive ? '비활성' : '활성화';
            btn.disabled    = false;

            var row  = document.getElementById('notice-row-' + idx);
            var link = row ? row.querySelector('.bo-table-link') : null;
            if (link) link.style.color = isActive ? '' : '#9ca3af';

            showMsg((isActive ? '활성' : '비활성') + '으로 변경되었습니다.', 'success');
        })
        .catch(function () { showMsg('서버 연결 실패', 'error'); btn.disabled = false; });
    };

    // 공지사항 고정 토글
    window.toggleNoticePin = function (idx, btn) {
        btn.disabled = true;
        var csrf = getCsrf();
        var body = new FormData();
        body.append(csrf.name, csrf.hash);

        fetch('/backoffice/notices/' + idx + '/pin', {
            method : 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body   : body,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) { showMsg(data.message || '오류 발생', 'error'); btn.disabled = false; return; }
            updateCsrf(data.csrf_name, data.csrf_hash);

            var isPinned = data.is_pinned === 1;
            var icon = document.getElementById('notice-pin-badge-' + idx);
            if (icon) { icon.textContent = isPinned ? '📌' : '—'; icon.title = isPinned ? '고정 공지' : '일반 공지'; }

            btn.className   = 'bo-btn-action ' + (isPinned ? 'deactivate' : 'activate');
            btn.textContent = isPinned ? '고정해제' : '고정';
            btn.disabled    = false;

            showMsg(isPinned ? '상단 고정되었습니다.' : '고정이 해제되었습니다.', 'success');
        })
        .catch(function () { showMsg('서버 연결 실패', 'error'); btn.disabled = false; });
    };
}());
</script>

<?= view('backoffice/partials/footer') ?>
