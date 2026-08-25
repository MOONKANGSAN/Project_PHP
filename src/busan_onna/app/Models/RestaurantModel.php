<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 맛집(busan_restaurant) 모델
 */
class RestaurantModel extends Model
{
    protected $table      = 'busan_restaurant';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state', 'name', 'star_point', 'info',
        'address1', 'address2', 'phone',
        'category_num', 'price_range',
        'thumb_idx',
        'reg_date', 'edit_date',
        'view_cnt', 'like_cnt',
        'sido', 'latitude', 'longitude', 'reg_id',
        'open_time', 'parking',
    ];

    // 카테고리 정의
    public const CATEGORIES = [
        1 => '한식',
        2 => '일식',
        3 => '중식',
        4 => '양식',
        5 => '분식/간식',
        6 => '카페/디저트',
        7 => '뷔페',
        8 => '기타',
    ];

    // 가격대 정의
    public const PRICE_RANGES = [
        1 => '₩ (1만원 미만)',
        2 => '₩₩ (1~3만원)',
        3 => '₩₩₩ (3만원 이상)',
    ];

    /**
     * 이름 검색 + 상태 필터 후 페이지네이션
     */
    public function getList(string $q = '', string $state = '', string $sido = '', string $category = '', string $sort = ''): array
    {
        if ($q !== '')       { $this->like('name', $q); }
        if ($state !== '')   { $this->where('state', (int) $state); }
        if ($sido !== '')    { $this->where('sido', $sido); }
        if ($category !== '') { $this->where('category_num', (int) $category); }

        // 정렬: 좋아요순 / 최신순(기본) / 가나다순
        switch ($sort) {
            case 'like':
                $this->orderBy('like_cnt', 'DESC');
                break;
            case 'name':
                $this->orderBy('name', 'ASC');
                break;
            case 'edit_date':
                $this->orderBy('edit_date', 'DESC');
                break;
            case 'new':
            default:
                $this->orderBy('reg_date', 'DESC')->orderBy('idx', 'DESC');
                break;
        }

        return $this->paginate(20);
    }
}
