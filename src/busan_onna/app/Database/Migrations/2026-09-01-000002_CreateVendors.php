<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 입점 판매자 테이블 생성 — state: 0=대기, 1=승인, 2=거절
 */
class CreateVendors extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'user_idx'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'shop_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'note'      => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'reg_date'  => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable('vendors');
    }

    public function down(): void
    {
        $this->forge->dropTable('vendors');
    }
}
