<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 메인 배너 모델 (main_banner 테이블)
 * 배너 목록 조회·상태 변경·순서 변경을 처리한다.
 */
class MainBannerModel extends Model
{
    protected $table      = 'main_banner';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state',
        'image_url',
        'alt_text',
        'title',
        'subtitle',
        'link_url',
        'sort_order',
        'reg_id',
        'reg_date',
        'edit_date',
    ];

    protected $useTimestamps = false;

    /**
     * 백오피스 배너 목록 (상태 필터 + 순서·IDX 정렬 + 페이지네이션)
     */
    public function getList(string $state = ''): array
    {
        if ($state !== '') {
            $this->where('state', (int) $state);
        }

        return $this->orderBy('sort_order', 'ASC')
                    ->orderBy('idx', 'DESC')
                    ->paginate(20);
    }

    /**
     * 프론트엔드용 활성 배너 목록 (state=1만, 순서 정렬)
     */
    public function getActiveBanners(): array
    {
        return $this->where('state', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('idx', 'ASC')
                    ->findAll();
    }
}
