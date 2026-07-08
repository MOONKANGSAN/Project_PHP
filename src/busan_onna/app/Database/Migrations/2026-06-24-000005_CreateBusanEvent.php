<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 행사·축제(busan_event) 테이블 생성
 */
class CreateBusanEvent extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'info'         => ['type' => 'TEXT', 'null' => true],
            'star_point'   => ['type' => 'DECIMAL', 'constraint' => '3,1', 'default' => '0.0'],
            'address1'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'address2'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'detail_url'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'thumb_idx'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'hashtag_idx'  => ['type' => 'TEXT', 'null' => true],
            'reg_date'     => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'edit_date'    => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'view_cnt'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'like_cnt'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'sido'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'latitude'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reg_id'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'price_range'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'start_date'   => ['type' => 'DATE', 'null' => true],
            'end_date'     => ['type' => 'DATE', 'null' => true],
            'category_num' => ['type' => 'TINYINT', 'constraint' => 3, 'default' => 0],
            'host'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_free'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('state');
        $this->forge->addKey(['start_date', 'end_date']);

        $this->forge->createTable('busan_event', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('busan_event', true);
    }
}
