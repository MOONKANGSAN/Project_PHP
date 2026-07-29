<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 추천/비추천 반응 테이블 생성 마이그레이션
 * spot / restaurant / festival 세 서비스의 유저 반응을 단일 테이블로 관리한다.
 */
class CreateReactions extends Migration
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
            // 반응한 사용자 (user_info.idx 참조)
            'user_idx' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            // 반응 대상 서비스 구분
            'target_type' => [
                'type'       => 'ENUM',
                'constraint' => ['spot', 'restaurant', 'festival'],
            ],
            // 대상 서비스의 PK
            'target_idx' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            // 반응 종류: 추천 / 비추천
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['like', 'dislike'],
            ],
            // 반응 등록 일시
            'created_at' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 1인 1반응 보장: 같은 유저가 같은 대상에 중복 반응 불가
        $this->forge->addUniqueKey(['user_idx', 'target_type', 'target_idx'], 'uq_user_target');
        // 집계 쿼리 성능을 위한 인덱스
        $this->forge->addKey(['target_type', 'target_idx'], false, false, 'idx_target');

        $this->forge->createTable('reactions', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('reactions', true);
    }
}
