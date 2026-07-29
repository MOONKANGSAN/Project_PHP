<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * reactions 테이블에 state 컬럼 추가
 * 1: 활성(카운트 포함), 9: 취소(카운트 제외), 0: 재활성화(카운트 포함)
 */
class AddStateToReactions extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('reactions', [
            'state' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'type',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('reactions', 'state');
    }
}
