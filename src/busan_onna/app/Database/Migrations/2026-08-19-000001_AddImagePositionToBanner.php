<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * main_banner 테이블에 이미지 표시 위치(image_position) 컬럼 추가
 * 드래그로 조정한 object-position 값을 "X Y" 형식(정수 0~100)으로 저장한다.
 */
class AddImagePositionToBanner extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('main_banner', [
            'image_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => '50 50',
                'after'      => 'image_url',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('main_banner', 'image_position');
    }
}
