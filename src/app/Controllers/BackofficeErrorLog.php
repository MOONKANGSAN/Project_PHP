<?php

namespace App\Controllers;

use App\Models\ErrorLogModel;

/**
 * 백오피스 — 에러 로그 관리 컨트롤러
 * /backoffice/error-logs/* 요청을 처리한다.
 */
class BackofficeErrorLog extends BaseController
{
    private ErrorLogModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new ErrorLogModel();
    }

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
            'types'       => ErrorLogModel::TYPES,
            'typeColors'  => ErrorLogModel::TYPE_COLORS,
        ], $extra);
    }

    /**
     * GET /backoffice/error-logs
     * 에러 로그 목록
     */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q')     ?? '');
        $type  = (string) ($this->request->getGet('type')  ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        // 미해결 건수 (상단 요약용)
        $unresolvedCount = $this->model->where('state', 0)->countAllResults();

        $items = $this->model->getList($q, $type, $state);

        return view('backoffice/error_log/list', $this->base('에러 로그', [
            'items'          => $items,
            'pager'          => $this->model->pager,
            'q'              => $q,
            'type'           => $type,
            'state'          => $state,
            'unresolvedCount'=> $unresolvedCount,
        ]));
    }

    /**
     * POST /backoffice/error-logs/(:num)/state
     * 해결/미해결 토글 (AJAX JSON 응답)
     */
    public function toggleState(int $idx): \CodeIgniter\HTTP\ResponseInterface
    {
        $item = $this->model->find($idx);
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => '존재하지 않는 로그입니다.']);
        }

        $result = $this->model->toggleState($idx);

        return $this->response->setJSON([
            'success'    => true,
            'state'      => $result['state'],
            'csrf_name'  => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * POST /backoffice/error-logs/(:num)/feedback
     * 피드백(해결내용) 저장
     */
    public function saveFeedback(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 로그입니다.');
            return redirect()->back();
        }

        $feedback = (string) ($this->request->getPost('feedback') ?? '');
        $this->model->saveFeedback($idx, $feedback);

        session()->setFlashdata('success', '피드백이 저장되었습니다.');
        return redirect()->back();
    }
}
