<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 장바구니 테이블 생성
 */
class CreateCart extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'goods_idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'option_value_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'quantity'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'reg_date'         => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey(['user_idx', 'goods_idx']);
        $this->forge->createTable('cart');
    }

    public function down(): void
    {
        $this->forge->dropTable('cart');
    }
}
