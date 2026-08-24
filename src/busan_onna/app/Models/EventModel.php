<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 행사·축제(busan_event) 모델
 */
class EventModel extends Model
{
    protected $table      = 'busan_event';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state', 'name', 'info', 'star_point',
        'address1', 'address2', 'detail_url',
        'thumb_idx',
        'reg_date', 'edit_date',
        'view_cnt', 'like_cnt',
        'sido', 'latitude', 'longitude', 'reg_id',
        'price_range', 'start_date', 'end_date',
        'category_num', 'host', 'is_free',
    ];

    public const CATEGORIES = [
        1 => '음악/공연',
        2 => '문화/예술',
        3 => '해양/해변',
        4 => '음식/미식',
        5 => '스포츠',
        6 => '지역축제',
        7 => '전시/박람회',
        8 => '기타',
    ];

    public function getList(string $q = '', string $state = '', string $sido = '', string $category = '', string $sort = ''): array
    {
        if ($q !== '')        { $this->like('name', $q); }
        if ($state !== '')    { $this->where('state', (int) $state); }
        if ($sido !== '')     { $this->where('sido', $sido); }
        if ($category !== '') { $this->where('category_num', (int) $category); }

        // 정렬: 좋아요순 / 최신순(기본) / 가나다순
        switch ($sort) {
            case 'like':
                $this->orderBy('like_cnt', 'DESC');
                break;
            case 'name':
                $this->orderBy('name', 'ASC');
                break;
            case 'new':
            default:
                $this->orderBy('reg_date', 'DESC')->orderBy('idx', 'DESC');
                break;
        }

        return $this->paginate(20);
    }
}
