<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 여행코스 마스터 모델 (travel_course)
 */
class TravelCourseModel extends Model
{
    protected $table      = 'travel_course';
    protected $primaryKey = 'idx';
    protected $returnType = 'array';

    protected $allowedFields = [
        'state', 'title', 'description', 'sido',
        'thumb_url', 'reg_id', 'reg_date', 'edit_date',
    ];

    /**
     * 목록 조회 (검색·상태 필터 + 페이징)
     */
    public function getList(string $q = '', string $state = ''): array
    {
        $builder = $this->orderBy('reg_date', 'DESC');

        if ($q !== '') {
            $builder->like('title', $q);
        }
        if ($state !== '') {
            $builder->where('state', (int) $state);
        }

        return $builder->paginate(20, 'default');
    }
}
