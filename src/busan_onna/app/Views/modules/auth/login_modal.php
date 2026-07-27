<!-- ===================== 로그인 모달 ===================== -->
<div class="modal-overlay" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
    <div class="modal-box modal-box--sm">

        <div class="modal-header">
            <h2 class="modal-title" id="loginModalTitle">로그인</h2>
            <button type="button" class="modal-close" id="btnCloseLogin" aria-label="닫기">&times;</button>
        </div>

        <form class="signup-form" id="loginForm" novalidate>
            <?= csrf_field() ?>

            <!-- 아이디 -->
            <div class="form-group" id="lfg-id">
                <label class="form-label" for="loginId">아이디 <span class="required">*</span></label>
                <input type="text" id="loginId" name="id"
                       class="form-input" placeholder="아이디 입력"
                       autocomplete="username" maxlength="50"
                       value="<?= esc($saved_id ?? '') ?>">
                <span class="form-error" id="lerr-id"></span>
            </div>

            <!-- 비밀번호 -->
            <div class="form-group" id="lfg-password">
                <label class="form-label" for="loginPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="loginPw" name="password"
                       class="form-input" placeholder="비밀번호 입력"
                       autocomplete="current-password" maxlength="100">
                <span class="form-error" id="lerr-password"></span>
            </div>

            <!-- 체크박스 옵션 -->
            <div class="login-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="save_id" id="chkSaveId" value="1"
                           <?= !empty($saved_id) ? 'checked' : '' ?>>
                    <span class="checkbox-text">아이디 저장</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="keep_login" id="chkKeepLogin" value="1">
                    <span class="checkbox-text">상시 로그인</span>
                </label>
            </div>

            <!-- 전송 메시지 -->
            <div class="form-msg" id="loginFormMsg" style="display:none"></div>

            <button type="submit" class="btn-submit" id="btnSubmitLogin">로그인</button>

            <div class="login-footer">
                <span>아직 회원이 아니신가요?</span>
                <button type="button" class="link-btn" id="btnSwitchToSignup">회원가입</button>
            </div>
        </form>

    </div>
</div>
