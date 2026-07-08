<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * FAQ 테이블 생성 마이그레이션
 * content는 WYSIWYG 에디터 HTML을 저장하므로 MEDIUMTEXT를 사용한다.
 */
class CreateFaq extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            // 고유 식별자 (PK, 자동증가)
            'idx' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 노출 상태: 1=활성(공개), 0=비활성(숨김)
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // FAQ 카테고리: 1=서비스 이용, 2=계정/로그인, 3=결제, 4=기타
            'faq_type' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 4,
            ],
            // 질문 제목
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // 답변 본문 (WYSIWYG 에디터 HTML 저장, MEDIUMTEXT = 최대 16MB)
            'content' => [
                'type' => 'MEDIUMTEXT',
            ],
            // 조회수
            'view_cnt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            // 노출 순서 (낮을수록 상단 노출, 기본값 100으로 여유 확보)
            'sort_order' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 100,
            ],
            // 등록한 관리자 아이디
            'reg_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            // 최초 등록 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            // 마지막 수정 일시 (NULL = 수정 이력 없음)
            'edit_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 카테고리·상태 기준 목록 조회 최적화
        $this->forge->addKey(['faq_type', 'state']);
        // 노출 순서 정렬 최적화
        $this->forge->addKey(['sort_order', 'idx']);

        $this->forge->createTable('faq', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('faq', true);
    }
}
