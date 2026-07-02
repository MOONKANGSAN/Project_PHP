<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * busan_place, busan_event 테이블에 경도(longitude) 컬럼 추가
 */
class AddLongitudeToPlaceAndEvent extends Migration
{
    public function up(): void
    {
        $col = [
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'after'      => 'latitude',
            ],
        ];

        $this->forge->addColumn('busan_place', $col);
        $this->forge->addColumn('busan_event', $col);
    }

    public function down(): void
    {
        $this->forge->dropColumn('busan_place', 'longitude');
        $this->forge->dropColumn('busan_event', 'longitude');
    }
}
