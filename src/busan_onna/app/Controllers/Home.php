<?php

namespace App\Controllers;

use App\Models\MainBannerModel;
use App\Models\PlaceModel;
use App\Models\RestaurantModel;
use App\Models\ThumbnailModel;
use App\Models\HashtagNumberModel;
use App\Models\BusanMapsModel;
use App\Models\BusanMapsTop5Model;
use App\Models\TravelCourseModel;
use App\Models\TravelCourseItemModel;

class Home extends BaseController
{
    public function index(): string
    {
        // DB에서 활성 배너 목록 조회 (state=1, sort_order ASC)
        $bannerModel        = new MainBannerModel();
        $placeModel         = new PlaceModel();
        $restaurantModel    = new RestaurantModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();

        // DB에서 활성 관광지 최신 6개 조회
        $spotsRaw = $placeModel->where('state', 1)
                               ->orderBy('idx', 'DESC')
                               ->limit(6)
                               ->findAll();

        // 각 관광지에 대표 썸네일·구(district) 추가
        foreach ($spotsRaw as &$s) {
            $thumbs         = $thumbnailModel->getByPlace((int) $s['idx']);
            $s['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $s['address1'] ?? '', $m);
            $s['district'] = $m[1] ?? '';
        }
        unset($s);

        // DB에서 활성 맛집 최신 6개 조회
        $restaurantsRaw = $restaurantModel->where('state', 1)
                                          ->orderBy('idx', 'DESC')
                                          ->limit(6)
                                          ->findAll();

        // 각 맛집에 대표 썸네일·해시태그·구(district) 추가
        foreach ($restaurantsRaw as &$r) {
            $thumbs         = $thumbnailModel->getByRestaurant((int) $r['idx']);
            $r['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $r['tags']      = $hashtagNumberModel->getTagsByRestaurant((int) $r['idx']);
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $r['address1'] ?? '', $m);
            $r['district'] = $m[1] ?? '';
        }
        unset($r);

        // 지역별 탐색: 활성 구·군 목록 + 각 지역 TOP5 (state=1만)
        $mapsModel    = new BusanMapsModel();
        $top5Model    = new BusanMapsTop5Model();
        $activeRegions = $mapsModel->getActiveList();
        $top5Grouped   = $top5Model->getActiveGroupedByRegion();

        // 여행코스: 활성 코스 최신 3개 + 각 항목 조회
        $courseModel = new TravelCourseModel();
        $itemModel   = new TravelCourseItemModel();

        $coursesRaw = $courseModel->where('state', 1)
                                  ->orderBy('idx', 'DESC')
                                  ->limit(3)
                                  ->findAll();

        // 카드 색상 (순서 고정)
        $courseColors = ['#2563eb', '#8854d0', '#e67e22'];

        foreach ($coursesRaw as $ci => &$c) {
            $c['items'] = $itemModel->getByCourse((int) $c['idx']);
            $c['color'] = $courseColors[$ci % count($courseColors)];
        }
        unset($c);

        $data = [
            'banners'              => $bannerModel->getActiveBanners(),
            'regionList'           => $activeRegions,
            'regionTop5'           => $top5Grouped,
            'spots'                => $spotsRaw,
            'placeCategories'      => PlaceModel::CATEGORIES,
            'restaurants'          => $restaurantsRaw,
            'restaurantCategories' => RestaurantModel::CATEGORIES,
            'restaurantPrices'     => RestaurantModel::PRICE_RANGES,
            'courses'              => $coursesRaw,
        ];

        // 아이디 저장 쿠키가 있으면 로그인 모달 ID 필드에 미리 채워준다
        $data['saved_id'] = $this->request->getCookie('saved_id') ?? '';

        return view('home/index', $data);
    }

    /**
     * GET /hotplace
     * GET /hotplace/{지역명}
     * 지역별 핫플레이스 리스트 — 추후 구현 예정, 현재는 임시 페이지
     */
    public function hotplace(string $district = ''): string
    {
        $district = urldecode($district);
        return view('home/hotplace_temp', [
            'district' => $district,
            'saved_id' => $this->request->getCookie('saved_id') ?? '',
        ]);
    }
}
