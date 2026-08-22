<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 이벤트 방문 후기(event_review) 테이블 생성
 * '부산 골목 탐험단' 등 방문인증형 site_event 후기를 저장한다.
 * hashtag_number와 마찬가지로 site_event.idx를 소프트 참조(event_idx)한다.
 */
class CreateEventReview extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            // site_event.idx 참조
            'event_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            // user_info.idx / id 스냅샷 (탈퇴 후에도 작성 당시 아이디 표시 유지)
            'user_idx'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'   => ['type' => 'VARCHAR', 'constraint' => 50],
            // 방문한 '숨은 명소' 이름 (자유 입력, 선택)
            'spot_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'content'   => ['type' => 'TEXT'],
            'photo_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
            'like_cnt'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            // 1=노출 9=삭제
            'state'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'reg_date'  => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('event_idx');
        $this->forge->addKey('state');

        $this->forge->createTable('event_review', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('event_review', true);
    }
}
