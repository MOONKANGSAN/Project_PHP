<?php
namespace App\Libraries;

/**
 * PortOne V2 REST API 결제 검증 라이브러리
 * - 이니시스 카드 결제, 카카오페이 공통 사용
 * - API Secret을 Authorization 헤더에 직접 사용 (토큰 교환 불필요)
 * - 검증 엔드포인트: GET https://api.portone.io/payments/{paymentId}
 */
class PortOnePayment
{
    private string $apiSecret;
    private string $baseUrl = 'https://api.portone.io';

    public function __construct()
    {
        $this->apiSecret = env('PORTONE_V2_API_SECRET', '');
    }

    /**
     * paymentId(=주문번호)로 결제 검증 — 상태·금액 일치 확인
     *
     * @return array{valid: bool, data: array|null, error: string}
     */
    public function verify(string $paymentId, int $expectedAmount): array
    {
        try {
            $url      = $this->baseUrl . '/payments/' . urlencode($paymentId);
            $response = $this->get($url);

            log_message('debug', '[PortOne V2] verify paymentId=' . $paymentId
                . ' status=' . ($response['status'] ?? 'N/A'));

            // 오류 응답: type 또는 code 필드가 있고 status 없음
            if (isset($response['type']) || (isset($response['code']) && !isset($response['status']))) {
                $msg = $response['message'] ?? ($response['type'] ?? ($response['code'] ?? 'API 오류'));
                return ['valid' => false, 'data' => null, 'error' => $msg];
            }

            if (($response['status'] ?? '') !== 'PAID') {
                return ['valid'  => false, 'data' => $response,
                        'error' => '미결제 상태: ' . ($response['status'] ?? 'UNKNOWN')];
            }

            $paidAmount = (int)($response['amount']['total'] ?? 0);
            if ($paidAmount !== $expectedAmount) {
                return ['valid'  => false, 'data' => $response,
                        'error' => "금액 불일치 (기대: {$expectedAmount}, 실제: {$paidAmount})"];
            }

            return ['valid' => true, 'data' => $response, 'error' => ''];

        } catch (\Throwable $e) {
            log_message('error', '[PortOne V2] exception: ' . $e->getMessage());
            return ['valid' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * V2 REST API GET 요청
     * Authorization: PortOne {apiSecret} — 별도 토큰 발급 없이 Secret 직접 전송
     */
    private function get(string $url): array
    {
        $sslVerify = (ENVIRONMENT === 'production');
        $curl      = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: PortOne ' . $this->apiSecret,
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
            CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
        ]);

        $raw      = curl_exec($curl);
        $error    = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        log_message('debug', '[PortOne V2] GET ' . $url . ' → HTTP ' . $httpCode);

        if ($raw === false) {
            throw new \RuntimeException('cURL 오류: ' . $error);
        }

        $decoded = json_decode($raw, true);
        if ($decoded === null) {
            throw new \RuntimeException('응답 파싱 실패: ' . $raw);
        }

        return $decoded;
    }
}
