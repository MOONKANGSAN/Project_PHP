<?php

namespace App\Controllers;

use App\Models\GoodsModel;
use App\Models\GoodsImagesModel;
use App\Models\PickupLocationModel;

/**
 * 백오피스 — 굿즈 상품 + 픽업 장소 관리 컨트롤러
 * /backoffice/goods/*              : 상품 목록·등록·수정·삭제·상태 토글
 * /backoffice/pickup-locations/*   : 픽업 장소 목록·등록·상태 토글
 */
class BackofficeGoods extends BaseController
{
    private GoodsModel       $model;
    private GoodsImagesModel $imagesModel;

    /** 이미지 저장 경로 (public/ 기준) */
    private const UPLOAD_DIR = 'uploads/goods/';
    /** 상품당 최대 이미지 수 */
    private const MAX_IMAGES = 3;

    /**
     * 컨트롤러 초기화 — 부모 initController 호출 후 모델 바인딩
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $req,
        \CodeIgniter\HTTP\ResponseInterface $res,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($req, $res, $logger);
        $this->model       = new GoodsModel();
        $this->imagesModel = new GoodsImagesModel();
    }

    /**
     * 공통 뷰 데이터 조립
     */
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
     * 업로드된 이미지 파일들을 저장하고 goods_images에 insert
     * @param  \CodeIgniter\HTTP\Files\UploadedFile[] $files
     * @param  int  $goodsIdx
     * @param  int  $startOrder   sort_order 시작 번호
     * @return int  실제 업로드된 파일 수
     */
    private function saveImages(array $files, int $goodsIdx, int $startOrder): int
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

            $this->imagesModel->insert([
                'goods_idx'  => $goodsIdx,
                'image_path' => '/' . self::UPLOAD_DIR . $newName,
                'sort_order' => $startOrder + $count,
                'state'      => 1,
                'reg_date'   => date('Y-m-d H:i:s'),
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * goods.thumbnail을 첫 번째 이미지 경로로 동기화
     * 이미지가 없으면 null 처리
     */
    private function syncThumbnail(int $goodsIdx): void
    {
        $images = $this->imagesModel->getByGoods($goodsIdx);
        $this->model->update($goodsIdx, [
            'thumbnail' => !empty($images) ? $images[0]['image_path'] : null,
        ]);
    }

    // ─── 굿즈 상품 관리 ───────────────────────────────────────────────────

    /**
     * GET /backoffice/goods
     * 상품 목록 — q(상품명 검색), state(상태 필터)
     */
    public function list(): string
    {
        $q     = trim($this->request->getGet('q')     ?? '');
        $state = trim($this->request->getGet('state') ?? '');

        if ($q !== '')     $this->model->like('name', $q);
        if ($state !== '') $this->model->where('state', (int) $state);

        $items = $this->model->orderBy('idx', 'DESC')->paginate(20) ?? [];

        return view('backoffice/goods/list', $this->base('굿즈 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'state' => $state,
        ]));
    }

    /**
     * GET /backoffice/goods/register
     * 상품 등록 폼 — 픽업 장소 목록과 빈 이미지 배열을 함께 전달
     */
    public function register(): string
    {
        $pickups = (new PickupLocationModel())->getActive();

        return view('backoffice/goods/form', $this->base('상품 등록', [
            'goods'           => null,
            'pickups'         => $pickups,
            'existing_images' => [],
        ]));
    }

    /**
     * POST /backoffice/goods/register
     * 상품 등록 처리 — INSERT 후 이미지 업로드
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data             = $this->buildData();
        $data['reg_date'] = date('Y-m-d H:i:s');

        $this->model->insert($data);
        $goodsIdx = (int) $this->model->getInsertID();

        // 이미지 업로드 (최대 3개)
        $files = $this->request->getFileMultiple('images') ?? [];
        if (!empty($files)) {
            $this->saveImages($files, $goodsIdx, 1);
            $this->syncThumbnail($goodsIdx);
        }

        session()->setFlashdata('success', '상품이 등록되었습니다.');
        return redirect()->to('/backoffice/goods');
    }

    /**
     * GET /backoffice/goods/(:num)/edit
     * 상품 수정 폼 — 기존 데이터와 등록 이미지 목록을 함께 전달
     */
    public function edit(int $idx): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $goods = $this->model->find($idx);
        if (!$goods) {
            session()->setFlashdata('error', '존재하지 않는 상품입니다.');
            return redirect()->to('/backoffice/goods');
        }

        $pickups = (new PickupLocationModel())->getActive();

        return view('backoffice/goods/form', $this->base('상품 수정', [
            'goods'           => $goods,
            'pickups'         => $pickups,
            'existing_images' => $this->imagesModel->getByGoods($idx),
        ]));
    }

    /**
     * POST /backoffice/goods/(:num)/edit
     * 상품 수정 처리 — 이미지 삭제 → 재정렬 → 신규 업로드 순으로 처리
     */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $goods = $this->model->find($idx);
        if (!$goods) {
            session()->setFlashdata('error', '존재하지 않는 상품입니다.');
            return redirect()->to('/backoffice/goods');
        }

        $this->model->update($idx, $this->buildData());

        // 삭제 요청된 이미지 처리 — 본 상품 소속 이미지인지 검증 후 삭제
        $deleteIds = (array) ($this->request->getPost('delete_imgs') ?? []);
        foreach ($deleteIds as $imgId) {
            $img = $this->imagesModel->find((int) $imgId);
            if ($img && (int) $img['goods_idx'] === $idx) {
                $this->imagesModel->deleteWithFile((int) $imgId);
            }
        }

        // 삭제 후 남은 이미지 sort_order 재정렬
        $this->imagesModel->reorderByGoods($idx);

        // 남은 슬롯 계산 후 신규 이미지 업로드
        $existingCount = count($this->imagesModel->getByGoods($idx));
        $remaining     = self::MAX_IMAGES - $existingCount;

        if ($remaining > 0) {
            $files = $this->request->getFileMultiple('images') ?? [];
            if (!empty($files)) {
                $this->saveImages(array_slice($files, 0, $remaining), $idx, $existingCount + 1);
            }
        }

        $this->syncThumbnail($idx);

        session()->setFlashdata('success', '수정되었습니다.');
        return redirect()->to('/backoffice/goods');
    }

    /**
     * POST /backoffice/goods/(:num)/state
     * 판매 상태 토글 — 1(판매중) ↔ 0(중지)
     */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $goods = $this->model->find($idx);
        if (!$goods) {
            return redirect()->back();
        }

        $this->model->update($idx, [
            'state'     => (int) $goods['state'] === 1 ? 0 : 1,
            'edit_date' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back();
    }

    /**
     * POST /backoffice/goods/(:num)/delete
     * 논리 삭제 — state = 9 으로 변경 (실제 행 삭제 없음)
     */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $goods = $this->model->find($idx);
        if (!$goods) {
            session()->setFlashdata('error', '존재하지 않는 상품입니다.');
            return redirect()->back();
        }

        $this->model->update($idx, [
            'state'     => 9,
            'edit_date' => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', "상품 [{$goods['name']}]이 삭제되었습니다.");
        return redirect()->to('/backoffice/goods');
    }

    /**
     * POST 폼 데이터 공통 조립
     * 등록·수정 양쪽에서 재사용 — reg_date는 등록 시에만 별도 추가
     * thumbnail은 saveImages/syncThumbnail에서 별도 처리
     */
    private function buildData(): array
    {
        return [
            'name'          => trim($this->request->getPost('name')           ?? ''),
            'description'   => trim($this->request->getPost('description')    ?? ''),
            'price'         => (int) ($this->request->getPost('price')         ?? 0),
            'stock'         => (int) ($this->request->getPost('stock')         ?? 0),
            'delivery_type' => (int) ($this->request->getPost('delivery_type') ?? 1),
            'state'         => 1,
            'edit_date'     => date('Y-m-d H:i:s'),
        ];
    }

    // ─── 픽업 장소 관리 ───────────────────────────────────────────────────

    /**
     * GET /backoffice/pickup-locations
     * 픽업 장소 전체 목록
     */
    public function pickupList(): string
    {
        $model   = new PickupLocationModel();
        $pickups = $model->orderBy('idx', 'DESC')->findAll();

        return view('backoffice/goods/pickup_list', $this->base('픽업 장소 관리', [
            'pickups' => $pickups,
        ]));
    }

    /**
     * POST /backoffice/pickup-locations/store
     * 픽업 장소 등록 — name, address 필드 INSERT
     */
    public function pickupStore(): \CodeIgniter\HTTP\RedirectResponse
    {
        $model = new PickupLocationModel();
        $model->insert([
            'name'    => trim($this->request->getPost('name')    ?? ''),
            'address' => trim($this->request->getPost('address') ?? ''),
            'state'   => 1,
        ]);

        session()->setFlashdata('success', '픽업 장소가 추가되었습니다.');
        return redirect()->to('/backoffice/pickup-locations');
    }

    /**
     * POST /backoffice/pickup-locations/(:num)/state
     * 픽업 장소 상태 토글 — 1(활성) ↔ 0(비활성)
     */
    public function pickupToggle(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $model = new PickupLocationModel();
        $item  = $model->find($idx);
        if (!$item) {
            return redirect()->back();
        }

        $model->update($idx, ['state' => (int) $item['state'] === 1 ? 0 : 1]);

        return redirect()->back();
    }
}
