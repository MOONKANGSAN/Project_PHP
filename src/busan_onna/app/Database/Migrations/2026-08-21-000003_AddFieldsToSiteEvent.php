<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * site_event에 백오피스 관리용 컬럼 추가
 * use_view_file: 연결 링크 사용 여부 (0=기본뷰, 1=view_file 사용)
 * sub_title: 한줄 소개 (기본뷰용)
 * cta_text / cta_url: 기본뷰 하단 CTA 버튼
 */
class AddFieldsToSiteEvent extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('site_event', [
            'use_view_file' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'state',
            ],
            'sub_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
                'default'    => null,
                'after'      => 'title',
            ],
            'cta_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'content',
            ],
            'cta_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
                'after'      => 'cta_text',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('site_event', ['use_view_file', 'sub_title', 'cta_text', 'cta_url']);
    }
}
