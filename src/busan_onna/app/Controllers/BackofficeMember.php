<?php

namespace App\Controllers;

use App\Models\UserInfoModel;
use App\Models\UserInfoState5Model;

/**
 * 백오피스 — 회원 관리 컨트롤러
 * /backoffice/members/* 요청을 처리한다.
 */
class BackofficeMember extends BaseController
{
    private UserInfoModel       $model;
    private UserInfoState5Model $state5Model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model       = new UserInfoModel();
        $this->state5Model = new UserInfoState5Model();
    }

    // 공통 뷰 데이터 구성
    private function base(string $title, array $extra = []): array
    {
        return array_merge([
            'page_title'  => $title,
            'admin'       => [
                'idx'   => session()->get('backoffice.idx'),
                'id'    => session()->get('backoffice.id'),
                'level' => session()->get('backoffice.level'),
            ],
            'current_uri' => '/' . uri_string(),
        ], $extra);
    }

    /**
     * GET /backoffice/members
     * 회원 목록 조회 (검색·상태 필터 + 페이지네이션)
     */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q') ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $state);

        return view('backoffice/member/list', $this->base('회원 정보 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'state' => $state,
        ]));
    }

    /**
     * POST /backoffice/members/(:num)/state
     * 회원 활성/비활성 상태 토글
     */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item) {
            $this->model->update($idx, ['state' => $item['state'] ? 0 : 1]);
            $label = $item['state'] ? '비활성' : '활성';
            session()->setFlashdata('success', "회원({$item['id']})이 {$label} 처리되었습니다.");
        }

        return redirect()->back();
    }

    /**
     * POST /backoffice/members/(:num)/login-as
     * 관리자가 해당 회원의 세션으로 전환하여 메인 서비스에 로그인
     * — 기존 관리자 세션은 유지되고 user.* 세션만 덮어씀
     */
    public function loginAs(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 회원입니다.');
            return redirect()->back();
        }

        session()->set([
            'user.idx'            => $item['idx'],
            'user.id'             => $item['id'],
            'user.email'          => $item['email'],
            // 관리자가 유저로 접속 중임을 표시 (원래 계정으로 복귀할 때 사용)
            'admin_impersonating' => true,
        ]);

        session()->setFlashdata('success', "[{$item['id']}] 계정으로 로그인되었습니다. 메인 페이지로 이동합니다.");
        return redirect()->to('/');
    }

    /**
     * POST /backoffice/members/(:num)/withdraw
     * 회원 탈퇴 처리: user_info.state = 5 + user_info_state5 이력 INSERT
     */
    public function withdraw(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 회원입니다.');
            return redirect()->back();
        }

        if ((int) $item['state'] === 5) {
            session()->setFlashdata('error', '이미 탈퇴 처리된 회원입니다.');
            return redirect()->back();
        }

        // 탈퇴 사유 최대 30자 처리 (프론트 maxlength와 이중 방어)
        $reason = mb_substr(trim((string) ($this->request->getPost('reason') ?? '')), 0, 30);

        // user_info 상태를 5(탈퇴)로 변경
        $this->model->update($idx, ['state' => 5]);

        // 탈퇴 이력 기록
        $this->state5Model->recordWithdraw($idx, $reason !== '' ? $reason : null);

        session()->setFlashdata('success', "[{$item['id']}] 회원이 탈퇴 처리되었습니다.");
        return redirect()->back();
    }

    /**
     * POST /backoffice/members/(:num)/restore
     * 탈퇴회원 복원: user_info.state = 1 + user_info_state5.state = 0
     */
    public function restore(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 회원입니다.');
            return redirect()->back();
        }

        if ((int) $item['state'] !== 5) {
            session()->setFlashdata('error', '탈퇴 상태가 아닌 회원입니다.');
            return redirect()->back();
        }

        // user_info 상태를 1(활성)로 복원
        $this->model->update($idx, ['state' => 1]);

        // 탈퇴 이력을 복원(state=0)으로 변경
        $this->state5Model->recordRestore($idx);

        session()->setFlashdata('success', "[{$item['id']}] 회원이 복원되었습니다.");
        return redirect()->back();
    }

    /**
     * GET /backoffice/withdrawn-members
     * 탈퇴회원 목록 조회 (user_info.state=5, 탈퇴 이력 JOIN)
     */
    public function withdrawnList(): string
    {
        $q     = (string) ($this->request->getGet('q') ?? '');
        $items = $this->model->getWithdrawnList($q);

        // 탈퇴 이력(사유)을 user_info_idx 기준으로 맵핑
        $state5Map = [];
        if (!empty($items)) {
            $idxList = array_column($items, 'idx');
            $logs    = $this->state5Model->whereIn('user_info_idx', $idxList)
                                         ->where('state', 1)
                                         ->orderBy('idx', 'DESC')
                                         ->findAll();
            // 회원 idx당 가장 최근 이력 1건만 보관
            foreach ($logs as $log) {
                $key = $log['user_info_idx'];
                if (!isset($state5Map[$key])) {
                    $state5Map[$key] = $log;
                }
            }
        }

        return view('backoffice/member/withdrawn_list', $this->base('탈퇴회원 관리', [
            'items'      => $items,
            'pager'      => $this->model->pager,
            'q'          => $q,
            'state5Map'  => $state5Map,
        ]));
    }

    /**
     * POST /backoffice/members/(:num)/reset-password
     * 비밀번호를 0000으로 초기화
     */
    public function resetPassword(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 회원입니다.');
            return redirect()->back();
        }

        // 비밀번호를 0000으로 초기화 (beforeUpdate 콜백으로 자동 bcrypt 해싱됨)
        $this->model->update($idx, ['password' => '0000']);

        session()->setFlashdata('success', "[{$item['id']}] 비밀번호가 <strong>0000</strong>으로 초기화되었습니다.");
        return redirect()->back();
    }
}
