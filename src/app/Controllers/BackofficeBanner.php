<?php

namespace App\Controllers;

use App\Models\MainBannerModel;

/**
 * 백오피스 — 배너 관리 컨트롤러
 * /backoffice/banners/* 요청을 처리한다.
 */
class BackofficeBanner extends BaseController
{
    private MainBannerModel $model;

    /** 이미지 저장 경로 (public/ 기준) */
    private const UPLOAD_DIR = 'uploads/banners/';

    /** 허용 이미지 확장자 */
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new MainBannerModel();
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
     * GET /backoffice/banners
     * 배너 목록
     */
    public function list(): string
    {
        $state = (string) ($this->request->getGet('state') ?? '');
        $items = $this->model->getList($state);

        return view('backoffice/banner/list', $this->base('배너 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'state' => $state,
        ]));
    }

    /**
     * GET /backoffice/banners/register
     * 배너 등록 폼
     */
    public function register(): string
    {
        return view('backoffice/banner/form', $this->base('배너 등록', [
            'item' => null,
            'mode' => 'register',
        ]));
    }

    /**
     * POST /backoffice/banners/register
     * 배너 저장
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'title'    => 'permit_empty|max_length[200]',
            'subtitle' => 'permit_empty|max_length[300]',
            'alt_text' => 'permit_empty|max_length[200]',
            'link_url' => 'permit_empty|max_length[500]',
            'state'    => 'required|in_list[0,1]',
            'image'    => 'uploaded[image]|max_size[image,5120]|ext_in[image,jpg,jpeg,png,webp,gif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $imageUrl = $this->uploadImage();
        if ($imageUrl === null) {
            return redirect()->back()->withInput()
                ->with('form_errors', ['image' => '이미지 업로드에 실패했습니다.']);
        }

        $this->model->insert([
            'state'      => $this->request->getPost('state'),
            'image_url'  => $imageUrl,
            'alt_text'   => $this->request->getPost('alt_text')  ?: null,
            'location'   => $this->request->getPost('location')  ?: null,
            'title'      => $this->request->getPost('title')     ?: null,
            'subtitle'   => $this->request->getPost('subtitle')  ?: null,
            'link_url'   => $this->request->getPost('link_url')  ?: null,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 100),
            'reg_id'     => session()->get('backoffice.id'),
            'reg_date'   => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', '배너가 등록되었습니다.');
        return redirect()->to('/backoffice/banners');
    }

    /**
     * GET /backoffice/banners/(:num)/edit
     * 배너 수정 폼
     */
    public function edit(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 배너입니다.');
            return redirect()->to('/backoffice/banners');
        }

        return view('backoffice/banner/form', $this->base('배너 수정', [
            'item' => $item,
            'mode' => 'edit',
        ]));
    }

    /**
     * POST /backoffice/banners/(:num)/edit
     * 배너 수정 저장
     */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 배너입니다.');
            return redirect()->to('/backoffice/banners');
        }

        $file = $this->request->getFile('image');
        $hasNewImage = $file && $file->isValid() && !$file->hasMoved();

        $rules = [
            'title'    => 'permit_empty|max_length[200]',
            'subtitle' => 'permit_empty|max_length[300]',
            'alt_text' => 'permit_empty|max_length[200]',
            'link_url' => 'permit_empty|max_length[500]',
            'state'    => 'required|in_list[0,1]',
        ];
        if ($hasNewImage) {
            $rules['image'] = 'max_size[image,5120]|ext_in[image,jpg,jpeg,png,webp,gif]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $updateData = [
            'state'      => $this->request->getPost('state'),
            'alt_text'   => $this->request->getPost('alt_text')  ?: null,
            'location'   => $this->request->getPost('location')  ?: null,
            'title'      => $this->request->getPost('title')     ?: null,
            'subtitle'   => $this->request->getPost('subtitle')  ?: null,
            'link_url'   => $this->request->getPost('link_url')  ?: null,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 100),
            'edit_date'  => date('Y-m-d H:i:s'),
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

        session()->setFlashdata('success', '배너가 수정되었습니다.');
        return redirect()->to('/backoffice/banners');
    }

    /**
     * POST /backoffice/banners/(:num)/state
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
     * POST /backoffice/banners/(:num)/delete
     * 배너 삭제 (이미지 파일도 함께 제거)
     */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item) {
            $this->deleteImageFile($item['image_url']);
            $this->model->delete($idx);
            session()->setFlashdata('success', '배너가 삭제되었습니다.');
        }

        return redirect()->to('/backoffice/banners');
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
