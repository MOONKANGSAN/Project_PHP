<?php
namespace App\Libraries;

/**
 * PortOne(포트원·구 아임포트) REST API v1 결제 검증 라이브러리
 *
 * 사용 방법:
 *   1. 프론트에서 PortOne SDK로 결제 후 imp_uid 수신
 *   2. 서버에서 verify(imp_uid, 주문금액) 호출
 *   3. 금액·상태 일치 시 주문 확정 처리
 *
 * 무료 테스트: https://admin.portone.io 무료 회원가입 후 .env 키 설정
 */
class PortOnePayment
{
    private string $impKey;
    private string $impSecret;
    private string $baseUrl = 'https://api.iamport.kr';

    public function __construct()
    {
        $this->impKey    = env('PORTONE_IMP_KEY', '');
        $this->impSecret = env('PORTONE_IMP_SECRET', '');
    }

    /**
     * imp_uid로 결제 정보 조회 후 금액 검증
     *
     * @return array{valid: bool, data: array|null, error: string}
     */
    public function verify(string $impUid, int $expectedAmount): array
    {
        try {
            $token    = $this->getToken();
            $response = $this->request('GET', "/payments/{$impUid}", [], $token);

            if ($response['code'] !== 0) {
                return ['valid' => false, 'data' => null, 'error' => $response['message']];
            }

            $payment = $response['response'];

            if ($payment['status'] !== 'paid') {
                return ['valid' => false, 'data' => $payment, 'error' => '결제 미완료 상태: ' . $payment['status']];
            }

            if ((int) $payment['amount'] !== $expectedAmount) {
                return ['valid' => false, 'data' => $payment, 'error' => '결제 금액 불일치 (기대: ' . $expectedAmount . ', 실제: ' . $payment['amount'] . ')'];
            }

            return ['valid' => true, 'data' => $payment, 'error' => ''];

        } catch (\Throwable $e) {
            return ['valid' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * PortOne REST API 액세스 토큰 발급
     */
    private function getToken(): string
    {
        $response = $this->request('POST', '/users/getToken', [
            'imp_key'    => $this->impKey,
            'imp_secret' => $this->impSecret,
        ]);

        if ($response['code'] !== 0) {
            throw new \RuntimeException('PortOne 토큰 발급 실패: ' . $response['message']);
        }

        return $response['response']['access_token'];
    }

    /**
     * PortOne REST API HTTP 요청
     */
    private function request(string $method, string $path, array $body = [], string $token = ''): array
    {
        $url  = $this->baseUrl . $path;
        $curl = curl_init($url);

        $headers = ['Content-Type: application/json'];
        if ($token !== '') {
            $headers[] = "Authorization: Bearer {$token}";
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        if ($method !== 'GET' && !empty($body)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $options);

        $result = curl_exec($curl);
        $error  = curl_error($curl);
        curl_close($curl);

        if ($result === false) {
            throw new \RuntimeException('cURL 오류: ' . $error);
        }

        $decoded = json_decode($result, true);
        if ($decoded === null) {
            throw new \RuntimeException('PortOne 응답 파싱 실패');
        }

        return $decoded;
    }
}
