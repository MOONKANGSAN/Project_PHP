<?php

namespace App\Controllers;

use App\Models\InquiryModel;

/**
 * 백오피스 — 고객문의 관리 컨트롤러
 * /backoffice/inquiries/* 요청을 처리한다.
 */
class BackofficeInquiry extends BaseController
{
    private InquiryModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new InquiryModel();
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
            'types'       => InquiryModel::TYPES,
            'states'      => InquiryModel::STATES,
        ], $extra);
    }

    /**
     * GET /backoffice/inquiries
     * 고객문의 목록 (검색·유형·상태 필터 + 페이지네이션)
     */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q')     ?? '');
        $type  = (string) ($this->request->getGet('type')  ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $type, $state);

        return view('backoffice/inquiry/list', $this->base('고객문의', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'type'  => $type,
            'state' => $state,
        ]));
    }

    /**
     * GET /backoffice/inquiries/(:num)
     * 문의 상세 + 답변 작성 폼
     */
    public function view(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 문의입니다.');
            return redirect()->to('/backoffice/inquiries');
        }

        return view('backoffice/inquiry/view', $this->base('문의 상세', [
            'item' => $item,
        ]));
    }

    /**
     * POST /backoffice/inquiries/(:num)/answer
     * 답변 저장 (저장 시 state → 2 자동 변경)
     */
    public function saveAnswer(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 문의입니다.');
            return redirect()->to('/backoffice/inquiries');
        }

        $answer = trim((string) ($this->request->getPost('answer') ?? ''));
        if ($answer === '') {
            session()->setFlashdata('error', '답변 내용을 입력해주세요.');
            return redirect()->back();
        }

        $this->model->saveAnswer($idx, $answer);

        session()->setFlashdata('success', '답변이 저장되었습니다.');
        return redirect()->to("/backoffice/inquiries/{$idx}");
    }

    /**
     * POST /backoffice/inquiries/(:num)/answer/delete
     * 답변 삭제 (state → 1 접수로 되돌림)
     */
    public function deleteAnswer(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 문의입니다.');
            return redirect()->to('/backoffice/inquiries');
        }

        $this->model->deleteAnswer($idx);

        session()->setFlashdata('success', '답변이 삭제되었습니다.');
        return redirect()->to("/backoffice/inquiries/{$idx}");
    }

    /**
     * POST /backoffice/inquiries/(:num)/delete
     * 소프트 삭제 (state = 9, 휴지통으로 이동)
     */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 문의입니다.');
            return redirect()->back();
        }

        $this->model->softDelete($idx);
        session()->setFlashdata('success', "문의 [{$item['title']}]이 휴지통으로 이동되었습니다.");
        return redirect()->to('/backoffice/inquiries');
    }

    /**
     * POST /backoffice/inquiries/(:num)/state
     * 문의 상태 변경 (숨김/접수 토글)
     */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item) {
            // 답변완료 상태는 숨김/접수만 토글, 답변완료는 답변 저장으로만 변경
            $newState = (int) $item['state'] === 0 ? 1 : 0;
            $this->model->update($idx, ['state' => $newState]);
            $label = $newState === 1 ? '접수' : '숨김';
            session()->setFlashdata('success', "문의가 [{$label}] 상태로 변경되었습니다.");
        }

        return redirect()->back();
    }
}
