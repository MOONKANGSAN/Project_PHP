<?php

namespace App\Controllers;

use App\Models\PlaceModel;
use App\Models\HashtagModel;
use App\Models\HashtagNumberModel;
use App\Models\ThumbnailModel;

/**
 * 백오피스 — 관광지 관리 컨트롤러
 */
class BackofficePlace extends BaseController
{
    private PlaceModel         $model;
    private HashtagModel       $hashtagModel;
    private HashtagNumberModel $hashtagNumberModel;
    private ThumbnailModel     $thumbnailModel;

    private const UPLOAD_DIR = 'uploads/thumbnails/';
    private const MAX_IMAGES = 8;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model              = new PlaceModel();
        $this->hashtagModel       = new HashtagModel();
        $this->hashtagNumberModel = new HashtagNumberModel();
        $this->thumbnailModel     = new ThumbnailModel();
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
            'categories'  => PlaceModel::CATEGORIES,
        ], $extra);
    }

    /**
     * 해시태그 이름 배열을 hashtag / hashtag_number 테이블에 저장 (place_idx 기준)
     */
    private function saveHashtags(int $placeIdx, array $tagNames): void
    {
        $tagNames = array_filter(array_unique(array_slice($tagNames, 0, 5)));

        foreach ($tagNames as $name) {
            $name = mb_substr(trim($name), 0, 50);
            if ($name === '') continue;

            $hashtagIdx = $this->hashtagModel->findOrCreate($name);
            $this->hashtagNumberModel->insert([
                'hashtag_idx' => $hashtagIdx,
                'place_idx'   => $placeIdx,
                'reg_date'    => date('Y-m-d H:i:s'),
                'state'       => 1,
            ]);
            $this->hashtagModel->recalcUseCount($hashtagIdx);
        }
    }

    /**
     * 이미지 업로드 후 busan_thumbnail에 저장 (place_idx 기준)
     * @param  \CodeIgniter\HTTP\Files\UploadedFile[] $files
     */
    private function saveImages(array $files, int $placeIdx, int $startOrder): int
    {
        $uploadDir = FCPATH . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $count = 0;
        foreach ($files as $file) {
            if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) continue;
            if (!$file->isValid() || $file->hasMoved()) continue;
            if (($startOrder + $count) > self::MAX_IMAGES) break;

            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);

            $this->thumbnailModel->insert([
                'img_order' => $startOrder + $count,
                'img_url'   => '/' . self::UPLOAD_DIR . $newName,
                'reg_date'  => date('Y-m-d H:i:s'),
                'state'     => 1,
                'place_idx' => $placeIdx,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * 대표이미지(thumb_idx)를 첫 번째 이미지로 동기화
     */
    private function syncThumbIdx(int $placeIdx): void
    {
        $first = $this->thumbnailModel->getByPlace($placeIdx);
        $this->model->update($placeIdx, [
            'thumb_idx' => $first ? $first[0]['idx'] : null,
        ]);
    }

    /** GET /backoffice/spots */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q') ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $state);

        return view('backoffice/place/list', $this->base('관광지 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'state' => $state,
        ]));
    }

    /** GET /backoffice/spots/register */
    public function register(): string
    {
        return view('backoffice/place/form', $this->base('관광지 등록', [
            'item'              => null,
            'mode'              => 'register',
            'existing_hashtags' => [],
            'existing_images'   => [],
        ]));
    }

    /** POST /backoffice/spots/register */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name'  => 'required|max_length[100]',
            'state' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $openStart = $this->request->getPost('open_start');
        $openEnd   = $this->request->getPost('open_end');
        $openTime  = ($openStart && $openEnd) ? "{$openStart}~{$openEnd}" : null;

        $this->model->insert([
            'state'         => $this->request->getPost('state'),
            'name'          => $this->request->getPost('name'),
            'star_point'    => 0,
            'info'          => $this->request->getPost('info'),
            'address1'      => $this->request->getPost('address1'),
            'address2'      => $this->request->getPost('address2'),
            'sido'          => $this->request->getPost('sido'),
            'open_time'     => $openTime,
            'admission_fee' => $this->request->getPost('admission_fee'),
            'parking'       => $this->request->getPost('parking') ?: 0,
            'category_num'  => $this->request->getPost('category_num') ?: 0,
            'reg_id'        => session()->get('backoffice.id'),
            'reg_date'      => date('Y-m-d H:i:s'),
            'edit_date'     => date('Y-m-d H:i:s'),
        ]);

        $placeIdx = (int) $this->model->getInsertID();

        // 해시태그 저장 (최대 5개)
        $tagNames = $this->request->getPost('hashtag_names') ?? [];
        $this->saveHashtags($placeIdx, (array) $tagNames);

        // 이미지 업로드 (최대 8개)
        $files = $this->request->getFileMultiple('images') ?? [];
        if (!empty($files)) {
            $this->saveImages($files, $placeIdx, 1);
            $this->syncThumbIdx($placeIdx);
        }

        session()->setFlashdata('success', '관광지가 등록되었습니다.');
        return redirect()->to('/backoffice/spots');
    }

    /** GET /backoffice/spots/(:num)/edit */
    public function edit(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 항목입니다.');
            return redirect()->to('/backoffice/spots');
        }

        return view('backoffice/place/form', $this->base('관광지 수정', [
            'item'              => $item,
            'mode'              => 'edit',
            'existing_hashtags' => $this->hashtagNumberModel->getTagsByPlace($idx),
            'existing_images'   => $this->thumbnailModel->getByPlace($idx),
        ]));
    }

    /** POST /backoffice/spots/(:num)/edit */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 항목입니다.');
            return redirect()->to('/backoffice/spots');
        }

        $rules = [
            'name'  => 'required|max_length[100]',
            'state' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $openStart = $this->request->getPost('open_start');
        $openEnd   = $this->request->getPost('open_end');
        $openTime  = ($openStart && $openEnd) ? "{$openStart}~{$openEnd}" : null;

        $this->model->update($idx, [
            'state'         => $this->request->getPost('state'),
            'name'          => $this->request->getPost('name'),
            'star_point'    => 0,
            'info'          => $this->request->getPost('info'),
            'address1'      => $this->request->getPost('address1'),
            'address2'      => $this->request->getPost('address2'),
            'sido'          => $this->request->getPost('sido'),
            'open_time'     => $openTime,
            'admission_fee' => $this->request->getPost('admission_fee'),
            'parking'       => $this->request->getPost('parking') ?: 0,
            'category_num'  => $this->request->getPost('category_num') ?: 0,
            'edit_date'     => date('Y-m-d H:i:s'),
        ]);

        // 해시태그 — 기존 삭제 후 재저장
        $removedTagIds = $this->hashtagNumberModel->deleteByPlace($idx);
        foreach ($removedTagIds as $tagId) {
            $this->hashtagModel->recalcUseCount((int) $tagId);
        }
        $tagNames = $this->request->getPost('hashtag_names') ?? [];
        $this->saveHashtags($idx, (array) $tagNames);

        // 이미지 — 삭제 요청 처리 후 신규 업로드
        $deleteIds = (array) ($this->request->getPost('delete_imgs') ?? []);
        foreach ($deleteIds as $imgId) {
            $img = $this->thumbnailModel->find((int) $imgId);
            if ($img && (int) $img['place_idx'] === $idx) {
                $this->thumbnailModel->deleteWithFile((int) $imgId);
            }
        }

        $this->thumbnailModel->reorderByPlace($idx);

        $existingCount = count($this->thumbnailModel->getByPlace($idx));
        $remaining     = self::MAX_IMAGES - $existingCount;

        if ($remaining > 0) {
            $files = $this->request->getFileMultiple('images') ?? [];
            if (!empty($files)) {
                $this->saveImages(array_slice($files, 0, $remaining), $idx, $existingCount + 1);
            }
        }

        $this->syncThumbIdx($idx);

        session()->setFlashdata('success', '관광지 정보가 수정되었습니다.');
        return redirect()->to('/backoffice/spots');
    }

    /** POST /backoffice/spots/(:num)/state */
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
}
