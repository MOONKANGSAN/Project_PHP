<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 해시태그 연결(join) 테이블
 * hashtag.idx ↔ busan_restaurant / busan_place / busan_event 를 N:M으로 연결
 * 각 콘텐츠당 최대 5개 태그 제한은 서비스 레이어에서 관리
 */
class CreateHashtagNumber extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'reg_date'       => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'state'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'hashtag_idx'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'restaurant_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'place_idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'event_idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('hashtag_idx');
        $this->forge->addKey('restaurant_idx');
        $this->forge->addKey('place_idx');
        $this->forge->addKey('event_idx');

        $this->forge->createTable('hashtag_number', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('hashtag_number', true);
    }
}
