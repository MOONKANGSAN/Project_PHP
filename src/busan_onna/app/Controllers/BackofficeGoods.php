<?php

namespace App\Controllers;

use App\Models\GoodsModel;
use App\Models\PickupLocationModel;

/**
 * 백오피스 — 굿즈 상품 + 픽업 장소 관리 컨트롤러
 * /backoffice/goods/*         : 상품 목록·등록·수정·삭제·상태 토글
 * /backoffice/pickup-locations/* : 픽업 장소 목록·등록·상태 토글
 */
class BackofficeGoods extends BaseController
{
    /** 굿즈 상품 모델 */
    private GoodsModel $model;

    /**
     * 컨트롤러 초기화 — 부모 initController 호출 후 모델 바인딩
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $req,
        \CodeIgniter\HTTP\ResponseInterface $res,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($req, $res, $logger);
        $this->model = new GoodsModel();
    }

    /**
     * 공통 뷰 데이터 조립
     * 페이지 제목, 세션 기반 관리자 정보, 현재 URI 포함
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

    // ─── 굿즈 상품 관리 ───────────────────────────────────────────────────

    /**
     * GET /backoffice/goods
     * 상품 목록 — q(상품명 검색), state(상태 필터)
     */
    public function list(): string
    {
        $q     = trim($this->request->getGet('q')     ?? '');
        $state = trim($this->request->getGet('state') ?? '');

        // 검색어가 있으면 상품명 LIKE 필터 적용
        if ($q !== '')     $this->model->like('name', $q);
        // 상태값이 있으면 state 컬럼 일치 필터 적용
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
     * 상품 등록 폼 — 픽업 장소 목록을 함께 전달
     */
    public function register(): string
    {
        $pickups = (new PickupLocationModel())->getActive();

        return view('backoffice/goods/form', $this->base('상품 등록', [
            'goods'   => null,
            'pickups' => $pickups,
        ]));
    }

    /**
     * POST /backoffice/goods/register
     * 상품 등록 처리 — buildData()로 폼 데이터 조립 후 INSERT
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data            = $this->buildData();
        $data['reg_date'] = date('Y-m-d H:i:s');

        $this->model->insert($data);

        session()->setFlashdata('success', '상품이 등록되었습니다.');
        return redirect()->to('/backoffice/goods');
    }

    /**
     * GET /backoffice/goods/(:num)/edit
     * 상품 수정 폼 — idx로 기존 데이터 조회 후 폼에 전달
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
            'goods'   => $goods,
            'pickups' => $pickups,
        ]));
    }

    /**
     * POST /backoffice/goods/(:num)/edit
     * 상품 수정 처리 — buildData()로 폼 데이터 조립 후 UPDATE
     */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $goods = $this->model->find($idx);
        if (!$goods) {
            session()->setFlashdata('error', '존재하지 않는 상품입니다.');
            return redirect()->to('/backoffice/goods');
        }

        $this->model->update($idx, $this->buildData());

        session()->setFlashdata('success', '수정되었습니다.');
        return redirect()->to('/backoffice/goods');
    }

    /**
     * POST /backoffice/goods/(:num)/state
     * 판매상태 토글 — 1(판매중) ↔ 0(중지)
     */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $goods = $this->model->find($idx);
        if (!$goods) {
            return redirect()->back();
        }

        $newState = (int) $goods['state'] === 1 ? 0 : 1;
        $this->model->update($idx, [
            'state'     => $newState,
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
     */
    private function buildData(): array
    {
        return [
            'name'          => trim($this->request->getPost('name')          ?? ''),
            'description'   => trim($this->request->getPost('description')   ?? ''),
            'price'         => (int) ($this->request->getPost('price')        ?? 0),
            'stock'         => (int) ($this->request->getPost('stock')        ?? 0),
            'delivery_type' => (int) ($this->request->getPost('delivery_type') ?? 1),
            'thumbnail'     => trim($this->request->getPost('thumbnail')     ?? ''),
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
