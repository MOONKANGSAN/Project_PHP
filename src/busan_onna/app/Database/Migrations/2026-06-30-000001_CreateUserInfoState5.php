<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 탈퇴회원 이력 테이블 생성 마이그레이션
 * user_info.state = 5인 탈퇴 처리 이력을 기록하며,
 * 복원 시 state = 0으로 업데이트한다.
 */
class CreateUserInfoState5 extends Migration
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
            // 이력 상태: 1=탈퇴 중, 0=복원(탈퇴 취소)
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 대상 회원의 user_info.idx
            'user_info_idx' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            // 탈퇴 처리 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            // 탈퇴 사유 (관리자 입력, NULL 허용)
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 특정 회원의 탈퇴 이력을 빠르게 조회하기 위한 인덱스
        $this->forge->addKey('user_info_idx');

        $this->forge->createTable('user_info_state5', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('user_info_state5', true);
    }
}
