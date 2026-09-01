<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * 굿즈 추가 이미지 테이블 생성
 */
class CreateGoodsImages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'goods_idx'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'sort_order' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('goods_idx');
        $this->forge->createTable('goods_images');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_images');
    }
}
