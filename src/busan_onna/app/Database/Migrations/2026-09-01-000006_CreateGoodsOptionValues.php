<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * 굿즈 옵션 값 테이블 (빨강/L 등 개별 재고·추가금액 포함)
 */
class CreateGoodsOptionValues extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'option_idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'value'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'additional_price' => ['type' => 'INT', 'default' => 0],
            'stock'            => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('option_idx');
        $this->forge->createTable('goods_option_values');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_option_values');
    }
}
