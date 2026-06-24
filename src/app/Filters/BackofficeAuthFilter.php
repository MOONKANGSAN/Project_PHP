<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 백오피스 인증 필터
 * backoffice.idx 세션이 없으면 로그인 페이지로 리다이렉트한다.
 */
class BackofficeAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('backoffice.idx')) {
            return redirect()->to('/backoffice/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 응답 후 처리 없음
    }
}
