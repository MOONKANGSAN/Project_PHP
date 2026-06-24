<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 백오피스(관리자) 사용자 모델
 * backoffice_user 테이블과 매핑되며 관리자 계정 관리에 사용된다.
 */
class BackofficeUserModel extends Model
{
    protected $table      = 'backoffice_user';
    protected $primaryKey = 'idx';

    // 대량 할당을 허용할 컬럼 명시
    protected $allowedFields = [
        'state',
        'id',
        'password',
        'level',
        'plus_ip',
    ];

    protected $useTimestamps = false;

    // 유효성 검사 규칙
    protected $validationRules = [
        'id'       => 'required|min_length[4]|max_length[50]|is_unique[backoffice_user.id]',
        'password' => 'required|min_length[8]',
        'level'    => 'required|in_list[1,2]',
    ];

    protected $validationMessages = [
        'id' => [
            'is_unique' => '이미 사용 중인 관리자 아이디입니다.',
        ],
        'level' => [
            'in_list' => '권한 레벨은 1(일반관리자) 또는 2(슈퍼관리자)만 허용됩니다.',
        ],
    ];

    // insert·update 전 비밀번호 자동 해싱 콜백
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * password 필드가 있을 경우 bcrypt로 해싱한다.
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash(
                $data['data']['password'],
                PASSWORD_BCRYPT
            );
        }

        return $data;
    }

    /**
     * 아이디로 활성 관리자 단건 조회
     */
    public function findByLoginId(string $loginId): array|null
    {
        return $this->where('id', $loginId)
                    ->where('state', 1)
                    ->first();
    }
}
