<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * busan_restaurant 테이블에 경도(longitude) 컬럼 추가
 * 네이버 지도 API 연동으로 위도(latitude)와 함께 좌표를 저장한다.
 */
class AddLongitudeToRestaurant extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('busan_restaurant', [
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'after'      => 'latitude',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('busan_restaurant', 'longitude');
    }
}
