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

        // DB에서 활성 관광지 좋아요 많은 순 6개 조회
        // like_cnt는 스케줄러1(`php spark like:sync`, 매 정각 cron)이 reactions 테이블의
        // 활성화된 좋아요(state != 9)를 집계해서 반영해주는 테이블 컬럼 — 메인 페이지는
        // 실시간 집계 대신 이 컬럼 값을 그대로 정렬·표시 기준으로 사용한다.
        $spotsRaw = $placeModel->where('state', 1)
                               ->orderBy('like_cnt', 'DESC')
                               ->orderBy('idx', 'DESC')
                               ->limit(6)
                               ->findAll();

        // 각 관광지에 대표 썸네일·구(district)·좋아요 수(뷰 표시용 like_count 키) 추가
        foreach ($spotsRaw as &$s) {
            $thumbs          = $thumbnailModel->getByPlace((int) $s['idx']);
            $s['thumbnail']  = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $s['like_count'] = (int) ($s['like_cnt'] ?? 0);
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $s['address1'] ?? '', $m);
            $s['district'] = $m[1] ?? '';
        }
        unset($s);

        // DB에서 활성 맛집 좋아요 많은 순 6개 조회 (관광지와 동일하게 like_cnt 컬럼 기준)
        $restaurantsRaw = $restaurantModel->where('state', 1)
                                          ->orderBy('like_cnt', 'DESC')
                                          ->orderBy('idx', 'DESC')
                                          ->limit(6)
                                          ->findAll();

        // 각 맛집에 대표 썸네일·해시태그·구(district)·좋아요 수(뷰 표시용 like_count 키) 추가
        foreach ($restaurantsRaw as &$r) {
            $thumbs          = $thumbnailModel->getByRestaurant((int) $r['idx']);
            $r['thumbnail']  = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $r['tags']       = $hashtagNumberModel->getTagsByRestaurant((int) $r['idx']);
            $r['like_count'] = (int) ($r['like_cnt'] ?? 0);
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
