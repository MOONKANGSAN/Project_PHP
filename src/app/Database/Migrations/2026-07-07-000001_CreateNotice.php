<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 공지사항 테이블 생성 마이그레이션
 * content는 WYSIWYG 에디터 HTML을 저장하므로 TEXT 타입을 사용한다.
 */
class CreateNotice extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 노출 상태: 0=숨김, 1=활성, 9=소프트삭제
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 상단 고정 여부: 0=일반, 1=고정
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // WYSIWYG 에디터 HTML (TEXT = 최대 65KB)
            'content' => [
                'type' => 'TEXT',
            ],
            'view_cnt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'reg_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'edit_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 고정 공지 + 상태 기준 목록 조회 최적화
        $this->forge->addKey(['state', 'is_pinned', 'idx']);

        $this->forge->createTable('notice', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('notice', true);
    }
}
