<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * 주문 라인 아이템 테이블 — 가격은 주문 시점 스냅샷으로 저장
 */
class CreateOrderItems extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'goods_idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'vendor_idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'option_value_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'goods_name'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'option_label'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'quantity'         => ['type' => 'SMALLINT', 'unsigned' => true],
            'unit_price'       => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('order_idx');
        $this->forge->createTable('order_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('order_items');
    }
}
