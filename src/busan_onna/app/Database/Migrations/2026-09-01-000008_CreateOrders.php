<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 주문 헤더 테이블 생성
 * status: pending / paid / preparing / shipped / delivered / cancelled
 * delivery_type: 1=택배, 2=픽업
 */
class CreateOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'order_no'            => ['type' => 'VARCHAR', 'constraint' => 30],
            'user_idx'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'total_price'         => ['type' => 'INT', 'unsigned' => true],
            'delivery_type'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'recipient_name'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'recipient_phone'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'default' => null],
            'delivery_address'    => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true, 'default' => null],
            'pickup_location_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'payment_key'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'payment_method'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'paid_at'             => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'reg_date'            => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('order_no');
        $this->forge->addKey('user_idx');
        $this->forge->createTable('orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('orders');
    }
}
