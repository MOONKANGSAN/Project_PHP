<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 공지사항 모델 (notice 테이블)
 * 목록 조회·조회수 증가·등록·수정·소프트 삭제를 처리한다.
 */
class NoticeModel extends Model
{
    protected $table      = 'notice';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state',
        'is_pinned',
        'title',
        'content',
        'view_cnt',
        'reg_id',
        'reg_date',
        'edit_date',
    ];

    protected $useTimestamps = false;

    public const STATE_DELETED = 9;

    /**
     * 백오피스 목록 조회 — 삭제(state=9) 제외, 검색·상태 필터
     */
    public function getList(string $q = '', string $state = '', string $pinned = ''): array
    {
        $this->where('state !=', self::STATE_DELETED);

        if ($q !== '') {
            $this->like('title', $q);
        }
        if ($state !== '') {
            $this->where('state', (int) $state);
        }
        if ($pinned !== '') {
            $this->where('is_pinned', (int) $pinned);
        }

        return $this->orderBy('is_pinned', 'DESC')
                    ->orderBy('idx', 'DESC')
                    ->paginate(20);
    }

    /**
     * 프론트 목록 조회 — 활성(state=1)만, 고정 공지 우선
     */
    public function getPublicList(int $limit = 20): array
    {
        return $this->where('state', 1)
                    ->orderBy('is_pinned', 'DESC')
                    ->orderBy('idx', 'DESC')
                    ->findAll($limit);
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
