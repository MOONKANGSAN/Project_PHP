<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * v_region_content View 전용 읽기 모델
 * busan_restaurant · busan_place · busan_event UNION View를 단일 인터페이스로 조회한다.
 */
class RegionContentModel extends Model
{
    protected $table         = 'v_region_content';
    protected $primaryKey    = 'idx';
    protected $allowedFields = [];   // View는 쓰기 불가
    protected $useTimestamps = false;

    /**
     * 지역별 탐색 통합 검색
     *
     * @param string $q          검색어 (name LIKE 매칭)
     * @param string $regionName 지역명 (address1 LIKE 필터, 빈 문자열이면 전체)
     * @param string $type       'restaurant' | 'place' | 'event' | '' (빈 값이면 전체)
     * @return array             검색 결과 행 배열 (content_type별 최대 10건, 전체 최대 30건)
     */
    public function searchByRegion(string $q, string $regionName, string $type): array
    {
        $this->where('state', 1)
             ->like('name', $q, 'both');

        if ($regionName !== '') {
            $this->like('address1', $regionName, 'both');
        }

        if ($type !== '') {
            $this->where('content_type', $type);
            return $this->orderBy('name', 'ASC')->findAll(10);
        }

        // 타입 전체 검색: 최대 30건 반환 (컨트롤러에서 타입별 10건 재제한)
        return $this->orderBy('content_type', 'ASC')
                    ->orderBy('name', 'ASC')
                    ->findAll(30);
    }
}
