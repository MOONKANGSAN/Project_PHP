<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 지역별 탐색 TOP5 항목 테이블 (busan_maps_top5) 생성
 * busan_maps 지역 1개당 최대 5개의 추천 항목을 저장한다.
 */
class CreateBusanMapsTop5 extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            // 고유 식별자
            'idx' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 노출 상태: 1=활성, 0=비활성
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 연결 지역 (busan_maps.idx)
            'main_idx' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            // 항목 제목 (예: 영도 등대, 깡깡이마을)
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // 클릭 시 이동할 URL
            'link_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            // TOP5 내 노출 순서 (1~5)
            'sort_order' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            // 항목 간단 설명 (선택)
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
            ],
            // 썸네일 이미지 URL (선택)
            'thumb_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            // 등록 관리자 아이디
            'reg_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            // 등록 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 지역별 TOP5 조회 최적화
        $this->forge->addKey(['main_idx', 'state', 'sort_order']);

        $this->forge->createTable('busan_maps_top5', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('busan_maps_top5', true);
    }
}
