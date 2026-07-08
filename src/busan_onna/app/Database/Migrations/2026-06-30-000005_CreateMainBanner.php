<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 메인 배너 테이블 생성
 * 메인 페이지 상단 슬라이더에 노출될 배너를 관리한다.
 */
class CreateMainBanner extends Migration
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
            // 노출 상태: 1=활성(노출), 0=비활성(숨김)
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 배너 이미지 경로 (uploads/banners/xxx.jpg 형식)
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            // 이미지 대체 텍스트 — 이미지 설명 및 접근성(alt 속성)
            'alt_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            // 배너 오버레이 제목 (슬라이더 위에 겹쳐 표시할 텍스트)
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            // 배너 부제목
            'subtitle' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
            ],
            // 배너 클릭 시 이동할 URL (NULL = 링크 없음)
            'link_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
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
        // 상태·순서 기준 정렬 최적화
        $this->forge->addKey(['state', 'sort_order']);

        $this->forge->createTable('main_banner', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('main_banner', true);
    }
}
