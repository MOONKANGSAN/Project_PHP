<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 백오피스 인증 필터
 * backoffice.idx 세션이 없으면 로그인 페이지로 리다이렉트하고,
 * GET 요청 시 최근 방문 페이지 이력을 세션에 기록한다.
 */
class BackofficeAuthFilter implements FilterInterface
{
    // URI 접두사 → [표시명, 목록 URL] 매핑 (순서 중요: 구체적인 패턴 먼저)
    private const PAGE_MAP = [
        'backoffice/withdrawn-members' => ['name' => '탈퇴회원 관리',  'url' => '/backoffice/withdrawn-members'],
        'backoffice/restaurants'       => ['name' => '맛집 관리',       'url' => '/backoffice/restaurants'],
        'backoffice/spots'             => ['name' => '관광지 관리',     'url' => '/backoffice/spots'],
        'backoffice/festivals'         => ['name' => '행사/축제 관리',  'url' => '/backoffice/festivals'],
        'backoffice/members'           => ['name' => '회원 관리',       'url' => '/backoffice/members'],
        'backoffice/notices'           => ['name' => '공지사항 관리',   'url' => '/backoffice/notices'],
        'backoffice/inquiries'         => ['name' => '고객문의',        'url' => '/backoffice/inquiries'],
        'backoffice/faqs'              => ['name' => 'FAQ 관리',        'url' => '/backoffice/faqs'],
        'backoffice/trash'             => ['name' => '휴지통',          'url' => '/backoffice/trash'],
        'backoffice/error-logs'        => ['name' => '에러 로그',       'url' => '/backoffice/error-logs'],
        'backoffice/banners'           => ['name' => '배너 관리',       'url' => '/backoffice/banners'],
        'backoffice/region-explore'    => ['name' => '지역별 탐색',     'url' => '/backoffice/region-explore'],
        'backoffice/travel-courses'    => ['name' => '여행코스 관리',   'url' => '/backoffice/travel-courses'],
        'backoffice/site/admins'       => ['name' => '관리자 추가',     'url' => '/backoffice/site/admins/add'],
        'backoffice/site-config'       => ['name' => '사이트 설정',     'url' => '/backoffice/site-config'],
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('backoffice.idx')) {
            return redirect()->to('/backoffice/login');
        }

        // GET 요청일 때만 방문 이력 기록
        // uri_string()은 base URL을 제외한 앱 상대 경로를 반환 (앞 슬래시 없음)
        if (strtolower($request->getMethod()) === 'get') {
            $this->recordNavHistory(uri_string());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function recordNavHistory(string $uri): void
    {
        $matched = null;
        foreach (self::PAGE_MAP as $prefix => $page) {
            if (str_starts_with($uri, $prefix)) {
                $matched = $page;
                break;
            }
        }

        if ($matched === null) {
            return;
        }

        $history = session()->get('backoffice.nav_history') ?? [];

        // 같은 URL 중복 제거 후 맨 앞에 삽입, 최대 4개 유지
        $history = array_values(array_filter($history, fn($h) => $h['url'] !== $matched['url']));
        array_unshift($history, $matched);
        $history = array_slice($history, 0, 4);

        session()->set('backoffice.nav_history', $history);
    }
}
