<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * busan_maps_top5 테이블에 콘텐츠 연결 컬럼 추가
 * - content_type: 연결된 콘텐츠 종류 (restaurant / place / event)
 * - content_idx:  해당 콘텐츠 테이블의 idx (뷰 페이지 URL 생성에 사용)
 */
class AddContentFieldsToBusanMapsTop5 extends Migration
{
    public function up(): void
    {
        // content_type — sort_order 뒤에 삽입
        $this->forge->addColumn('busan_maps_top5', [
            'content_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'sort_order',
                'comment'    => 'restaurant | place | event',
            ],
        ]);

        // content_idx — content_type 뒤에 삽입
        $this->forge->addColumn('busan_maps_top5', [
            'content_idx' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'content_type',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('busan_maps_top5', ['content_type', 'content_idx']);
    }
}
