/* =============================================
   부산온나 메인 스크립트
   ============================================= */

/* ---- 전역 이미지 오류 핸들러 (서버 부재/500 에러 시 대체 이미지 표시) ---- */
document.addEventListener('error', function (e) {
    if (e.target.tagName === 'IMG' && e.target.src !== location.origin + '/img/no-image.svg') {
        e.target.src = '/img/no-image.svg';
    }
}, true);

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

