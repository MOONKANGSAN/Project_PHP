/**
 * custom-select.js
 * -----------------------------------------------------------------------
 * 서비스/백오피스 전 영역의 <select> 를 감지해 옵션 목록까지 커스텀 디자인이
 * 적용된 드롭다운(UI)으로 자동 변환하는 스크립트입니다.
 *
 * - 원본 <select> 는 그대로 두고 화면에서만 숨깁니다(폼 전송, name/value,
 *   기존 change 이벤트 리스너, required 검증 등이 모두 그대로 동작합니다).
 * - 트리거 버튼은 원본 select 와 동일한 class 를 그대로 물려받기 때문에
 *   backoffice.css / busan.css / view_3.css 에 이미 정의된 select 박스
 *   디자인(테두리·라운드·화살표 아이콘)을 그대로 재사용합니다.
 * - 옵션 목록 패널만 이 파일이 새로 그리며, 색상 테마는
 *   panelVariant() 에서 select 의 class 를 보고 'bo' / 'service' / 'course'
 *   중 하나로 판단합니다. 실제 배경/테두리/hover 색상은 각 CSS 파일의
 *   .cs-panel / .cs-option 규칙에서 정의합니다.
 * - travel_course/form.php 처럼 자바스크립트가 나중에 새로운 <select> 를
 *   추가하는 경우를 위해 MutationObserver 로 자동 재적용합니다.
 * - place/form.php, event/form.php, restaurant/form.php 처럼 페이지
 *   자체 스크립트가 sel.selectedIndex 를 직접 바꾸는 경우에도 트리거
 *   라벨이 함께 갱신되도록 selectedIndex/value 접근자를 가로챕니다.
 * ------------------------------------------------------------------- */
(function () {
    'use strict';

    var TARGET_SELECTOR = '.bo-form-select, .filter-select, .form-select, .c-form-select, .re-search-type';

    // 이미 커스텀 UI가 적용된 select 는 다시 건드리지 않도록 표시
    var ENHANCED_FLAG = 'csEnhanced';

    // 현재 열려있는 패널을 닫는 함수(한 번에 하나만 열리도록)
    var closeOpenPanel = null;

    function panelVariant(select) {
        if (select.classList.contains('c-form-select')) return 'course';
        if (select.classList.contains('bo-form-select') || select.classList.contains('re-search-type')) return 'bo';
        return 'service';
    }

    // select 인스턴스의 value / selectedIndex 를 가로채서, 페이지 자체 스크립트가
    // 직접 값을 바꾸더라도 트리거 라벨이 함께 갱신되도록 합니다.
    function interceptProperty(el, prop, onSet) {
        var desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, prop);
        if (!desc || !desc.set || !desc.get) return;
        Object.defineProperty(el, prop, {
            configurable: true,
            enumerable: true,
            get: function () { return desc.get.call(el); },
            set: function (v) {
                desc.set.call(el, v);
                onSet();
            }
        });
    }

    function enhance(select) {
        if (!select || select.tagName !== 'SELECT') return;
        if (select.dataset[ENHANCED_FLAG]) return;
        if (select.multiple) return; // 다중 선택 select 는 대상에서 제외
        select.dataset[ENHANCED_FLAG] = '1';

        var variant = panelVariant(select);
        var originalClassName = select.className;

        var wrap = document.createElement('div');
        wrap.className = 'cs-wrap';
        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);

        // 원본 select 는 화면에서만 숨김(레이아웃/폼 전송/접근성 트리에는 그대로 존재)
        select.classList.add('cs-native');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = (originalClassName + ' cs-trigger').trim();
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        if (select.disabled) trigger.disabled = true;

        var label = document.createElement('span');
        label.className = 'cs-trigger-label';
        trigger.appendChild(label);
        wrap.appendChild(trigger);

        var panel = document.createElement('div');
        panel.className = 'cs-panel cs-panel-' + variant;
        panel.setAttribute('role', 'listbox');
        panel.hidden = true;
        document.body.appendChild(panel);

        var activeIndex = -1;

        function renderOptions() {
            panel.innerHTML = '';
            Array.prototype.forEach.call(select.options, function (opt, i) {
                var item = document.createElement('div');
                item.className = 'cs-option';
                item.setAttribute('role', 'option');
                item.dataset.index = String(i);
                item.textContent = opt.textContent;
                if (opt.disabled) {
                    item.classList.add('is-disabled');
                    item.setAttribute('aria-disabled', 'true');
                } else {
                    item.addEventListener('click', function () {
                        selectIndex(i, true);
                        close();
                        trigger.focus();
                    });
                }
                if (i === select.selectedIndex) item.classList.add('is-selected');
                panel.appendChild(item);
            });
        }

        function syncLabel() {
            var opt = select.options[select.selectedIndex];
            label.textContent = opt ? opt.textContent : '';
            Array.prototype.forEach.call(panel.children, function (item, i) {
                item.classList.toggle('is-selected', i === select.selectedIndex);
            });
        }

        function selectIndex(i, fireEvent) {
            if (i < 0 || i >= select.options.length) return;
            if (select.options[i].disabled) return;
            var changed = select.selectedIndex !== i;
            select.selectedIndex = i; // syncLabel() 은 아래 interceptProperty 를 통해 자동 호출됨
            if (changed && fireEvent) {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function positionPanel() {
            var rect = trigger.getBoundingClientRect();
            var panelHeight = panel.offsetHeight || 260;
            var openUp = (window.innerHeight - rect.bottom) < (panelHeight + 12) && rect.top > (panelHeight + 12);
            panel.style.left = Math.round(rect.left) + 'px';
            panel.style.width = Math.round(rect.width) + 'px';
            if (openUp) {
                panel.style.top = 'auto';
                panel.style.bottom = Math.round(window.innerHeight - rect.top + 6) + 'px';
            } else {
                panel.style.bottom = 'auto';
                panel.style.top = Math.round(rect.bottom + 6) + 'px';
            }
        }

        function highlight(i) {
            activeIndex = i;
            Array.prototype.forEach.call(panel.children, function (item, idx) {
                item.classList.toggle('is-active', idx === i);
            });
            var activeEl = panel.children[i];
            if (activeEl && activeEl.scrollIntoView) activeEl.scrollIntoView({ block: 'nearest' });
        }

        function moveActive(dir) {
            var len = select.options.length;
            if (!len) return;
            var i = activeIndex;
            for (var step = 0; step < len; step++) {
                i = (i + dir + len) % len;
                if (!select.options[i].disabled) { highlight(i); break; }
            }
        }

        function onDocClick(e) {
            if (!wrap.contains(e.target) && !panel.contains(e.target)) close();
        }

        function onKeydown(e) {
            // 패널이 열려있는 동안의 키 입력은 여기서만 처리하고, 같은 이벤트가
            // trigger 자체의 keydown 리스너로 다시 전달되어 재오픈되지 않도록 막습니다.
            e.stopPropagation();
            if (e.key === 'Escape') { close(); trigger.focus(); return; }
            if (e.key === 'ArrowDown') { e.preventDefault(); moveActive(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); moveActive(-1); }
            else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (activeIndex >= 0) { selectIndex(activeIndex, true); close(); trigger.focus(); }
            } else if (e.key === 'Tab') {
                close();
            }
        }

        function open() {
            if (trigger.disabled) return;
            if (closeOpenPanel && closeOpenPanel !== close) closeOpenPanel();
            panel.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            wrap.classList.add('is-open');
            activeIndex = select.selectedIndex;
            highlight(activeIndex);
            positionPanel();
            document.addEventListener('click', onDocClick, true);
            document.addEventListener('keydown', onKeydown, true);
            window.addEventListener('scroll', positionPanel, true);
            window.addEventListener('resize', positionPanel);
            closeOpenPanel = close;
        }

        function close() {
            if (panel.hidden) return;
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            wrap.classList.remove('is-open');
            document.removeEventListener('click', onDocClick, true);
            document.removeEventListener('keydown', onKeydown, true);
            window.removeEventListener('scroll', positionPanel, true);
            window.removeEventListener('resize', positionPanel);
            if (closeOpenPanel === close) closeOpenPanel = null;
        }

        trigger.addEventListener('click', function () {
            if (panel.hidden) open(); else close();
        });
        trigger.addEventListener('keydown', function (e) {
            if (panel.hidden && (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                open();
            }
        });

        // <label for="select-id"> 로 연결된 라벨을 클릭하면 브라우저 기본
        // select 가 아니라 커스텀 트리거가 열리도록 가로챕니다.
        if (select.id) {
            var relatedLabel = document.querySelector('label[for="' + select.id.replace(/"/g, '\\"') + '"]');
            if (relatedLabel) {
                relatedLabel.addEventListener('click', function (e) {
                    e.preventDefault();
                    trigger.focus();
                    if (panel.hidden) open(); else close();
                });
            }
        }

        // 페이지 스크립트가 sel.value / sel.selectedIndex 를 직접 바꾸는 경우 대응
        interceptProperty(select, 'value', syncLabel);
        interceptProperty(select, 'selectedIndex', syncLabel);

        // 옵션 목록 자체가 나중에 다시 채워지는 경우(지역 선택 등) 패널을 재생성
        var optionsObserver = new MutationObserver(function () {
            renderOptions();
            syncLabel();
        });
        optionsObserver.observe(select, { childList: true });

        // disabled 여부가 바뀌는 경우 트리거에도 반영
        var disabledObserver = new MutationObserver(function () {
            trigger.disabled = select.disabled;
            if (select.disabled) close();
        });
        disabledObserver.observe(select, { attributes: true, attributeFilter: ['disabled'] });

        renderOptions();
        syncLabel();
    }

    function enhanceAll(root) {
        if (!root || !root.querySelectorAll) return;
        var nodes = root.querySelectorAll(TARGET_SELECTOR);
        Array.prototype.forEach.call(nodes, enhance);
        if (root.nodeType === 1 && root.matches && root.matches(TARGET_SELECTOR)) enhance(root);
    }

    function init() {
        enhanceAll(document);

        var bodyObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                Array.prototype.forEach.call(m.addedNodes, function (node) {
                    if (node.nodeType !== 1) return;
                    enhanceAll(node);
                });
            });
        });
        bodyObserver.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
