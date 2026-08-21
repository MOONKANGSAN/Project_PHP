<?php

namespace App\Controllers;

use App\Models\SiteEventModel;

/**
 * 백오피스 — 사이트 자체 이벤트 관리 컨트롤러
 */
class BackofficeSiteEvent extends BaseController
{
    private SiteEventModel $model;

    private const UPLOAD_DIR = 'uploads/events/';

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
     * 대표 이미지 업로드 처리
     * 업로드된 파일을 저장하고 경로를 반환, 없으면 null 반환
     */
    private function uploadThumb(): ?string
    {
        $file = $this->request->getFile('thumb_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadDir = FCPATH . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return '/' . self::UPLOAD_DIR . $newName;
    }

    /** GET /backoffice/site-events */
    public function list(): string
    {
        $q      = trim($this->request->getGet('q')      ?? '');
        $state  = trim($this->request->getGet('state')  ?? '');
        $type   = trim($this->request->getGet('type')   ?? '');

        $query = $this->model->where('state !=', 9);

        if ($state !== '') {
            $query->where('state', (int) $state);
        }
        if ($type !== '') {
            $query->where('event_type', (int) $type);
        }
        if ($q !== '') {
            $query->like('title', $q, 'both');
        }

        $items = $query->orderBy('idx', 'DESC')->paginate(20);
        $pager = $this->model->pager;

        return view('backoffice/site_event/list', $this->base('사이트 이벤트 관리', [
            'items' => $items,
            'pager' => $pager,
            'q'     => $q,
            'state' => $state,
            'type'  => $type,
        ]));
    }

    /** GET /backoffice/site-events/register */
    public function register(): string
    {
        return view('backoffice/site_event/form', $this->base('사이트 이벤트 등록', [
            'item' => null,
            'mode' => 'register',
        ]));
    }

    /** POST /backoffice/site-events/register */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $useViewFile = (int) $this->request->getPost('use_view_file');

        $rules = [
            'title'      => 'required|max_length[200]',
            'state'      => 'required|in_list[0,1]',
            'start_date' => 'required|valid_date',
            'end_date'   => 'required|valid_date',
        ];
        if ($useViewFile) {
            $rules['view_file'] = 'required|max_length[50]|regex_match[/^[a-zA-Z0-9_]+$/]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        // 대표 이미지 업로드 처리
        $thumbUrl = $this->uploadThumb();

        $this->model->insert([
            'state'         => $this->request->getPost('state'),
            'use_view_file' => $useViewFile,
            'event_type'    => $this->request->getPost('event_type') ?: 4,
            'title'         => $this->request->getPost('title'),
            'sub_title'     => $this->request->getPost('sub_title')  ?: null,
            'content'       => $this->request->getPost('content')    ?: null,
            'cta_text'      => $this->request->getPost('cta_text')   ?: null,
            'cta_url'       => $this->request->getPost('cta_url')    ?: null,
            'thumb_url'     => $thumbUrl ?? ($this->request->getPost('thumb_url') ?: null),
            'view_file'     => $useViewFile ? preg_replace('/[^a-zA-Z0-9_]/', '', $this->request->getPost('view_file') ?? '') : null,
            'start_date'    => $this->request->getPost('start_date'),
            'end_date'      => $this->request->getPost('end_date'),
            'reg_id'        => session()->get('backoffice.id'),
            'reg_date'      => date('Y-m-d H:i:s'),
            'edit_date'     => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', '이벤트가 등록되었습니다.');
        return redirect()->to('/backoffice/site-events');
    }

    /** GET /backoffice/site-events/(:num)/edit */
    public function edit(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item || (int) $item['state'] === 9) {
            session()->setFlashdata('error', '존재하지 않는 이벤트입니다.');
            return redirect()->to('/backoffice/site-events');
        }

        return view('backoffice/site_event/form', $this->base('사이트 이벤트 수정', [
            'item' => $item,
            'mode' => 'edit',
        ]));
    }

    /** POST /backoffice/site-events/(:num)/edit */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item || (int) $item['state'] === 9) {
            session()->setFlashdata('error', '존재하지 않는 이벤트입니다.');
            return redirect()->to('/backoffice/site-events');
        }

        $useViewFile = (int) $this->request->getPost('use_view_file');

        $rules = [
            'title'      => 'required|max_length[200]',
            'state'      => 'required|in_list[0,1]',
            'start_date' => 'required|valid_date',
            'end_date'   => 'required|valid_date',
        ];
        if ($useViewFile) {
            $rules['view_file'] = 'required|max_length[50]|regex_match[/^[a-zA-Z0-9_]+$/]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        // 대표 이미지 — 새 파일이 있으면 교체, 없으면 기존 유지
        $thumbUrl = $this->uploadThumb();
        if ($thumbUrl === null) {
            $thumbUrl = $this->request->getPost('thumb_url') ?: $item['thumb_url'];
        }

        $this->model->update($idx, [
            'state'         => $this->request->getPost('state'),
            'use_view_file' => $useViewFile,
            'event_type'    => $this->request->getPost('event_type') ?: 4,
            'title'         => $this->request->getPost('title'),
            'sub_title'     => $this->request->getPost('sub_title')  ?: null,
            'content'       => $this->request->getPost('content')    ?: null,
            'cta_text'      => $this->request->getPost('cta_text')   ?: null,
            'cta_url'       => $this->request->getPost('cta_url')    ?: null,
            'thumb_url'     => $thumbUrl,
            'view_file'     => $useViewFile ? preg_replace('/[^a-zA-Z0-9_]/', '', $this->request->getPost('view_file') ?? '') : null,
            'start_date'    => $this->request->getPost('start_date'),
            'end_date'      => $this->request->getPost('end_date'),
            'edit_date'     => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', '이벤트가 수정되었습니다.');
        return redirect()->to('/backoffice/site-events');
    }

    /** POST /backoffice/site-events/(:num)/state */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item && (int) $item['state'] !== 9) {
            $this->model->update($idx, [
                'state'     => $item['state'] ? 0 : 1,
                'edit_date' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->back();
    }

    /** POST /backoffice/site-events/(:num)/delete */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if ($item) {
            $this->model->update($idx, [
                'state'     => 9,
                'edit_date' => date('Y-m-d H:i:s'),
            ]);
        }

        session()->setFlashdata('success', '이벤트가 삭제되었습니다.');
        return redirect()->to('/backoffice/site-events');
    }
}
