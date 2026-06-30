<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 고객문의 모델 (inquiry 테이블)
 * 문의 목록 조회·답변 저장·상태 변경·소프트 삭제를 처리한다.
 */
class InquiryModel extends Model
{
    protected $table      = 'inquiry';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state',
        'id',
        'inquiry_type',
        'title',
        'content',
        'is_public',
        'reg_date',
        'answer',
        'answer_date',
    ];

    protected $useTimestamps = false;

    // 문의 유형 정의
    public const TYPES = [
        1 => '계정/로그인',
        2 => '서비스 이용',
        3 => '오류/버그',
        4 => '기타',
    ];

    // 처리 상태 정의 (9 = 소프트 삭제)
    public const STATES = [
        0 => '숨김',
        1 => '접수',
        2 => '답변완료',
    ];

    public const STATE_DELETED = 9;

    /**
     * 백오피스 목록 조회 — 삭제(state=9) 항목 자동 제외
     */
    public function getList(string $q = '', string $type = '', string $state = ''): array
    {
        $this->where('state !=', self::STATE_DELETED);

        if ($q !== '') {
            $this->groupStart()
                 ->like('title', $q)
                 ->orLike('id', $q)
                 ->groupEnd();
        }
        if ($type !== '') {
            $this->where('inquiry_type', (int) $type);
        }
        if ($state !== '') {
            $this->where('state', (int) $state);
        }

        return $this->orderBy('idx', 'DESC')->paginate(20);
    }

    /**
     * 휴지통 목록 (state = 9)
     */
    public function getTrashList(string $q = ''): array
    {
        $this->where('state', self::STATE_DELETED);

        if ($q !== '') {
            $this->groupStart()
                 ->like('title', $q)
                 ->orLike('id', $q)
                 ->groupEnd();
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
     * 답변 저장 + 상태를 답변완료(2)로 변경
     */
    public function saveAnswer(int $idx, string $answer): bool
    {
        return $this->update($idx, [
            'answer'      => $answer,
            'answer_date' => date('Y-m-d H:i:s'),
            'state'       => 2,
        ]);
    }

    /**
     * 답변 삭제 + 상태를 접수(1)로 되돌림
     */
    public function deleteAnswer(int $idx): bool
    {
        return $this->update($idx, [
            'answer'      => null,
            'answer_date' => null,
            'state'       => 1,
        ]);
    }
}
