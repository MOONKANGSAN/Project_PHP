<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 여행코스 항목 테이블 (travel_course_item) 생성
 * 코스 하나당 최대 8개 항목을 가진다.
 */
class CreateTravelCourseItem extends Migration
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
            // 소속 코스 idx (travel_course.idx)
            'course_idx' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            // 코스 내 표시 순서 (1~8)
            'item_order' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 1,
            ],
            // 연결 콘텐츠 유형: restaurant / place / event / custom
            'content_type' => [
                'type'       => 'ENUM',
                'constraint' => ['restaurant', 'place', 'event', 'custom'],
                'default'    => 'custom',
            ],
            // 기존 콘텐츠 idx 연결 (직접입력이면 NULL)
            'content_idx' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true,
            ],
            // 항목 이름
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            // 항목 설명
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // 권장 체류 시간 (예: 1시간, 30분)
            'stay_time' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            // 주소
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // 위도
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            // 경도
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            // 등록 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey(['course_idx', 'item_order']);
        $this->forge->addForeignKey('course_idx', 'travel_course', 'idx', 'CASCADE', 'CASCADE');

        $this->forge->createTable('travel_course_item', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('travel_course_item', true);
    }
}
