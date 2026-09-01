<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 배송 정보 테이블 — 택배 주문에만 사용
 * status: ready / shipped / in_transit / delivered
 */
class CreateDeliveries extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_idx'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'courier'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'tracking_no' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ready'],
            'updated_at'  => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('order_idx');
        $this->forge->createTable('deliveries');
    }

    public function down(): void
    {
        $this->forge->dropTable('deliveries');
    }
}
