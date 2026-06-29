<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 해시태그 원본 테이블 — 실제 태그 텍스트와 사용 횟수를 저장
 */
class CreateHashtag extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'reg_date'  => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'use_count' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('name');

        $this->forge->createTable('hashtag', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('hashtag', true);
    }
}
