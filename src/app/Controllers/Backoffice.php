<?php

namespace App\Controllers;

use App\Models\BackofficeUserModel;
use App\Models\UserInfoModel;

/**
 * 백오피스 컨트롤러
 * /backoffice/* 모든 페이지를 처리한다.
 */
class Backoffice extends BaseController
{
    // ----------------------------------------------------------------
    // 내부 헬퍼
    // ----------------------------------------------------------------

    private function isLoggedIn(): bool
    {
        return (bool) session()->get('backoffice.idx');
    }

    /**
     * 모든 뷰에 공통으로 전달할 데이터 반환
     */
    private function base(string $pageTitle, array $extra = []): array
    {
        return array_merge([
            'page_title'  => $pageTitle,
            'admin'       => [
                'idx'   => session()->get('backoffice.idx'),
                'id'    => session()->get('backoffice.id'),
                'level' => session()->get('backoffice.level'),
            ],
            'current_uri' => '/' . uri_string(),
        ], $extra);
    }

    // ----------------------------------------------------------------
    // 진입점 — 로그인 여부에 따라 분기
    // ----------------------------------------------------------------

    public function index()
    {
        return $this->isLoggedIn()
            ? redirect()->to('/backoffice/dashboard')
            : redirect()->to('/backoffice/login');
    }

    // ----------------------------------------------------------------
    // 인증 (로그인 필터 미적용)
    // ----------------------------------------------------------------

    /** GET /backoffice/login */
    public function login(): string
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('/backoffice/dashboard');
        }
        return view('backoffice/login', $this->base('로그인'));
    }

    /** POST /backoffice/login */
    public function doLogin()
    {
        $model = new BackofficeUserModel();
        $admin = $model->findByLoginId((string) $this->request->getPost('id'));

        if (!$admin || !password_verify((string) $this->request->getPost('password'), $admin['password'])) {
            return view('backoffice/login', $this->base('로그인', [
                'login_error' => '아이디 또는 비밀번호가 올바르지 않습니다.',
            ]));
        }

        // 세션 고정 공격 방지
        session()->regenerate();
        session()->set([
            'backoffice.idx'   => $admin['idx'],
            'backoffice.id'    => $admin['id'],
            'backoffice.level' => $admin['level'],
        ]);

        return redirect()->to('/backoffice/dashboard');
    }

    /** GET /backoffice/logout */
    public function logout()
    {
        session()->remove(['backoffice.idx', 'backoffice.id', 'backoffice.level']);
        return redirect()->to('/backoffice/login');
    }

    /** GET /backoffice/add-admin */
    public function addAdmin(): string
    {
        return view('backoffice/add_admin', $this->base('관리자 추가'));
    }

    /** POST /backoffice/add-admin */
    public function doAddAdmin()
    {
        $rules = [
            'id'             => 'required|min_length[4]|max_length[50]|is_unique[backoffice_user.id]',
            'password'       => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'level'          => 'required|in_list[1,2]',
        ];

        $messages = [
            'id'             => ['is_unique' => '이미 사용 중인 아이디입니다.'],
            'password_confirm' => ['matches' => '비밀번호가 일치하지 않습니다.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return view('backoffice/add_admin', $this->base('관리자 추가', [
                'form_errors' => $this->validator->getErrors(),
                'old'         => $this->request->getPost(),
            ]));
        }

        $model = new BackofficeUserModel();
        $model->insert([
            'state'   => 1,
            'id'      => $this->request->getPost('id'),
            'password' => $this->request->getPost('password'),
            'level'   => (int) $this->request->getPost('level'),
            'plus_ip' => $this->request->getIPAddress(),
        ]);

        session()->setFlashdata('success', '관리자 계정이 생성되었습니다. 로그인해주세요.');
        return redirect()->to('/backoffice/login');
    }

    // ----------------------------------------------------------------
    // 보호 페이지 (BackofficeAuthFilter 적용)
    // ----------------------------------------------------------------

    /** GET /backoffice/dashboard */
    public function dashboard(): string
    {
        // 대시보드용 간단 통계
        $userModel = new UserInfoModel();
        $stats = [
            'total_members' => $userModel->where('state', 1)->countAllResults(),
            'total_admins'  => (new BackofficeUserModel())->where('state', 1)->countAllResults(),
        ];

        return view('backoffice/dashboard', $this->base('대시보드', ['stats' => $stats]));
    }

    // 각 관리 페이지 — 미구현 시 placeholder 뷰 사용
    public function restaurants(): string
    {
        return view('backoffice/placeholder', $this->base('맛집 관리'));
    }

    public function spots(): string
    {
        return view('backoffice/placeholder', $this->base('관광지 관리'));
    }

    public function festivals(): string
    {
        return view('backoffice/placeholder', $this->base('주요행사 및 축제관리'));
    }

    public function errorLogs(): string
    {
        return view('backoffice/placeholder', $this->base('에러 로그'));
    }

    public function siteConfig(): string
    {
        return view('backoffice/placeholder', $this->base('헤더 및 Footer 수정'));
    }
}
