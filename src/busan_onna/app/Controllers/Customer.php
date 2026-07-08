<?php

namespace App\Controllers;

use App\Models\FaqModel;
use App\Models\InquiryModel;
use App\Models\NoticeModel;

/**
 * 고객센터 페이지 컨트롤러 (/customer)
 * 공지사항(준비중) / FAQs / 고객문의 탭을 AJAX로 제공한다.
 */
class Customer extends BaseController
{
    /**
     * 메인 페이지 렌더링
     * GET /customer?tab=notice|faq|inquiry
     */
    public function index(): string
    {
        $tab = trim($this->request->getGet('tab') ?? 'faq');
        if (!in_array($tab, ['notice', 'faq', 'inquiry'], true)) {
            $tab = 'faq';
        }

        return view('service/customer/index', [
            'activeTab' => $tab,
            'saved_id'  => $this->request->getCookie('saved_id') ?? '',
        ]);
    }

    /**
     * 공지사항 목록 AJAX
     * GET /customer/ajax/notice
     * 반환: HTML 조각
     */
    public function ajaxNotice(): string
    {
        $noticeModel = new NoticeModel();
        $notices     = $noticeModel->getPublicList(50);

        return view('service/customer/notice_partial', [
            'notices' => $notices,
        ]);
    }

    /**
     * 공지사항 상세 조회수 증가 AJAX
     * POST /customer/notice/(:num)/view
     * 반환: JSON { success }
     */
    public function noticeView(int $idx): void
    {
        $noticeModel = new NoticeModel();
        $notice      = $noticeModel->where('state', 1)->find($idx);

        if ($notice) {
            $noticeModel->increaseViewCnt($idx);
        }

        $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');
        echo json_encode(['success' => (bool) $notice]);
    }

    /**
     * FAQ 목록 AJAX
     * GET /customer/ajax/faq?type=
     * 반환: HTML 조각
     */
    public function ajaxFaq(): string
    {
        $faqModel = new FaqModel();
        $type     = trim($this->request->getGet('type') ?? '');

        $query = $faqModel->where('state', 1);

        if ($type !== '') {
            $query->where('faq_type', (int) $type);
        }

        $faqs = $query->orderBy('sort_order', 'ASC')->orderBy('idx', 'DESC')->findAll();

        return view('service/customer/faq_partial', [
            'faqs'       => $faqs,
            'types'      => FaqModel::TYPES,
            'activeType' => $type,
        ]);
    }

    /**
     * 내 고객문의 목록 AJAX
     * GET /customer/ajax/inquiry
     * 반환: HTML 조각 (로그인 필요)
     */
    public function ajaxInquiry(): string
    {
        $userId = session()->get('user.id');

        if (!$userId) {
            return view('service/customer/inquiry_partial', [
                'isLoggedIn' => false,
                'inquiries'  => [],
                'types'      => InquiryModel::TYPES,
            ]);
        }

        $inquiryModel = new InquiryModel();

        // 내 문의 목록 (삭제 제외)
        $inquiries = $inquiryModel
            ->where('id', $userId)
            ->where('state !=', InquiryModel::STATE_DELETED)
            ->orderBy('idx', 'DESC')
            ->findAll(20);

        return view('service/customer/inquiry_partial', [
            'isLoggedIn' => true,
            'userId'     => $userId,
            'inquiries'  => $inquiries,
            'types'      => InquiryModel::TYPES,
        ]);
    }

    /**
     * 고객문의 등록 AJAX
     * POST /customer/inquiry/store
     * 반환: JSON { success, message }
     */
    public function inquiryStore(): void
    {
        $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

        $userId = session()->get('user.id');
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        $title   = trim($this->request->getPost('title')        ?? '');
        $content = trim($this->request->getPost('content')      ?? '');
        $type    = (int) ($this->request->getPost('inquiry_type') ?? 4);
        $isPublic = (int) ($this->request->getPost('is_public')  ?? 0);

        if ($title === '' || $content === '') {
            echo json_encode(['success' => false, 'message' => '제목과 내용을 입력해주세요.']);
            return;
        }

        if (mb_strlen($title) > 200) {
            echo json_encode(['success' => false, 'message' => '제목은 200자 이내로 입력해주세요.']);
            return;
        }

        $inquiryModel = new InquiryModel();
        $inquiryModel->insert([
            'state'        => 1,
            'id'           => $userId,
            'inquiry_type' => $type,
            'title'        => $title,
            'content'      => $content,
            'is_public'    => $isPublic,
            'reg_date'     => date('Y-m-d H:i:s'),
        ]);

        echo json_encode(['success' => true, 'message' => '문의가 등록되었습니다.']);
    }
}
