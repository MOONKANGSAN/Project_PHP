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

    // 구별 데이터: 좌표는 700×580 viewBox 기준
    const districts = [
        {
            id: 'gijang', name: '기장군', cx: 572, cy: 118, color: '#6c5ce7',
            spots: ['죽성성당', '일광해수욕장', '기장시장', '용궁사']
        },
        {
            id: 'geumjeong', name: '금정구', cx: 400, cy: 118, color: '#00b894',
            spots: ['금정산성', '범어사', '금정산 등산로']
        },
        {
            id: 'haeundae', name: '해운대구', cx: 522, cy: 210, color: '#0984e3',
            spots: ['해운대해수욕장', '동백섬', 'APEC 나루공원', '영화의전당']
        },
        {
            id: 'dongnae', name: '동래구', cx: 330, cy: 196, color: '#e17055',
            spots: ['동래온천', '복천박물관', '동래읍성', '금강공원']
        },
        {
            id: 'buk', name: '북구', cx: 213, cy: 147, color: '#00cec9',
            spots: ['화명수목원', '금곡대나무숲', '화명동 카페거리']
        },
        {
            id: 'sasang', name: '사상구', cx: 184, cy: 252, color: '#fdcb6e',
            spots: ['삼락생태공원', '낙동강 둔치', '사상인디스테이션']
        },
        {
            id: 'gangseo', name: '강서구', cx: 85, cy: 310, color: '#55efc4',
            spots: ['을숙도', '에코델타시티', '낙동강 하구 철새도래지']
        },
        {
            id: 'yeonje', name: '연제구', cx: 308, cy: 272, color: '#a29bfe',
            spots: ['배산임수공원', '연산동 고분군']
        },
        {
            id: 'busanjin', name: '부산진구', cx: 262, cy: 318, color: '#fd79a8',
            spots: ['서면 번화가', '부산시민공원', '어린이대공원', '진시장']
        },
        {
            id: 'dong', name: '동구', cx: 350, cy: 347, color: '#e84393',
            spots: ['초량이바구길', '이바구공작소', '부산역 차이나타운']
        },
        {
            id: 'suyeong', name: '수영구', cx: 478, cy: 328, color: '#74b9ff',
            spots: ['광안리해수욕장', '민락수변공원', '수영사적공원']
        },
        {
            id: 'nam', name: '남구', cx: 415, cy: 415, color: '#636e72',
            spots: ['이기대공원', '유엔기념공원', '오륙도 해맞이공원']
        },
        {
            id: 'saha', name: '사하구', cx: 150, cy: 402, color: '#e056fd',
            spots: ['감천문화마을', '다대포해수욕장', '몰운대']
        },
        {
            id: 'seo', name: '서구', cx: 210, cy: 408, color: '#f9ca24',
            spots: ['송도해수욕장', '암남공원', '남항대교 전망']
        },
        {
            id: 'jung', name: '중구', cx: 268, cy: 448, color: '#ff7675',
            spots: ['자갈치시장', '국제시장', '용두산공원', 'BIFF 광장']
        },
        {
            id: 'yeongdo', name: '영도구', cx: 305, cy: 513, color: '#0984e3',
            spots: ['태종대', '흰여울문화마을', '절영해안산책로', '해양박물관']
        },
    ];

    districts.forEach(d => {
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'district-marker');

        // 글로우 원
        const glow = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        glow.setAttribute('class', 'marker-glow');
        glow.setAttribute('cx', d.cx);
        glow.setAttribute('cy', d.cy);
        glow.setAttribute('r', '18');
        glow.setAttribute('fill', d.color);
        glow.setAttribute('opacity', '0.22');

        // 메인 원
        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dot.setAttribute('class', 'marker-dot');
        dot.setAttribute('cx', d.cx);
        dot.setAttribute('cy', d.cy);
        dot.setAttribute('r', '10');
        dot.setAttribute('fill', d.color);
        dot.setAttribute('stroke', '#fff');
        dot.setAttribute('stroke-width', '2.5');

        // 텍스트 라벨
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', d.cx);
        label.setAttribute('y', d.cy + 27);
        label.setAttribute('text-anchor', 'middle');
        label.setAttribute('class', 'district-label');
        label.textContent = d.name;

        g.appendChild(glow);
        g.appendChild(dot);
        g.appendChild(label);

        // 호버 이벤트
        g.addEventListener('mouseenter', () => {
            glow.setAttribute('opacity', '0.42');
            glow.setAttribute('r', '22');
            dot.setAttribute('r', '13');
            renderPanel(d);
        });
        g.addEventListener('mouseleave', () => {
            glow.setAttribute('opacity', '0.22');
            glow.setAttribute('r', '18');
            dot.setAttribute('r', '10');
        });
        g.addEventListener('click', () => renderPanel(d));

        svg.appendChild(g);
    });

    function renderPanel(d) {
        infoDefault.style.display = 'none';
        infoContent.style.display = 'block';
        infoContent.innerHTML = `
            <span class="panel-tag" style="background:${d.color}">지역 안내</span>
            <h3 class="panel-district-name">${d.name}</h3>
            <p class="panel-sub">주요 명소 및 관광지</p>
            <ul class="panel-spots">
                ${d.spots.map(s => `
                    <li class="panel-spot-item">
                        <span class="spot-dot" style="background:${d.color}"></span>
                        <span>${s}</span>
                    </li>
                `).join('')}
            </ul>
            <a href="#" class="btn-panel" style="background:${d.color}">더 알아보기 →</a>
        `;
    }
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
