<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * main_banner 테이블에 location(노출 지역구) 컬럼 추가
 */
class AddLocationToBanner extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('main_banner', [
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'alt_text',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('main_banner', 'location');
    }
}
