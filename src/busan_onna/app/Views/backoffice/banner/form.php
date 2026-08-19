<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $mode === 'register' ? '배너 등록' : '배너 수정' ?></h1>
            <p class="bo-page-desc">메인 페이지 슬라이더 배너를 <?= $mode === 'register' ? '등록' : '수정' ?>합니다.</p>
        </div>
        <a href="/backoffice/banners" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<!-- 유효성 에러 -->
<?php if (session()->getFlashdata('form_errors')): ?>
    <div class="bo-alert bo-alert-error">
        <?php foreach ((array) session()->getFlashdata('form_errors') as $err): ?>
            <div><?= esc($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$action = $mode === 'register'
    ? '/backoffice/banners/register'
    : '/backoffice/banners/' . $item['idx'] . '/edit';

$v = fn(string $f, mixed $d = '') => old($f) ?? ($item[$f] ?? $d);
?>

<form id="bannerForm" method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- ===== 배너 미리보기 ===== -->
    <div class="bo-card" style="margin-bottom:20px;padding:0;overflow:hidden;">

        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:13px;font-weight:600;color:#374151;">실제 배너 미리보기</span>
                <span style="font-size:12px;color:#9ca3af;">— 메인 페이지에서 보이는 실제 비율(1920 × 640)</span>
            </div>
            <span id="previewHint" style="font-size:12px;color:#3b82f6;display:<?= ($mode === 'edit' && $item['image_url']) ? 'none' : 'block' ?>">
                아래에서 이미지를 선택하면 미리보기가 업데이트됩니다
            </span>
        </div>

        <!-- 미리보기 컨테이너: aspect-ratio 3/1 = 1920:640 -->
        <div id="bannerPreview" style="
            position:relative;
            width:100%;
            aspect-ratio:3/1;
            overflow:hidden;
            background:linear-gradient(135deg,#0a3d62 0%,#1a6b9a 45%,#48c6ef 100%);
        ">
            <!-- 배경 이미지 레이어 -->
            <img id="previewImg"
                 src="<?= ($mode === 'edit' && $item['image_url']) ? esc($item['image_url']) : '' ?>"
                 alt=""
                 style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;
                        object-position:<?= esc($v('image_position', '50 50')) !== '' ? str_replace(' ', '% ', esc($v('image_position', '50 50'))).'%' : '50% 50%' ?>;
                        display:<?= ($mode === 'edit' && $item['image_url']) ? 'block' : 'none' ?>;">

            <!-- 드래그 위치 조정 힌트 (이미지가 잘릴 때만 표시) -->
            <div id="dragHint" style="
                display:none;
                position:absolute;
                top:10px;left:10px;
                background:rgba(0,0,0,0.58);
                color:#fff;
                font-size:12px;
                padding:5px 10px;
                border-radius:6px;
                z-index:10;
                align-items:center;
                gap:6px;
                pointer-events:none;
                user-select:none;
            ">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M5 9l-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M12 3v18M3 12h18"/>
                </svg>
                드래그하여 표시 영역 조정
            </div>

            <!-- 어두운 오버레이 (실제 배너와 동일) -->
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.28);"></div>

            <!-- 텍스트 콘텐츠 오버레이 -->
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;
                        justify-content:center;text-align:center;padding:24px;z-index:2;">
                <h2 id="previewTitle"
                    style="color:#fff;font-size:clamp(20px,3.5vw,52px);font-weight:900;
                           line-height:1.2;margin:0 0 12px;text-shadow:0 2px 24px rgba(0,0,0,.35);
                           display:<?= $v('title') ? 'block' : 'none' ?>;">
                    <?= esc($v('title')) ?>
                </h2>
                <p id="previewSubtitle"
                   style="color:rgba(255,255,255,.88);font-size:clamp(13px,1.6vw,22px);
                          margin:0;text-shadow:0 1px 8px rgba(0,0,0,.3);
                          display:<?= $v('subtitle') ? 'block' : 'none' ?>;">
                    <?= esc($v('subtitle')) ?>
                </p>
                <!-- 이미지 미선택 시 안내 문구 -->
                <div id="previewPlaceholder" style="display:<?= ($mode === 'edit' && $item['image_url']) ? 'none' : 'flex' ?>;
                     flex-direction:column;align-items:center;gap:10px;opacity:.55;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span style="color:#fff;font-size:14px;">이미지를 선택해주세요</span>
                </div>
            </div>

            <!-- 크기 표시 워터마크 -->
            <div style="position:absolute;bottom:10px;right:12px;background:rgba(0,0,0,.4);
                        color:#fff;font-size:11px;padding:3px 8px;border-radius:4px;z-index:3;letter-spacing:.5px;">
                1920 × 640
            </div>
        </div>
    </div>

    <!-- ===== 폼 영역 ===== -->
    <div class="bo-card" style="margin-bottom:20px;">

        <!-- 상단 2열: 상태 / 노출 순서 -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
            <div>
                <label class="bo-form-label">노출 상태 <span style="color:#ef4444">*</span></label>
                <select name="state" class="bo-form-select" style="width:100%">
                    <option value="1" <?= (string)$v('state', 1) === '1' ? 'selected' : '' ?>>활성 (노출)</option>
                    <option value="0" <?= (string)$v('state', 1) === '0' ? 'selected' : '' ?>>비활성 (숨김)</option>
                </select>
            </div>
            <div>
                <label class="bo-form-label">노출 순서</label>
                <input type="number" name="sort_order" min="1" max="9999"
                       value="<?= esc($v('sort_order', 100)) ?>"
                       class="bo-form-input" style="width:100%"
                       placeholder="낮을수록 먼저 노출">
            </div>
            <div>
                <label class="bo-form-label">등록자</label>
                <input type="text"
                       value="<?= esc($item['reg_id'] ?? session()->get('backoffice.id')) ?>"
                       class="bo-form-input" style="width:100%;background:#f9fafb;color:#9ca3af;" readonly>
            </div>
            <div style="display:flex;flex-direction:column;justify-content:flex-end;">
                <?php if ($mode === 'edit'): ?>
                    <span style="font-size:12px;color:#9ca3af;">등록: <?= substr($item['reg_date'], 0, 10) ?></span>
                    <?php if ($item['edit_date']): ?>
                        <span style="font-size:12px;color:#9ca3af;">수정: <?= substr($item['edit_date'], 0, 10) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 이미지 업로드 -->
        <div style="margin-bottom:20px;">
            <label class="bo-form-label">
                배너 이미지 <span style="color:#ef4444"><?= $mode === 'register' ? '*' : '' ?></span>
                <span style="color:#9ca3af;font-weight:400;font-size:12px;margin-left:4px">
                    (권장: 1920×640px, jpg/png/webp, 최대 5MB)
                </span>
            </label>

            <!-- 파일 선택 영역 -->
            <label for="imageInput" id="imageDropZone"
                   style="display:flex;align-items:center;gap:14px;padding:16px 20px;
                          border:2px dashed #d1d5db;border-radius:8px;cursor:pointer;
                          transition:border-color .15s,background .15s;"
                   onmouseover="this.style.borderColor='#3b82f6';this.style.background='#eff6ff'"
                   onmouseout="this.style.borderColor='#d1d5db';this.style.background=''">
                <div style="width:44px;height:44px;background:#f1f5f9;border-radius:8px;flex-shrink:0;
                            display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <div>
                    <div id="fileNameDisplay" style="font-size:13px;font-weight:600;color:#374151;">
                        <?= ($mode === 'edit' && $item['image_url'])
                            ? '현재: ' . esc(basename($item['image_url'])) . ' (새 파일 선택 시 교체)'
                            : '클릭하여 이미지 선택' ?>
                    </div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                        JPG, PNG, WEBP, GIF — 최대 5MB
                    </div>
                </div>
                <input type="file" id="imageInput" name="image" accept="image/*"
                       style="display:none;" onchange="onImageChange(this)">
            </label>
        </div>

        <!-- 이미지 설명 (alt_text) + 노출 지역구 -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div>
                <label class="bo-form-label">이미지 설명 (alt 텍스트)</label>
                <input type="text" name="alt_text"
                       value="<?= esc($v('alt_text')) ?>"
                       class="bo-form-input" style="width:100%"
                       placeholder="이미지 내용을 간략히 설명해주세요. (스크린리더 및 SEO에 사용)">
            </div>
            <div>
                <label class="bo-form-label">
                    노출 지역구
                    <span style="color:#9ca3af;font-weight:400;font-size:12px;margin-left:4px">(배너에 📍 위치로 표시)</span>
                </label>
                <?php
                $busanDistricts = [
                    '' => '선택 안 함',
                    '중구'    => '중구',
                    '서구'    => '서구',
                    '동구'    => '동구',
                    '영도구'  => '영도구',
                    '부산진구'=> '부산진구',
                    '동래구'  => '동래구',
                    '남구'    => '남구',
                    '북구'    => '북구',
                    '해운대구'=> '해운대구',
                    '사하구'  => '사하구',
                    '금정구'  => '금정구',
                    '강서구'  => '강서구',
                    '연제구'  => '연제구',
                    '수영구'  => '수영구',
                    '사상구'  => '사상구',
                    '기장군'  => '기장군',
                ];
                $selectedLoc = $v('location', '');
                ?>
                <select name="location" class="bo-form-select" style="width:100%">
                    <?php foreach ($busanDistricts as $val => $label): ?>
                    <option value="<?= esc($val) ?>" <?= $selectedLoc === $val ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- 배너 제목 -->
        <div style="margin-bottom:20px;">
            <label class="bo-form-label">배너 제목 <span style="color:#9ca3af;font-weight:400;font-size:12px;">(이미지 위 오버레이 텍스트)</span></label>
            <input type="text" name="title" id="inputTitle"
                   value="<?= esc($v('title')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="예: 부산의 밤, 광안대교의 빛"
                   oninput="updatePreviewText()">
        </div>

        <!-- 배너 부제목 -->
        <div style="margin-bottom:20px;">
            <label class="bo-form-label">배너 부제목</label>
            <input type="text" name="subtitle" id="inputSubtitle"
                   value="<?= esc($v('subtitle')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="예: 화려한 야경과 낭만이 가득한 부산의 밤"
                   oninput="updatePreviewText()">
        </div>

        <!-- 링크 URL -->
        <div>
            <label class="bo-form-label">링크 URL <span style="color:#9ca3af;font-weight:400;font-size:12px;">(배너 클릭 시 이동, 미입력 시 링크 없음)</span></label>
            <input type="text" name="link_url"
                   value="<?= esc($v('link_url')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="/spots 또는 https://example.com">
        </div>
    </div>

    <!-- 이미지 표시 위치 (드래그로 결정한 object-position, "X Y" 형식 0~100) -->
    <input type="hidden" name="image_position" id="imagePosition"
           value="<?= esc($v('image_position', '50 50')) ?>">

    <!-- 저장 버튼 -->
    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="/backoffice/banners" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $mode === 'register' ? '배너 등록' : '저장하기' ?>
        </button>
    </div>
</form>

<script>
(function () {
    var img       = document.getElementById('previewImg');
    var container = document.getElementById('bannerPreview');
    var posInput  = document.getElementById('imagePosition');
    var dragHint  = document.getElementById('dragHint');

    // 현재 object-position 값 (0~100 퍼센트)
    var posX = 50, posY = 50;
    var dragging = false;
    var lastX, lastY;
    var overflowX = 0, overflowY = 0;

    // object-position CSS 및 hidden input 동기화
    function applyPos() {
        img.style.objectPosition = posX + '% ' + posY + '%';
        posInput.value = Math.round(posX) + ' ' + Math.round(posY);
    }

    // 이미지가 컨테이너를 넘치는 픽셀 계산 (object-fit:cover 기준)
    function calcOverflow() {
        var cW = container.offsetWidth;
        var cH = container.offsetHeight;
        var nW = img.naturalWidth;
        var nH = img.naturalHeight;
        if (!nW || !nH) { overflowX = overflowY = 0; return; }
        var scale = Math.max(cW / nW, cH / nH);
        overflowX = Math.max(0, nW * scale - cW);
        overflowY = Math.max(0, nH * scale - cH);
    }

    // 드래그 가능 여부에 따라 커서·힌트 UI 갱신
    // container에 cursor를 적용해야 오버레이 div 위에서도 작동함
    function updateDragUI() {
        calcOverflow();
        var canDrag = overflowX > 1 || overflowY > 1;
        dragHint.style.display = canDrag ? 'flex' : 'none';
        container.style.cursor = canDrag ? 'grab' : 'default';
    }

    // 이미지 로드 완료 시 저장된 위치로 초기화
    img.addEventListener('load', function () {
        var saved = (posInput.value || '50 50').split(' ');
        posX = parseFloat(saved[0]) || 50;
        posY = parseFloat(saved[1]) || 50;
        applyPos();
        updateDragUI();
    });

    // ── 마우스 드래그 ─────────────────────────────────────────
    // img가 아닌 container에 등록: 오버레이 div가 img 위를 덮어 img의 mousedown이 차단되기 때문
    container.addEventListener('mousedown', function (e) {
        if (img.style.display === 'none') return;
        if (overflowX <= 1 && overflowY <= 1) return;
        dragging = true;
        lastX = e.clientX;
        lastY = e.clientY;
        container.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function (e) {
        if (!dragging) return;
        var dx = e.clientX - lastX;
        var dy = e.clientY - lastY;
        lastX = e.clientX;
        lastY = e.clientY;

        calcOverflow();
        // 드래그 방향이 반대여야 이미지가 자연스럽게 따라옴
        if (overflowX > 1) posX = Math.max(0, Math.min(100, posX - (dx / overflowX * 100)));
        if (overflowY > 1) posY = Math.max(0, Math.min(100, posY - (dy / overflowY * 100)));
        applyPos();
    });

    document.addEventListener('mouseup', function () {
        if (!dragging) return;
        dragging = false;
        container.style.cursor = (overflowX > 1 || overflowY > 1) ? 'grab' : 'default';
    });

    // ── 터치 드래그 (태블릿·모바일 관리자) ──────────────────────
    container.addEventListener('touchstart', function (e) {
        if (img.style.display === 'none') return;
        if (overflowX <= 1 && overflowY <= 1) return;
        var t = e.touches[0];
        dragging = true;
        lastX = t.clientX;
        lastY = t.clientY;
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchmove', function (e) {
        if (!dragging) return;
        var t = e.touches[0];
        var dx = t.clientX - lastX;
        var dy = t.clientY - lastY;
        lastX = t.clientX;
        lastY = t.clientY;

        calcOverflow();
        if (overflowX > 1) posX = Math.max(0, Math.min(100, posX - (dx / overflowX * 100)));
        if (overflowY > 1) posY = Math.max(0, Math.min(100, posY - (dy / overflowY * 100)));
        applyPos();
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchend', function () { dragging = false; });

    // 브라우저 기본 이미지 드래그 방지
    img.addEventListener('dragstart', function (e) { e.preventDefault(); });

    // ── 이미지 선택 시 미리보기 업데이트 ────────────────────────
    window.onImageChange = function (input) {
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        document.getElementById('fileNameDisplay').textContent = file.name;
        document.getElementById('previewHint').style.display = 'none';

        var reader = new FileReader();
        reader.onload = function (e) {
            // 새 이미지 선택 시 위치 중앙으로 초기화
            posX = 50; posY = 50;
            applyPos();

            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById('previewPlaceholder').style.display = 'none';
            // load 이벤트에서 updateDragUI() 자동 호출됨
        };
        reader.readAsDataURL(file);
    };

    // ── 제목·부제목 실시간 미리보기 ──────────────────────────────
    window.updatePreviewText = function () {
        var title    = document.getElementById('inputTitle').value.trim();
        var subtitle = document.getElementById('inputSubtitle').value.trim();

        var previewTitle    = document.getElementById('previewTitle');
        var previewSubtitle = document.getElementById('previewSubtitle');

        previewTitle.textContent   = title;
        previewTitle.style.display = title ? 'block' : 'none';

        previewSubtitle.textContent   = subtitle;
        previewSubtitle.style.display = subtitle ? 'block' : 'none';
    };
}());
</script>

<?= view('backoffice/partials/footer') ?>
