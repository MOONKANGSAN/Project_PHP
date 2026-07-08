<?php

namespace App\Controllers;

use App\Models\InquiryModel;
use App\Models\FaqModel;

/**
 * 백오피스 — 휴지통 컨트롤러
 * 소프트 삭제된 고객문의·FAQ를 탭 전환 형식으로 표시한다.
 * ?tab=inquiry (기본) / ?tab=faq
 */
class BackofficeTrash extends BaseController
{
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
     * GET /backoffice/trash
     * 고객문의 탭 / FAQs 탭 전환 뷰
     */
    public function index(): string
    {
        $tab = $this->request->getGet('tab') ?? 'inquiry';
        $tab = in_array($tab, ['inquiry', 'faq']) ? $tab : 'inquiry';
        $q   = (string) ($this->request->getGet('q') ?? '');

        $inquiryModel = new InquiryModel();
        $faqModel     = new FaqModel();

        // 각 탭의 휴지통 건수 (탭 배지용)
        $inquiryCount = (new InquiryModel())->where('state', InquiryModel::STATE_DELETED)->countAllResults();
        $faqCount     = (new FaqModel())->where('state', FaqModel::STATE_DELETED)->countAllResults();

        $items = [];
        $pager = null;

        if ($tab === 'inquiry') {
            $items = $inquiryModel->getTrashList($q);
            $pager = $inquiryModel->pager;
        } else {
            $items = $faqModel->getTrashList($q);
            $pager = $faqModel->pager;
        }

        return view('backoffice/trash/index', $this->base('휴지통', [
            'tab'          => $tab,
            'q'            => $q,
            'items'        => $items,
            'pager'        => $pager,
            'inquiryCount' => $inquiryCount,
            'faqCount'     => $faqCount,
            'inquiryTypes' => InquiryModel::TYPES,
            'faqTypes'     => FaqModel::TYPES,
        ]));
    }

    /**
     * POST /backoffice/trash/inquiry/(:num)/restore
     * 고객문의 복원 (state = 1)
     */
    public function restoreInquiry(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $model = new InquiryModel();
        $item  = $model->find($idx);
        if ($item && (int) $item['state'] === InquiryModel::STATE_DELETED) {
            $model->update($idx, ['state' => 1]);
            session()->setFlashdata('success', "문의 [{$item['title']}]이 복원되었습니다.");
        }
        return redirect()->to('/backoffice/trash?tab=inquiry');
    }

    /**
     * POST /backoffice/trash/faq/(:num)/restore
     * FAQ 복원 (state = 1)
     */
    public function restoreFaq(int $idx): \CodeIgniter\HTTP\RedirectResponse
    {
        $model = new FaqModel();
        $item  = $model->find($idx);
        if ($item && (int) $item['state'] === FaqModel::STATE_DELETED) {
            $model->update($idx, ['state' => 1]);
            session()->setFlashdata('success', "FAQ [{$item['title']}]이 복원되었습니다.");
        }
        return redirect()->to('/backoffice/trash?tab=faq');
    }
}
