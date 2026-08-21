<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 부산온나 자체 이벤트(site_event) 테이블 생성
 * busan_event의 idx/state/날짜/카운트 구조 참고
 */
class CreateSiteEvent extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'content'    => ['type' => 'TEXT', 'null' => true],
            'thumb_url'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
            // 1=방문인증 2=투표 3=공모전 4=기타
            'event_type' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 4],
            'start_date' => ['type' => 'DATE', 'null' => true, 'default' => null],
            'end_date'   => ['type' => 'DATE', 'null' => true, 'default' => null],
            'reg_date'   => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'edit_date'  => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'view_cnt'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'like_cnt'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'reg_id'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('state');
        $this->forge->addKey(['start_date', 'end_date']);

        $this->forge->createTable('site_event', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('site_event', true);
    }
}
