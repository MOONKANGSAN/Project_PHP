<!-- ===================== 회원가입 모달 ===================== -->
<div class="modal-overlay" id="signupModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">

        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">회원가입</h2>
            <button type="button" class="modal-close" id="btnCloseSignup" aria-label="닫기">&times;</button>
        </div>

        <form class="signup-form" id="signupForm" novalidate>
            <?= csrf_field() ?>

            <!-- 아이디 -->
            <div class="form-group" id="fg-id">
                <label class="form-label" for="signupId">아이디 <span class="required">*</span></label>
                <input type="text" id="signupId" name="id"
                       class="form-input" placeholder="영문·숫자 4자 이상" autocomplete="username" maxlength="50">
                <span class="form-error" id="err-id"></span>
            </div>

            <!-- 비밀번호 -->
            <div class="form-group" id="fg-password">
                <label class="form-label" for="signupPw">비밀번호 <span class="required">*</span></label>
                <input type="password" id="signupPw" name="password"
                       class="form-input" placeholder="8자 이상 입력" autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password"></span>
            </div>

            <!-- 비밀번호 확인 -->
            <div class="form-group" id="fg-password_confirm">
                <label class="form-label" for="signupPwConfirm">비밀번호 확인 <span class="required">*</span></label>
                <input type="password" id="signupPwConfirm" name="password_confirm"
                       class="form-input" placeholder="비밀번호를 다시 입력" autocomplete="new-password" maxlength="100">
                <span class="form-error" id="err-password_confirm"></span>
            </div>

            <!-- 이메일 -->
            <div class="form-group" id="fg-email">
                <label class="form-label" for="emailLocal">이메일 <span class="required">*</span></label>
                <div class="email-wrap">
                    <input type="text" id="emailLocal" class="form-input email-local"
                           placeholder="이메일 아이디" autocomplete="email">
                    <span class="at-sign">@</span>
                    <select id="emailDomainSelect" class="form-select email-domain-select">
                        <option value="naver.com">naver.com</option>
                        <option value="gmail.com">gmail.com</option>
                        <option value="daum.net">daum.net</option>
                        <option value="kakao.com">kakao.com</option>
                        <option value="nate.com">nate.com</option>
                        <option value="direct">직접입력</option>
                    </select>
                    <input type="text" id="emailDomainDirect" class="form-input email-domain-direct"
                           placeholder="도메인 직접 입력" style="display:none">
                </div>
                <!-- 실제 서버로 전송되는 완성된 이메일 값 -->
                <input type="hidden" name="email" id="emailFull">
                <span class="form-error" id="err-email"></span>
            </div>

            <!-- 휴대폰 -->
            <div class="form-group" id="fg-phone">
                <label class="form-label" for="signupPhone">휴대폰 번호</label>
                <input type="tel" id="signupPhone" name="phone"
                       class="form-input" placeholder="010-0000-0000" maxlength="13">
                <span class="form-error" id="err-phone"></span>
            </div>

            <!-- 전송 메시지 영역 -->
            <div class="form-msg" id="formMsg" style="display:none"></div>

            <button type="submit" class="btn-submit" id="btnSubmitSignup">가입하기</button>
        </form>

    </div>
</div>
