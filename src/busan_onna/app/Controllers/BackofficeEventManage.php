<?php

namespace App\Controllers;

use App\Models\SiteEventModel;

/**
 * 백오피스 — 이벤트 관리 컨트롤러
 * '사이트 이벤트'(BackofficeSiteEvent)에서는 이벤트의 기본 정보(제목/기간/대표이미지 등)를
 * 등록·수정하고, 이 컨트롤러는 개별 이벤트의 운영 데이터(방문 후기, 좋아요·참여 집계 등)를
 * 이벤트 전용 화면에서 관리한다.
 */
class BackofficeEventManage extends BaseController
{
    private SiteEventModel $model;

    /** 전용 관리 화면을 제공하는 이벤트의 view_file 목록 */
    private const MANAGED_VIEW_FILES = ['view_1', 'view_2'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new SiteEventModel();
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
        ], $extra);
    }

    /**
     * GET /backoffice/event-manage
     * 전용 관리 화면이 있는 이벤트 목록
     */
    public function index(): string
    {
        $items = $this->model->whereIn('view_file', self::MANAGED_VIEW_FILES)
                              ->where('state !=', 9)
                              ->orderBy('idx', 'ASC')
                              ->findAll();

        return view('backoffice/event_manage/index', $this->base('이벤트 관리', [
            'items' => $items,
        ]));
    }

    /**
     * GET /backoffice/event-manage/(:num)
     * 이벤트별 전용 관리 화면 (view_file에 따라 분기)
     */
    public function manage(int $idx): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);

        if (!$item || (int) $item['state'] === 9 || !in_array($item['view_file'], self::MANAGED_VIEW_FILES, true)) {
            session()->setFlashdata('error', '관리할 수 없는 이벤트입니다.');
            return redirect()->to('/backoffice/event-manage');
        }

        return view('backoffice/event_manage/' . $item['view_file'], $this->base($item['title'] . ' 관리', [
            'event' => $item,
        ]));
    }
}
