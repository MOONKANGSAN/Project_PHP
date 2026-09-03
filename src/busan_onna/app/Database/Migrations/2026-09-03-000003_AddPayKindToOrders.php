<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * orders 테이블에 결제 분류(pay_kind) 컬럼 추가
 * 값: kakao(카카오페이), inicis(이니시스 카드)
 */
class AddPayKindToOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('orders', [
            'pay_kind' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'payment_method',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('orders', 'pay_kind');
    }
}
