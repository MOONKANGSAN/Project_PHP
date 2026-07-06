<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\PlaceModel;
use App\Models\EventModel;
use App\Models\ThumbnailModel;
use App\Models\HashtagNumberModel;
use App\Models\BusanMapsModel;

/**
 * 서비스(프론트) 페이지 컨트롤러
 */
class Service extends BaseController
{
    /**
     * 맛집 리스트 페이지
     */
    public function restaurants(): string
    {
        $restaurantModel    = new RestaurantModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();
        $db                 = \Config\Database::connect();

        // 필터·검색 파라미터
        $district = trim($this->request->getGet('district') ?? '');
        $category = trim($this->request->getGet('category') ?? '');
        $search   = trim($this->request->getGet('q')        ?? '');

        // 맛집 목록 (state=1, 필터 적용)
        $query = $restaurantModel->where('state', 1);

        if ($district !== '') {
            $query->like('address1', $district, 'both');
        }
        if ($category !== '') {
            $query->where('category_num', (int) $category);
        }
        if ($search !== '') {
            // 이름 OR 해시태그 이름으로 검색
            $taggedIdxs = $db->table('hashtag h')
                             ->select('hn.restaurant_idx')
                             ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                             ->like('h.name', $search, 'both')
                             ->where('hn.state', 1)
                             ->where('hn.restaurant_idx IS NOT NULL')
                             ->get()->getResultArray();

            $idxList = array_map('intval', array_column($taggedIdxs, 'restaurant_idx'));

            if (!empty($idxList)) {
                $query->groupStart()
                      ->like('name', $search, 'both')
                      ->orWhereIn('idx', $idxList)
                      ->groupEnd();
            } else {
                $query->like('name', $search, 'both');
            }
        }

        // 한 페이지 9건 페이지네이션
        $restaurants = $query->orderBy('idx', 'DESC')->paginate(9);
        $pager       = $restaurantModel->pager;
        $totalCount  = $pager->getTotal();

        // 각 맛집에 대표 썸네일·해시태그·구(district) 추가
        foreach ($restaurants as &$r) {
            $thumbs          = $thumbnailModel->getByRestaurant((int) $r['idx']);
            $r['thumbnail']  = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $r['tags']       = $hashtagNumberModel->getTagsByRestaurant((int) $r['idx']);

            // address1에서 '구/군' 추출 (예: "부산광역시 해운대구 ..." → "해운대구")
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $r['address1'] ?? '', $m);
            $r['district'] = $m[1] ?? '';
        }
        unset($r);

        // 필터 드롭다운용 구 목록 (DB 기준 동적 생성)
        $allAddresses = $db->table('busan_restaurant')
                           ->select('address1')
                           ->where('state', 1)
                           ->get()->getResultArray();

        $districtList = [];
        foreach ($allAddresses as $row) {
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $row['address1'] ?? '', $m);
            if (!empty($m[1]) && !in_array($m[1], $districtList, true)) {
                $districtList[] = $m[1];
            }
        }
        sort($districtList);

        $data = [
            'restaurants'    => $restaurants,
            'pager'          => $pager,
            'totalCount'     => $totalCount,
            'districtList'   => $districtList,
            'categories'     => RestaurantModel::CATEGORIES,
            'priceRanges'    => RestaurantModel::PRICE_RANGES,
            'activeDistrict' => $district,
            'activeCategory' => $category,
            'activeSearch'   => $search,
            'saved_id'       => $this->request->getCookie('saved_id') ?? '',
        ];

        return view('service/restaurant/list', $data);
    }

    /**
     * 맛집 상세 뷰 페이지
     * GET /restaurants/{idx}
     */
    public function restaurantView(int $idx): string
    {
        $restaurantModel    = new RestaurantModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();

        // state=1(활성) 맛집만 조회
        $restaurant = $restaurantModel->where('state', 1)->find($idx);

        if (!$restaurant) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 썸네일 전체 + 해시태그
        $thumbnails = $thumbnailModel->getByRestaurant($idx);
        $tags       = $hashtagNumberModel->getTagsByRestaurant($idx);

        // 조회수 +1
        $restaurantModel->update($idx, ['view_cnt' => ((int) ($restaurant['view_cnt'] ?? 0)) + 1]);

        return view('service/restaurant/view', [
            'restaurant'       => $restaurant,
            'thumbnails'       => $thumbnails,
            'tags'             => $tags,
            'categories'       => RestaurantModel::CATEGORIES,
            'priceRanges'      => RestaurantModel::PRICE_RANGES,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            'naverMapClientId' => env('NAVER_MAP_CLIENT_ID', ''),
        ]);
    }

    /**
     * 검색어 자동완성 API (AJAX)
     * GET /restaurants/suggest?q=검색어
     * 반환: JSON { suggestions: [{type, label, value}, ...] }
     */
    public function suggest(): void
    {
        // JSON 응답 헤더
        $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

        $q = trim($this->request->getGet('q') ?? '');

        // 빈 검색어 또는 1자 미만은 빈 결과 반환
        if (mb_strlen($q) < 1) {
            echo json_encode(['suggestions' => []]);
            return;
        }

        $db          = \Config\Database::connect();
        $suggestions = [];

        // 1. 맛집 이름 검색 (최대 5건)
        $names = $db->table('busan_restaurant')
                    ->select('name')
                    ->like('name', $q, 'both')
                    ->where('state', 1)
                    ->orderBy('view_cnt', 'DESC')
                    ->limit(5)
                    ->get()->getResultArray();

        foreach ($names as $row) {
            $suggestions[] = [
                'type'  => 'name',
                'label' => $row['name'],
                'value' => $row['name'],
            ];
        }

        // 2. 해시태그 검색 (최대 5건, 사용 빈도 내림차순)
        $tags = $db->table('hashtag')
                   ->select('name, use_count')
                   ->like('name', $q, 'both')
                   ->orderBy('use_count', 'DESC')
                   ->limit(5)
                   ->get()->getResultArray();

        foreach ($tags as $row) {
            $suggestions[] = [
                'type'  => 'hashtag',
                'label' => $row['name'],
                'value' => $row['name'],
            ];
        }

        // 3. 지역(구/군) 검색 — 등록된 주소에서 구 이름 추출 후 검색어 포함 여부 확인
        $allAddresses = $db->table('busan_restaurant')
                           ->select('address1')
                           ->where('state', 1)
                           ->get()->getResultArray();

        $districtSeen = [];
        foreach ($allAddresses as $row) {
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $row['address1'] ?? '', $m);
            if (empty($m[1])) continue;

            $dist = $m[1];
            if (in_array($dist, $districtSeen, true)) continue;
            if (mb_strpos($dist, $q) === false) continue;

            $districtSeen[] = $dist;
            $suggestions[]  = [
                'type'  => 'district',
                'label' => $dist,
                'value' => $dist,
            ];

            if (count($districtSeen) >= 3) break;
        }

        echo json_encode(['suggestions' => $suggestions], JSON_UNESCAPED_UNICODE);
    }

    // ================================================================
    // 관광지
    // ================================================================

    /**
     * 관광지 상세 뷰 페이지
     * GET /spots/{idx}
     */
    public function spotView(int $idx): string
    {
        $placeModel         = new PlaceModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();

        $spot = $placeModel->where('state', 1)->find($idx);

        if (!$spot) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $thumbnails = $thumbnailModel->getByPlace($idx);
        $tags       = $hashtagNumberModel->getTagsByPlace($idx);

        // 조회수 +1
        $placeModel->update($idx, ['view_cnt' => ((int)($spot['view_cnt'] ?? 0)) + 1]);

        return view('service/spot/view', [
            'spot'             => $spot,
            'thumbnails'       => $thumbnails,
            'tags'             => $tags,
            'categories'       => PlaceModel::CATEGORIES,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            'naverMapClientId' => env('NAVER_MAP_CLIENT_ID', ''),
        ]);
    }

    /**
     * 관광지 리스트 페이지
     */
    public function spots(): string
    {
        $placeModel         = new PlaceModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();
        $db                 = \Config\Database::connect();

        $district = trim($this->request->getGet('district') ?? '');
        $category = trim($this->request->getGet('category') ?? '');
        $search   = trim($this->request->getGet('q')        ?? '');

        $query = $placeModel->where('state', 1);

        if ($district !== '') {
            $query->like('address1', $district, 'both');
        }
        if ($category !== '') {
            $query->where('category_num', (int) $category);
        }
        if ($search !== '') {
            $taggedIdxs = $db->table('hashtag h')
                             ->select('hn.place_idx')
                             ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                             ->like('h.name', $search, 'both')
                             ->where('hn.state', 1)
                             ->where('hn.place_idx IS NOT NULL')
                             ->get()->getResultArray();

            $idxList = array_map('intval', array_column($taggedIdxs, 'place_idx'));

            if (!empty($idxList)) {
                $query->groupStart()
                      ->like('name', $search, 'both')
                      ->orWhereIn('idx', $idxList)
                      ->groupEnd();
            } else {
                $query->like('name', $search, 'both');
            }
        }

        $spots      = $query->orderBy('idx', 'DESC')->paginate(9);
        $pager      = $placeModel->pager;
        $totalCount = $pager->getTotal();

        foreach ($spots as &$s) {
            $thumbs         = $thumbnailModel->getByPlace((int) $s['idx']);
            $s['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $s['tags']      = $hashtagNumberModel->getTagsByPlace((int) $s['idx']);

            preg_match('/부산광역시\s+(\S+(?:구|군))/', $s['address1'] ?? '', $m);
            $s['district'] = $m[1] ?? '';
        }
        unset($s);

        $allAddresses = $db->table('busan_place')
                           ->select('address1')
                           ->where('state', 1)
                           ->get()->getResultArray();

        $districtList = [];
        foreach ($allAddresses as $row) {
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $row['address1'] ?? '', $m);
            if (!empty($m[1]) && !in_array($m[1], $districtList, true)) {
                $districtList[] = $m[1];
            }
        }
        sort($districtList);

        return view('service/spot/list', [
            'spots'          => $spots,
            'pager'          => $pager,
            'totalCount'     => $totalCount,
            'districtList'   => $districtList,
            'categories'     => PlaceModel::CATEGORIES,
            'activeDistrict' => $district,
            'activeCategory' => $category,
            'activeSearch'   => $search,
            'saved_id'       => $this->request->getCookie('saved_id') ?? '',
        ]);
    }

    /**
     * 관광지 검색 자동완성 API
     * GET /spots/suggest?q=검색어
     */
    public function spotsSuggest(): void
    {
        $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

        $q = trim($this->request->getGet('q') ?? '');
        if (mb_strlen($q) < 1) {
            echo json_encode(['suggestions' => []]);
            return;
        }

        $db          = \Config\Database::connect();
        $suggestions = [];

        $names = $db->table('busan_place')
                    ->select('name')
                    ->like('name', $q, 'both')
                    ->where('state', 1)
                    ->orderBy('view_cnt', 'DESC')
                    ->limit(5)
                    ->get()->getResultArray();

        foreach ($names as $row) {
            $suggestions[] = ['type' => 'name', 'label' => $row['name'], 'value' => $row['name']];
        }

        $tags = $db->table('hashtag')
                   ->select('name')
                   ->like('name', $q, 'both')
                   ->orderBy('use_count', 'DESC')
                   ->limit(5)
                   ->get()->getResultArray();

        foreach ($tags as $row) {
            $suggestions[] = ['type' => 'hashtag', 'label' => $row['name'], 'value' => $row['name']];
        }

        $allAddresses = $db->table('busan_place')
                           ->select('address1')
                           ->where('state', 1)
                           ->get()->getResultArray();

        $districtSeen = [];
        foreach ($allAddresses as $row) {
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $row['address1'] ?? '', $m);
            if (empty($m[1]) || in_array($m[1], $districtSeen, true) || mb_strpos($m[1], $q) === false) continue;
            $districtSeen[] = $m[1];
            $suggestions[]  = ['type' => 'district', 'label' => $m[1], 'value' => $m[1]];
            if (count($districtSeen) >= 3) break;
        }

        echo json_encode(['suggestions' => $suggestions], JSON_UNESCAPED_UNICODE);
    }

    // ================================================================
    // 축제·행사
    // ================================================================

    /**
     * 축제·행사 상세 뷰 페이지
     * GET /festivals/{idx}
     */
    public function festivalView(int $idx): string
    {
        $eventModel         = new EventModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();

        $festival = $eventModel->where('state', 1)->find($idx);

        if (!$festival) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $thumbnails = $thumbnailModel->getByEvent($idx);
        $tags       = $hashtagNumberModel->getTagsByEvent($idx);

        // 진행 상태 계산
        $today = date('Y-m-d');
        if (!empty($festival['start_date']) && !empty($festival['end_date'])) {
            if ($today < $festival['start_date'])      $festival['status'] = 'upcoming';
            elseif ($today > $festival['end_date'])    $festival['status'] = 'ended';
            else                                       $festival['status'] = 'ongoing';
        } else {
            $festival['status'] = '';
        }

        // 조회수 +1
        $eventModel->update($idx, ['view_cnt' => ((int)($festival['view_cnt'] ?? 0)) + 1]);

        return view('service/festival/view', [
            'festival'         => $festival,
            'thumbnails'       => $thumbnails,
            'tags'             => $tags,
            'categories'       => EventModel::CATEGORIES,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            'naverMapClientId' => env('NAVER_MAP_CLIENT_ID', ''),
        ]);
    }

    /**
     * 축제 리스트 페이지
     */
    public function festivals(): string
    {
        $eventModel         = new EventModel();
        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();
        $db                 = \Config\Database::connect();

        $district = trim($this->request->getGet('district') ?? '');
        $category = trim($this->request->getGet('category') ?? '');
        $search   = trim($this->request->getGet('q')        ?? '');
        $isFree   = trim($this->request->getGet('is_free')  ?? '');

        $query = $eventModel->where('state', 1);

        if ($district !== '') {
            $query->like('address1', $district, 'both');
        }
        if ($category !== '') {
            $query->where('category_num', (int) $category);
        }
        if ($isFree !== '') {
            $query->where('is_free', (int) $isFree);
        }
        if ($search !== '') {
            $taggedIdxs = $db->table('hashtag h')
                             ->select('hn.event_idx')
                             ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                             ->like('h.name', $search, 'both')
                             ->where('hn.state', 1)
                             ->where('hn.event_idx IS NOT NULL')
                             ->get()->getResultArray();

            $idxList = array_map('intval', array_column($taggedIdxs, 'event_idx'));

            if (!empty($idxList)) {
                $query->groupStart()
                      ->like('name', $search, 'both')
                      ->orWhereIn('idx', $idxList)
                      ->groupEnd();
            } else {
                $query->like('name', $search, 'both');
            }
        }

        $festivals  = $query->orderBy('start_date', 'DESC')->paginate(9);
        $pager      = $eventModel->pager;
        $totalCount = $pager->getTotal();

        foreach ($festivals as &$f) {
            $thumbs         = $thumbnailModel->getByEvent((int) $f['idx']);
            $f['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $f['tags']      = $hashtagNumberModel->getTagsByEvent((int) $f['idx']);

            preg_match('/부산광역시\s+(\S+(?:구|군))/', $f['address1'] ?? '', $m);
            $f['district'] = $m[1] ?? '';

            // 행사 진행 상태 (진행중/예정/종료)
            $today = date('Y-m-d');
            if (!empty($f['start_date']) && !empty($f['end_date'])) {
                if ($today < $f['start_date']) {
                    $f['status'] = 'upcoming';
                } elseif ($today > $f['end_date']) {
                    $f['status'] = 'ended';
                } else {
                    $f['status'] = 'ongoing';
                }
            } else {
                $f['status'] = '';
            }
        }
        unset($f);

        $allAddresses = $db->table('busan_event')
                           ->select('address1')
                           ->where('state', 1)
                           ->get()->getResultArray();

        $districtList = [];
        foreach ($allAddresses as $row) {
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $row['address1'] ?? '', $m);
            if (!empty($m[1]) && !in_array($m[1], $districtList, true)) {
                $districtList[] = $m[1];
            }
        }
        sort($districtList);

        return view('service/festival/list', [
            'festivals'      => $festivals,
            'pager'          => $pager,
            'totalCount'     => $totalCount,
            'districtList'   => $districtList,
            'categories'     => EventModel::CATEGORIES,
            'activeDistrict' => $district,
            'activeCategory' => $category,
            'activeSearch'   => $search,
            'activeIsFree'   => $isFree,
            'saved_id'       => $this->request->getCookie('saved_id') ?? '',
        ]);
    }

    // ================================================================
    // 지역별 핫플레이스
    // ================================================================

    /**
     * 지역별 핫플레이스 리스트 페이지
     * GET /hotplace          — 전체 지역
     * GET /hotplace/{idx}    — 특정 지역(busan_maps.idx)
     * ?tab=spot|restaurant|festival
     */
    public function hotplace(int $idx = 0): string
    {
        $tab      = trim($this->request->getGet('tab')      ?? 'spot');
        $category = trim($this->request->getGet('category') ?? '');
        $search   = trim($this->request->getGet('q')        ?? '');

        // 허용된 탭 값만 사용
        if (!in_array($tab, ['spot', 'restaurant', 'festival'], true)) {
            $tab = 'spot';
        }

        $thumbnailModel     = new ThumbnailModel();
        $hashtagNumberModel = new HashtagNumberModel();
        $db                 = \Config\Database::connect();

        // 지역별 탐색 활성 지역 목록 (상단 지역 탭용)
        $mapsModel  = new BusanMapsModel();
        $regionList = $mapsModel->getActiveList();

        // idx로 현재 선택된 지역명 조회 (address1 필터에 사용)
        $district = '';
        if ($idx > 0) {
            $region = $mapsModel->find($idx);
            if ($region) {
                $district = $region['name'];
            }
        }

        $items      = [];
        $pager      = null;
        $totalCount = 0;
        $categories = [];

        if ($tab === 'spot') {
            // ---- 관광지 ----
            $placeModel = new PlaceModel();
            $query      = $placeModel->where('state', 1);

            if ($district !== '') {
                $query->like('address1', $district, 'both');
            }
            if ($category !== '') {
                $query->where('category_num', (int) $category);
            }
            if ($search !== '') {
                $taggedIdxs = $db->table('hashtag h')
                                 ->select('hn.place_idx')
                                 ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                                 ->like('h.name', $search, 'both')
                                 ->where('hn.state', 1)
                                 ->where('hn.place_idx IS NOT NULL')
                                 ->get()->getResultArray();
                $idxList = array_map('intval', array_column($taggedIdxs, 'place_idx'));
                if (!empty($idxList)) {
                    $query->groupStart()->like('name', $search, 'both')->orWhereIn('idx', $idxList)->groupEnd();
                } else {
                    $query->like('name', $search, 'both');
                }
            }

            $items      = $query->orderBy('idx', 'DESC')->paginate(9);
            $pager      = $placeModel->pager;
            $totalCount = $pager->getTotal();
            $categories = PlaceModel::CATEGORIES;

            foreach ($items as &$s) {
                $thumbs         = $thumbnailModel->getByPlace((int) $s['idx']);
                $s['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
                $s['tags']      = $hashtagNumberModel->getTagsByPlace((int) $s['idx']);
                preg_match('/부산광역시\s+(\S+(?:구|군))/', $s['address1'] ?? '', $m);
                $s['district'] = $m[1] ?? '';
            }
            unset($s);

        } elseif ($tab === 'restaurant') {
            // ---- 맛집 ----
            $restaurantModel = new RestaurantModel();
            $query           = $restaurantModel->where('state', 1);

            if ($district !== '') {
                $query->like('address1', $district, 'both');
            }
            if ($category !== '') {
                $query->where('category_num', (int) $category);
            }
            if ($search !== '') {
                $taggedIdxs = $db->table('hashtag h')
                                 ->select('hn.restaurant_idx')
                                 ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                                 ->like('h.name', $search, 'both')
                                 ->where('hn.state', 1)
                                 ->where('hn.restaurant_idx IS NOT NULL')
                                 ->get()->getResultArray();
                $idxList = array_map('intval', array_column($taggedIdxs, 'restaurant_idx'));
                if (!empty($idxList)) {
                    $query->groupStart()->like('name', $search, 'both')->orWhereIn('idx', $idxList)->groupEnd();
                } else {
                    $query->like('name', $search, 'both');
                }
            }

            $items      = $query->orderBy('idx', 'DESC')->paginate(9);
            $pager      = $restaurantModel->pager;
            $totalCount = $pager->getTotal();
            $categories = RestaurantModel::CATEGORIES;

            foreach ($items as &$r) {
                $thumbs         = $thumbnailModel->getByRestaurant((int) $r['idx']);
                $r['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
                $r['tags']      = $hashtagNumberModel->getTagsByRestaurant((int) $r['idx']);
                preg_match('/부산광역시\s+(\S+(?:구|군))/', $r['address1'] ?? '', $m);
                $r['district'] = $m[1] ?? '';
            }
            unset($r);

        } else {
            // ---- 축제 ----
            $eventModel = new EventModel();
            $query      = $eventModel->where('state', 1);

            if ($district !== '') {
                $query->like('address1', $district, 'both');
            }
            if ($category !== '') {
                $query->where('category_num', (int) $category);
            }
            if ($search !== '') {
                $taggedIdxs = $db->table('hashtag h')
                                 ->select('hn.event_idx')
                                 ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                                 ->like('h.name', $search, 'both')
                                 ->where('hn.state', 1)
                                 ->where('hn.event_idx IS NOT NULL')
                                 ->get()->getResultArray();
                $idxList = array_map('intval', array_column($taggedIdxs, 'event_idx'));
                if (!empty($idxList)) {
                    $query->groupStart()->like('name', $search, 'both')->orWhereIn('idx', $idxList)->groupEnd();
                } else {
                    $query->like('name', $search, 'both');
                }
            }

            $items      = $query->orderBy('start_date', 'DESC')->paginate(9);
            $pager      = $eventModel->pager;
            $totalCount = $pager->getTotal();
            $categories = EventModel::CATEGORIES;

            $today = date('Y-m-d');
            foreach ($items as &$f) {
                $thumbs         = $thumbnailModel->getByEvent((int) $f['idx']);
                $f['thumbnail'] = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
                $f['tags']      = $hashtagNumberModel->getTagsByEvent((int) $f['idx']);
                preg_match('/부산광역시\s+(\S+(?:구|군))/', $f['address1'] ?? '', $m);
                $f['district'] = $m[1] ?? '';
                if (!empty($f['start_date']) && !empty($f['end_date'])) {
                    if ($today < $f['start_date'])   $f['status'] = 'upcoming';
                    elseif ($today > $f['end_date']) $f['status'] = 'ended';
                    else                             $f['status'] = 'ongoing';
                } else {
                    $f['status'] = '';
                }
            }
            unset($f);
        }

        return view('service/hotplace/list', [
            'regionList'     => $regionList,
            'activeIdx'      => $idx,
            'activeDistrict' => $district,
            'activeTab'      => $tab,
            'activeCategory' => $category,
            'activeSearch'   => $search,
            'items'          => $items,
            'pager'          => $pager,
            'totalCount'     => $totalCount,
            'categories'     => $categories,
            'priceRanges'    => RestaurantModel::PRICE_RANGES,
            'saved_id'       => $this->request->getCookie('saved_id') ?? '',
        ]);
    }

    /**
     * 축제 검색 자동완성 API
     * GET /festivals/suggest?q=검색어
     */
    public function festivalsSuggest(): void
    {
        $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

        $q = trim($this->request->getGet('q') ?? '');
        if (mb_strlen($q) < 1) {
            echo json_encode(['suggestions' => []]);
            return;
        }

        $db          = \Config\Database::connect();
        $suggestions = [];

        $names = $db->table('busan_event')
                    ->select('name')
                    ->like('name', $q, 'both')
                    ->where('state', 1)
                    ->orderBy('view_cnt', 'DESC')
                    ->limit(5)
                    ->get()->getResultArray();

        foreach ($names as $row) {
            $suggestions[] = ['type' => 'name', 'label' => $row['name'], 'value' => $row['name']];
        }

        $tags = $db->table('hashtag')
                   ->select('name')
                   ->like('name', $q, 'both')
                   ->orderBy('use_count', 'DESC')
                   ->limit(5)
                   ->get()->getResultArray();

        foreach ($tags as $row) {
            $suggestions[] = ['type' => 'hashtag', 'label' => $row['name'], 'value' => $row['name']];
        }

        $allAddresses = $db->table('busan_event')
                           ->select('address1')
                           ->where('state', 1)
                           ->get()->getResultArray();

        $districtSeen = [];
        foreach ($allAddresses as $row) {
            preg_match('/부산광역시\s+(\S+(?:구|군))/', $row['address1'] ?? '', $m);
            if (empty($m[1]) || in_array($m[1], $districtSeen, true) || mb_strpos($m[1], $q) === false) continue;
            $districtSeen[] = $m[1];
            $suggestions[]  = ['type' => 'district', 'label' => $m[1], 'value' => $m[1]];
            if (count($districtSeen) >= 3) break;
        }

        echo json_encode(['suggestions' => $suggestions], JSON_UNESCAPED_UNICODE);
    }
}
