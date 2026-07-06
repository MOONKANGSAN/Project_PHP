<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\FacilityModel;
use App\Models\HashtagModel;
use App\Models\HashtagNumberModel;
use App\Models\ThumbnailModel;

/**
 * 백오피스 — 행사·축제 관리 컨트롤러
 */
class BackofficeEvent extends BaseController
{
    private EventModel         $model;
    private FacilityModel      $facilityModel;
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
        $this->model              = new EventModel();
        $this->facilityModel      = new FacilityModel();
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
            'current_uri'     => '/' . uri_string(),
            'categories'      => EventModel::CATEGORIES,
            'naver_client_id' => env('NAVER_MAP_CLIENT_ID', ''),
        ], $extra);
    }

    /**
     * POST 데이터에서 편의시설 필드만 추출
     */
    private function extractFacilityData(): array
    {
        $data = [];
        foreach (array_keys(FacilityModel::FIELDS) as $field) {
            $data[$field] = (int) $this->request->getPost($field);
        }
        return $data;
    }

    /**
     * 해시태그 이름 배열을 hashtag / hashtag_number 테이블에 저장 (event_idx 기준)
     */
    private function saveHashtags(int $eventIdx, array $tagNames): void
    {
        $tagNames = array_filter(array_unique(array_slice($tagNames, 0, 5)));

        foreach ($tagNames as $name) {
            $name = mb_substr(trim($name), 0, 50);
            if ($name === '') continue;

            $hashtagIdx = $this->hashtagModel->findOrCreate($name);
            $this->hashtagNumberModel->insert([
                'hashtag_idx' => $hashtagIdx,
                'event_idx'   => $eventIdx,
                'reg_date'    => date('Y-m-d H:i:s'),
                'state'       => 1,
            ]);
            $this->hashtagModel->recalcUseCount($hashtagIdx);
        }
    }

    /**
     * 이미지 업로드 후 busan_thumbnail에 저장 (event_idx 기준)
     * @param  \CodeIgniter\HTTP\Files\UploadedFile[] $files
     */
    private function saveImages(array $files, int $eventIdx, int $startOrder): int
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
                'event_idx' => $eventIdx,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * 대표이미지(thumb_idx)를 첫 번째 이미지로 동기화
     */
    private function syncThumbIdx(int $eventIdx): void
    {
        $first = $this->thumbnailModel->getByEvent($eventIdx);
        $this->model->update($eventIdx, [
            'thumb_idx' => $first ? $first[0]['idx'] : null,
        ]);
    }

    /** GET /backoffice/festivals */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q') ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $state);

        return view('backoffice/event/list', $this->base('행사·축제 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'state' => $state,
        ]));
    }

    /** GET /backoffice/festivals/register */
    public function register(): string
    {
        return view('backoffice/event/form', $this->base('행사·축제 등록', [
            'item'              => null,
            'mode'              => 'register',
            'existing_hashtags' => [],
            'existing_images'   => [],
            'facility'          => [],
        ]));
    }

    /** POST /backoffice/festivals/register */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name'       => 'required|max_length[150]',
            'state'      => 'required|in_list[0,1]',
            'start_date' => 'required|valid_date',
            'end_date'   => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'state'        => $this->request->getPost('state'),
            'name'         => $this->request->getPost('name'),
            'info'         => $this->request->getPost('info'),
            'star_point'   => 0,
            'address1'     => $this->request->getPost('address1'),
            'address2'     => $this->request->getPost('address2'),
            'detail_url'   => $this->request->getPost('detail_url'),
            'sido'         => $this->request->getPost('sido'),
            'latitude'     => $this->request->getPost('latitude')  ?: null,
            'longitude'    => $this->request->getPost('longitude') ?: null,
            'price_range'  => $this->request->getPost('price_range') ?: 1,
            'start_date'   => $this->request->getPost('start_date'),
            'end_date'     => $this->request->getPost('end_date'),
            'category_num' => $this->request->getPost('category_num') ?: 0,
            'host'         => $this->request->getPost('host'),
            'is_free'      => $this->request->getPost('is_free') ?: 0,
            'reg_id'       => session()->get('backoffice.id'),
            'reg_date'     => date('Y-m-d H:i:s'),
            'edit_date'    => date('Y-m-d H:i:s'),
        ]);

        $eventIdx = (int) $this->model->getInsertID();

        // 편의시설 저장 (busan_facility)
        $this->facilityModel->saveForEvent($eventIdx, $this->extractFacilityData());

        // 해시태그 저장 (최대 5개)
        $tagNames = $this->request->getPost('hashtag_names') ?? [];
        $this->saveHashtags($eventIdx, (array) $tagNames);

        // 이미지 업로드 (최대 8개)
        $files = $this->request->getFileMultiple('images') ?? [];
        if (!empty($files)) {
            $this->saveImages($files, $eventIdx, 1);
            $this->syncThumbIdx($eventIdx);
        }

        session()->setFlashdata('success', '행사·축제가 등록되었습니다.');
        return redirect()->to('/backoffice/festivals');
    }

    /** GET /backoffice/festivals/(:num)/edit */
    public function edit(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 항목입니다.');
            return redirect()->to('/backoffice/festivals');
        }

        return view('backoffice/event/form', $this->base('행사·축제 수정', [
            'item'              => $item,
            'mode'              => 'edit',
            'existing_hashtags' => $this->hashtagNumberModel->getTagsByEvent($idx),
            'existing_images'   => $this->thumbnailModel->getByEvent($idx),
            'facility'          => $this->facilityModel->getByEvent($idx) ?? [],
        ]));
    }

    /** POST /backoffice/festivals/(:num)/edit */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 항목입니다.');
            return redirect()->to('/backoffice/festivals');
        }

        $rules = [
            'name'       => 'required|max_length[150]',
            'state'      => 'required|in_list[0,1]',
            'start_date' => 'required|valid_date',
            'end_date'   => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $this->model->update($idx, [
            'state'        => $this->request->getPost('state'),
            'name'         => $this->request->getPost('name'),
            'info'         => $this->request->getPost('info'),
            'star_point'   => 0,
            'address1'     => $this->request->getPost('address1'),
            'address2'     => $this->request->getPost('address2'),
            'detail_url'   => $this->request->getPost('detail_url'),
            'sido'         => $this->request->getPost('sido'),
            'latitude'     => $this->request->getPost('latitude')  ?: null,
            'longitude'    => $this->request->getPost('longitude') ?: null,
            'price_range'  => $this->request->getPost('price_range') ?: 1,
            'start_date'   => $this->request->getPost('start_date'),
            'end_date'     => $this->request->getPost('end_date'),
            'category_num' => $this->request->getPost('category_num') ?: 0,
            'host'         => $this->request->getPost('host'),
            'is_free'      => $this->request->getPost('is_free') ?: 0,
            'edit_date'    => date('Y-m-d H:i:s'),
        ]);

        // 편의시설 저장 (busan_facility)
        $this->facilityModel->saveForEvent($idx, $this->extractFacilityData());

        // 해시태그 — 기존 삭제 후 재저장
        $removedTagIds = $this->hashtagNumberModel->deleteByEvent($idx);
        foreach ($removedTagIds as $tagId) {
            $this->hashtagModel->recalcUseCount((int) $tagId);
        }
        $tagNames = $this->request->getPost('hashtag_names') ?? [];
        $this->saveHashtags($idx, (array) $tagNames);

        // 이미지 — 삭제 요청 처리 후 신규 업로드
        $deleteIds = (array) ($this->request->getPost('delete_imgs') ?? []);
        foreach ($deleteIds as $imgId) {
            $img = $this->thumbnailModel->find((int) $imgId);
            if ($img && (int) $img['event_idx'] === $idx) {
                $this->thumbnailModel->deleteWithFile((int) $imgId);
            }
        }

        $this->thumbnailModel->reorderByEvent($idx);

        $existingCount = count($this->thumbnailModel->getByEvent($idx));
        $remaining     = self::MAX_IMAGES - $existingCount;

        if ($remaining > 0) {
            $files = $this->request->getFileMultiple('images') ?? [];
            if (!empty($files)) {
                $this->saveImages(array_slice($files, 0, $remaining), $idx, $existingCount + 1);
            }
        }

        $this->syncThumbIdx($idx);

        session()->setFlashdata('success', '행사·축제 정보가 수정되었습니다.');
        return redirect()->to('/backoffice/festivals');
    }

    /** POST /backoffice/festivals/(:num)/state */
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
