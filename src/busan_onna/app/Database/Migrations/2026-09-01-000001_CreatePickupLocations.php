<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 픽업 수령 고정 장소 테이블 생성
 */
class CreatePickupLocations extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'name'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'address'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'reg_date' => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable('pickup_locations');
    }

    public function down(): void
    {
        $this->forge->dropTable('pickup_locations');
    }
}
