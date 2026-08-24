<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\PlaceModel;
use App\Models\EventModel;
use App\Models\ThumbnailModel;
use App\Models\HashtagNumberModel;
use App\Models\BusanMapsModel;
use App\Models\TravelCourseModel;
use App\Models\TravelCourseItemModel;
use App\Models\ReactionModel;
use App\Models\SiteEventModel;
use App\Models\EventReviewModel;
use App\Models\EventLikeLogModel;

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
        // sort 파라미터 자체가 없으면(최초 진입) 기본 정렬을 '좋아요순'으로 하되,
        // "최신순"(value="") 옵션을 사용자가 명시적으로 선택한 경우(sort= 빈 값으로 전송)는
        // 구분해서 최신순이 그대로 동작하도록 null과 빈 문자열을 다르게 취급한다.
        $sortParam = $this->request->getGet('sort');
        $sort      = $sortParam === null ? 'like' : trim($sortParam);

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

        // 정렬: 좋아요순(reactions 집계) / 최신순(기본) / 가나다순
        switch ($sort) {
            case 'like':
                $query->select("busan_restaurant.*, (SELECT COUNT(*) FROM reactions WHERE reactions.target_type = 'restaurant' AND reactions.type = 'like' AND reactions.state != 9 AND reactions.target_idx = busan_restaurant.idx) AS like_count", false)
                      ->orderBy('like_count', 'DESC');
                break;
            case 'name':
                $query->orderBy('name', 'ASC');
                break;
            case 'new':
            default:
                $query->orderBy('idx', 'DESC');
                break;
        }

        // 한 페이지 9건 페이지네이션
        $restaurants = $query->paginate(9);
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

        // 맛집 like_count 일괄 조회 (단일 쿼리)
        if (!empty($restaurants)) {
            $rows = $db->table('reactions')
                       ->select('target_idx, COUNT(*) as cnt')
                       ->where('target_type', 'restaurant')
                       ->where('type', 'like')
                       ->where('state !=', 9)
                       ->whereIn('target_idx', array_column($restaurants, 'idx'))
                       ->groupBy('target_idx')
                       ->get()->getResultArray();
            $likeCounts = array_column($rows, 'cnt', 'target_idx');
            foreach ($restaurants as &$r) {
                $r['like_count'] = (int)($likeCounts[$r['idx']] ?? 0);
            }
            unset($r);
        }

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
            'activeSort'     => $sort,
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

        // 추천/비추천 집계 및 현재 유저 반응 조회
        $reactionModel  = new ReactionModel();
        $reactionCounts = $reactionModel->getCounts('restaurant', $idx);
        $userReaction   = null;
        if ($userIdx = (int) session()->get('user.idx')) {
            $row          = $reactionModel->getUserReaction($userIdx, 'restaurant', $idx);
            $userReaction = ($row && (int) $row['state'] !== 9) ? $row['type'] : null;
        }

        return view('service/restaurant/view', [
            'restaurant'       => $restaurant,
            'thumbnails'       => $thumbnails,
            'tags'             => $tags,
            'categories'       => RestaurantModel::CATEGORIES,
            'priceRanges'      => RestaurantModel::PRICE_RANGES,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            // 카카오맵 SDK 로드에 쓰는 JavaScript 키 (공개용, 도메인으로 보호됨)
            'kakaoMapJsKey'    => env('KAKAO_MAP_JS_KEY', ''),
            'likeCount'        => $reactionCounts['like'],
            'dislikeCount'     => $reactionCounts['dislike'],
            'userReaction'     => $userReaction,
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

        // 추천/비추천 집계 및 현재 유저 반응 조회
        $reactionModel  = new ReactionModel();
        $reactionCounts = $reactionModel->getCounts('spot', $idx);
        $userReaction   = null;
        if ($userIdx = (int) session()->get('user.idx')) {
            $row          = $reactionModel->getUserReaction($userIdx, 'spot', $idx);
            $userReaction = ($row && (int) $row['state'] !== 9) ? $row['type'] : null;
        }

        return view('service/spot/view', [
            'spot'             => $spot,
            'thumbnails'       => $thumbnails,
            'tags'             => $tags,
            'categories'       => PlaceModel::CATEGORIES,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            // 카카오맵 SDK 로드에 쓰는 JavaScript 키 (공개용, 도메인으로 보호됨)
            'kakaoMapJsKey'    => env('KAKAO_MAP_JS_KEY', ''),
            'likeCount'        => $reactionCounts['like'],
            'dislikeCount'     => $reactionCounts['dislike'],
            'userReaction'     => $userReaction,
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
        // sort 파라미터가 아예 없으면(최초 진입) 기본 정렬을 '좋아요순'으로,
        // "최신순"을 명시적으로 선택한 경우(sort= 빈 값)는 구분해서 그대로 동작하게 한다.
        $sortParam = $this->request->getGet('sort');
        $sort      = $sortParam === null ? 'like' : trim($sortParam);

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

        // 정렬: 좋아요순(reactions 집계) / 최신순(기본) / 가나다순
        switch ($sort) {
            case 'like':
                $query->select("busan_place.*, (SELECT COUNT(*) FROM reactions WHERE reactions.target_type = 'spot' AND reactions.type = 'like' AND reactions.state != 9 AND reactions.target_idx = busan_place.idx) AS like_count", false)
                      ->orderBy('like_count', 'DESC');
                break;
            case 'name':
                $query->orderBy('name', 'ASC');
                break;
            case 'new':
            default:
                $query->orderBy('idx', 'DESC');
                break;
        }

        $spots      = $query->paginate(9);
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

        // 관광지 like_count 일괄 조회 (단일 쿼리)
        if (!empty($spots)) {
            $rows = $db->table('reactions')
                       ->select('target_idx, COUNT(*) as cnt')
                       ->where('target_type', 'spot')
                       ->where('type', 'like')
                       ->where('state !=', 9)
                       ->whereIn('target_idx', array_column($spots, 'idx'))
                       ->groupBy('target_idx')
                       ->get()->getResultArray();
            $likeCounts = array_column($rows, 'cnt', 'target_idx');
            foreach ($spots as &$s) {
                $s['like_count'] = (int)($likeCounts[$s['idx']] ?? 0);
            }
            unset($s);
        }

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
            'activeSort'     => $sort,
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

        // 추천/비추천 집계 및 현재 유저 반응 조회
        $reactionModel  = new ReactionModel();
        $reactionCounts = $reactionModel->getCounts('festival', $idx);
        $userReaction   = null;
        if ($userIdx = (int) session()->get('user.idx')) {
            $row          = $reactionModel->getUserReaction($userIdx, 'festival', $idx);
            $userReaction = ($row && (int) $row['state'] !== 9) ? $row['type'] : null;
        }

        return view('service/festival/view', [
            'festival'         => $festival,
            'thumbnails'       => $thumbnails,
            'tags'             => $tags,
            'categories'       => EventModel::CATEGORIES,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            // 카카오맵 SDK 로드에 쓰는 JavaScript 키 (공개용, 도메인으로 보호됨)
            'kakaoMapJsKey'    => env('KAKAO_MAP_JS_KEY', ''),
            'likeCount'        => $reactionCounts['like'],
            'dislikeCount'     => $reactionCounts['dislike'],
            'userReaction'     => $userReaction,
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
        // sort 파라미터가 아예 없으면(최초 진입) 기본 정렬을 '좋아요순'으로,
        // "최신순"을 명시적으로 선택한 경우(sort= 빈 값)는 구분해서 그대로 동작하게 한다.
        $sortParam = $this->request->getGet('sort');
        $sort      = $sortParam === null ? 'like' : trim($sortParam);

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

        // 정렬: 좋아요순(reactions 집계) / 최신순(기본, 시작일 기준) / 가나다순
        switch ($sort) {
            case 'like':
                $query->select("busan_event.*, (SELECT COUNT(*) FROM reactions WHERE reactions.target_type = 'festival' AND reactions.type = 'like' AND reactions.state != 9 AND reactions.target_idx = busan_event.idx) AS like_count", false)
                      ->orderBy('like_count', 'DESC');
                break;
            case 'name':
                $query->orderBy('name', 'ASC');
                break;
            case 'new':
            default:
                $query->orderBy('start_date', 'DESC');
                break;
        }

        $festivals  = $query->paginate(9);
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

        // 축제 like_count 일괄 조회 (단일 쿼리)
        if (!empty($festivals)) {
            $rows = $db->table('reactions')
                       ->select('target_idx, COUNT(*) as cnt')
                       ->where('target_type', 'festival')
                       ->where('type', 'like')
                       ->where('state !=', 9)
                       ->whereIn('target_idx', array_column($festivals, 'idx'))
                       ->groupBy('target_idx')
                       ->get()->getResultArray();
            $likeCounts = array_column($rows, 'cnt', 'target_idx');
            foreach ($festivals as &$f) {
                $f['like_count'] = (int)($likeCounts[$f['idx']] ?? 0);
            }
            unset($f);
        }

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
            'activeSort'     => $sort,
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

    // ================================================================
    // 여행코스
    // ================================================================

    /**
     * 여행코스 목록 페이지
     * GET /travel-courses
     */
    public function travelCourses(): string
    {
        $courseModel = new TravelCourseModel();
        $db          = \Config\Database::connect();

        $sido   = trim($this->request->getGet('sido') ?? '');
        $search = trim($this->request->getGet('q')    ?? '');
        $sort   = trim($this->request->getGet('sort')  ?? '');

        $query = $courseModel->where('state', 1);

        if ($sido !== '') {
            $query->where('sido', $sido);
        }
        if ($search !== '') {
            $query->like('title', $search, 'both');
        }

        // 정렬: 최신순(기본) / 가나다순 — 여행코스는 좋아요 기능이 없어 좋아요순 미제공
        switch ($sort) {
            case 'name':
                $query->orderBy('title', 'ASC');
                break;
            case 'new':
            default:
                $query->orderBy('idx', 'DESC');
                break;
        }

        $courses    = $query->paginate(9);
        $pager      = $courseModel->pager;
        $totalCount = $pager->getTotal();

        // 항목 수를 한 번의 쿼리로 가져와 N+1 방지
        $idxList    = array_column($courses, 'idx');
        $itemCounts = [];
        if (!empty($idxList)) {
            $rows = $db->table('travel_course_item')
                       ->select('course_idx, COUNT(*) as cnt')
                       ->whereIn('course_idx', $idxList)
                       ->groupBy('course_idx')
                       ->get()->getResultArray();
            foreach ($rows as $row) {
                $itemCounts[(int) $row['course_idx']] = (int) $row['cnt'];
            }
        }
        foreach ($courses as &$c) {
            $c['item_count'] = $itemCounts[(int) $c['idx']] ?? 0;
        }
        unset($c);

        // 지역 필터 드롭다운용 sido 목록
        $sidoRows = $db->table('travel_course')
                       ->select('sido')
                       ->where('state', 1)
                       ->where('sido IS NOT NULL')
                       ->where('sido !=', '')
                       ->distinct()
                       ->orderBy('sido', 'ASC')
                       ->get()->getResultArray();

        return view('service/travel_course/list', [
            'courses'      => $courses,
            'pager'        => $pager,
            'totalCount'   => $totalCount,
            'sidoList'     => array_column($sidoRows, 'sido'),
            'activeSido'   => $sido,
            'activeSearch' => $search,
            'activeSort'   => $sort,
            'saved_id'     => $this->request->getCookie('saved_id') ?? '',
        ]);
    }

    /**
     * 여행코스 상세 페이지
     * GET /travel-courses/{idx}
     */
    public function travelCourseView(int $idx): string
    {
        $courseModel = new TravelCourseModel();
        $itemModel   = new TravelCourseItemModel();

        $course = $courseModel->where('state', 1)->find($idx);

        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $items = $itemModel->getByCourse($idx);

        return view('service/travel_course/view', [
            'course'           => $course,
            'items'            => $items,
            'saved_id'         => $this->request->getCookie('saved_id') ?? '',
            // 카카오맵 SDK 로드에 쓰는 JavaScript 키 (공개용, 도메인으로 보호됨)
            'kakaoMapJsKey'    => env('KAKAO_MAP_JS_KEY', ''),
        ]);
    }

    // ================================================================
    // 사이트 이벤트
    // ================================================================

    /**
     * 이벤트 리스트 페이지
     * GET /events
     */
    public function events(): string
    {
        $siteEventModel = new SiteEventModel();

        $type   = trim($this->request->getGet('type')   ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $search = trim($this->request->getGet('q')      ?? '');
        $sort   = trim($this->request->getGet('sort')    ?? '');

        $query = $siteEventModel->where('state', 1);

        if ($type !== '') {
            $query->where('event_type', (int) $type);
        }
        if ($search !== '') {
            $query->like('title', $search, 'both');
        }

        // 정렬: 최신순(기본, 시작일 기준) / 가나다순 — 이벤트는 실사용 좋아요 집계가 없어 좋아요순 미제공
        switch ($sort) {
            case 'name':
                $query->orderBy('title', 'ASC');
                break;
            case 'new':
            default:
                $query->orderBy('start_date', 'DESC');
                break;
        }

        // 진행 상태 필터: DB 연산 없이 날짜 비교로 PHP 레벨에서 처리
        $events     = $query->paginate(9);
        $pager      = $siteEventModel->pager;
        $totalCount = $pager->getTotal();

        $today = date('Y-m-d');
        foreach ($events as &$e) {
            if (!empty($e['start_date']) && !empty($e['end_date'])) {
                if ($today < $e['start_date'])      $e['event_status'] = 'upcoming';
                elseif ($today > $e['end_date'])    $e['event_status'] = 'ended';
                else                                $e['event_status'] = 'ongoing';
            } else {
                $e['event_status'] = '';
            }
        }
        unset($e);

        // 진행상태 필터는 PHP 레벨에서 적용 후 페이지네이션 totalCount는 근사치로 유지
        if ($status !== '') {
            $events = array_filter($events, fn($e) => ($e['event_status'] ?? '') === $status);
        }

        return view('service/event/list', [
            'events'      => array_values($events),
            'pager'       => $pager,
            'totalCount'  => $totalCount,
            'activeType'   => $type,
            'activeStatus' => $status,
            'activeSearch' => $search,
            'activeSort'   => $sort,
            'saved_id'     => $this->request->getCookie('saved_id') ?? '',
        ]);
    }

    /**
     * 이벤트 상세 뷰 페이지
     * GET /events/{idx}
     * DB의 view_file 값으로 service/event/views/{view_file}.php 를 동적 호출
     */
    public function eventView(int $idx): string
    {
        $siteEventModel = new SiteEventModel();

        $event = $siteEventModel->where('state', 1)->find($idx);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 진행 상태 계산
        $today = date('Y-m-d');
        if (!empty($event['start_date']) && !empty($event['end_date'])) {
            if ($today < $event['start_date'])      $event['event_status'] = 'upcoming';
            elseif ($today > $event['end_date'])    $event['event_status'] = 'ended';
            else                                    $event['event_status'] = 'ongoing';
        } else {
            $event['event_status'] = '';
        }

        // 조회수 +1
        $siteEventModel->update($idx, ['view_cnt' => ((int)($event['view_cnt'] ?? 0)) + 1]);

        // '숨은 명소' 태그 카드 (맛집·관광지 통합, 최대 10개 — '부산 골목 탐험단' 캐러셀용)
        $hiddenSpotCards = $this->getHiddenSpotCards(10);

        // 방문 후기 목록 (최신순) — 목록 노출용으로 작성자 아이디는 마스킹 처리
        $eventReviewModel = new EventReviewModel();
        $eventReviews     = $eventReviewModel->getByEvent($idx, 30);
        foreach ($eventReviews as &$rv) {
            $rv['user_id'] = $this->maskUserId($rv['user_id']);
        }
        unset($rv);

        // '국밥' 맛집 목록 + 좋아요 투표 집계 ('마! 이게 진짜 국밥이다!' 이벤트용)
        $gukbapCards       = $this->getGukbapCards();
        $eventLikeLogModel = new EventLikeLogModel();
        $gukbapVoteCounts  = $eventLikeLogModel->getVoteCountsByEvent($idx);

        $voterIdx            = (int) session()->get('user.idx');
        $myTodayVote         = $voterIdx ? $eventLikeLogModel->getTodayVote($idx, $voterIdx) : null;
        $myParticipationDays = $voterIdx ? $eventLikeLogModel->getParticipationDays($idx, $voterIdx) : 0;

        $viewData = [
            'event'               => $event,
            'saved_id'            => $this->request->getCookie('saved_id') ?? '',
            'hiddenSpotCards'     => $hiddenSpotCards,
            'eventReviews'        => $eventReviews,
            'gukbapCards'         => $gukbapCards,
            'gukbapVoteCounts'    => $gukbapVoteCounts,
            'myTodayVote'         => $myTodayVote,
            'myParticipationDays' => $myParticipationDays,
        ];

        // use_view_file=0이면 기본 뷰 렌더링
        if (!(int)($event['use_view_file'] ?? 0)) {
            return view('service/event/views/view_default', $viewData);
        }

        // use_view_file=1: view_file 값으로 개별 뷰 파일 동적 호출 (경로 탐색 방지)
        $viewFile = preg_replace('/[^a-zA-Z0-9_]/', '', $event['view_file'] ?? '');

        $viewPath = APPPATH . 'Views/service/event/views/' . $viewFile . '.php';
        if ($viewFile === '' || !is_file($viewPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('service/event/views/' . $viewFile, $viewData);
    }

    /**
     * 이름에 '국밥'이 들어간 맛집 목록 카드로 변환
     * ('마! 이게 진짜 국밥이다!' 이벤트 — 국밥 맛집 전체보기 캐러셀용)
     */
    private function getGukbapCards(): array
    {
        $restaurantModel = new RestaurantModel();
        $thumbnailModel  = new ThumbnailModel();

        $restaurants = $restaurantModel->where('state', 1)
                                        ->like('name', '국밥')
                                        ->orderBy('idx', 'DESC')
                                        ->findAll();

        $cards = [];
        foreach ($restaurants as $r) {
            $thumbs  = $thumbnailModel->getByRestaurant((int) $r['idx']);
            $cards[] = [
                'idx'       => (int) $r['idx'],
                'name'      => $r['name'],
                'category'  => RestaurantModel::CATEGORIES[(int) ($r['category_num'] ?? 0)] ?? '맛집',
                'thumbnail' => !empty($thumbs) ? $thumbs[0]['img_url'] : null,
                'link'      => '/restaurants/' . (int) $r['idx'],
            ];
        }

        return $cards;
    }

    /**
     * 국밥 맛집 좋아요 투표 등록 (AJAX POST → JSON 응답)
     * POST /events/(:num)/gukbap-like
     * 로그인 필요. 이벤트 단위로 1인 1일 1회만 가능(event_like_log 유니크 제약).
     * 서로 다른 날짜로 3회 이상 참여하면 추첨 대상이 된다.
     */
    public function eventLikeStore(int $idx): \CodeIgniter\HTTP\ResponseInterface
    {
        $siteEventModel = new SiteEventModel();
        $event = $siteEventModel->where('state', 1)->find($idx);

        if (!$event) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => '존재하지 않는 이벤트입니다.',
            ]);
        }

        $userIdx = (int) session()->get('user.idx');
        if (!$userIdx) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => '로그인이 필요합니다.',
            ]);
        }

        $json          = $this->request->getJSON(true) ?? [];
        $restaurantIdx = (int) ($json['restaurant_idx'] ?? $this->request->getPost('restaurant_idx') ?? 0);

        if ($restaurantIdx <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '잘못된 요청입니다.',
            ]);
        }

        // 이름에 '국밥'이 들어간 노출 상태 맛집만 투표 대상으로 허용
        $restaurantModel = new RestaurantModel();
        $restaurant = $restaurantModel->where('state', 1)->find($restaurantIdx);
        if (!$restaurant || mb_strpos($restaurant['name'], '국밥') === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '참여 대상 맛집이 아닙니다.',
            ]);
        }

        $eventLikeLogModel = new EventLikeLogModel();

        if ($eventLikeLogModel->hasLikedToday($idx, $userIdx)) {
            return $this->response->setJSON([
                'success'       => false,
                'already_voted' => true,
                'message'       => '오늘은 이미 참여하셨어요. 내일 다시 참여해주세요!',
            ]);
        }

        $eventLikeLogModel->tryLike($idx, $userIdx, $restaurantIdx);

        $voteCounts         = $eventLikeLogModel->getVoteCountsByEvent($idx);
        $participationDays  = $eventLikeLogModel->getParticipationDays($idx, $userIdx);

        return $this->response->setJSON([
            'success'            => true,
            'message'            => $participationDays >= 3
                ? "투표 완료! {$participationDays}일째 참여 중이에요 — 추첨 대상에 등록되었습니다 🎉"
                : "투표 완료! {$participationDays}일째 참여 중이에요. 3일 이상 참여하면 추첨 대상이 됩니다.",
            'restaurant_idx'     => $restaurantIdx,
            'vote_count'         => (int) ($voteCounts[$restaurantIdx] ?? 0),
            'participation_days' => $participationDays,
        ]);
    }

    /**
     * '숨은 명소' 해시태그가 붙은 맛집·관광지를 통합해 카드 배열로 반환
     * 맛집/관광지 구분 없이 무작위로 섞어 최대 $limit개를 돌려준다.
     */
    private function getHiddenSpotCards(int $limit = 10): array
    {
        $db = \Config\Database::connect();

        // '숨은 명소' 태그와 연결된 맛집/관광지 idx 조회
        $tagRows = $db->table('hashtag h')
                      ->select('hn.restaurant_idx, hn.place_idx')
                      ->join('hashtag_number hn', 'hn.hashtag_idx = h.idx')
                      ->where('h.name', '숨은 명소')
                      ->where('hn.state', 1)
                      ->get()->getResultArray();

        $restaurantIdxs = array_values(array_filter(array_map(
            static fn ($r) => $r['restaurant_idx'] !== null ? (int) $r['restaurant_idx'] : null,
            $tagRows
        )));
        $placeIdxs = array_values(array_filter(array_map(
            static fn ($r) => $r['place_idx'] !== null ? (int) $r['place_idx'] : null,
            $tagRows
        )));

        $cards = [];

        if (!empty($restaurantIdxs)) {
            $restaurantModel = new RestaurantModel();
            $thumbnailModel  = new ThumbnailModel();

            $restaurants = $restaurantModel->whereIn('idx', $restaurantIdxs)->where('state', 1)->findAll();
            foreach ($restaurants as $r) {
                $thumbs  = $thumbnailModel->getByRestaurant((int) $r['idx']);
                $cards[] = [
                    'type'      => 'restaurant',
                    'type_label'=> '맛집',
                    'idx'       => (int) $r['idx'],
                    'name'      => $r['name'],
                    'category'  => RestaurantModel::CATEGORIES[(int) ($r['category_num'] ?? 0)] ?? '맛집',
                    'thumbnail' => !empty($thumbs) ? $thumbs[0]['img_url'] : null,
                    'link'      => '/restaurants/' . (int) $r['idx'],
                ];
            }
        }

        if (!empty($placeIdxs)) {
            $placeModel     = new PlaceModel();
            $thumbnailModel = new ThumbnailModel();

            $places = $placeModel->whereIn('idx', $placeIdxs)->where('state', 1)->findAll();
            foreach ($places as $p) {
                $thumbs  = $thumbnailModel->getByPlace((int) $p['idx']);
                $cards[] = [
                    'type'      => 'place',
                    'type_label'=> '관광지',
                    'idx'       => (int) $p['idx'],
                    'name'      => $p['name'],
                    'category'  => PlaceModel::CATEGORIES[(int) ($p['category_num'] ?? 0)] ?? '관광지',
                    'thumbnail' => !empty($thumbs) ? $thumbs[0]['img_url'] : null,
                    'link'      => '/spots/' . (int) $p['idx'],
                ];
            }
        }

        // 맛집·관광지 구분 없이 뒤섞은 뒤 최대 $limit개만 노출
        shuffle($cards);

        return array_slice($cards, 0, $limit);
    }

    /**
     * 방문 후기 등록 처리 (AJAX POST → JSON 응답)
     * POST /events/(:num)/reviews
     * 로그인 필요. 사진은 선택 첨부(최대 5MB, 이미지 파일만 허용)
     */
    public function eventReviewStore(int $idx): \CodeIgniter\HTTP\ResponseInterface
    {
        $siteEventModel = new SiteEventModel();
        $event = $siteEventModel->where('state', 1)->find($idx);

        if (!$event) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => '존재하지 않는 이벤트입니다.',
            ]);
        }

        $userIdx = (int) session()->get('user.idx');
        $userId  = (string) session()->get('user.id');

        if (!$userIdx) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => '로그인이 필요합니다.',
            ]);
        }

        $rules = [
            'content'   => 'required|max_length[1000]',
            'spot_name' => 'permit_empty|max_length[100]',
            'photo'     => 'permit_empty|is_image[photo]|max_size[photo,5120]|mime_in[photo,image/jpg,image/jpeg,image/png,image/gif,image/webp]',
        ];
        $messages = [
            'content' => [
                'required'   => '후기 내용을 입력해주세요.',
                'max_length' => '후기는 1,000자 이내로 입력해주세요.',
            ],
            'photo' => [
                'is_image' => '이미지 파일만 첨부할 수 있습니다.',
                'max_size' => '이미지 용량은 5MB 이하만 첨부할 수 있습니다.',
                'mime_in'  => '이미지 파일만 첨부할 수 있습니다.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $this->validator->getErrors()['content']
                    ?? $this->validator->getErrors()['photo']
                    ?? '입력값을 확인해주세요.',
            ]);
        }

        $photoUrl = $this->uploadReviewPhoto();

        $eventReviewModel = new EventReviewModel();
        $eventReviewModel->insert([
            'event_idx' => $idx,
            'user_idx'  => $userIdx,
            'user_id'   => $userId,
            'spot_name' => trim($this->request->getPost('spot_name') ?? '') ?: null,
            'content'   => trim($this->request->getPost('content') ?? ''),
            'photo_url' => $photoUrl,
            'state'     => 1,
            'reg_date'  => date('Y-m-d H:i:s'),
        ]);

        $newIdx = $eventReviewModel->getInsertID();
        $review = $eventReviewModel->find($newIdx);

        return $this->response->setJSON([
            'success' => true,
            'message' => '후기가 등록되었습니다. 골목 탐험을 공유해주셔서 감사합니다!',
            'review'  => [
                'user_id'   => $this->maskUserId($review['user_id']),
                'spot_name' => $review['spot_name'],
                'content'   => $review['content'],
                'photo_url' => $review['photo_url'],
                'reg_date'  => date('Y.m.d', strtotime($review['reg_date'])),
            ],
        ]);
    }

    /**
     * 후기 사진 업로드 처리 (선택 첨부)
     */
    private function uploadReviewPhoto(): ?string
    {
        $file = $this->request->getFile('photo');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadDir = FCPATH . 'uploads/event_reviews/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return '/uploads/event_reviews/' . $newName;
    }

    /**
     * 후기 목록에 표시할 아이디 마스킹 (예: hongkim → hon**m)
     */
    private function maskUserId(string $id): string
    {
        $len = mb_strlen($id);
        if ($len <= 2) {
            return mb_substr($id, 0, 1) . str_repeat('*', max(1, $len - 1));
        }
        return mb_substr($id, 0, $len - 2) . str_repeat('*', 2);
    }

    /**
     * 나만의 부산 코스 공모전 — 뱓등 폼 항목 연결용 검색 API
     * (백오피스 여행코스 검색과 동일한 방식이나, 서비스에 노출 중인 state=1 항목만 대상으로 제한)
     * GET /events/course-content-search?type=restaurant|place|event&q=검색어
     */
    public function courseContentSearch(): \CodeIgniter\HTTP\ResponseInterface
    {
        $type = $this->request->getGet('type');
        $q    = trim((string) ($this->request->getGet('q') ?? ''));

        if ($q === '' || !in_array($type, ['restaurant', 'place', 'event'], true)) {
            return $this->response->setJSON([]);
        }

        $results = match ($type) {
            'restaurant' => (new RestaurantModel())->where('state', 1)->like('name', $q)->select('idx, name, address1')->limit(10)->findAll(),
            'place'      => (new PlaceModel())->where('state', 1)->like('name', $q)->select('idx, name, address1')->limit(10)->findAll(),
            'event'      => (new EventModel())->where('state', 1)->like('name', $q)->select('idx, name, address1')->limit(10)->findAll(),
        };

        return $this->response->setJSON($results);
    }

    /**
     * 나만의 부산 코스 공모전 — 이용자 코스 등록
     * 기존 travel_course / travel_course_item 테이블을 그대로 사용하도록,
     * state=7("사용자 요청")로 저장해 백오피스 승인 전까지 일반 여행코스 목록과 구분한다.
     * POST /events/{idx}/course-submit
     */
    public function courseSubmit(int $idx): \CodeIgniter\HTTP\ResponseInterface
    {
        $siteEventModel = new SiteEventModel();
        $event = $siteEventModel->where('state', 1)->find($idx);

        if (!$event) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => '존재하지 않는 이벤트입니다.',
            ]);
        }

        $userIdx = (int) session()->get('user.idx');
        $userId  = (string) session()->get('user.id');

        if (!$userIdx) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => '로그인이 필요합니다.',
            ]);
        }

        $rules = [
            'title'       => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[2000]',
            'thumb_img'   => 'permit_empty|max_size[thumb_img,5120]|ext_in[thumb_img,jpg,jpeg,png,gif,webp]|mime_in[thumb_img,image/jpg,image/jpeg,image/png,image/gif,image/webp]',
        ];
        $messages = [
            'title' => [
                'required'   => '코스명을 입력해주세요.',
                'max_length' => '코스명은 100자 이내로 입력해주세요.',
            ],
            'thumb_img' => [
                'max_size' => '이미지 용량은 5MB 이하만 첨부할 수 있습니다.',
                'ext_in'   => 'jpg, png, gif, webp 이미지 파일만 첨부할 수 있습니다.',
                'mime_in'  => '이미지 파일만 첨부할 수 있습니다.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $this->validator->getErrors()['title']
                    ?? $this->validator->getErrors()['thumb_img']
                    ?? '입력값을 확인해주세요.',
            ]);
        }

        // 장소 최소 3곳 검증 (서버측 재검증)
        $rawItems   = $this->request->getPost('items') ?? [];
        $rawItems   = is_array($rawItems) ? $rawItems : [];
        $validItems = array_filter($rawItems, fn($i) => trim((string) ($i['name'] ?? '')) !== '');

        if (count($validItems) < 3) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '장소를 최소 3곳 이상 입력해주세요.',
            ]);
        }

        $thumbUrl = $this->uploadCourseThumb();

        $travelCourseModel = new TravelCourseModel();
        $travelCourseModel->insert([
            'state'       => 7, // 사용자 요청 (백오피스 승인 대기)
            'title'       => trim($this->request->getPost('title')),
            'description' => trim($this->request->getPost('description') ?? '') ?: null,
            'sido'        => $this->request->getPost('sido') ?: null,
            'thumb_url'   => $thumbUrl,
            'reg_id'      => $userId,
            'reg_date'    => date('Y-m-d H:i:s'),
            'edit_date'   => date('Y-m-d H:i:s'),
        ]);

        $courseIdx = (int) $travelCourseModel->getInsertID();

        $travelCourseItemModel = new TravelCourseItemModel();
        $travelCourseItemModel->replaceByCourse($courseIdx, $rawItems);

        return $this->response->setJSON([
            'success' => true,
            'message' => '코스가 접수되었습니다. 검토 후 채택 여부를 안내드릴게요. 소중한 코스를 공유해주셔서 감사합니다!',
        ]);
    }

    /**
     * 코스 대표 썸네일 업로드 처리 (선택 첨부)
     */
    private function uploadCourseThumb(): ?string
    {
        $file = $this->request->getFile('thumb_img');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadDir = FCPATH . 'uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return '/uploads/thumbnails/' . $newName;
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
