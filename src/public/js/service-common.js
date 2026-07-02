/* =============================================
   서비스 페이지 공통 스크립트
   (맛집/관광지/축제 리스트 공통)
   각 페이지에서 const SUGGEST_URL = '...' 을 먼저 선언하고 이 파일을 로드한다.
   ============================================= */

/* ---- 검색어 자동완성 (AJAX) ---- */
(function initSearchSuggest() {
    const input    = document.getElementById('searchInput');
    const dropdown = document.getElementById('suggestDropdown');
    const form     = document.getElementById('filterForm');
    const districtSelect = document.querySelector('select[name="district"]');
    if (!input || !dropdown || typeof SUGGEST_URL === 'undefined') return;

    let timer;
    let activeIdx = -1;

    function highlight(text, q) {
        if (!q) return escHtml(text);
        const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        return escHtml(text).replace(re, m => `<mark class="suggest-mark">${m}</mark>`);
    }
    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderDropdown(suggestions, q) {
        if (!suggestions.length) { closeDropdown(); return; }

        const groups   = { name: [], hashtag: [], district: [] };
        const groupMeta = {
            name:     { icon: '🔎', label: '이름' },
            hashtag:  { icon: '🏷️', label: '해시태그' },
            district: { icon: '📍', label: '지역' },
        };

        suggestions.forEach(s => { if (groups[s.type]) groups[s.type].push(s); });

        let html = '';
        let itemIdx = 0;
        ['name', 'hashtag', 'district'].forEach(type => {
            if (!groups[type].length) return;
            html += `<div class="suggest-group-label">${groupMeta[type].label}</div>`;
            groups[type].forEach(s => {
                html += `<div class="suggest-item" data-value="${escHtml(s.value)}" data-type="${type}" data-idx="${itemIdx++}">
                    <span class="suggest-item-icon">${groupMeta[type].icon}</span>
                    <span class="suggest-item-text">${highlight(s.label, q)}</span>
                </div>`;
            });
        });

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
        activeIdx = -1;

        dropdown.querySelectorAll('.suggest-item').forEach(item => {
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectItem(this);
            });
        });
    }

    function selectItem(el) {
        const value = el.dataset.value;
        const type  = el.dataset.type;
        if (type === 'district') {
            if (districtSelect) districtSelect.value = value;
            input.value = '';
        } else {
            input.value = value;
        }
        closeDropdown();
        form.submit();
    }

    function closeDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        activeIdx = -1;
    }

    function updateActive(newIdx) {
        const items = dropdown.querySelectorAll('.suggest-item');
        if (!items.length) return;
        items.forEach(el => el.classList.remove('suggest-item--active'));
        activeIdx = (newIdx + items.length) % items.length;
        items[activeIdx].classList.add('suggest-item--active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (!q) { closeDropdown(); return; }
        timer = setTimeout(() => {
            fetch(SUGGEST_URL + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => renderDropdown(data.suggestions || [], q))
                .catch(() => closeDropdown());
        }, 250);
    });

    input.addEventListener('keydown', function (e) {
        const items = dropdown.querySelectorAll('.suggest-item');
        if (dropdown.style.display === 'none' || !items.length) return;
        if (e.key === 'ArrowDown')  { e.preventDefault(); updateActive(activeIdx + 1); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); updateActive(activeIdx - 1); }
        else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); selectItem(items[activeIdx]); }
        else if (e.key === 'Escape') { closeDropdown(); }
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) closeDropdown();
    });

    input.addEventListener('focus', function () {
        if (this.value.trim()) this.dispatchEvent(new Event('input'));
    });
})();

/* ---- 카드/리스트 뷰 토글 ---- */
(function initViewToggle() {
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const btnCard  = document.getElementById('btnCardView');
    const btnList  = document.getElementById('btnListView');
    if (!btnCard || !btnList) return;

    /* 페이지별 별도 키로 저장하여 서로 독립 */
    const storageKey = 'viewMode_' + location.pathname.replace(/\//g, '_');
    if (localStorage.getItem(storageKey) === 'list') {
        cardView.style.display = 'none';
        listView.style.display = 'flex';
        btnCard.classList.remove('active');
        btnList.classList.add('active');
    }

    btnCard.addEventListener('click', function () {
        cardView.style.display = 'grid';
        listView.style.display = 'none';
        btnCard.classList.add('active');
        btnList.classList.remove('active');
        localStorage.setItem(storageKey, 'card');
    });

    btnList.addEventListener('click', function () {
        cardView.style.display = 'none';
        listView.style.display = 'flex';
        btnCard.classList.remove('active');
        btnList.classList.add('active');
        localStorage.setItem(storageKey, 'list');
    });
})();

/* ---- 검색 엔터 키 ---- */
(function initSearchEnter() {
    const input = document.querySelector('.filter-search input');
    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') document.getElementById('filterForm').submit();
        });
    }
})();
