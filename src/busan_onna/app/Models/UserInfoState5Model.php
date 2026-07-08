<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 탈퇴회원 이력 모델 (user_info_state5 테이블)
 * 탈퇴 처리 시 INSERT, 복원 시 state = 0 으로 UPDATE한다.
 */
class UserInfoState5Model extends Model
{
    protected $table      = 'user_info_state5';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state',
        'user_info_idx',
        'reg_date',
        'reason',
    ];

    protected $useTimestamps = false;

    /**
     * 탈퇴 이력 등록
     * user_info.state = 5 처리와 동시에 호출한다.
     */
    public function recordWithdraw(int $userInfoIdx, ?string $reason = null): void
    {
        $this->insert([
            'state'         => 1,
            'user_info_idx' => $userInfoIdx,
            'reg_date'      => date('Y-m-d H:i:s'),
            'reason'        => $reason ?: null,
        ]);
    }

    /**
     * 복원 처리: 해당 회원의 가장 최근 활성(state=1) 이력을 0으로 변경
     */
    public function recordRestore(int $userInfoIdx): void
    {
        $this->where('user_info_idx', $userInfoIdx)
             ->where('state', 1)
             ->set(['state' => 0])
             ->update();
    }

    /**
     * 특정 회원의 탈퇴 이력 목록 조회 (최신순)
     */
    public function getByUser(int $userInfoIdx): array
    {
        return $this->where('user_info_idx', $userInfoIdx)
                    ->orderBy('idx', 'DESC')
                    ->findAll();
    }

    /**
     * 특정 회원의 가장 최근 활성 탈퇴 이력 단건 조회
     */
    public function getLatestWithdraw(int $userInfoIdx): array|null
    {
        return $this->where('user_info_idx', $userInfoIdx)
                    ->where('state', 1)
                    ->orderBy('idx', 'DESC')
                    ->first();
    }
}
