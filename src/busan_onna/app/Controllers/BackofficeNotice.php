<?php

namespace App\Controllers;

use App\Models\NoticeModel;

/**
 * 백오피스 — 공지사항 관리 컨트롤러
 * /backoffice/notices/* 요청을 처리한다.
 */
class BackofficeNotice extends BaseController
{
    private NoticeModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new NoticeModel();
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
     * GET /backoffice/notices
     * 공지사항 목록 (검색·상태·고정 필터)
     */
    public function list(): string
    {
        $q      = (string) ($this->request->getGet('q')      ?? '');
        $state  = (string) ($this->request->getGet('state')  ?? '');
        $pinned = (string) ($this->request->getGet('pinned') ?? '');

        $items = $this->model->getList($q, $state, $pinned);

        return view('backoffice/notice/list', $this->base('공지사항 관리', [
            'items'  => $items,
            'pager'  => $this->model->pager,
            'q'      => $q,
            'state'  => $state,
            'pinned' => $pinned,
        ]));
    }

    /**
     * GET /backoffice/notices/register
     * 공지사항 등록 폼
     */
    public function register(): string
    {
        return view('backoffice/notice/form', $this->base('공지사항 등록', [
            'item'      => null,
            'mode'      => 'register',
            'extra_css' => ['https://uicdn.toast.com/editor/latest/toastui-editor.min.css'],
        ]));
    }

    /**
     * POST /backoffice/notices/register
     * 공지사항 저장
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'title'   => 'required|max_length[200]',
            'content' => 'required',
            'state'   => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'state'     => $this->request->getPost('state'),
            'is_pinned' => (int) (bool) $this->request->getPost('is_pinned'),
            'title'     => $this->request->getPost('title'),
            'content'   => $this->request->getPost('content'),
            'reg_id'    => session()->get('backoffice.id'),
            'reg_date'  => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', '공지사항이 등록되었습니다.');
        return redirect()->to('/backoffice/notices');
    }

    /**
     * GET /backoffice/notices/(:num)/edit
     * 공지사항 수정 폼
     */
    public function edit(int $idx): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 공지사항입니다.');
            return redirect()->to('/backoffice/notices');
        }

        return view('backoffice/notice/form', $this->base('공지사항 수정', [
            'item'      => $item,
            'mode'      => 'edit',
            'extra_css' => ['https://uicdn.toast.com/editor/latest/toastui-editor.min.css'],
        ]));
    }

    /**
     * POST /backoffice/notices/(:num)/edit
     * 공지사항 수정 저장
     */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 공지사항입니다.');
            return redirect()->to('/backoffice/notices');
        }

        $rules = [
            'title'   => 'required|max_length[200]',
            'content' => 'required',
            'state'   => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $this->model->update($idx, [
            'state'     => $this->request->getPost('state'),
            'is_pinned' => (int) (bool) $this->request->getPost('is_pinned'),
            'title'     => $this->request->getPost('title'),
            'content'   => $this->request->getPost('content'),
            'edit_date' => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', '공지사항이 수정되었습니다.');
        return redirect()->to('/backoffice/notices');
    }

    /**
     * POST /backoffice/notices/(:num)/state
     * 활성/비활성 토글 (AJAX)
     */
    public function toggleState(int $idx): mixed
    {
        $item = $this->model->find($idx);
        if (!$item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => '존재하지 않는 공지사항입니다.']);
            }
            return redirect()->back();
        }

        $newState = (int) $item['state'] === 1 ? 0 : 1;
        $this->model->update($idx, [
            'state'     => $newState,
            'edit_date' => date('Y-m-d H:i:s'),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'   => true,
                'state'     => $newState,
                'csrf_name' => csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * POST /backoffice/notices/(:num)/pin
     * 상단 고정/해제 토글 (AJAX)
     */
    public function togglePin(int $idx): mixed
    {
        $item = $this->model->find($idx);
        if (!$item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => '존재하지 않는 공지사항입니다.']);
            }
            return redirect()->back();
        }

        $newPinned = (int) $item['is_pinned'] === 1 ? 0 : 1;
        $this->model->update($idx, [
            'is_pinned' => $newPinned,
            'edit_date' => date('Y-m-d H:i:s'),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'   => true,
                'is_pinned' => $newPinned,
                'csrf_name' => csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * POST /backoffice/notices/(:num)/delete
     * 소프트 삭제 (state = 9)
     */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 공지사항입니다.');
            return redirect()->back();
        }

        $this->model->softDelete($idx);
        session()->setFlashdata('success', "공지사항 [{$item['title']}]이 삭제되었습니다.");
        return redirect()->to('/backoffice/notices');
    }
}
