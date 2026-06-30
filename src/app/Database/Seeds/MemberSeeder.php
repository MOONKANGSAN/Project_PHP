<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 회원 더미 데이터 시더 (30명)
 * 실행: php spark db:seed MemberSeeder
 * 비밀번호 초기값: 0000 (bcrypt 해싱 후 저장)
 */
class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        // 이미 더미 데이터가 있으면 중복 삽입 방지
        $existing = $db->table('user_info')->like('id', 'testuser')->countAllResults();
        if ($existing > 0) {
            echo "더미 회원 데이터가 이미 존재합니다. 건너뜁니다.\n";
            return;
        }

        // 비밀번호 '0000'을 미리 bcrypt 해싱 (유효성 검사 우회를 위해 DB 빌더 직접 사용)
        $hashedPwd = password_hash('0000', PASSWORD_BCRYPT);

        $firstNames = ['김', '이', '박', '최', '정', '강', '조', '윤', '장', '임'];
        $lastNames  = ['민준', '서연', '도윤', '서현', '지우', '지민', '준서', '수아', '예준', '지아',
                       '현우', '채원', '건우', '나윤', '민재', '다은', '준혁', '하은', '시우', '수빈',
                       '주원', '지유', '승우', '지현', '태양', '혜린', '성민', '유진', '찬영', '소희'];

        $areas = ['seoul', 'busan', 'incheon', 'daegu', 'gwangju', 'daejeon', 'ulsan'];

        $members = [];
        for ($i = 1; $i <= 30; $i++) {
            $num      = str_pad($i, 2, '0', STR_PAD_LEFT);
            $lastName = $lastNames[$i - 1];
            $area     = $areas[($i - 1) % count($areas)];

            // 가입일: 최근 6개월 내 분산된 날짜
            $daysAgo = (int) (($i - 1) * 6);
            $regDate = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

            // 전화번호
            $mid   = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $last  = str_pad($i, 4, '0', STR_PAD_LEFT);
            $phone = "010-{$mid}-{$last}";

            $members[] = [
                'state'    => 1,
                'id'       => "testuser{$num}",
                'password' => $hashedPwd,
                'email'    => "testuser{$num}@{$area}.test",
                'phone'    => $phone,
                'reg_date' => $regDate,
            ];
        }

        // DB 빌더 직접 사용 (모델 유효성 검사 우회, 비밀번호는 위에서 직접 해싱)
        $db->table('user_info')->insertBatch($members);

        echo "회원 더미 데이터 30명이 삽입되었습니다. (초기 비밀번호: 0000)\n";
    }
}
