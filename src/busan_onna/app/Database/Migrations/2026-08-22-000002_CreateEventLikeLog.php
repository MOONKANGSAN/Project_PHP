<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 이벤트 전용 좋아요 로그(event_like_log) 테이블 생성
 * '마! 이게 진짜 국밥이다!' 등 투표형 이벤트에서 1인 1일 1회 좋아요를 기록하고,
 * 서로 다른 날짜로 3회 이상 참여한 회원을 추첨 대상으로 집계하는 데 사용한다.
 */
class CreateEventLikeLog extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            // site_event.idx 참조
            'event_idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            // busan_restaurant.idx 참조 (어느 맛집에 투표했는지)
            'restaurant_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            // 좋아요를 누른 날짜 (1인 1일 1회 제한 기준)
            'like_date'      => ['type' => 'DATE'],
            'reg_date'       => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 1인 1일 1회 제한: 같은 이벤트에서 같은 유저가 같은 날 두 번 참여 불가
        $this->forge->addUniqueKey(['event_idx', 'user_idx', 'like_date'], 'uq_event_user_day');
        // 맛집별 득표 집계용 인덱스
        $this->forge->addKey(['event_idx', 'restaurant_idx'], false, false, 'idx_event_restaurant');

        $this->forge->createTable('event_like_log', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('event_like_log', true);
    }
}
