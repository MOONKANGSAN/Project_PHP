<?php

namespace App\Controllers;

use App\Models\FaqModel;

/**
 * 백오피스 — FAQ 관리 컨트롤러
 * /backoffice/faqs/* 요청을 처리한다.
 */
class BackofficeFaq extends BaseController
{
    private FaqModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new FaqModel();
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
            'types'       => FaqModel::TYPES,
        ], $extra);
    }

    /**
     * GET /backoffice/faqs
     * FAQ 목록 (검색·카테고리·상태 필터)
     */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q')     ?? '');
        $type  = (string) ($this->request->getGet('type')  ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $type, $state);

        return view('backoffice/faq/list', $this->base('FAQs 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'type'  => $type,
            'state' => $state,
        ]));
    }

    /**
     * GET /backoffice/faqs/register
     * FAQ 등록 폼
     */
    public function register(): string
    {
        return view('backoffice/faq/form', $this->base('FAQ 등록', [
            'item'      => null,
            'mode'      => 'register',
            'extra_css' => ['https://uicdn.toast.com/editor/latest/toastui-editor.min.css'],
        ]));
    }

    /**
     * POST /backoffice/faqs/register
     * FAQ 저장
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'title'    => 'required|max_length[200]',
            'content'  => 'required',
            'faq_type' => 'required|in_list[1,2,3,4]',
            'state'    => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'state'      => $this->request->getPost('state'),
            'faq_type'   => $this->request->getPost('faq_type'),
            'title'      => $this->request->getPost('title'),
            'content'    => $this->request->getPost('content'),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 100),
            'reg_id'     => session()->get('backoffice.id'),
            'reg_date'   => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', 'FAQ가 등록되었습니다.');
        return redirect()->to('/backoffice/faqs');
    }

    /**
     * GET /backoffice/faqs/(:num)/edit
     * FAQ 수정 폼
     */
    public function edit(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 FAQ입니다.');
            return redirect()->to('/backoffice/faqs');
        }

        return view('backoffice/faq/form', $this->base('FAQ 수정', [
            'item'      => $item,
            'mode'      => 'edit',
            'extra_css' => ['https://uicdn.toast.com/editor/latest/toastui-editor.min.css'],
        ]));
    }

    /**
     * POST /backoffice/faqs/(:num)/edit
     * FAQ 수정 저장
     */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 FAQ입니다.');
            return redirect()->to('/backoffice/faqs');
        }

        $rules = [
            'title'    => 'required|max_length[200]',
            'content'  => 'required',
            'faq_type' => 'required|in_list[1,2,3,4]',
            'state'    => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $this->model->update($idx, [
            'state'      => $this->request->getPost('state'),
            'faq_type'   => $this->request->getPost('faq_type'),
            'title'      => $this->request->getPost('title'),
            'content'    => $this->request->getPost('content'),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 100),
            'edit_date'  => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', 'FAQ가 수정되었습니다.');
        return redirect()->to('/backoffice/faqs');
    }

    /**
     * POST /backoffice/faqs/(:num)/state
     * 활성/비활성 토글 (AJAX 요청 시 JSON 응답)
     */
    public function toggleState(int $idx): mixed
    {
        $item = $this->model->find($idx);
        if (!$item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => '존재하지 않는 FAQ입니다.']);
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
                'success'    => true,
                'state'      => $newState,
                'csrf_name'  => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * POST /backoffice/faqs/(:num)/delete
     * 소프트 삭제 (state = 9, 휴지통으로 이동)
     */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 FAQ입니다.');
            return redirect()->back();
        }

        $this->model->softDelete($idx);
        session()->setFlashdata('success', "FAQ [{$item['title']}]이 휴지통으로 이동되었습니다.");
        return redirect()->to('/backoffice/faqs');
    }
}
