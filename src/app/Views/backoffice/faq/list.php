<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">FAQs 관리</h1>
            <p class="bo-page-desc">자주 묻는 질문을 등록하고 관리합니다.</p>
        </div>
        <a href="/backoffice/faqs/register" class="bo-btn bo-btn-primary">+ FAQ 등록</a>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- AJAX 결과 메시지 (상태 토글용) -->
<div id="ajaxMsg" style="display:none"></div>

<!-- CSRF 메타 (AJAX 요청 시 사용) -->
<meta name="csrf-name" content="<?= csrf_token() ?>">
<meta name="csrf-hash" content="<?= csrf_hash() ?>">

<div class="bo-card">
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="제목 검색...">

            <select name="type" class="bo-form-select bo-filter-select">
                <option value="">전체 카테고리</option>
                <?php foreach ($types as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $type === (string) $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="state" class="bo-form-select bo-filter-select">
                <option value="">전체 상태</option>
                <option value="1" <?= $state === '1' ? 'selected' : '' ?>>활성</option>
                <option value="0" <?= $state === '0' ? 'selected' : '' ?>>비활성</option>
            </select>

            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/faqs" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="width:110px;text-align:center">카테고리</th>
                    <th style="text-align:center">제목</th>
                    <th style="width:70px;text-align:center">조회수</th>
                    <th style="width:70px;text-align:center">순서</th>
                    <th style="width:80px;text-align:center">상태</th>
                    <th style="width:130px;text-align:center">등록일</th>
                    <th style="width:140px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="8" class="bo-table-empty">등록된 FAQ가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <tr id="faq-row-<?= $row['idx'] ?>">
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <td class="text-center">
                        <span style="font-size:12px;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#475569;">
                            <?= esc($types[$row['faq_type']] ?? '기타') ?>
                        </span>
                    </td>

                    <td>
                        <a href="/backoffice/faqs/<?= $row['idx'] ?>/edit" class="bo-table-link"
                           style="<?= !(int)$row['state'] ? 'color:#9ca3af;' : '' ?>">
                            <?= esc($row['title']) ?>
                        </a>
                        <?php if ($row['edit_date']): ?>
                            <span style="font-size:11px;color:#9ca3af;margin-left:6px">수정됨</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center text-muted"><?= number_format($row['view_cnt']) ?></td>
                    <td class="text-center text-muted"><?= $row['sort_order'] ?></td>

                    <!-- 상태 배지 (AJAX 토글 후 JS가 업데이트) -->
                    <td class="text-center">
                        <span class="faq-state-badge bo-badge <?= $row['state'] ? 'badge-active' : 'badge-inactive' ?>"
                              id="faq-badge-<?= $row['idx'] ?>">
                            <?= $row['state'] ? '활성' : '비활성' ?>
                        </span>
                    </td>

                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>

                    <!-- 관리 버튼 -->
                    <td class="text-center">
                        <div class="bo-action-btns">
                            <a href="/backoffice/faqs/<?= $row['idx'] ?>/edit" class="bo-btn-action edit">수정</a>

                            <!-- 상태 토글: AJAX -->
                            <button type="button"
                                    class="bo-btn-action <?= $row['state'] ? 'deactivate' : 'activate' ?>"
                                    id="faq-toggle-<?= $row['idx'] ?>"
                                    onclick="toggleFaqState(<?= $row['idx'] ?>, this)">
                                <?= $row['state'] ? '비활성' : '활성화' ?>
                            </button>

                            <!-- 삭제 (휴지통 이동) -->
                            <form method="post" action="/backoffice/faqs/<?= $row['idx'] ?>/delete" style="display:inline"
                                  onsubmit="return confirm('이 FAQ를 휴지통으로 이동하시겠습니까?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="bo-btn-action" style="background:#6b7280;color:#fff" title="삭제">🗑</button>
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
    // CSRF 값 관리 (AJAX 요청마다 갱신)
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

    // AJAX 결과 메시지 표시
    function showMsg(msg, type) {
        var el = document.getElementById('ajaxMsg');
        el.className = 'bo-alert bo-alert-' + (type === 'error' ? 'error' : 'success');
        el.innerHTML = msg;
        el.style.display = 'block';
        clearTimeout(el._timer);
        el._timer = setTimeout(function () { el.style.display = 'none'; }, 3000);
    }

    window.toggleFaqState = function (idx, btn) {
        btn.disabled = true;

        var csrf = getCsrf();
        var body = new FormData();
        body.append(csrf.name, csrf.hash);

        fetch('/backoffice/faqs/' + idx + '/state', {
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

            // CSRF 갱신
            updateCsrf(data.csrf_name, data.csrf_hash);

            var isActive = data.state === 1;

            // 배지 업데이트
            var badge = document.getElementById('faq-badge-' + idx);
            badge.className = 'faq-state-badge bo-badge ' + (isActive ? 'badge-active' : 'badge-inactive');
            badge.textContent = isActive ? '활성' : '비활성';

            // 토글 버튼 업데이트
            btn.className  = 'bo-btn-action ' + (isActive ? 'deactivate' : 'activate');
            btn.textContent = isActive ? '비활성' : '활성화';
            btn.disabled   = false;

            // 제목 링크 색상 업데이트
            var row = document.getElementById('faq-row-' + idx);
            var link = row ? row.querySelector('.bo-table-link') : null;
            if (link) link.style.color = isActive ? '' : '#9ca3af';

            showMsg((isActive ? '활성' : '비활성') + '으로 변경되었습니다.', 'success');
        })
        .catch(function () {
            showMsg('서버 연결에 실패했습니다.', 'error');
            btn.disabled = false;
        });
    };
}());
</script>

<?= view('backoffice/partials/footer') ?>
