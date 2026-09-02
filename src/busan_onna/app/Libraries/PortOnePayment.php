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
            /* [DEBUG] 토큰 발급에 사용 중인 키 앞 8자 확인 */
            log_message('debug', '[PortOne] impKey=' . substr($this->impKey, 0, 8) . '...');

            $token    = $this->getToken();
            log_message('debug', '[PortOne] token 발급 성공 (앞 20자): ' . substr($token, 0, 20));

            [$response, $raw] = $this->requestWithRaw('GET', "/payments/{$impUid}", [], $token);

            /* [DEBUG] PortOne raw 응답 전체 로그 */
            log_message('debug', '[PortOne verify] raw=' . $raw);

            if ($response['code'] !== 0) {
                return ['valid' => false, 'data' => null, 'error' => $response['message'],
                        '_raw' => $raw];
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
            log_message('error', '[PortOne verify] exception: ' . $e->getMessage());
            return ['valid' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * PortOne REST API 액세스 토큰 발급
     */
    private function getToken(): string
    {
        [$response, $raw] = $this->requestWithRaw('POST', '/users/getToken', [
            'imp_key'    => $this->impKey,
            'imp_secret' => $this->impSecret,
        ]);

        if ($response['code'] !== 0) {
            throw new \RuntimeException('PortOne 토큰 발급 실패: ' . $response['message'] . ' | raw=' . $raw);
        }

        return $response['response']['access_token'];
    }

    /**
     * PortOne REST API HTTP 요청 — 응답 원본(raw)도 함께 반환
     * @return array{0: array, 1: string}
     */
    private function requestWithRaw(string $method, string $path, array $body = [], string $token = ''): array
    {
        $url  = $this->baseUrl . $path;
        $curl = curl_init($url);

        $headers = ['Content-Type: application/json'];
        if ($token !== '') {
            $headers[] = "Authorization: Bearer {$token}";
        }

        // 운영 환경에서만 SSL 인증서 검증 — 로컬 Windows 개발 환경은 CA 번들 미설정으로 검증 실패
        $sslVerify = (ENVIRONMENT === 'production');

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
            CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
        ];

        if ($method !== 'GET' && !empty($body)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $options);

        $raw   = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        log_message('debug', '[PortOne cURL] ' . $method . ' ' . $url . ' → HTTP ' . $httpCode);

        if ($raw === false) {
            throw new \RuntimeException('cURL 오류: ' . $error);
        }

        $decoded = json_decode($raw, true);
        if ($decoded === null) {
            throw new \RuntimeException('PortOne 응답 파싱 실패 | raw=' . $raw);
        }

        return [$decoded, $raw];
    }
}
