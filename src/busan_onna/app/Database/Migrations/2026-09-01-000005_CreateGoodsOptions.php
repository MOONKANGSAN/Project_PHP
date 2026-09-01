<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * 굿즈 옵션 그룹 테이블 (색상, 사이즈 등 옵션 종류)
 */
class CreateGoodsOptions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'goods_idx'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'option_name' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('goods_idx');
        $this->forge->createTable('goods_options');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_options');
    }
}
