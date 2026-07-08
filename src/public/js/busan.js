/* =============================================
   부산온나 메인 스크립트
   ============================================= */

/* ---- 배너 슬라이더 ---- */
(function initSlider() {
    const slides  = document.querySelectorAll('.banner-slide');
    const dots    = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('bannerPrev');
    const nextBtn = document.getElementById('bannerNext');
    if (!slides.length) return;

    let current = 0;
    let timer;

    function goTo(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = (idx + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }

    function startAuto() { timer = setInterval(() => goTo(current + 1), 4500); }
    function stopAuto()  { clearInterval(timer); }

    prevBtn.addEventListener('click', () => { stopAuto(); goTo(current - 1); startAuto(); });
    nextBtn.addEventListener('click', () => { stopAuto(); goTo(current + 1); startAuto(); });
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { stopAuto(); goTo(i); startAuto(); });
    });

    startAuto();
})();

/* ---- 헤더 스크롤 효과 ---- */
(function initHeaderScroll() {
    const header = document.getElementById('siteHeader');
    window.addEventListener('scroll', () => {
        header.style.background = window.scrollY > 60
            ? 'rgba(8, 15, 30, 0.98)'
            : 'rgba(8, 15, 30, 0.90)';
    });
})();

/* ---- 부산 지도 인터랙티브 마커 ---- */
(function initMap() {
    const svg         = document.getElementById('busanMap');
    const infoDefault = document.querySelector('.map-info-default');
    const infoContent = document.getElementById('mapInfoContent');
    if (!svg || !infoContent) return;

    // 구별 데이터: 좌표는 실제 busan_map.svg 의 1254×1254 viewBox 기준 (구 중심점)
    const districts = [
        {
            id: 'gijang',    name: '기장군',   cx: 992, cy: 341, color: '#6c5ce7',
            spots: ['죽성성당', '일광해수욕장', '기장시장', '용궁사']
        },
        {
            id: 'geumjeong', name: '금정구',   cx: 725, cy: 411, color: '#00b894',
            spots: ['금정산성', '범어사', '금정산 등산로']
        },
        {
            id: 'haeundae',  name: '해운대구', cx: 875, cy: 586, color: '#0984e3',
            spots: ['해운대해수욕장', '동백섬', 'APEC 나루공원', '영화의전당']
        },
        {
            id: 'dongnae',   name: '동래구',   cx: 699, cy: 559, color: '#e17055',
            spots: ['동래온천', '복천박물관', '동래읍성', '금강공원']
        },
        {
            id: 'buk',       name: '북구',     cx: 565, cy: 488, color: '#00cec9',
            spots: ['화명수목원', '금곡대나무숲', '화명동 카페거리']
        },
        {
            id: 'sasang',    name: '사상구',   cx: 481, cy: 710, color: '#fdcb6e',
            spots: ['삼락생태공원', '낙동강 둔치', '사상인디스테이션']
        },
        {
            id: 'gangseo',   name: '강서구',   cx: 263, cy: 854, color: '#55efc4',
            spots: ['을숙도', '에코델타시티', '낙동강 하구 철새도래지']
        },
        {
            id: 'yeonje',    name: '연제구',   cx: 685, cy: 650, color: '#a29bfe',
            spots: ['배산임수공원', '연산동 고분군']
        },
        {
            id: 'busanjin',  name: '부산진구', cx: 635, cy: 688, color: '#fd79a8',
            spots: ['서면 번화가', '부산시민공원', '어린이대공원', '진시장']
        },
        {
            id: 'dong',      name: '동구',     cx: 624, cy: 796, color: '#e84393',
            spots: ['초량이바구길', '이바구공작소', '부산역 차이나타운']
        },
        {
            id: 'suyeong',   name: '수영구',   cx: 792, cy: 706, color: '#74b9ff',
            spots: ['광안리해수욕장', '민락수변공원', '수영사적공원']
        },
        {
            id: 'nam',       name: '남구',     cx: 741, cy: 792, color: '#636e72',
            spots: ['이기대공원', '유엔기념공원', '오륙도 해맞이공원']
        },
        {
            id: 'saha',      name: '사하구',   cx: 453, cy: 951, color: '#e056fd',
            spots: ['감천문화마을', '다대포해수욕장', '몰운대']
        },
        {
            id: 'seo',       name: '서구',     cx: 545, cy: 829, color: '#f9ca24',
            spots: ['송도해수욕장', '암남공원', '남항대교 전망']
        },
        {
            id: 'jung',      name: '중구',     cx: 604, cy: 870, color: '#ff7675',
            spots: ['자갈치시장', '국제시장', '용두산공원', 'BIFF 광장']
        },
        {
            id: 'yeongdo',   name: '영도구',   cx: 670, cy: 951, color: '#0984e3',
            spots: ['태종대', '흰여울문화마을', '절영해안산책로', '해양박물관']
        },
    ];

    districts.forEach(d => {
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'district-marker');

        // 글로우 원 (1254×1254 viewBox에 맞게 크기 조정)
        const glow = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        glow.setAttribute('class', 'marker-glow');
        glow.setAttribute('cx', d.cx);
        glow.setAttribute('cy', d.cy);
        glow.setAttribute('r', '30');
        glow.setAttribute('fill', d.color);
        glow.setAttribute('opacity', '0.22');

        // 메인 원
        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dot.setAttribute('class', 'marker-dot');
        dot.setAttribute('cx', d.cx);
        dot.setAttribute('cy', d.cy);
        dot.setAttribute('r', '18');
        dot.setAttribute('fill', d.color);
        dot.setAttribute('stroke', '#fff');
        dot.setAttribute('stroke-width', '4');

        g.appendChild(glow);
        g.appendChild(dot);

        // 호버 이벤트
        g.addEventListener('mouseenter', () => {
            glow.setAttribute('opacity', '0.42');
            glow.setAttribute('r', '38');
            dot.setAttribute('r', '23');
            renderPanel(d);
        });
        g.addEventListener('mouseleave', () => {
            glow.setAttribute('opacity', '0.22');
            glow.setAttribute('r', '30');
            dot.setAttribute('r', '18');
        });
        g.addEventListener('click', () => renderPanel(d));

        svg.appendChild(g);
    });

    // 지역명 → busan_maps.idx 역방향 맵
    const nameToIdx = {};
    if (window.regionList) {
        Object.values(window.regionList).forEach(r => {
            nameToIdx[r.name] = r.idx;
        });
    }

    // content_type → 뱃지 레이블
    const typeLabel = { restaurant: '맛집', place: '관광지', event: '행사' };
    const typeEmoji = { restaurant: '🍽️', place: '🗺️', event: '🎉' };

    function renderPanel(d) {
        infoDefault.style.display = 'none';
        infoContent.style.display = 'block';

        // DB 데이터 조회
        const regionIdx = nameToIdx[d.name];
        const top5      = (window.regionTop5 && regionIdx && window.regionTop5[regionIdx]) || [];

        // TOP5 목록 HTML 생성
        let listHtml;
        if (top5.length > 0) {
            listHtml = `<ul class="panel-spots">
                ${top5.map(item => {
                    const emoji = typeEmoji[item.content_type] || '📍';
                    const label = typeLabel[item.content_type] || '';
                    const url   = item.link_url || '#';
                    return `
                    <li class="panel-spot-item">
                        <span class="spot-dot" style="background:${d.color}"></span>
                        <a href="${url}" class="panel-spot-link" style="color:inherit;text-decoration:none;">
                            <span class="spot-type-emoji">${emoji}</span>
                            <span class="spot-name">${item.title}</span>
                            ${label ? `<span class="spot-type-badge" style="background:${d.color}22;color:${d.color}">${label}</span>` : ''}
                        </a>
                    </li>`;
                }).join('')}
            </ul>`;
        } else {
            // DB 데이터 없을 때 하드코딩 spots 폴백
            listHtml = `<ul class="panel-spots">
                ${(d.spots || []).map(s => `
                    <li class="panel-spot-item">
                        <span class="spot-dot" style="background:${d.color}"></span>
                        <span>${s}</span>
                    </li>
                `).join('')}
            </ul>`;
        }

        // 더 알아보기 URL: /hotplace/{busan_maps.idx}
        const hotplaceUrl = regionIdx ? `/hotplace/${regionIdx}` : '#';

        infoContent.innerHTML = `
            <span class="panel-tag" style="background:${d.color}">지역 안내</span>
            <h3 class="panel-district-name">${d.name}</h3>
            <p class="panel-sub">${d.name} 지역별 탐색 TOP5</p>
            ${listHtml}
            <a href="${hotplaceUrl}" class="btn-panel" style="background:${d.color}">더 알아보기 →</a>
        `;
    }
})();

/* ---- 로그인 모달 ---- */
(function initLoginModal() {
    const overlay    = document.getElementById('loginModal');
    const btnOpen    = document.getElementById('btnOpenLogin');
    const btnClose   = document.getElementById('btnCloseLogin');
    const form       = document.getElementById('loginForm');
    const formMsg    = document.getElementById('loginFormMsg');
    const btnSubmit  = document.getElementById('btnSubmitLogin');
    const btnSwitch  = document.getElementById('btnSwitchToSignup');
    if (!overlay) return;

    /* 모달 열기 */
    function openModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        /* 아이디 저장 쿠키가 없으면 아이디 필드 포커스, 있으면 비밀번호 필드 포커스 */
        const loginId = document.getElementById('loginId');
        const target  = loginId.value ? document.getElementById('loginPw') : loginId;
        target.focus();
    }

    /* 모달 닫기 + 폼 초기화 */
    function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        /* 아이디 저장 쿠키 값은 유지, 비밀번호만 초기화 */
        document.getElementById('loginPw').value = '';
        clearAllErrors();
        hideMsg();
    }

    if (btnOpen)  btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });

    /* 로그인 → 회원가입 모달 전환 */
    if (btnSwitch) {
        btnSwitch.addEventListener('click', () => {
            closeModal();
            setTimeout(() => {
                const signupModal = document.getElementById('signupModal');
                if (signupModal) {
                    signupModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }
            }, 200);
        });
    }

    /* ---- 에러 표시 / 제거 ---- */
    function setError(key, msg) {
        const errEl = document.getElementById(`lerr-${key}`);
        const fgEl  = document.getElementById(`lfg-${key}`);
        if (errEl) errEl.textContent = msg;
        if (fgEl)  fgEl.querySelectorAll('.form-input').forEach(el => el.classList.add('is-error'));
    }
    function clearError(key) {
        const errEl = document.getElementById(`lerr-${key}`);
        const fgEl  = document.getElementById(`lfg-${key}`);
        if (errEl) errEl.textContent = '';
        if (fgEl)  fgEl.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-error'));
    }
    function clearAllErrors() { ['id', 'password'].forEach(clearError); }

    /* ---- 메시지 ---- */
    function showMsg(msg, type) {
        formMsg.textContent = msg;
        formMsg.className   = `form-msg ${type}`;
        formMsg.style.display = 'block';
    }
    function hideMsg() {
        formMsg.style.display = 'none';
        formMsg.textContent   = '';
    }

    /* ---- 클라이언트 사전 검증 ---- */
    function validateClient() {
        let valid = true;
        clearAllErrors();
        if (!document.getElementById('loginId').value.trim()) {
            setError('id', '아이디를 입력해주세요.'); valid = false;
        }
        if (!document.getElementById('loginPw').value) {
            setError('password', '비밀번호를 입력해주세요.'); valid = false;
        }
        return valid;
    }

    /* ---- AJAX 폼 제출 ---- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMsg();
        if (!validateClient()) return;

        btnSubmit.disabled     = true;
        btnSubmit.textContent  = '로그인 중...';

        try {
            const res  = await fetch('/auth/login', { method: 'POST', body: new FormData(form) });
            const data = await res.json();

            if (data.success) {
                showMsg(data.message, 'success');
                /* 세션 상태를 서버에서 다시 렌더링하기 위해 페이지 새로고침 */
                setTimeout(() => window.location.reload(), 800);
            } else {
                if (data.errors) {
                    Object.entries(data.errors).forEach(([k, v]) => setError(k, v));
                } else {
                    showMsg('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            }
        } catch {
            showMsg('서버 연결에 실패했습니다. 잠시 후 다시 시도해주세요.', 'error');
        } finally {
            // 3초 후 버튼 복원 (연속 클릭 방지)
            setTimeout(function () {
                btnSubmit.disabled    = false;
                btnSubmit.textContent = '로그인';
            }, 3000);
        }
    });
})();

/* ---- 회원가입 모달 ---- */
(function initSignupModal() {
    const overlay       = document.getElementById('signupModal');
    const btnOpen       = document.getElementById('btnOpenSignup');
    const btnClose      = document.getElementById('btnCloseSignup');
    const form          = document.getElementById('signupForm');
    const domainSelect  = document.getElementById('emailDomainSelect');
    const domainDirect  = document.getElementById('emailDomainDirect');
    const emailFull     = document.getElementById('emailFull');
    const formMsg       = document.getElementById('formMsg');
    const btnSubmit     = document.getElementById('btnSubmitSignup');
    if (!overlay || !btnOpen) return;

    /* 모달 열기 */
    function openModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('signupId').focus();
    }

    /* 모달 닫기 + 폼 초기화 */
    function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        form.reset();
        domainDirect.style.display = 'none';
        domainSelect.style.display = '';
        clearAllErrors();
        hideMsg();
    }

    btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);

    /* 오버레이 배경 클릭 시 닫기 */
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    /* ESC 키 닫기 */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });

    /* ---- 이메일 도메인 셀렉트 처리 ---- */
    domainSelect.addEventListener('change', () => {
        if (domainSelect.value === 'direct') {
            domainSelect.style.display = 'none';
            domainDirect.style.display = '';
            domainDirect.value = '';
            domainDirect.focus();
        }
    });

    /* 직접입력 필드에서 백스페이스로 빈값이 되면 셀렉트로 복귀 */
    domainDirect.addEventListener('input', () => {
        if (domainDirect.value === '') {
            domainDirect.style.display = 'none';
            domainSelect.style.display = '';
            domainSelect.value = 'naver.com';
        }
    });

    /* ---- 이메일 완성값 조합 ---- */
    function buildEmail() {
        const local  = document.getElementById('emailLocal').value.trim();
        const domain = domainSelect.style.display === 'none'
            ? domainDirect.value.trim()
            : domainSelect.value;
        return local && domain ? `${local}@${domain}` : '';
    }

    /* ---- 에러 표시 / 제거 ---- */
    function setError(fieldKey, msg) {
        const errEl  = document.getElementById(`err-${fieldKey}`);
        const fgEl   = document.getElementById(`fg-${fieldKey}`);
        if (errEl) errEl.textContent = msg;
        if (fgEl)  fgEl.querySelectorAll('.form-input, .form-select').forEach(el => el.classList.add('is-error'));
    }
    function clearError(fieldKey) {
        const errEl = document.getElementById(`err-${fieldKey}`);
        const fgEl  = document.getElementById(`fg-${fieldKey}`);
        if (errEl) errEl.textContent = '';
        if (fgEl)  fgEl.querySelectorAll('.form-input, .form-select').forEach(el => el.classList.remove('is-error'));
    }
    function clearAllErrors() {
        ['id','password','password_confirm','email','phone'].forEach(clearError);
    }

    /* ---- 전송 메시지 ---- */
    function showMsg(msg, type) {
        formMsg.textContent = msg;
        formMsg.className   = `form-msg ${type}`;
        formMsg.style.display = 'block';
    }
    function hideMsg() {
        formMsg.style.display = 'none';
        formMsg.textContent   = '';
    }

    /* ---- 클라이언트 사전 검증 ---- */
    function validateClient() {
        let valid = true;
        clearAllErrors();

        const id     = document.getElementById('signupId').value.trim();
        const pw     = document.getElementById('signupPw').value;
        const pwc    = document.getElementById('signupPwConfirm').value;
        const email  = buildEmail();
        const emailL = document.getElementById('emailLocal').value.trim();

        if (id.length < 4)           { setError('id', '아이디는 4자 이상 입력해주세요.'); valid = false; }
        if (pw.length < 8)           { setError('password', '비밀번호는 8자 이상 입력해주세요.'); valid = false; }
        if (pw !== pwc)              { setError('password_confirm', '비밀번호가 일치하지 않습니다.'); valid = false; }
        if (!emailL)                 { setError('email', '이메일을 입력해주세요.'); valid = false; }
        else if (!/^[^\s@]+$/.test(emailL)) { setError('email', '이메일 아이디에 잘못된 문자가 포함되어 있습니다.'); valid = false; }

        return valid;
    }

    /* ---- 폼 AJAX 제출 ---- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMsg();
        if (!validateClient()) return;

        /* 숨겨진 email 필드에 완성된 이메일 값 설정 */
        emailFull.value = buildEmail();

        const formData = new FormData(form);
        btnSubmit.disabled = true;
        btnSubmit.textContent = '처리 중...';

        try {
            const res  = await fetch('/auth/register', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                showMsg(data.message, 'success');
                form.reset();
                domainDirect.style.display = 'none';
                domainSelect.style.display = '';
                /* 2초 후 자동으로 모달 닫기 */
                setTimeout(closeModal, 2000);
            } else {
                /* 서버 에러를 각 필드에 표시 */
                if (data.errors) {
                    Object.entries(data.errors).forEach(([key, msg]) => setError(key, msg));
                } else {
                    showMsg('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            }
        } catch {
            showMsg('서버 연결에 실패했습니다. 잠시 후 다시 시도해주세요.', 'error');
        } finally {
            // 3초 후 버튼 복원 (연속 클릭 방지)
            setTimeout(function () {
                btnSubmit.disabled    = false;
                btnSubmit.textContent = '가입하기';
            }, 3000);
        }
    });
})();

/* ---- 스크롤 페이드인 (Intersection Observer) ---- */
(function initFadeIn() {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.fade-in').forEach(el => el.classList.add('visible'));
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
})();
