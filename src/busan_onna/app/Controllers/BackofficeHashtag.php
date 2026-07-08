<?php

namespace App\Controllers;

use App\Models\HashtagModel;

/**
 * 백오피스 해시태그 API 컨트롤러
 * 폼의 태그 자동완성에 사용하는 JSON 엔드포인트를 제공
 */
class BackofficeHashtag extends BaseController
{
    private HashtagModel $hashtagModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->hashtagModel = new HashtagModel();
    }

    /**
     * GET /backoffice/hashtags/search?q=키워드
     * 키워드와 일치하는 기존 해시태그 목록을 JSON으로 반환 (자동완성용)
     */
    public function search(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q    = trim((string) ($this->request->getGet('q') ?? ''));
        $tags = $q !== '' ? $this->hashtagModel->search($q) : [];

        return $this->response
                    ->setContentType('application/json')
                    ->setJSON($tags);
    }
}
