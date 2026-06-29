<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 이미지 join 테이블
 * 하나의 콘텐츠(맛집/관광지/행사)에 최대 8장의 이미지를 연결
 */
class CreateBusanThumbnail extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'img_order'      => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 1],
            'img_url'        => ['type' => 'VARCHAR', 'constraint' => 500],
            'reg_date'       => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'state'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'restaurant_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'place_idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'event_idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey(['restaurant_idx', 'img_order']);
        $this->forge->addKey(['place_idx',      'img_order']);
        $this->forge->addKey(['event_idx',       'img_order']);

        $this->forge->createTable('busan_thumbnail', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('busan_thumbnail', true);
    }
}
