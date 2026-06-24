<?php

namespace App\Filters;

use App\Models\UserInfoModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 상시 로그인 필터
 * 세션이 만료됐을 때 remember_me 쿠키를 검증해 세션을 자동으로 복원한다.
 */
class RememberMeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 이미 로그인 상태면 스킵
        if (session()->get('user.idx')) {
            return;
        }

        $raw = $request->getCookie('remember_me');
        if (!$raw) {
            return;
        }

        // base64 디코딩 후 파트 분리
        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            return;
        }

        $parts = explode('|', $decoded, 3);
        if (count($parts) !== 3) {
            return;
        }

        [$idx, $expire, $sig] = $parts;

        // 만료 시간 확인
        if ((int) $expire < time()) {
            return;
        }

        // HMAC 서명 검증 (타이밍 공격 방지용 hash_equals 사용)
        $secret   = hash_hmac('sha256', 'remember_me_secret', base_url());
        $expected = hash_hmac('sha256', $idx . '|' . $expire, $secret);
        if (!hash_equals($expected, $sig)) {
            return;
        }

        // 유효한 유저인지 DB 확인 후 세션 복원
        $model = new UserInfoModel();
        $user  = $model->find((int) $idx);

        if (!$user || (int) $user['state'] !== 1) {
            return;
        }

        session()->set([
            'user.idx'   => $user['idx'],
            'user.id'    => $user['id'],
            'user.email' => $user['email'],
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 응답 후 처리 없음
    }
}
