<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 고객문의 테이블 생성 마이그레이션
 * 문의 유형·공개여부·답변 상태를 포함하며, 답변 작성 시 state = 2로 변경된다.
 */
class CreateInquiry extends Migration
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
            // 처리 상태: 1=접수(미답변), 2=답변완료, 0=숨김
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 작성자 아이디 (user_info.id 참조, 문자열로 보관하여 탈퇴 후에도 이력 유지)
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            // 문의 유형: 1=계정/로그인, 2=서비스 이용, 3=오류/버그, 4=기타
            'inquiry_type' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 4,
            ],
            // 문의 제목
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // 문의 내용
            'content' => [
                'type' => 'TEXT',
            ],
            // 공개/비공개: 1=공개, 0=비공개
            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // 문의 등록 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            // 관리자 답변 (NULL = 미답변)
            'answer' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // 답변 등록 일시 (NULL = 미답변)
            'answer_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 작성자 기준 조회 인덱스
        $this->forge->addKey('id');
        // 상태·등록일 기준 목록 조회 최적화
        $this->forge->addKey(['state', 'reg_date']);

        $this->forge->createTable('inquiry', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('inquiry', true);
    }
}
