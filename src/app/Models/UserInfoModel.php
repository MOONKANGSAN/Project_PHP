<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 일반 사용자 모델
 * user_info 테이블과 매핑되며 회원가입·로그인·정보수정에 사용된다.
 */
class UserInfoModel extends Model
{
    protected $table      = 'user_info';
    protected $primaryKey = 'idx';

    // 대량 할당(insert/update)을 허용할 컬럼 명시
    protected $allowedFields = [
        'state',
        'id',
        'password',
        'email',
        'phone',
        'reg_date',
    ];

    // reg_date는 DB DEFAULT로 처리하므로 자동 타임스탬프 비활성화
    protected $useTimestamps = false;

    // 유효성 검사 규칙
    protected $validationRules = [
        'id'       => 'required|min_length[4]|max_length[50]|is_unique[user_info.id]',
        'password' => 'required|min_length[8]',
        'email'    => 'required|valid_email|max_length[100]|is_unique[user_info.email]',
        'phone'    => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'id' => [
            'is_unique' => '이미 사용 중인 아이디입니다.',
        ],
        'email' => [
            'is_unique' => '이미 등록된 이메일입니다.',
        ],
    ];

    // insert 전 비밀번호 자동 해싱 콜백
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
     * 아이디로 활성 사용자 단건 조회
     */
    public function findByLoginId(string $loginId): array|null
    {
        return $this->where('id', $loginId)
                    ->where('state', 1)
                    ->first();
    }

    /**
     * 회원 목록 조회 (검색·상태 필터 + 페이지네이션)
     * 탈퇴회원(state=5)은 기본 제외
     */
    public function getList(string $q = '', string $state = ''): array
    {
        // 탈퇴회원은 별도 페이지에서 관리하므로 항상 제외
        $this->where('state !=', 5);

        if ($q !== '') {
            $this->groupStart()
                 ->like('id', $q)
                 ->orLike('email', $q)
                 ->orLike('phone', $q)
                 ->groupEnd();
        }
        if ($state !== '') {
            $this->where('state', (int) $state);
        }

        return $this->orderBy('idx', 'DESC')->paginate(20);
    }

    /**
     * 탈퇴회원 목록 조회 (state=5 고정, 검색 + 페이지네이션)
     */
    public function getWithdrawnList(string $q = ''): array
    {
        $this->where('state', 5);

        if ($q !== '') {
            $this->groupStart()
                 ->like('id', $q)
                 ->orLike('email', $q)
                 ->orLike('phone', $q)
                 ->groupEnd();
        }

        return $this->orderBy('idx', 'DESC')->paginate(20);
    }
}
