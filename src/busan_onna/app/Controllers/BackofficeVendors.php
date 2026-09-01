<?php

namespace App\Controllers;

use App\Models\VendorModel;

/**
 * 백오피스 — 판매자 관리 컨트롤러
 * /backoffice/vendors/* 요청을 처리한다.
 * state: 0=대기, 1=승인, 2=거절
 */
class BackofficeVendors extends BaseController
{
    private VendorModel $model;

    /** 판매자 상태 레이블 */
    public const STATE_LABELS = [0 => '대기', 1 => '승인', 2 => '거절'];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $req,
        \CodeIgniter\HTTP\ResponseInterface $res,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($req, $res, $logger);
        $this->model = new VendorModel();
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
     * GET /backoffice/vendors
     * 판매자 목록 (상태 필터)
     */
    public function list(): string
    {
        $state   = trim($this->request->getGet('state') ?? '');
        $vendors = $this->model->getList($state);

        return view('backoffice/vendors/list', $this->base('판매자 관리', [
            'vendors' => $vendors,
            'pager'   => $this->model->pager,
            'state'   => $state,
            'labels'  => self::STATE_LABELS,
        ]));
    }

    /**
     * GET /backoffice/vendors/(:num)
     * 판매자 상세 조회
     */
    public function detail(int $idx): string
    {
        $vendor = $this->model->find($idx);

        return view('backoffice/vendors/detail', $this->base('판매자 상세', [
            'vendor' => $vendor,
            'labels' => self::STATE_LABELS,
        ]));
    }

    /**
     * POST /backoffice/vendors/(:num)/approve
     * 판매자 승인 처리 (state → 1)
     */
    public function approve(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->update($idx, ['state' => 1]);
        return redirect()->to("/backoffice/vendors/{$idx}")->with('success', '판매자가 승인되었습니다.');
    }

    /**
     * POST /backoffice/vendors/(:num)/reject
     * 판매자 거절 처리 (state → 2)
     */
    public function reject(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->update($idx, ['state' => 2]);
        return redirect()->to("/backoffice/vendors/{$idx}")->with('success', '판매자가 거절되었습니다.');
    }
}
