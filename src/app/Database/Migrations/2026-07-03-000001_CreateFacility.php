<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 편의시설 테이블 생성
 * restaurant_idx / place_idx / event_idx 중 하나와 연결되며,
 * 각 항목은 TINYINT로 관리한다.
 *
 * 포장        takeout       : 0=불가, 1=가능
 * 무선인터넷  wifi          : 0=불가, 1=가능
 * 화장실      toilet        : 0=없음, 1=있음, 2=남녀 화장실 구분, 3=건물내 화장실 이용가능
 * 단체손님    group_seat    : 0=불가, 1=가능
 * 예약        reservation   : 0=불가, 1=가능
 * 대기공간    waiting_area  : 0=없음, 1=있음
 * 유아의자    baby_chair    : 0=없음, 1=있음
 */
class CreateFacility extends Migration
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

            // 상태: 1=활성, 0=비활성
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],

            // ── 콘텐츠 연결 키 (셋 중 하나만 사용) ──────────────────────
            'restaurant_idx' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'place_idx' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'event_idx' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],

            // ── 편의시설 항목 ─────────────────────────────────────────────

            // 포장: 0=불가, 1=가능
            'takeout' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 무선인터넷: 0=불가, 1=가능
            'wifi' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 화장실: 0=없음, 1=있음, 2=남녀 화장실 구분, 3=건물내 화장실 이용가능
            'toilet' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 단체손님: 0=불가, 1=가능
            'group_seat' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 예약: 0=불가, 1=가능
            'reservation' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 대기공간: 0=없음, 1=있음
            'waiting_area' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 유아의자: 0=없음, 1=있음
            'baby_chair' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 등록 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],

            // 수정 일시
            'edit_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 각 콘텐츠 타입별 조회 최적화
        $this->forge->addKey('restaurant_idx');
        $this->forge->addKey('place_idx');
        $this->forge->addKey('event_idx');

        $this->forge->createTable('busan_facility', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('busan_facility', true);
    }
}
