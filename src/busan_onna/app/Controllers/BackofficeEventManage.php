<?php

namespace App\Controllers;

use App\Models\SiteEventModel;
use App\Models\EventReviewModel;
use App\Models\EventLikeLogModel;
use App\Models\HashtagNumberModel;
use App\Models\HashtagModel;
use App\Models\RestaurantModel;
use App\Models\PlaceModel;
use App\Models\ThumbnailModel;

/**
 * 백오피스 — 이벤트 관리 컨트롤러
 * '사이트 이벤트'(BackofficeSiteEvent)에서는 이벤트의 기본 정보(제목/기간/대표이미지 등)를
 * 등록·수정하고, 이 컨트롤러는 개별 이벤트의 운영 데이터(방문 후기, 좋아요·참여 집계 등)를
 * 이벤트 전용 화면에서 관리한다.
 */
class BackofficeEventManage extends BaseController
{
    private SiteEventModel $model;

    /** 전용 관리 화면을 제공하는 이벤트의 view_file 목록 */
    private const MANAGED_VIEW_FILES = ['view_1', 'view_2'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new SiteEventModel();
    }

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
     * GET /backoffice/event-manage
     * 전용 관리 화면이 있는 이벤트 목록
     */
    public function index(): string
    {
        $items = $this->model->whereIn('view_file', self::MANAGED_VIEW_FILES)
                              ->where('state !=', 9)
                              ->orderBy('idx', 'ASC')
                              ->findAll();

        return view('backoffice/event_manage/index', $this->base('이벤트 관리', [
            'items' => $items,
        ]));
    }

    /**
     * GET /backoffice/event-manage/(:num)
     * 이벤트별 전용 관리 화면 (view_file에 따라 분기)
     */
    public function manage(int $idx): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($idx);

        if (!$item || (int) $item['state'] === 9 || !in_array($item['view_file'], self::MANAGED_VIEW_FILES, true)) {
            session()->setFlashdata('error', '관리할 수 없는 이벤트입니다.');
            return redirect()->to('/backoffice/event-manage');
        }

        $data = ['event' => $item];

        // '부산 골목 탐험단' 전용 관리 데이터: 방문 후기 목록 + 숨은 명소 태그 목록
        if ($item['view_file'] === 'view_1') {
            $eventReviewModel = new EventReviewModel();
            $data['reviews'] = $eventReviewModel->where('event_idx', $idx)
                                                 ->orderBy('idx', 'DESC')
                                                 ->findAll();

            $data['hiddenSpots'] = $this->getHiddenSpotListWithThumbnails();
        }

        // '마! 이게 진짜 국밥이다!' 전용 관리 데이터: 국밥 맛집별 좋아요 집계 + 좋아요 로그
        if ($item['view_file'] === 'view_2') {
            $eventLikeLogModel = new EventLikeLogModel();

            $data['gukbapItems'] = $this->getGukbapItemsWithLikeCount($idx);
            $data['likeLogs']    = $eventLikeLogModel->getLogsByEvent($idx);
        }

        return view('backoffice/event_manage/' . $item['view_file'], $this->base($item['title'] . ' 관리', $data));
    }

    /**
     * GET /backoffice/event-manage/(:num)/reviews/(:num)
     * 방문 후기 상세 (모달 AJAX)
     */
    public function reviewDetail(int $idx, int $reviewIdx): \CodeIgniter\HTTP\ResponseInterface
    {
        $eventReviewModel = new EventReviewModel();
        $review = $eventReviewModel->where('event_idx', $idx)->find($reviewIdx);

        if (!$review) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => '존재하지 않는 후기입니다.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'review'  => [
                'idx'       => (int) $review['idx'],
                'user_id'   => $review['user_id'],
                'spot_name' => $review['spot_name'],
                'content'   => $review['content'],
                'photo_url' => $review['photo_url'],
                'state'     => (int) $review['state'],
                'reg_date'  => date('Y-m-d H:i', strtotime($review['reg_date'])),
            ],
        ]);
    }

    /**
     * POST /backoffice/event-manage/hidden-spots/(:num)/state
     * '숨은 명소' 태그 연결(hashtag_number) 활성/비활성 토글
     */
    public function toggleHiddenSpot(int $hnIdx): \CodeIgniter\HTTP\RedirectResponse
    {
        $hashtagNumberModel = new HashtagNumberModel();
        $row = $hashtagNumberModel->find($hnIdx);

        if ($row) {
            $hashtagNumberModel->update($hnIdx, [
                'state' => $row['state'] ? 0 : 1,
            ]);

            // 태그 사용 카운트 재집계
            (new HashtagModel())->recalcUseCount((int) $row['hashtag_idx']);
        }

        return redirect()->back();
    }

    /**
     * '숨은 명소' 태그가 연결된 맛집·관광지 전체 목록(활성/비활성 모두) + 썸네일
     */
    private function getHiddenSpotListWithThumbnails(): array
    {
        $rows = (new HashtagNumberModel())->getHiddenSpotList();

        $thumbnailModel = new ThumbnailModel();
        foreach ($rows as &$row) {
            $thumbs = $row['type'] === 'restaurant'
                ? $thumbnailModel->getByRestaurant((int) $row['content_idx'])
                : $thumbnailModel->getByPlace((int) $row['content_idx']);
            $row['thumbnail']      = !empty($thumbs) ? $thumbs[0]['img_url'] : null;
            $row['category_label'] = $row['type'] === 'restaurant'
                ? (RestaurantModel::CATEGORIES[(int) $row['category_num']] ?? '기타')
                : (PlaceModel::CATEGORIES[(int) $row['category_num']] ?? '기타');
        }
        unset($row);

        return $rows;
    }

    /**
     * '국밥' 맛집(이름에 '국밥' 포함, 노출 상태) 목록 + 이벤트별 누적 좋아요 수
     * 좋아요 수 내림차순으로 정렬해 반환
     */
    private function getGukbapItemsWithLikeCount(int $eventIdx): array
    {
        $restaurants = (new RestaurantModel())->where('state', 1)
                                               ->like('name', '국밥')
                                               ->orderBy('idx', 'DESC')
                                               ->findAll();

        $voteCounts = (new EventLikeLogModel())->getVoteCountsByEvent($eventIdx);

        $items = [];
        foreach ($restaurants as $r) {
            $items[] = [
                'idx'        => (int) $r['idx'],
                'name'       => $r['name'],
                'category'   => RestaurantModel::CATEGORIES[(int) ($r['category_num'] ?? 0)] ?? '기타',
                'like_count' => (int) ($voteCounts[$r['idx']] ?? 0),
            ];
        }

        usort($items, static fn ($a, $b) => $b['like_count'] <=> $a['like_count']);

        return $items;
    }
}
