<?php

namespace App\Controllers;

use App\Models\UserInfoModel;
use CodeIgniter\Cookie\Cookie;

/**
 * 인증 컨트롤러 - 회원가입 / 로그인 / 로그아웃 처리
 */
class Auth extends BaseController
{
    /**
     * 회원가입 처리 (AJAX POST → JSON 응답)
     */
    public function register(): \CodeIgniter\HTTP\ResponseInterface
    {
        // 서버사이드 유효성 검사 규칙
        $rules = [
            'id'             => 'required|min_length[4]|max_length[50]|is_unique[user_info.id]',
            'password'       => 'required|min_length[8]|max_length[100]',
            'password_confirm' => 'required|matches[password]',
            'email'          => 'required|valid_email|max_length[100]|is_unique[user_info.email]',
            'phone'          => 'permit_empty|max_length[20]',
        ];

        $messages = [
            'id' => [
                'min_length' => '아이디는 4자 이상 입력해주세요.',
                'is_unique'  => '이미 사용 중인 아이디입니다.',
            ],
            'password' => [
                'min_length' => '비밀번호는 8자 이상 입력해주세요.',
            ],
            'password_confirm' => [
                'matches' => '비밀번호가 일치하지 않습니다.',
            ],
            'email' => [
                'valid_email' => '올바른 이메일 형식이 아닙니다.',
                'is_unique'   => '이미 등록된 이메일입니다.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $model = new UserInfoModel();
        $model->insert([
            'state'    => 1,
            'id'       => $this->request->getPost('id'),
            'password' => $this->request->getPost('password'),
            'email'    => $this->request->getPost('email'),
            'phone'    => $this->request->getPost('phone') ?? '',
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => '회원가입이 완료되었습니다. 로그인해주세요.',
        ]);
    }

    /**
     * 로그인 처리 (AJAX POST → JSON 응답)
     * 아이디 저장: saved_id 쿠키 (30일)
     * 상시 로그인: HMAC 서명된 remember_me 쿠키 (30일)
     */
    public function login(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'id'       => 'required',
            'password' => 'required',
        ];

        $messages = [
            'id'       => ['required' => '아이디를 입력해주세요.'],
            'password' => ['required' => '비밀번호를 입력해주세요.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $model = new UserInfoModel();
        $user  = $model->findByLoginId($this->request->getPost('id'));

        // 아이디 없음 또는 비밀번호 불일치 — 동일 메시지로 계정 존재 여부 노출 방지
        if (!$user || !password_verify($this->request->getPost('password'), $user['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => ['id' => '아이디 또는 비밀번호가 올바르지 않습니다.'],
            ]);
        }

        // 세션 고정 공격 방지: 로그인 시 세션 ID 재발급
        session()->regenerate();
        session()->set([
            'user.idx'   => $user['idx'],
            'user.id'    => $user['id'],
            'user.email' => $user['email'],
        ]);

        // 아이디 저장: save_id 체크 시 쿠키에 아이디만 보관 (비밀번호 X)
        if ($this->request->getPost('save_id')) {
            $this->response->setCookie(new Cookie('saved_id', $user['id'], [
                'expires'  => time() + 30 * 24 * 3600,
                'httponly' => false, // 서버 PHP에서만 사용, JS 접근 불필요
                'samesite' => 'Lax',
            ]));
        } else {
            $this->response->deleteCookie('saved_id');
        }

        // 상시 로그인: HMAC 서명 토큰을 HttpOnly 쿠키에 저장
        if ($this->request->getPost('keep_login')) {
            $expire = time() + 30 * 24 * 3600;
            $secret = hash_hmac('sha256', 'remember_me_secret', base_url());
            $sig    = hash_hmac('sha256', $user['idx'] . '|' . $expire, $secret);
            $token  = base64_encode($user['idx'] . '|' . $expire . '|' . $sig);

            $this->response->setCookie(new Cookie('remember_me', $token, [
                'expires'  => $expire,
                'httponly' => true,  // JS 접근 차단으로 XSS 방어
                'samesite' => 'Lax',
            ]));
        } else {
            $this->response->deleteCookie('remember_me');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => '로그인 되었습니다!',
            'user_id' => $user['id'],
        ]);
    }

    /**
     * 로그아웃: 세션 파기 + remember_me 쿠키 삭제 후 메인으로 이동
     *
     * [버그 수정] 기존 코드는 `$this->response->deleteCookie(...)`로 쿠키 삭제를
     * 걸어둔 뒤 `redirect()->to('/')`를 반환했는데, `redirect()`가 만드는
     * RedirectResponse는 $this->response와는 별개의 새 인스턴스라서 그 위에 걸어둔
     * deleteCookie가 실제 응답에는 반영되지 않고 유실됐다(CI4의
     * RedirectResponse::withCookies() 문서에 명시된 바로 그 문제).
     * 그 결과 "상시 로그인"으로 로그인했던 사용자는 remember_me 쿠키가 브라우저에
     * 그대로 남아있게 되고, 리다이렉트 목적지(/)로 이동하는 순간 전역 필터
     * RememberMeFilter가 그 쿠키로 세션을 즉시 재복원시켜버려 로그아웃 버튼을
     * 눌러도 로그아웃이 안 되는 것처럼 보였다.
     * → 실제로 브라우저에 전송되는 RedirectResponse 객체에 직접 deleteCookie를
     *   걸어서, 쿠키 삭제가 응답에 확실히 포함되도록 수정.
     */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();

        $response = redirect()->to('/');
        $response->deleteCookie('remember_me');

        return $response;
    }
}
