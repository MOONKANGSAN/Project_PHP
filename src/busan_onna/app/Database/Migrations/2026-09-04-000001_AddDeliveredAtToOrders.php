<?php
// 배송완료 시각 기록 — 환불 요청 시 7일 경과 여부 계산에 사용
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddDeliveredAtToOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('orders', [
            'delivered_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'after'   => 'paid_at',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('orders', 'delivered_at');
    }
}
