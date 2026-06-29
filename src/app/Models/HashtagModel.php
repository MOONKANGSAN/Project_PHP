<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 해시태그 원본 테이블 모델
 */
class HashtagModel extends Model
{
    protected $table      = 'hashtag';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['name', 'reg_date', 'use_count'];

    /**
     * 이름으로 태그를 찾아 idx 반환, 없으면 새로 생성 후 idx 반환
     */
    public function findOrCreate(string $name): int
    {
        $name = mb_substr(trim($name), 0, 50);

        $row = $this->where('name', $name)->first();
        if ($row) {
            return (int) $row['idx'];
        }

        $this->insert(['name' => $name, 'reg_date' => date('Y-m-d H:i:s'), 'use_count' => 0]);
        return (int) $this->getInsertID();
    }

    /**
     * 이름 키워드로 기존 태그 검색 (자동완성용)
     */
    public function search(string $q, int $limit = 10): array
    {
        return $this->like('name', $q)
                    ->orderBy('use_count', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * hashtag_number 테이블의 실제 연결 수를 집계해 use_count 갱신
     */
    public function recalcUseCount(int $hashtagIdx): void
    {
        $count = $this->db->table('hashtag_number')
                          ->where('hashtag_idx', $hashtagIdx)
                          ->where('state', 1)
                          ->countAllResults();

        $this->update($hashtagIdx, ['use_count' => $count]);
    }
}
