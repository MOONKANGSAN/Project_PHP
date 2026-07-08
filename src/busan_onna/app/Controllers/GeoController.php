<?php

namespace App\Controllers;

/**
 * 네이버 Geocoding API 서버사이드 프록시
 * Client Secret 키를 프론트에 노출하지 않기 위해 서버에서 중계한다.
 * /backoffice/geo/search?q=주소
 */
class GeoController extends BaseController
{
    /**
     * GET /backoffice/geo/search
     * 주소 문자열 → 위도/경도 변환 (Naver Geocoding API 중계)
     */
    public function search(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));

        if ($q === '') {
            return $this->response->setJSON([
                'status'    => 'ERROR',
                'message'   => '검색어를 입력해주세요.',
                'addresses' => [],
            ]);
        }

        $clientId     = env('NAVER_MAP_CLIENT_ID');
        $clientSecret = env('NAVER_MAP_CLIENT_SECRET');

        $url = 'https://naveropenapi.apigw.naver.com/map-geocode/v2/geocode?query=' . urlencode($q);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => [
                'X-NCP-APIGW-API-KEY-ID: ' . $clientId,
                'X-NCP-APIGW-API-KEY: '    . $clientSecret,
            ],
        ]);

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $httpCode !== 200) {
            return $this->response->setJSON([
                'status'    => 'ERROR',
                'message'   => '지도 API 호출에 실패했습니다.',
                'addresses' => [],
            ]);
        }

        return $this->response->setJSON(json_decode($result, true));
    }
}
