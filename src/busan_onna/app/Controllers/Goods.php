<?php

namespace App\Controllers;

use App\Models\GoodsModel;
use App\Models\GoodsOptionModel;
use App\Models\GoodsOptionValueModel;

/**
 * 굿즈 목록·상세 컨트롤러
 * GET /goods       — 상품 목록
 * GET /goods/{idx} — 상품 상세
 */
class Goods extends BaseController
{
    /**
     * GET /goods — 상품 목록
     * 검색(q), 배송유형(delivery_type), 정렬(sort) 필터 지원
     */
    public function index(): string
    {
        $model = new GoodsModel();

        // 검색·필터 파라미터 수집
        $q            = trim($this->request->getGet('q')             ?? '');
        $deliveryType = trim($this->request->getGet('delivery_type') ?? '');
        $sort         = trim($this->request->getGet('sort')          ?? 'latest');

        // 모델 메서드를 통해 12개/페이지 페이지네이션 목록 조회
        $items = $model->getList($q, $deliveryType, $sort);

        return view('service/goods/index', [
            'items'        => $items,
            'pager'        => $model->pager,
            'q'            => $q,
            'deliveryType' => $deliveryType,
            'sort'         => $sort,
        ]);
    }

    /**
     * GET /goods/{idx} — 상품 상세
     * 404: 존재하지 않는 상품 접근 시 PageNotFoundException 발생
     */
    public function detail(int $idx): string
    {
        $goodsModel       = new GoodsModel();
        $optionModel      = new GoodsOptionModel();
        $optionValueModel = new GoodsOptionValueModel();

        // 상품 단건 조회 (없으면 404)
        $goods = $goodsModel->getDetail($idx);
        if ($goods === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 옵션 그룹별로 선택값 배열을 함께 조립
        $rawOptions = $optionModel->getByGoods($idx);
        $options    = array_map(function ($opt) use ($optionValueModel) {
            $opt['values'] = $optionValueModel->getByOption($opt['idx']);
            return $opt;
        }, $rawOptions);

        // 상세 페이지 하단 추천용 — 현재 상품 제외 최신 6개
        $otherGoods = $goodsModel->getOtherGoods($idx);

        return view('service/goods/detail', [
            'goods'      => $goods,
            'options'    => $options,
            'otherGoods' => $otherGoods,
        ]);
    }
}
