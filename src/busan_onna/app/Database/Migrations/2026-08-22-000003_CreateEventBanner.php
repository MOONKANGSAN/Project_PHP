<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 이벤트 배너(event_banner) 테이블 생성
 * 메인 페이지 "이벤트 배너" 영역에 노출할, site_event와 연결된 배너 이미지를 관리한다.
 * (일반 메인 배너(main_banner)와 달리 1905×600 비율 전용 슬롯이며, 텍스트 오버레이 없이
 *  이미지 자체를 그대로 노출하고 클릭 시 연결된 이벤트 상세(/events/{event_idx})로 이동한다.)
 */
class CreateEventBanner extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 연결된 이벤트 (site_event.idx)
            'event_idx' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            // 노출 상태: 1=활성(노출), 0=비활성(숨김)
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 배너 이미지 경로 (uploads/event_banners/xxx.jpg 형식, 권장 비율 1905×600)
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            // 이미지 표시 위치 — 드래그로 조정한 object-position 값 "X Y"(정수 0~100)
            'image_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => '50 50',
            ],
            // 노출 순서 (낮을수록 먼저 표시)
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
            // 마지막 수정 일시
            'edit_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey(['state', 'sort_order']);
        $this->forge->addKey('event_idx');
        // 이벤트 삭제 시 연결된 배너도 함께 삭제
        $this->forge->addForeignKey('event_idx', 'site_event', 'idx', 'CASCADE', 'CASCADE');

        $this->forge->createTable('event_banner', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('event_banner', true);
    }
}
