<?php
/**
 * 고객문의 탭 AJAX 부분 뷰
 * $isLoggedIn : 로그인 여부
 * $userId     : 로그인 사용자 아이디 (로그인 시)
 * $inquiries  : 내 문의 목록 배열
 * $types      : InquiryModel::TYPES 유형 목록
 */
?>

<?php if (!$isLoggedIn): ?>
<!-- 비로그인 유도 -->
<div class="inquiry-login-required">
    <div class="inquiry-login-icon">🔒</div>
    <h3>로그인이 필요한 서비스입니다</h3>
    <p>고객문의 서비스는 로그인 후 이용할 수 있습니다.<br>로그인하시면 문의를 등록하고 답변을 확인하실 수 있습니다.</p>
    <button type="button" class="btn-inquiry-login" onclick="document.getElementById('btnOpenLogin').click()">
        로그인하기
    </button>
</div>
<?php else: ?>

<!-- 문의 작성 버튼 -->
<div class="inquiry-header">
    <p class="inquiry-header-desc">문의하신 내용은 평일 09:00~18:00 내에 답변 드립니다.</p>
    <button type="button" class="btn-inquiry-write" id="btnToggleInquiryForm">문의 작성</button>
</div>

<!-- 문의 등록 폼 -->
<div class="inquiry-form-wrap" id="inquiryFormWrap" style="display:none">
    <form id="inquiryForm" novalidate>
        <div class="inquiry-form-inner">
            <div class="form-group">
                <label class="form-label" for="iq_type">문의 유형 <span class="required">*</span></label>
                <select id="iq_type" name="inquiry_type" class="form-select">
                    <?php foreach ($types as $num => $label): ?>
                    <option value="<?= (int)$num ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="iq_title">제목 <span class="required">*</span></label>
                <input type="text" id="iq_title" name="title" class="form-input"
                       placeholder="문의 제목을 입력해주세요" maxlength="200">
            </div>
            <div class="form-group">
                <label class="form-label" for="iq_content">내용 <span class="required">*</span></label>
                <textarea id="iq_content" name="content" class="form-textarea"
                          placeholder="문의 내용을 자세히 입력해주세요" rows="6" maxlength="2000"></textarea>
            </div>
            <div class="form-group form-group--inline">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_public" value="1">
                    <span class="checkbox-text">공개 문의로 등록</span>
                </label>
            </div>
            <div class="inquiry-form-actions">
                <button type="submit" class="btn-submit btn-submit--sm">등록하기</button>
            </div>
        </div>
    </form>
</div>

<!-- 내 문의 목록 -->
<div class="inquiry-list-section">
    <h3 class="inquiry-list-title">내 문의 내역</h3>

    <?php if (empty($inquiries)): ?>
    <div class="empty-result">
        <div class="empty-result-icon">📝</div>
        <p>등록한 문의가 없습니다.</p>
    </div>
    <?php else: ?>
    <div class="inquiry-list">
        <?php foreach ($inquiries as $iq): ?>
        <?php
        $stateLabel = match((int)$iq['state']) {
            2       => ['label' => '답변완료', 'class' => 'state-done'],
            default => ['label' => '접수중',   'class' => 'state-pending'],
        };
        ?>
        <div class="inquiry-item">
            <div class="inquiry-item-header">
                <span class="inquiry-type-badge"><?= esc($types[$iq['inquiry_type']] ?? '기타') ?></span>
                <span class="inquiry-title"><?= esc($iq['title']) ?></span>
                <span class="inquiry-state <?= $stateLabel['class'] ?>"><?= $stateLabel['label'] ?></span>
                <span class="inquiry-date"><?= esc(substr($iq['reg_date'] ?? '', 0, 10)) ?></span>
                <span class="inquiry-toggle-arrow">▼</span>
            </div>
            <div class="inquiry-item-body">
                <!-- 문의 내용 -->
                <div class="inquiry-content-wrap">
                    <span class="iq-label iq-label--q">문의</span>
                    <div class="iq-text"><?= nl2br(esc($iq['content'])) ?></div>
                </div>
                <!-- 답변 -->
                <?php if (!empty($iq['answer'])): ?>
                <div class="inquiry-answer-wrap">
                    <span class="iq-label iq-label--a">답변</span>
                    <div class="iq-text"><?= nl2br(esc($iq['answer'])) ?></div>
                    <div class="iq-answer-date">답변일: <?= esc(substr($iq['answer_date'] ?? '', 0, 10)) ?></div>
                </div>
                <?php else: ?>
                <div class="inquiry-pending-wrap">
                    <p class="inquiry-pending-msg">답변 대기 중입니다. 평일 09:00~18:00 내에 답변 드립니다.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>
