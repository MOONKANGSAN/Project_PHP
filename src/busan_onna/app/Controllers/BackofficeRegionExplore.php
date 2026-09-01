<?php

namespace App\Controllers;

use App\Models\BusanMapsModel;
use App\Models\BusanMapsTop5Model;
use App\Models\RegionContentModel;

/**
 * 백오피스 — 지역별 탐색 관리 컨트롤러
 * /backoffice/region-explore/* 요청을 처리한다.
 */
class BackofficeRegionExplore extends BaseController
{
    private BusanMapsModel     $mapsModel;
    private BusanMapsTop5Model $top5Model;
    private RegionContentModel $contentModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->mapsModel    = new BusanMapsModel();
        $this->top5Model    = new BusanMapsTop5Model();
        $this->contentModel = new RegionContentModel();
    }

    /**
     * 공통 뷰 데이터 생성
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
     * JSON 응답 헬퍼
     */
    private function json(array $data, int $status = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }

    // ----------------------------------------------------------------
    // 페이지
    // ----------------------------------------------------------------

    /**
     * GET /backoffice/region-explore
     * 지역별 탐색 관리 메인 페이지
     */
    public function index(): string
    {
        $regions = $this->mapsModel->getAllList();

        return view('backoffice/region_explore/index', $this->base('지역별 탐색 관리', [
            'regions' => $regions,
        ]));
    }

    // ----------------------------------------------------------------
    // AJAX API
    // ----------------------------------------------------------------

    /**
     * GET /backoffice/region-explore/(:num)/top5
     * 특정 지역의 TOP5 목록 조회 (JSON)
     */
    public function getTop5(int $regionIdx): \CodeIgniter\HTTP\ResponseInterface
    {
        $region = $this->mapsModel->find($regionIdx);
        if (!$region) {
            return $this->json(['success' => false, 'message' => '존재하지 않는 지역입니다.'], 404);
        }

        $items = $this->top5Model->getTop5WithContent($regionIdx);

        return $this->json([
            'success' => true,
            'region'  => $region,
            'items'   => $items,
        ]);
    }

    /**
     * POST /backoffice/region-explore/(:num)/top5/save
     * 특정 지역의 TOP5 저장 (JSON)
     * Body: { items: [ { title, link_url, description, thumb_url }, ... ] }
     */
    public function saveTop5(int $regionIdx): \CodeIgniter\HTTP\ResponseInterface
    {
        $region = $this->mapsModel->find($regionIdx);
        if (!$region) {
            return $this->json(['success' => false, 'message' => '존재하지 않는 지역입니다.'], 404);
        }

        $body  = $this->request->getJSON(true);
        $items = $body['items'] ?? [];

        // 최대 5개 제한
        if (count($items) > 5) {
            return $this->json(['success' => false, 'message' => 'TOP5는 최대 5개까지만 저장할 수 있습니다.'], 422);
        }

        // 각 항목 유효성 검사
        foreach ($items as $item) {
            if (empty(trim($item['title'] ?? ''))) {
                return $this->json(['success' => false, 'message' => '항목 제목을 입력해주세요.'], 422);
            }
        }

        $this->top5Model->replaceTop5(
            $regionIdx,
            $items,
            (string) session()->get('backoffice.id')
        );

        return $this->json([
            'success' => true,
            'message' => $region['name'] . ' TOP5가 저장되었습니다.',
        ]);
    }

    /**
     * GET /backoffice/region-explore/search
     * 맛집·관광지·행사를 통합 검색 (TOP5 추가용, JSON)
     * Query:
     *   q          = 검색어
     *   type       = restaurant | place | event (생략 시 전체)
     *   region_idx = busan_maps.idx (설정 시 해당 지역구로 address1 필터링)
     */
    public function search(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q         = trim((string) $this->request->getGet('q'));
        $type      = (string) $this->request->getGet('type');
        $regionIdx = (int) $this->request->getGet('region_idx');

        if (strlen($q) < 1) {
            return $this->json(['success' => false, 'message' => '검색어를 입력해주세요.'], 422);
        }

        // 선택된 지역명 조회 (region_idx가 있을 때만)
        $regionName = '';
        if ($regionIdx > 0) {
            $region     = $this->mapsModel->find($regionIdx);
            $regionName = $region['name'] ?? '';
        }

        // v_region_content View 단일 쿼리로 통합 검색
        $rows    = $this->contentModel->searchByRegion($q, $regionName, $type);
        $grouped = [];
        $results = [];
        foreach ($rows as $row) {
            $t = $row['content_type'];
            if (!isset($grouped[$t])) {
                $grouped[$t] = 0;
            }
            if ($grouped[$t] < 10) {
                $grouped[$t]++;
                $results[] = $this->formatSearchResult($row);
            }
        }

        return $this->json([
            'success'     => true,
            'region_name' => $regionName ?: null,
            'results'     => $results,
            'total'       => count($results),
        ]);
    }

    /**
     * v_region_content 행을 검색 결과 응답 형식으로 변환
     */
    private function formatSearchResult(array $row): array
    {
        static $urlMap  = ['restaurant' => '/restaurants/', 'place' => '/spots/', 'event' => '/festivals/'];
        static $nameMap = ['restaurant' => '맛집',         'place' => '관광지',  'event' => '행사/축제'];

        $type = $row['content_type'];

        return [
            'content_type' => $type,
            'content_idx'  => (int) $row['idx'],
            'type_name'    => $nameMap[$type] ?? $type,
            'title'        => $row['name'],
            'link_url'     => ($urlMap[$type] ?? '/') . $row['idx'],
            'address'      => trim(($row['address1'] ?? '') . ' ' . ($row['address2'] ?? '')),
        ];
    }

    // ----------------------------------------------------------------
    // 공개 API (메인 페이지에서 사용)
    // ----------------------------------------------------------------

    /**
     * GET /api/region-explore
     * 활성 지역 전체 목록 반환 (state=1)
     */
    public function apiRegions(): \CodeIgniter\HTTP\ResponseInterface
    {
        $regions = $this->mapsModel->getActiveList();
        return $this->json(['success' => true, 'regions' => $regions]);
    }

    /**
     * GET /api/region-explore/(:num)/top5
     * 특정 지역의 활성 TOP5 반환 (state=1, 메인 페이지용)
     */
    public function apiTop5(int $regionIdx): \CodeIgniter\HTTP\ResponseInterface
    {
        $region = $this->mapsModel->where('state', 1)->find($regionIdx);
        if (!$region) {
            return $this->json(['success' => false, 'message' => '존재하지 않는 지역입니다.'], 404);
        }

        $items = $this->top5Model->getTop5WithContent($regionIdx);

        return $this->json([
            'success' => true,
            'region'  => $region,
            'items'   => $items,
        ]);
    }

    /**
     * POST /backoffice/region-explore/(:num)/state
     * 지역 활성/비활성 토글
     */
    public function toggleState(int $regionIdx): \CodeIgniter\HTTP\ResponseInterface
    {
        $region = $this->mapsModel->find($regionIdx);
        if (!$region) {
            return $this->json(['success' => false, 'message' => '존재하지 않는 지역입니다.'], 404);
        }

        $newState = $region['state'] ? 0 : 1;
        $this->mapsModel->update($regionIdx, ['state' => $newState]);

        return $this->json([
            'success'   => true,
            'new_state' => $newState,
            'message'   => $region['name'] . ' 지역을 ' . ($newState ? '활성화' : '비활성화') . '했습니다.',
        ]);
    }
}
