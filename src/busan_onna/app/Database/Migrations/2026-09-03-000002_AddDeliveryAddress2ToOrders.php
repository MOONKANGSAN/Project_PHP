<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * orders 테이블에 배송지 상세 주소 컬럼 추가
 */
class AddDeliveryAddress2ToOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('orders', [
            'delivery_address2' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'default'    => null,
                'after'      => 'delivery_address',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('orders', 'delivery_address2');
    }
}
