<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 지역별 탐색 마스터 테이블 (busan_maps) 생성
 * 부산 구·군 지역 목록을 관리한다.
 */
class CreateBusanMaps extends Migration
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
            // 활성 상태: 1=노출, 0=숨김
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 지역 이름 (예: 영도구, 해운대구)
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            // 사이드바·목록 노출 순서 (낮을수록 먼저)
            'sort_order' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 100,
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
        $this->forge->addKey(['state', 'sort_order']);

        $this->forge->createTable('busan_maps', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        // 부산 16개 구·군 기초 데이터 삽입
        $regions = [
            ['name' => '중구',    'sort_order' => 1],
            ['name' => '서구',    'sort_order' => 2],
            ['name' => '동구',    'sort_order' => 3],
            ['name' => '영도구',  'sort_order' => 4],
            ['name' => '부산진구','sort_order' => 5],
            ['name' => '동래구',  'sort_order' => 6],
            ['name' => '남구',    'sort_order' => 7],
            ['name' => '북구',    'sort_order' => 8],
            ['name' => '해운대구','sort_order' => 9],
            ['name' => '사하구',  'sort_order' => 10],
            ['name' => '금정구',  'sort_order' => 11],
            ['name' => '강서구',  'sort_order' => 12],
            ['name' => '연제구',  'sort_order' => 13],
            ['name' => '수영구',  'sort_order' => 14],
            ['name' => '사상구',  'sort_order' => 15],
            ['name' => '기장군',  'sort_order' => 16],
        ];

        foreach ($regions as $region) {
            $this->db->table('busan_maps')->insert([
                'state'      => 1,
                'name'       => $region['name'],
                'sort_order' => $region['sort_order'],
                'reg_id'     => 'system',
                'reg_date'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('busan_maps', true);
    }
}
