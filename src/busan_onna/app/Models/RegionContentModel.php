<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * v_region_content View 전용 읽기 모델
 * busan_restaurant · busan_place · busan_event UNION View를 단일 인터페이스로 조회한다.
 *
 * View이므로 primaryKey 미사용 — idx가 content_type별로 독립적으로 존재하여 단독으로는 유일하지 않음
 */
class RegionContentModel extends Model
{
    protected $table         = 'v_region_content';
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
        // 상태 필터는 항상 적용
        $this->where('state', 1);

        // 검색어가 비어있지 않을 때만 LIKE 조건 추가 (full table scan 방지)
        if ($q !== '') {
            $this->like('name', $q, 'both');
        }

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
