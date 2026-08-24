<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이벤트 배너 모델 (event_banner 테이블)
 * 메인 페이지 "이벤트 배너" 영역에 노출할, site_event와 연결된 배너를 관리한다.
 */
class EventBannerModel extends Model
{
    protected $table      = 'event_banner';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state',
        'event_idx',
        'image_url',
        'image_position',
        'sort_order',
        'reg_id',
        'reg_date',
        'edit_date',
    ];

    /**
     * 백오피스 목록 (연결된 이벤트 제목 포함, 상태 필터)
     */
    public function getList(string $state = ''): array
    {
        $builder = $this->db->table('event_banner eb')
                            ->select('eb.*, se.title AS event_title, se.state AS event_state')
                            ->join('site_event se', 'se.idx = eb.event_idx', 'left');

        if ($state !== '') {
            $builder->where('eb.state', (int) $state);
        }

        return $builder->orderBy('eb.sort_order', 'ASC')
                        ->orderBy('eb.idx', 'DESC')
                        ->get()->getResultArray();
    }

    /**
     * 프론트엔드(메인 페이지)용 활성 배너 목록
     * 배너·연결된 이벤트가 모두 state=1인 것만, 노출 순서대로 반환
     */
    public function getActiveBanners(): array
    {
        return $this->db->table('event_banner eb')
                        ->select('eb.idx, eb.event_idx, eb.image_url, eb.image_position, eb.sort_order, se.title AS event_title')
                        ->join('site_event se', 'se.idx = eb.event_idx')
                        ->where('eb.state', 1)
                        ->where('se.state', 1)
                        ->orderBy('eb.sort_order', 'ASC')
                        ->orderBy('eb.idx', 'ASC')
                        ->get()->getResultArray();
    }
}
