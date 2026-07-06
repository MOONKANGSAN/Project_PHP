<?= view('backoffice/partials/header', $this->data) ?>

<style>
/* ===== 지역별 탐색 관리 레이아웃 ===== */
.re-wrap {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    min-height: 600px;
}

/* 좌측 지역 목록 패널 */
.re-region-panel {
    flex: 0 0 280px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}
.re-region-panel-header {
    padding: 14px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}
.re-region-list {
    list-style: none;
    margin: 0;
    padding: 8px 0;
}
.re-region-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 18px;
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all .15s;
    gap: 8px;
}
.re-region-item:hover {
    background: #f0f9ff;
    border-left-color: #93c5fd;
}
.re-region-item.active {
    background: #eff6ff;
    border-left-color: #3b82f6;
}
.re-region-name {
    font-size: 14px;
    font-weight: 500;
    color: #111827;
    flex: 1;
}
.re-region-item.inactive .re-region-name {
    color: #9ca3af;
    text-decoration: line-through;
}
.re-region-count {
    font-size: 11px;
    background: #e0e7ff;
    color: #4f46e5;
    border-radius: 20px;
    padding: 2px 8px;
    font-weight: 600;
}
.re-region-count.empty {
    background: #f3f4f6;
    color: #9ca3af;
}
.re-toggle-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 4px;
    font-size: 11px;
    border-radius: 4px;
    color: #9ca3af;
    transition: all .15s;
}
.re-toggle-btn:hover { background: #f3f4f6; color: #374151; }

/* 우측 편집 패널 */
.re-edit-panel {
    flex: 1;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    min-height: 500px;
}
.re-edit-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 500px;
    color: #9ca3af;
    gap: 12px;
}
.re-edit-placeholder .icon { font-size: 48px; opacity: .4; }
.re-edit-placeholder p { font-size: 14px; }

.re-edit-header {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.re-edit-title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}
.re-edit-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}
.re-edit-body {
    padding: 20px;
}

/* TOP5 항목 리스트 */
.re-top5-list {
    list-style: none;
    margin: 0 0 20px 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.re-top5-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 14px;
    cursor: grab;
}
.re-top5-item:active { cursor: grabbing; }
.re-top5-item.dragging { opacity: .5; border-style: dashed; }
.re-top5-item.drag-over { border-color: #3b82f6; background: #eff6ff; }
.re-top5-num {
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    background: #3b82f6;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.re-top5-info { flex: 1; min-width: 0; }
.re-top5-info-title {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.re-top5-info-url {
    font-size: 11px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.re-top5-remove {
    background: none;
    border: none;
    cursor: pointer;
    color: #ef4444;
    font-size: 16px;
    padding: 2px 4px;
    border-radius: 4px;
    flex-shrink: 0;
    line-height: 1;
    transition: background .15s;
}
.re-top5-remove:hover { background: #fee2e2; }

.re-top5-empty {
    text-align: center;
    padding: 20px;
    color: #9ca3af;
    font-size: 13px;
    border: 2px dashed #e5e7eb;
    border-radius: 8px;
    margin-bottom: 20px;
}
.re-top5-limit {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 12px;
    text-align: right;
}

/* 검색 영역 */
.re-search-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 16px;
    margin-top: 4px;
}
.re-search-section h4 {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 10px 0;
}
.re-search-bar {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}
.re-search-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    outline: none;
    transition: border-color .15s;
}
.re-search-input:focus { border-color: #3b82f6; }
.re-search-type {
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    background: #fff;
    outline: none;
}
.re-search-btn {
    padding: 8px 16px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}
.re-search-btn:hover { background: #2563eb; }

/* 검색 결과 */
.re-search-results {
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    display: none;
}
.re-search-results.visible { display: block; }
.re-search-result-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background .12s;
}
.re-search-result-item:last-child { border-bottom: none; }
.re-search-result-item:hover { background: #f0f9ff; }
.re-result-badge {
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}
.re-result-badge.restaurant { background: #fef3c7; color: #d97706; }
.re-result-badge.place      { background: #dcfce7; color: #16a34a; }
.re-result-badge.event      { background: #fce7f3; color: #db2777; }
.re-result-info { flex: 1; min-width: 0; }
.re-result-title {
    font-size: 13px;
    font-weight: 500;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.re-result-addr {
    font-size: 11px;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.re-result-add {
    font-size: 12px;
    color: #3b82f6;
    font-weight: 600;
    flex-shrink: 0;
}
.re-search-loading, .re-search-no-result {
    padding: 16px;
    text-align: center;
    font-size: 13px;
    color: #9ca3af;
}

/* 저장 버튼 */
.re-save-bar {
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
}
.re-save-msg {
    font-size: 13px;
    color: #16a34a;
    display: none;
}
.re-save-msg.visible { display: block; }
.re-save-msg.error { color: #dc2626; }
.re-btn-save {
    padding: 10px 28px;
    background: #111827;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.re-btn-save:hover { background: #374151; }
.re-btn-save:disabled { background: #9ca3af; cursor: not-allowed; }
</style>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">지역별 탐색 관리</h1>
            <p class="bo-page-desc">부산 구·군별 TOP5 추천 항목을 설정합니다. (지역 클릭 → 편집 패널 오픈)</p>
        </div>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="re-wrap">

    <!-- ===== 좌측: 지역 목록 ===== -->
    <div class="re-region-panel">
        <div class="re-region-panel-header">부산 구·군 목록</div>
        <ul class="re-region-list" id="regionList">
            <?php foreach ($regions as $region): ?>
            <li class="re-region-item <?= $region['state'] ? '' : 'inactive' ?>"
                data-idx="<?= $region['idx'] ?>"
                data-name="<?= esc($region['name']) ?>"
                data-state="<?= $region['state'] ?>"
                onclick="selectRegion(this)">
                <span class="re-region-name"><?= esc($region['name']) ?></span>
                <span class="re-region-count empty" id="cnt-<?= $region['idx'] ?>">0</span>
                <button class="re-toggle-btn"
                        title="활성/비활성 토글"
                        onclick="event.stopPropagation(); toggleRegionState(<?= $region['idx'] ?>, this)">
                    <?= $region['state'] ? '🟢' : '⚫' ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- ===== 우측: 편집 패널 ===== -->
    <div class="re-edit-panel" id="editPanel">
        <!-- 초기 안내 화면 -->
        <div class="re-edit-placeholder" id="editPlaceholder">
            <span class="icon">🗺️</span>
            <p>좌측에서 지역을 선택하면<br>TOP5를 편집할 수 있습니다.</p>
        </div>

        <!-- 편집 영역 (지역 선택 후 표시) -->
        <div id="editContent" style="display:none;">
            <div class="re-edit-header">
                <div>
                    <div class="re-edit-title" id="editTitle">지역명 TOP5</div>
                    <div class="re-edit-subtitle">최대 5개까지 설정 가능합니다. 순서를 드래그로 조정할 수 있습니다.</div>
                </div>
            </div>
            <div class="re-edit-body">
                <!-- TOP5 항목 목록 -->
                <div class="re-top5-limit">
                    <span id="top5CountText">0</span> / 5개
                </div>
                <ul class="re-top5-list" id="top5List">
                    <!-- JS로 동적 렌더링 -->
                </ul>
                <div class="re-top5-empty" id="top5Empty" style="display:none;">
                    아직 등록된 항목이 없습니다.<br>아래 검색으로 항목을 추가해주세요.
                </div>

                <!-- 검색 영역 -->
                <div class="re-search-section">
                    <h4>검색으로 항목 추가 <span id="searchRegionBadge" style="font-size:11px;font-weight:400;color:#6b7280;"></span></h4>
                    <div class="re-search-bar">
                        <select class="re-search-type" id="searchType">
                            <option value="">전체</option>
                            <option value="restaurant">맛집</option>
                            <option value="place">관광지</option>
                            <option value="event">행사/축제</option>
                        </select>
                        <input type="text" class="re-search-input" id="searchInput"
                               placeholder="검색어를 입력하세요"
                               onkeydown="if(event.key==='Enter') doSearch()">
                        <button class="re-search-btn" onclick="doSearch()">검색</button>
                    </div>
                    <div class="re-search-results" id="searchResults">
                        <!-- 검색 결과 JS로 렌더링 -->
                    </div>
                </div>

                <!-- 실행 쿼리 출력 패널 -->
                <div id="sqlDebugPanel" style="display:none;margin-top:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:12px;font-weight:600;color:#6b7280;">▶ 실행 쿼리</span>
                        <button onclick="document.getElementById('sqlDebugPanel').style.display='none'"
                                style="background:none;border:none;cursor:pointer;font-size:12px;color:#9ca3af;">닫기</button>
                    </div>
                    <div id="sqlDebugContent" style="font-size:11px;font-family:monospace;background:#1e293b;color:#7dd3fc;
                                                     padding:12px 14px;border-radius:8px;overflow-x:auto;white-space:pre-wrap;
                                                     line-height:1.7;max-height:260px;overflow-y:auto;">
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="re-save-bar">
                    <span class="re-save-msg" id="saveMsg"></span>
                    <button class="re-btn-save" id="saveBtn" onclick="saveTop5()">저장</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// ===== 상태 변수 =====
let currentRegionIdx  = null;   // 현재 선택된 지역 idx
let top5Items         = [];     // 현재 편집 중인 TOP5 배열
let dragSrcIndex      = null;   // 드래그 소스 인덱스

// ===== 지역 선택 =====
function selectRegion(el) {
    // 이전 active 제거
    document.querySelectorAll('.re-region-item').forEach(li => li.classList.remove('active'));
    el.classList.add('active');

    currentRegionIdx = parseInt(el.dataset.idx);
    const name = el.dataset.name;

    document.getElementById('editTitle').textContent = name + ' 지역별 탐색 TOP5';
    document.getElementById('editPlaceholder').style.display = 'none';
    document.getElementById('editContent').style.display     = 'block';

    // 지역 변경 시 검색 영역·쿼리 패널 초기화
    document.getElementById('searchInput').value        = '';
    document.getElementById('searchResults').className  = 're-search-results';
    document.getElementById('sqlDebugPanel').style.display = 'none';
    document.getElementById('searchRegionBadge').textContent = `— ${name} 지역 필터 적용`;

    loadTop5(currentRegionIdx);
}

// ===== TOP5 불러오기 (AJAX) =====
async function loadTop5(regionIdx) {
    try {
        const res  = await fetch(`/backoffice/region-explore/${regionIdx}/top5`);
        const data = await res.json();
        if (!data.success) return;

        top5Items = data.items.map(item => ({
            title:        item.title,
            link_url:     item.link_url     || '',
            description:  item.description  || '',
            thumb_url:    item.thumb_url    || '',
            content_type: item.content_type || '',
            content_idx:  item.content_idx  || null,
        }));
        renderTop5();
    } catch (e) {
        console.error('TOP5 불러오기 실패:', e);
    }
}

// ===== TOP5 목록 렌더링 =====
function renderTop5() {
    const list  = document.getElementById('top5List');
    const empty = document.getElementById('top5Empty');
    const countEl = document.getElementById('top5CountText');

    countEl.textContent = top5Items.length;
    list.innerHTML      = '';

    if (top5Items.length === 0) {
        empty.style.display = 'block';
        updateRegionCount(currentRegionIdx, 0);
        return;
    }
    empty.style.display = 'none';

    top5Items.forEach((item, i) => {
        const li = document.createElement('li');
        li.className     = 're-top5-item';
        li.draggable     = true;
        li.dataset.index = i;

        // 콘텐츠 타입별 배지 텍스트
        const typeLabelMap = { restaurant: '맛집', place: '관광지', event: '행사/축제' };
        const typeLabel    = item.content_type ? (typeLabelMap[item.content_type] || item.content_type) : '';

        li.innerHTML = `
            <span class="re-top5-num">${i + 1}</span>
            ${typeLabel ? `<span class="re-result-badge ${item.content_type}" style="font-size:10px;padding:2px 6px;">${typeLabel}</span>` : ''}
            <div class="re-top5-info">
                <div class="re-top5-info-title">${escHtml(item.title)}</div>
                <div class="re-top5-info-url">${item.link_url ? escHtml(item.link_url) : '링크 없음'}</div>
            </div>
            <button class="re-top5-remove" title="삭제" onclick="removeItem(${i})">✕</button>
        `;

        // 드래그 이벤트
        li.addEventListener('dragstart', onDragStart);
        li.addEventListener('dragover',  onDragOver);
        li.addEventListener('drop',      onDrop);
        li.addEventListener('dragend',   onDragEnd);

        list.appendChild(li);
    });

    updateRegionCount(currentRegionIdx, top5Items.length);
}

// ===== 항목 삭제 =====
function removeItem(index) {
    top5Items.splice(index, 1);
    renderTop5();
}

// ===== 지역 목록 카운트 배지 업데이트 =====
function updateRegionCount(regionIdx, count) {
    const el = document.getElementById(`cnt-${regionIdx}`);
    if (!el) return;
    el.textContent = count > 0 ? `TOP ${count}` : '0';
    el.className   = count > 0 ? 're-region-count' : 're-region-count empty';
}

// ===== 검색 =====
async function doSearch() {
    const q    = document.getElementById('searchInput').value.trim();
    const type = document.getElementById('searchType').value;
    const box  = document.getElementById('searchResults');

    if (!q) return;

    box.className = 're-search-results visible';
    box.innerHTML = '<div class="re-search-loading">검색 중…</div>';

    // 현재 선택된 지역구 idx를 함께 전송
    const regionParam = currentRegionIdx ? `&region_idx=${currentRegionIdx}` : '';
    const url = `/backoffice/region-explore/search?q=${encodeURIComponent(q)}&type=${type}${regionParam}`;

    try {
        const res  = await fetch(url);
        const data = await res.json();

        // ── 실행 쿼리 출력 ──
        renderSqlDebug(data.debug_sql, data.region_name);

        if (!data.success || data.results.length === 0) {
            const filterMsg = data.region_name
                ? ` (${data.region_name} 필터 적용됨)`
                : '';
            box.innerHTML = `<div class="re-search-no-result">검색 결과가 없습니다.${filterMsg}</div>`;
            return;
        }

        box.innerHTML = data.results.map(r => `
            <div class="re-search-result-item" onclick="addItem(${JSON.stringify(r).replace(/"/g,'&quot;')})">
                <span class="re-result-badge ${r.content_type}">${r.type_name}</span>
                <div class="re-result-info">
                    <div class="re-result-title">${escHtml(r.title)}</div>
                    <div class="re-result-addr">${escHtml(r.address || '주소 없음')}</div>
                </div>
                <span class="re-result-add">+ 추가</span>
            </div>
        `).join('');
    } catch (e) {
        box.innerHTML = '<div class="re-search-no-result">검색 중 오류가 발생했습니다.</div>';
    }
}

// ===== 실행 쿼리 패널 렌더링 =====
function renderSqlDebug(sqlMap, regionName) {
    const panel   = document.getElementById('sqlDebugPanel');
    const content = document.getElementById('sqlDebugContent');
    const badge   = document.getElementById('searchRegionBadge');

    // 지역 필터 뱃지 업데이트
    badge.textContent = regionName ? `— ${regionName} 지역 필터 적용` : '';

    if (!sqlMap || Object.keys(sqlMap).length === 0) {
        panel.style.display = 'none';
        return;
    }

    const typeLabel = { restaurant: '맛집', place: '관광지', event: '행사/축제' };
    let html = '';
    for (const [key, sql] of Object.entries(sqlMap)) {
        const label = typeLabel[key] || key;
        html += `<span style="color:#86efac;font-weight:bold;">-- [${escHtml(label)}]</span>\n`;
        html += highlightSql(sql) + '\n\n';
    }

    content.innerHTML = html.trim();
    panel.style.display = 'block';
}

// SQL 키워드를 색상 span으로 감싸 하이라이팅
function highlightSql(rawSql) {
    // 먼저 HTML 특수문자 이스케이프
    let safe = escHtml(rawSql);

    // SQL 키워드 강조 (대소문자 무시)
    const keywords = ['SELECT','FROM','WHERE','AND','OR','ORDER BY','GROUP BY',
                      'LIKE','LIMIT','JOIN','LEFT JOIN','INNER JOIN','ON','HAVING',
                      'INSERT INTO','UPDATE','SET','VALUES'];
    keywords.forEach(kw => {
        const re = new RegExp(`\\b(${kw})\\b`, 'gi');
        safe = safe.replace(re, '<span style="color:#fbbf24;font-weight:bold;">$1</span>');
    });

    // 문자열 리터럴 강조 (이스케이프된 따옴표 포함)
    safe = safe.replace(/'([^']*)'/g, '<span style="color:#f9a8d4;">\'$1\'</span>');

    // 백틱 컬럼·테이블명 강조
    safe = safe.replace(/`([^`]+)`/g, '<span style="color:#93c5fd;">`$1`</span>');

    return safe;
}

// ===== 항목 추가 =====
function addItem(resultObj) {
    if (top5Items.length >= 5) {
        showSaveMsg('TOP5는 최대 5개까지만 추가할 수 있습니다.', true);
        return;
    }
    // content_type + content_idx 기준 중복 체크 (동일 콘텐츠 2회 추가 방지)
    if (top5Items.some(it =>
        it.content_type === resultObj.content_type &&
        it.content_idx  === resultObj.content_idx
    )) {
        showSaveMsg('이미 추가된 항목입니다.', true);
        return;
    }

    top5Items.push({
        title:        resultObj.title,
        link_url:     resultObj.link_url     || '',
        content_type: resultObj.content_type || '',
        content_idx:  resultObj.content_idx  || null,
        description:  '',
        thumb_url:    '',
    });
    renderTop5();

    // 검색창 닫기
    document.getElementById('searchResults').className = 're-search-results';
    document.getElementById('searchInput').value       = '';
    hideSaveMsg();
}

// ===== TOP5 저장 (AJAX) =====
async function saveTop5() {
    if (!currentRegionIdx) return;

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.textContent = '저장 중…';

    try {
        const res = await fetch(`/backoffice/region-explore/${currentRegionIdx}/top5/save`, {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     getCsrfToken(),
            },
            body: JSON.stringify({ items: top5Items }),
        });
        const data = await res.json();
        showSaveMsg(data.message, !data.success);
    } catch (e) {
        showSaveMsg('저장 중 오류가 발생했습니다.', true);
    } finally {
        btn.disabled    = false;
        btn.textContent = '저장';
    }
}

// ===== 지역 활성/비활성 토글 =====
async function toggleRegionState(regionIdx, btnEl) {
    try {
        const res  = await fetch(`/backoffice/region-explore/${regionIdx}/state`, {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     getCsrfToken(),
            },
        });
        const data = await res.json();
        if (!data.success) return;

        const li = btnEl.closest('.re-region-item');
        if (data.new_state === 1) {
            li.classList.remove('inactive');
            li.dataset.state  = '1';
            btnEl.textContent = '🟢';
        } else {
            li.classList.add('inactive');
            li.dataset.state  = '0';
            btnEl.textContent = '⚫';
        }
    } catch (e) {
        console.error('상태 변경 실패:', e);
    }
}

// ===== 드래그 앤 드롭 정렬 =====
function onDragStart(e) {
    dragSrcIndex = parseInt(e.currentTarget.dataset.index);
    e.currentTarget.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}
function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    document.querySelectorAll('.re-top5-item').forEach(el => el.classList.remove('drag-over'));
    e.currentTarget.classList.add('drag-over');
}
function onDrop(e) {
    e.preventDefault();
    const targetIndex = parseInt(e.currentTarget.dataset.index);
    if (dragSrcIndex === null || dragSrcIndex === targetIndex) return;

    const moved = top5Items.splice(dragSrcIndex, 1)[0];
    top5Items.splice(targetIndex, 0, moved);
    renderTop5();
}
function onDragEnd(e) {
    document.querySelectorAll('.re-top5-item').forEach(el => {
        el.classList.remove('dragging', 'drag-over');
    });
    dragSrcIndex = null;
}

// ===== 유틸 =====
function showSaveMsg(msg, isError = false) {
    const el = document.getElementById('saveMsg');
    el.textContent = msg;
    el.className   = 're-save-msg visible' + (isError ? ' error' : '');
    if (!isError) setTimeout(() => el.className = 're-save-msg', 3000);
}
function hideSaveMsg() {
    document.getElementById('saveMsg').className = 're-save-msg';
}
function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}
function getCsrfToken() {
    // CodeIgniter4 CSRF 쿠키에서 토큰 읽기
    const match = document.cookie.match(/csrf_cookie_name=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

// ===== 초기 로드: 각 지역 TOP5 개수 배지 설정 =====
(async function initCounts() {
    const regions = <?= json_encode(array_map(fn($r) => ['idx' => $r['idx']], $regions)) ?>;
    await Promise.all(regions.map(async (r) => {
        try {
            const res  = await fetch(`/backoffice/region-explore/${r.idx}/top5`);
            const data = await res.json();
            if (data.success) updateRegionCount(r.idx, data.items.length);
        } catch {}
    }));
})();
</script>

<?= view('backoffice/partials/footer') ?>
