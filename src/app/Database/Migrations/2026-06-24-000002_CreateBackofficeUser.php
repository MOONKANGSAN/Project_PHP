<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 백오피스(관리자) 계정 테이블 생성 마이그레이션
 * 어드민 시스템 접근 계정, 권한 레벨, 등록 서버 IP를 저장한다.
 */
class CreateBackofficeUser extends Migration
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
            // 관리자 로그인 아이디 (중복 불가)
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            // bcrypt 해싱된 비밀번호 (VARCHAR 255 권장)
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // 어드민 권한 레벨: 1=일반관리자, 2=슈퍼관리자
            'level' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 계정 등록 시점의 서버 IP (IPv6 최대 45자)
            'plus_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 관리자 아이디 중복 방지 UNIQUE 인덱스
        $this->forge->addUniqueKey('id');

        $this->forge->createTable('backoffice_user', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('backoffice_user', true);
    }
}
