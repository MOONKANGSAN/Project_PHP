<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 에러 로그 테이블 생성
 * 사이트 운영 중 발생한 에러를 기록하고 해결 여부·피드백을 관리한다.
 */
class CreateErrorLog extends Migration
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
            // 처리 상태: 0=미해결, 1=해결됨
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            // 에러 유형: 1=PHP Exception, 2=DB Error, 3=HTTP 4xx, 4=HTTP 5xx, 5=기타
            'error_type' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 5,
            ],
            // 에러 코드 (HTTP 상태코드, PHP errno 등)
            'error_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            // 에러 구체적 내용
            'message' => [
                'type' => 'TEXT',
            ],
            // 에러 발생 파일 경로
            'file' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            // 에러 발생 라인
            'line' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            // 에러 발생 URL
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            // HTTP 메서드
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            // 클라이언트 IP (IPv6 포함 최대 45자)
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            // 에러 발생 일시
            'reg_date' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            // 해결 일시 (NULL = 미해결)
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // 해결 내용 피드백 (최대 50자)
            'feedback' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        // 상태·유형별 조회 최적화
        $this->forge->addKey(['state', 'error_type']);
        $this->forge->addKey('reg_date');

        $this->forge->createTable('error_log', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('error_log', true);
    }
}
