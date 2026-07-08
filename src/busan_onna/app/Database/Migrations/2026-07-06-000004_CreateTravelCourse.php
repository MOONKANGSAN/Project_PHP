<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 여행코스 마스터 테이블 (travel_course) 생성
 */
class CreateTravelCourse extends Migration
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
            // 코스 제목
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            // 코스 소개 설명
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // 대표 지역 (부산 구·군)
            'sido' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            // 대표 이미지 경로
            'thumb_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
            // 최종 수정 일시
            'edit_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey(['state', 'reg_date']);

        $this->forge->createTable('travel_course', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('travel_course', true);
    }
}
