<?php

namespace App\Controllers;

use App\Models\MainBannerModel;
use App\Models\PlaceModel;
use App\Models\RestaurantModel;
use App\Models\ThumbnailModel;
use App\Models\HashtagNumberModel;

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

        $data = [
            'banners'            => $bannerModel->getActiveBanners(),
            'spots'              => $spotsRaw,
            'placeCategories'    => PlaceModel::CATEGORIES,
            'restaurants'        => $restaurantsRaw,
            'restaurantCategories' => RestaurantModel::CATEGORIES,
            'restaurantPrices'   => RestaurantModel::PRICE_RANGES,
            'courses' => [
                [
                    'title'    => '해안 드라이브 코스',
                    'theme'    => '🌊 바다',
                    'duration' => '1일 코스',
                    'spots'    => ['해운대해수욕장', '광안리해수욕장', '이기대공원', '태종대'],
                    'desc'     => '부산의 아름다운 해안선을 따라 달리는 드라이브 코스',
                    'color'    => '#2e86de',
                ],
                [
                    'title'    => '역사·문화 탐방 코스',
                    'theme'    => '🏛️ 문화',
                    'duration' => '반일 코스',
                    'spots'    => ['용두산공원', '보수동 책방골목', '감천문화마을', '자갈치시장'],
                    'desc'     => '부산의 역사와 예술이 살아 숨쉬는 문화 탐방 코스',
                    'color'    => '#8854d0',
                ],
                [
                    'title'    => '미식 탐방 코스',
                    'theme'    => '🍽️ 맛집',
                    'duration' => '1일 코스',
                    'spots'    => ['돼지국밥(서면)', '씨앗호떡(남포동)', '어묵(자갈치)', '서면 낙곱새'],
                    'desc'     => '부산의 대표 향토 음식을 한 번에 맛보는 미식 기행',
                    'color'    => '#e67e22',
                ],
            ],
        ];

        // 아이디 저장 쿠키가 있으면 로그인 모달 ID 필드에 미리 채워준다
        $data['saved_id'] = $this->request->getCookie('saved_id') ?? '';

        return view('home/index', $data);
    }
}
