<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * FAQ 모델 (faq 테이블)
 * 목록 조회·조회수 증가·등록·수정·소프트 삭제를 처리한다.
 */
class FaqModel extends Model
{
    protected $table      = 'faq';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state',
        'faq_type',
        'title',
        'content',
        'view_cnt',
        'sort_order',
        'reg_id',
        'reg_date',
        'edit_date',
    ];

    protected $useTimestamps = false;

    // FAQ 카테고리 정의
    public const TYPES = [
        1 => '서비스 이용',
        2 => '계정/로그인',
        3 => '결제',
        4 => '기타',
    ];

    public const STATE_DELETED = 9;

    /**
     * 백오피스 목록 조회 — 삭제(state=9) 항목 자동 제외
     */
    public function getList(string $q = '', string $type = '', string $state = ''): array
    {
        $this->where('state !=', self::STATE_DELETED);

        if ($q !== '') {
            $this->like('title', $q);
        }
        if ($type !== '') {
            $this->where('faq_type', (int) $type);
        }
        if ($state !== '') {
            $this->where('state', (int) $state);
        }

        return $this->orderBy('sort_order', 'ASC')
                    ->orderBy('idx', 'DESC')
                    ->paginate(20);
    }

    /**
     * 휴지통 목록 (state = 9)
     */
    public function getTrashList(string $q = ''): array
    {
        $this->where('state', self::STATE_DELETED);

        if ($q !== '') {
            $this->like('title', $q);
        }

        return $this->orderBy('idx', 'DESC')->paginate(20);
    }

    /**
     * 소프트 삭제 (state = 9)
     */
    public function softDelete(int $idx): bool
    {
        return $this->update($idx, ['state' => self::STATE_DELETED]);
    }

    /**
     * 조회수 1 증가
     */
    public function increaseViewCnt(int $idx): void
    {
        $this->set('view_cnt', 'view_cnt + 1', false)
             ->where('idx', $idx)
             ->update();
    }
}
