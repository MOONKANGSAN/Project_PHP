<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 일반 사용자 테이블 생성 마이그레이션
 * 사용자 계정 정보(아이디, 비밀번호, 이메일, 연락처 등)를 저장한다.
 */
class CreateUserInfo extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            // 고유 식별자 (PK, 자동증가)
            'idx' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 계정 상태: 1=활성, 0=비활성, 9=정지
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 로그인 아이디 (중복 불가)
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            // bcrypt 해싱된 비밀번호 (VARCHAR 255 권장)
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // 이메일 (중복 불가)
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            // 휴대폰 번호
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
            // 가입 일시 (INSERT 시 자동으로 현재 시각 저장)
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 아이디·이메일 중복 방지 UNIQUE 인덱스
        $this->forge->addUniqueKey('id');
        $this->forge->addUniqueKey('email');

        $this->forge->createTable('user_info', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('user_info', true);
    }
}
