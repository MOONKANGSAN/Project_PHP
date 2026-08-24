<?php

namespace App\Controllers;

use App\Models\EventBannerModel;
use App\Models\SiteEventModel;

/**
 * 백오피스 — 이벤트 배너 관리 컨트롤러
 * /backoffice/event-banners/* 요청을 처리한다.
 * 메인 페이지 이벤트 배너 영역에 노출할, site_event와 연결된 배너 이미지를 관리한다.
 */
class BackofficeEventBanner extends BaseController
{
    private EventBannerModel $model;
    private SiteEventModel   $eventModel;

    /** 이미지 저장 경로 (public/ 기준) */
    private const UPLOAD_DIR = 'uploads/event_banners/';

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model      = new EventBannerModel();
        $this->eventModel = new SiteEventModel();
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
     * 배너에 연결할 수 있는 이벤트 목록 (기본적으로 노출 중인 이벤트만)
     */
    private function getSelectableEvents(): array
    {
        return $this->eventModel->where('state', 1)
                                 ->orderBy('idx', 'DESC')
                                 ->findAll();
    }

    /**
     * GET /backoffice/event-banners
     * 이벤트 배너 목록
     */
    public function list(): string
    {
        $state = (string) ($this->request->getGet('state') ?? '');
        $items = $this->model->getList($state);

        return view('backoffice/event_banner/list', $this->base('이벤트 배너 관리', [
            'items' => $items,
            'state' => $state,
        ]));
    }

    /**
     * GET /backoffice/event-banners/register
     * 이벤트 배너 등록 폼
     */
    public function register(): string
    {
        return view('backoffice/event_banner/form', $this->base('이벤트 배너 등록', [
            'item'   => null,
            'events' => $this->getSelectableEvents(),
            'mode'   => 'register',
        ]));
    }

    /**
     * POST /backoffice/event-banners/register
     * 이벤트 배너 저장
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'event_idx' => 'required|is_natural_no_zero',
            'state'     => 'required|in_list[0,1]',
            'image'     => 'uploaded[image]|max_size[image,5120]|ext_in[image,jpg,jpeg,png,webp,gif]|mime_in[image,image/jpg,image/jpeg,image/png,image/gif,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        if (!$this->eventModel->find((int) $this->request->getPost('event_idx'))) {
            return redirect()->back()->withInput()
                ->with('form_errors', ['event_idx' => '존재하지 않는 이벤트입니다.']);
        }

        $imageUrl = $this->uploadImage();
        if ($imageUrl === null) {
            return redirect()->back()->withInput()
                ->with('form_errors', ['image' => '이미지 업로드에 실패했습니다.']);
        }

        $this->model->insert([
            'state'          => $this->request->getPost('state'),
            'event_idx'      => (int) $this->request->getPost('event_idx'),
            'image_url'      => $imageUrl,
            'image_position' => $this->request->getPost('image_position') ?: '50 50',
            'sort_order'     => (int) ($this->request->getPost('sort_order') ?: 100),
            'reg_id'         => session()->get('backoffice.id'),
            'reg_date'       => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', '이벤트 배너가 등록되었습니다.');
        return redirect()->to('/backoffice/event-banners');
    }

    /**
     * GET /backoffice/event-banners/(:num)/edit
     * 이벤트 배너 수정 폼
     */
    public function edit(int $idx): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 배너입니다.');
            return redirect()->to('/backoffice/event-banners');
        }

        $events = $this->getSelectableEvents();
        // 현재 연결된 이벤트가 비활성 상태라도 선택지에 그대로 포함시켜 보여준다
        if (!in_array((int) $item['event_idx'], array_column($events, 'idx'), true)) {
            $linked = $this->eventModel->find($item['event_idx']);
            if ($linked) {
                array_unshift($events, $linked);
            }
        }

        return view('backoffice/event_banner/form', $this->base('이벤트 배너 수정', [
            'item'   => $item,
            'events' => $events,
            'mode'   => 'edit',
        ]));
    }

    /**
     * POST /backoffice/event-banners/(:num)/edit
     * 이벤트 배너 수정 저장
     */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 배너입니다.');
            return redirect()->to('/backoffice/event-banners');
        }

        $file = $this->request->getFile('image');
        $hasNewImage = $file && $file->isValid() && !$file->hasMoved();

        $rules = [
            'event_idx' => 'required|is_natural_no_zero',
            'state'     => 'required|in_list[0,1]',
        ];
        if ($hasNewImage) {
            $rules['image'] = 'max_size[image,5120]|ext_in[image,jpg,jpeg,png,webp,gif]|mime_in[image,image/jpg,image/jpeg,image/png,image/gif,image/webp]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        if (!$this->eventModel->find((int) $this->request->getPost('event_idx'))) {
            return redirect()->back()->withInput()
                ->with('form_errors', ['event_idx' => '존재하지 않는 이벤트입니다.']);
        }

        $updateData = [
            'state'          => $this->request->getPost('state'),
            'event_idx'      => (int) $this->request->getPost('event_idx'),
            'image_position' => $this->request->getPost('image_position') ?: '50 50',
            'sort_order'     => (int) ($this->request->getPost('sort_order') ?: 100),
            'edit_date'      => date('Y-m-d H:i:s'),
        ];

        if ($hasNewImage) {
            $imageUrl = $this->uploadImage();
            if ($imageUrl !== null) {
                // 기존 이미지 파일 삭제
                $this->deleteImageFile($item['image_url']);
                $updateData['image_url'] = $imageUrl;
            }
        }

        $this->model->update($idx, $updateData);

        session()->setFlashdata('success', '이벤트 배너가 수정되었습니다.');
        return redirect()->to('/backoffice/event-banners');
    }

    /**
     * POST /backoffice/event-banners/(:num)/state
     * 활성/비활성 토글
     */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item) {
            $this->model->update($idx, [
                'state'     => $item['state'] ? 0 : 1,
                'edit_date' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->back();
    }

    /**
     * POST /backoffice/event-banners/(:num)/delete
     * 이벤트 배너 삭제 (이미지 파일도 함께 제거)
     */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item) {
            $this->deleteImageFile($item['image_url']);
            $this->model->delete($idx);
            session()->setFlashdata('success', '이벤트 배너가 삭제되었습니다.');
        }

        return redirect()->to('/backoffice/event-banners');
    }

    // ----------------------------------------------------------------
    // 내부 헬퍼
    // ----------------------------------------------------------------

    /**
     * 업로드된 이미지를 저장하고 URL 경로를 반환
     */
    private function uploadImage(): ?string
    {
        $file      = $this->request->getFile('image');
        $uploadDir = FCPATH . self::UPLOAD_DIR;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return '/' . self::UPLOAD_DIR . $newName;
    }

    /**
     * 이미지 URL로부터 실제 파일 삭제
     */
    private function deleteImageFile(string $imageUrl): void
    {
        $filePath = FCPATH . ltrim($imageUrl, '/');
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
