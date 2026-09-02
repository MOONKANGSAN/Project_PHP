<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 굿즈 상품 테이블 생성 — state: 1=판매중, 0=중지, 2=품절
 * vendor_idx NULL = 운영자 직판
 * delivery_type: 1=택배, 2=픽업
 */
class CreateGoods extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'vendor_idx'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'   => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'price'         => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'stock'         => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'delivery_type' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'thumbnail'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
            'reg_date'      => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'edit_date'     => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable('goods');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods');
    }
}
