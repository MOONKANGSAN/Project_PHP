<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * goods_images 테이블에 state(활성여부), reg_date(등록일) 컬럼 추가
 */
class AddStateRegdateToGoodsImages extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('goods_images', [
            'state'    => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'sort_order',
            ],
            'reg_date' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'state',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('goods_images', ['state', 'reg_date']);
    }
}
