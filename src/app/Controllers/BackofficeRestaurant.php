<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\FacilityModel;
use App\Models\HashtagModel;
use App\Models\HashtagNumberModel;
use App\Models\ThumbnailModel;

/**
 * 백오피스 — 맛집 관리 컨트롤러
 */
class BackofficeRestaurant extends BaseController
{
    private RestaurantModel    $model;
    private FacilityModel      $facilityModel;
    private HashtagModel       $hashtagModel;
    private HashtagNumberModel $hashtagNumberModel;
    private ThumbnailModel     $thumbnailModel;

    /** 이미지 저장 경로 (public/ 기준) */
    private const UPLOAD_DIR = 'uploads/thumbnails/';
    /** 콘텐츠당 최대 이미지 수 */
    private const MAX_IMAGES = 8;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model              = new RestaurantModel();
        $this->facilityModel      = new FacilityModel();
        $this->hashtagModel       = new HashtagModel();
        $this->hashtagNumberModel = new HashtagNumberModel();
        $this->thumbnailModel     = new ThumbnailModel();
    }

    // 공통 뷰 데이터
    private function base(string $title, array $extra = []): array
    {
        return array_merge([
            'page_title'   => $title,
            'admin'        => [
                'idx'   => session()->get('backoffice.idx'),
                'id'    => session()->get('backoffice.id'),
                'level' => session()->get('backoffice.level'),
            ],
            'current_uri'     => '/' . uri_string(),
            'categories'      => RestaurantModel::CATEGORIES,
            'price_ranges'    => RestaurantModel::PRICE_RANGES,
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
     * 해시태그 이름 배열을 hashtag / hashtag_number 테이블에 저장 (최대 5개)
     */
    private function saveHashtags(int $restaurantIdx, array $tagNames): void
    {
        $tagNames = array_filter(array_unique(array_slice($tagNames, 0, 5)));

        foreach ($tagNames as $name) {
            $name = mb_substr(trim($name), 0, 50);
            if ($name === '') continue;

            $hashtagIdx = $this->hashtagModel->findOrCreate($name);
            $this->hashtagNumberModel->insert([
                'hashtag_idx'    => $hashtagIdx,
                'restaurant_idx' => $restaurantIdx,
                'reg_date'       => date('Y-m-d H:i:s'),
                'state'          => 1,
            ]);
            $this->hashtagModel->recalcUseCount($hashtagIdx);
        }
    }

    /**
     * 업로드된 이미지 파일들을 저장하고 busan_thumbnail에 insert
     * @param  \CodeIgniter\HTTP\Files\UploadedFile[] $files
     * @param  int  $restaurantIdx
     * @param  int  $startOrder    img_order 시작 번호
     * @return int  실제 업로드된 파일 수
     */
    private function saveImages(array $files, int $restaurantIdx, int $startOrder): int
    {
        $uploadDir = FCPATH . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $count = 0;
        foreach ($files as $file) {
            // UploadedFile 타입이 아닌 요소(빈 배열 등) 방어
            if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) continue;
            // 업로드 오류가 있거나 이미 이동된 파일은 건너뜀
            if (!$file->isValid() || $file->hasMoved()) continue;
            if (($startOrder + $count) > self::MAX_IMAGES) break;

            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);

            $this->thumbnailModel->insert([
                'img_order'      => $startOrder + $count,
                'img_url'        => '/' . self::UPLOAD_DIR . $newName,
                'reg_date'       => date('Y-m-d H:i:s'),
                'state'          => 1,
                'restaurant_idx' => $restaurantIdx,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * 대표이미지(thumb_idx)를 첫 번째 이미지(img_order=1)로 동기화
     */
    private function syncThumbIdx(int $restaurantIdx): void
    {
        $first = $this->thumbnailModel->getByRestaurant($restaurantIdx);
        $this->model->update($restaurantIdx, [
            'thumb_idx' => $first ? $first[0]['idx'] : null,
        ]);
    }

    /** GET /backoffice/restaurants */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q') ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $state);

        return view('backoffice/restaurant/list', $this->base('맛집 관리', [
            'items'  => $items,
            'pager'  => $this->model->pager,
            'q'      => $q,
            'state'  => $state,
        ]));
    }

    /** GET /backoffice/restaurants/register */
    public function register(): string
    {
        return view('backoffice/restaurant/form', $this->base('맛집 등록', [
            'item'              => null,
            'mode'              => 'register',
            'existing_hashtags' => [],
            'existing_images'   => [],
            'facility'          => [],
        ]));
    }

    /** POST /backoffice/restaurants/register */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name'         => 'required|max_length[100]',
            'state'        => 'required|in_list[0,1]',
            'category_num' => 'required|integer',
            'address1'     => 'permit_empty|max_length[200]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $phone     = implode('-', array_filter([
            $this->request->getPost('phone_1'),
            $this->request->getPost('phone_2'),
            $this->request->getPost('phone_3'),
        ]));
        $openStart = $this->request->getPost('open_start');
        $openEnd   = $this->request->getPost('open_end');
        $openTime  = ($openStart && $openEnd) ? "{$openStart}~{$openEnd}" : null;

        $this->model->insert([
            'state'        => $this->request->getPost('state'),
            'name'         => $this->request->getPost('name'),
            'star_point'   => 0,
            'info'         => $this->request->getPost('info'),
            'address1'     => $this->request->getPost('address1'),
            'address2'     => $this->request->getPost('address2'),
            'phone'        => $phone ?: null,
            'category_num' => $this->request->getPost('category_num'),
            'price_range'  => $this->request->getPost('price_range') ?: 1,
            'sido'         => $this->request->getPost('sido'),
            'latitude'     => $this->request->getPost('latitude')  ?: null,
            'longitude'    => $this->request->getPost('longitude') ?: null,
            'open_time'    => $openTime,
            'parking'      => $this->request->getPost('parking') ?: 0,
            'reg_id'       => session()->get('backoffice.id'),
            'reg_date'     => date('Y-m-d H:i:s'),
            'edit_date'    => date('Y-m-d H:i:s'),
        ]);

        $restaurantIdx = (int) $this->model->getInsertID();

        // 편의시설 저장 (busan_facility)
        $this->facilityModel->saveForRestaurant($restaurantIdx, $this->extractFacilityData());

        // 해시태그 저장 (최대 5개)
        $tagNames = $this->request->getPost('hashtag_names') ?? [];
        $this->saveHashtags($restaurantIdx, (array) $tagNames);

        // 이미지 업로드 (최대 8개)
        // getFileMultiple: name="images[]" 다중 파일을 UploadedFile[] 배열로 반환
        $files = $this->request->getFileMultiple('images') ?? [];
        if (!empty($files)) {
            $this->saveImages($files, $restaurantIdx, 1);
            $this->syncThumbIdx($restaurantIdx);
        }

        session()->setFlashdata('success', '맛집이 등록되었습니다.');
        return redirect()->to('/backoffice/restaurants');
    }

    /** GET /backoffice/restaurants/(:num)/edit */
    public function edit(int $idx): string
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 항목입니다.');
            return redirect()->to('/backoffice/restaurants');
        }

        return view('backoffice/restaurant/form', $this->base('맛집 수정', [
            'item'              => $item,
            'mode'              => 'edit',
            'existing_hashtags' => $this->hashtagNumberModel->getTagsByRestaurant($idx),
            'existing_images'   => $this->thumbnailModel->getByRestaurant($idx),
            'facility'          => $this->facilityModel->getByRestaurant($idx) ?? [],
        ]));
    }

    /** POST /backoffice/restaurants/(:num)/edit */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);
        if (!$item) {
            session()->setFlashdata('error', '존재하지 않는 항목입니다.');
            return redirect()->to('/backoffice/restaurants');
        }

        $rules = [
            'name'         => 'required|max_length[100]',
            'state'        => 'required|in_list[0,1]',
            'category_num' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $phone     = implode('-', array_filter([
            $this->request->getPost('phone_1'),
            $this->request->getPost('phone_2'),
            $this->request->getPost('phone_3'),
        ]));
        $openStart = $this->request->getPost('open_start');
        $openEnd   = $this->request->getPost('open_end');
        $openTime  = ($openStart && $openEnd) ? "{$openStart}~{$openEnd}" : null;

        $this->model->update($idx, [
            'state'        => $this->request->getPost('state'),
            'name'         => $this->request->getPost('name'),
            'star_point'   => 0,
            'info'         => $this->request->getPost('info'),
            'address1'     => $this->request->getPost('address1'),
            'address2'     => $this->request->getPost('address2'),
            'phone'        => $phone ?: null,
            'category_num' => $this->request->getPost('category_num'),
            'price_range'  => $this->request->getPost('price_range') ?: 1,
            'sido'         => $this->request->getPost('sido'),
            'latitude'     => $this->request->getPost('latitude')  ?: null,
            'longitude'    => $this->request->getPost('longitude') ?: null,
            'open_time'    => $openTime,
            'parking'      => $this->request->getPost('parking') ?: 0,
            'edit_date'    => date('Y-m-d H:i:s'),
        ]);

        // 편의시설 저장 (busan_facility)
        $this->facilityModel->saveForRestaurant($idx, $this->extractFacilityData());

        // 해시태그 — 기존 삭제 후 재저장
        $removedTagIds = $this->hashtagNumberModel->deleteByRestaurant($idx);
        foreach ($removedTagIds as $tagId) {
            $this->hashtagModel->recalcUseCount((int) $tagId);
        }
        $tagNames = $this->request->getPost('hashtag_names') ?? [];
        $this->saveHashtags($idx, (array) $tagNames);

        // 이미지 — 삭제 요청 처리 후 신규 업로드
        $deleteIds = (array) ($this->request->getPost('delete_imgs') ?? []);
        foreach ($deleteIds as $imgId) {
            $img = $this->thumbnailModel->find((int) $imgId);
            // 본 맛집에 속한 이미지인지 검증 후 삭제
            if ($img && (int) $img['restaurant_idx'] === $idx) {
                $this->thumbnailModel->deleteWithFile((int) $imgId);
            }
        }

        // 삭제 후 남은 이미지 재정렬
        $this->thumbnailModel->reorderByRestaurant($idx);

        // 남은 슬롯 계산 후 신규 이미지 업로드
        $existingCount = count($this->thumbnailModel->getByRestaurant($idx));
        $remaining     = self::MAX_IMAGES - $existingCount;

        if ($remaining > 0) {
            $files = $this->request->getFileMultiple('images') ?? [];
            if (!empty($files)) {
                $this->saveImages(array_slice($files, 0, $remaining), $idx, $existingCount + 1);
            }
        }

        $this->syncThumbIdx($idx);

        session()->setFlashdata('success', '맛집 정보가 수정되었습니다.');
        return redirect()->to('/backoffice/restaurants');
    }

    /** POST /backoffice/restaurants/(:num)/state — 활성/비활성 토글 */
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
