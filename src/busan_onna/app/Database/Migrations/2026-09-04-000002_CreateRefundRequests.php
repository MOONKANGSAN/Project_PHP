<?php
// 환불 요청 헤더 — 요청 단위로 사유·상태·관리자 메모를 관리
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateRefundRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_idx'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_idx'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reason'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'reason_text'  => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'admin_memo'   => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'processed_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'created_at'   => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('order_idx');
        $this->forge->addKey('user_idx');
        $this->forge->addKey('status');
        $this->forge->createTable('refund_requests');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_requests');
    }
}
