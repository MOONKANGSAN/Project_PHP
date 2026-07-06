<?php

namespace App\Controllers;

use App\Models\TravelCourseModel;
use App\Models\TravelCourseItemModel;
use App\Models\RestaurantModel;
use App\Models\PlaceModel;
use App\Models\EventModel;

/**
 * 백오피스 — 여행코스 관리 컨트롤러
 */
class BackofficeTravelCourse extends BaseController
{
    private TravelCourseModel     $model;
    private TravelCourseItemModel $itemModel;

    private const UPLOAD_DIR = 'uploads/thumbnails/';

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model     = new TravelCourseModel();
        $this->itemModel = new TravelCourseItemModel();
    }

    /**
     * 뷰 공통 데이터 조합
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
     * 대표 이미지 업로드 처리
     * - delete_thumb=1 이면 기존 파일 삭제 후 null 반환
     * - 새 파일이 있으면 기존 파일 삭제 후 신규 경로 반환
     * - 아무 변경도 없으면 기존 경로 그대로 반환
     */
    private function uploadThumb(?string $existingUrl): ?string
    {
        $file      = $this->request->getFile('thumb_img');
        $hasNewFile = $file && $file->isValid() && !$file->hasMoved();
        $doDelete   = (bool) $this->request->getPost('delete_thumb');

        // 기존 파일 물리 삭제 헬퍼
        $removeOld = function () use ($existingUrl): void {
            if ($existingUrl) {
                $path = FCPATH . ltrim($existingUrl, '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        };

        // 삭제 요청만 있고 신규 파일 없음
        if ($doDelete && !$hasNewFile) {
            $removeOld();
            return null;
        }

        // 신규 파일 없음 → 기존 유지
        if (!$hasNewFile) {
            return $existingUrl;
        }

        // 신규 파일 업로드 (기존 파일 먼저 삭제)
        $removeOld();

        $uploadDir = FCPATH . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return '/' . self::UPLOAD_DIR . $newName;
    }

    /**
     * POST 데이터에서 코스 항목 배열 조합
     * items[0][name], items[0][content_type], ... 형태로 전송된다.
     */
    private function parseItems(): array
    {
        $raw = $this->request->getPost('items') ?? [];
        return is_array($raw) ? $raw : [];
    }

    /** GET /backoffice/travel-courses */
    public function list(): string
    {
        $q     = (string) ($this->request->getGet('q') ?? '');
        $state = (string) ($this->request->getGet('state') ?? '');

        $items = $this->model->getList($q, $state);

        return view('backoffice/travel_course/list', $this->base('여행코스 관리', [
            'items' => $items,
            'pager' => $this->model->pager,
            'q'     => $q,
            'state' => $state,
        ]));
    }

    /** GET /backoffice/travel-courses/register */
    public function register(): string
    {
        return view('backoffice/travel_course/form', $this->base('여행코스 등록', [
            'course'       => null,
            'course_items' => [],
            'mode'         => 'register',
        ]));
    }

    /** POST /backoffice/travel-courses/register */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (!$this->validate([
            'title' => 'required|max_length[100]',
            'state' => 'required|in_list[0,1]',
        ])) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        // 항목 개수 검증
        $rawItems = $this->parseItems();
        $validItems = array_filter($rawItems, fn($i) => trim((string) ($i['name'] ?? '')) !== '');
        if (empty($validItems)) {
            return redirect()->back()->withInput()
                ->with('form_errors', ['items' => '코스 항목을 1개 이상 입력해주세요.']);
        }

        $thumbUrl = $this->uploadThumb(null);

        $this->model->insert([
            'state'       => $this->request->getPost('state'),
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'sido'        => $this->request->getPost('sido') ?: null,
            'thumb_url'   => $thumbUrl,
            'reg_id'      => session()->get('backoffice.id'),
            'reg_date'    => date('Y-m-d H:i:s'),
            'edit_date'   => date('Y-m-d H:i:s'),
        ]);

        $courseIdx = (int) $this->model->getInsertID();
        $this->itemModel->replaceByCourse($courseIdx, $rawItems);

        session()->setFlashdata('success', '여행코스가 등록되었습니다.');
        return redirect()->to('/backoffice/travel-courses');
    }

    /** GET /backoffice/travel-courses/(:num)/edit */
    public function edit(int $idx): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $course = $this->model->find($idx);
        if (!$course) {
            session()->setFlashdata('error', '존재하지 않는 코스입니다.');
            return redirect()->to('/backoffice/travel-courses');
        }

        return view('backoffice/travel_course/form', $this->base('여행코스 수정', [
            'course'       => $course,
            'course_items' => $this->itemModel->getByCourse($idx),
            'mode'         => 'edit',
        ]));
    }

    /** POST /backoffice/travel-courses/(:num)/edit */
    public function update(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $course = $this->model->find($idx);
        if (!$course) {
            session()->setFlashdata('error', '존재하지 않는 코스입니다.');
            return redirect()->to('/backoffice/travel-courses');
        }

        if (!$this->validate([
            'title' => 'required|max_length[100]',
            'state' => 'required|in_list[0,1]',
        ])) {
            return redirect()->back()->withInput()
                ->with('form_errors', $this->validator->getErrors());
        }

        $rawItems = $this->parseItems();
        $validItems = array_filter($rawItems, fn($i) => trim((string) ($i['name'] ?? '')) !== '');
        if (empty($validItems)) {
            return redirect()->back()->withInput()
                ->with('form_errors', ['items' => '코스 항목을 1개 이상 입력해주세요.']);
        }

        $thumbUrl = $this->uploadThumb($course['thumb_url']);

        $this->model->update($idx, [
            'state'       => $this->request->getPost('state'),
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'sido'        => $this->request->getPost('sido') ?: null,
            'thumb_url'   => $thumbUrl,
            'edit_date'   => date('Y-m-d H:i:s'),
        ]);

        $this->itemModel->replaceByCourse($idx, $rawItems);

        session()->setFlashdata('success', '여행코스가 수정되었습니다.');
        return redirect()->to('/backoffice/travel-courses');
    }

    /** POST /backoffice/travel-courses/(:num)/state */
    public function toggleState(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $course = $this->model->find($idx);
        if ($course) {
            $this->model->update($idx, [
                'state'     => $course['state'] ? 0 : 1,
                'edit_date' => date('Y-m-d H:i:s'),
            ]);
        }
        return redirect()->back();
    }

    /** POST /backoffice/travel-courses/(:num)/delete */
    public function delete(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $course = $this->model->find($idx);
        if ($course) {
            // 대표 이미지 파일 삭제
            if ($course['thumb_url']) {
                $path = FCPATH . ltrim($course['thumb_url'], '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            // 항목 삭제 후 코스 삭제 (FK CASCADE 되지만 명시적으로 처리)
            $this->itemModel->deleteByCourse($idx);
            $this->model->delete($idx);
        }

        session()->setFlashdata('success', '여행코스가 삭제되었습니다.');
        return redirect()->to('/backoffice/travel-courses');
    }

    /**
     * GET /backoffice/travel-courses/content-search
     * 항목 연결용 맛집·관광지·행사 Ajax 검색
     */
    public function contentSearch(): \CodeIgniter\HTTP\ResponseInterface
    {
        $type = $this->request->getGet('type');
        $q    = trim((string) ($this->request->getGet('q') ?? ''));

        if ($q === '' || !in_array($type, ['restaurant', 'place', 'event'], true)) {
            return $this->response->setJSON([]);
        }

        $results = match ($type) {
            'restaurant' => (new RestaurantModel())->like('name', $q)->select('idx, name, address1')->limit(10)->findAll(),
            'place'      => (new PlaceModel())->like('name', $q)->select('idx, name, address1')->limit(10)->findAll(),
            'event'      => (new EventModel())->like('name', $q)->select('idx, name, address1')->limit(10)->findAll(),
        };

        return $this->response->setJSON($results);
    }
}
