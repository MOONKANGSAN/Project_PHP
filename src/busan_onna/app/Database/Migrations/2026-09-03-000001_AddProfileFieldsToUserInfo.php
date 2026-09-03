<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * user_info 테이블에 닉네임(name)과 프로필 이미지(profile_image) 컬럼 추가
 */
class AddProfileFieldsToUserInfo extends Migration
{
    public function up(): void
    {
        // 닉네임: 프로필에 표시될 이름 (선택)
        $this->forge->addColumn('user_info', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
            ],
        ]);

        // 프로필 이미지 파일 경로 (uploads/profile/ 하위 저장)
        $this->forge->addColumn('user_info', [
            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('user_info', 'name');
        $this->forge->dropColumn('user_info', 'profile_image');
    }
}
