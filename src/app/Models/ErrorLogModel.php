<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 에러 로그 모델 (error_log 테이블)
 * 에러 목록 조회·상태 변경·피드백 저장을 처리한다.
 */
class ErrorLogModel extends Model
{
    protected $table      = 'error_log';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state',
        'error_type',
        'error_code',
        'message',
        'file',
        'line',
        'url',
        'method',
        'ip',
        'reg_date',
        'resolved_at',
        'feedback',
    ];

    protected $useTimestamps = false;

    // 에러 유형 정의
    public const TYPES = [
        1 => 'PHP Exception',
        2 => 'DB Error',
        3 => 'HTTP 4xx',
        4 => 'HTTP 5xx',
        5 => '기타',
    ];

    // 유형별 배지 색상 (뷰에서 사용)
    public const TYPE_COLORS = [
        1 => ['bg' => '#fef3c7', 'color' => '#d97706'],  // 주황
        2 => ['bg' => '#fce7f3', 'color' => '#be185d'],  // 분홍
        3 => ['bg' => '#eff6ff', 'color' => '#2563eb'],  // 파랑
        4 => ['bg' => '#fef2f2', 'color' => '#dc2626'],  // 빨강
        5 => ['bg' => '#f1f5f9', 'color' => '#475569'],  // 회색
    ];

    /**
     * 에러 로그 목록 조회 (유형·상태 필터 + 검색 + 페이지네이션)
     */
    public function getList(string $q = '', string $type = '', string $state = ''): array
    {
        if ($q !== '') {
            $this->groupStart()
                 ->like('message', $q)
                 ->orLike('url', $q)
                 ->orLike('ip', $q)
                 ->groupEnd();
        }
        if ($type !== '') {
            $this->where('error_type', (int) $type);
        }
        if ($state !== '') {
            $this->where('state', (int) $state);
        }

        return $this->orderBy('idx', 'DESC')->paginate(20);
    }

    /**
     * 해결/미해결 토글
     * 해결로 변경 시 resolved_at 기록, 미해결로 되돌릴 시 초기화
     */
    public function toggleState(int $idx): array
    {
        $item     = $this->find($idx);
        $newState = (int) $item['state'] === 0 ? 1 : 0;

        $this->update($idx, [
            'state'       => $newState,
            'resolved_at' => $newState === 1 ? date('Y-m-d H:i:s') : null,
        ]);

        return ['state' => $newState];
    }

    /**
     * 피드백(해결내용) 저장 — 최대 50자
     */
    public function saveFeedback(int $idx, string $feedback): void
    {
        $this->update($idx, [
            'feedback' => mb_substr(trim($feedback), 0, 50) ?: null,
        ]);
    }
}
