<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 관광지(busan_place) 모델
 */
class PlaceModel extends Model
{
    protected $table      = 'busan_place';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state', 'name', 'star_point', 'info',
        'address1', 'address2',
        'thumb_idx',
        'reg_date', 'edit_date',
        'view_cnt', 'like_cnt',
        'sido', 'latitude', 'reg_id',
        'open_time', 'admission_fee', 'parking',
        'category_num',
    ];

    public const CATEGORIES = [
        1 => '해변/해수욕장',
        2 => '공원/자연',
        3 => '문화재/역사',
        4 => '박물관/전시',
        5 => '테마파크/놀이',
        6 => '야경/전망대',
        7 => '시장/쇼핑',
        8 => '기타',
    ];

    public function getList(string $q = '', string $state = ''): array
    {
        if ($q !== '') {
            $this->like('name', $q);
        }
        if ($state !== '') {
            $this->where('state', (int) $state);
        }

        return $this->orderBy('idx', 'DESC')->paginate(20);
    }
}
