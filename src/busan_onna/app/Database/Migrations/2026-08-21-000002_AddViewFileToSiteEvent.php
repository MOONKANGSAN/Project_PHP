<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * site_event 테이블에 view_file 컬럼 추가
 * 이벤트별 개별 뷰 파일 지정용 (예: view_1, view_2)
 */
class AddViewFileToSiteEvent extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('site_event', [
            'view_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'thumb_url',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('site_event', 'view_file');
    }
}
