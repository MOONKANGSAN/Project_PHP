<?php
// 환불 요청 대상 상품 — 체크박스로 선택한 order_items를 연결
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateRefundRequestItems extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'refund_request_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'order_item_idx'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('refund_request_idx');
        $this->forge->createTable('refund_request_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_request_items');
    }
}
