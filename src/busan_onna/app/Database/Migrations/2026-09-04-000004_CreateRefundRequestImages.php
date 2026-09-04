<?php
// 환불 요청 첨부 이미지 — public/uploads/refunds/ 에 저장된 파일 경로
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateRefundRequestImages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'refund_request_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_path'          => ['type' => 'VARCHAR', 'constraint' => 300],
            'created_at'         => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('refund_request_idx');
        $this->forge->createTable('refund_request_images');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_request_images');
    }
}
