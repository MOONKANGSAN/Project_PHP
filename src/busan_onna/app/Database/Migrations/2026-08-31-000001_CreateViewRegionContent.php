<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * v_region_content View 생성
 * busan_restaurant / busan_place / busan_event 세 테이블을 UNION ALL로 통합한다.
 */
class CreateViewRegionContent extends Migration
{
    public function up(): void
    {
        $this->db->query('
            CREATE OR REPLACE VIEW v_region_content AS
              SELECT
                idx,
                \'restaurant\' AS content_type,
                name,
                address1,
                address2,
                state,
                like_cnt,
                reg_date
              FROM busan_restaurant
              UNION ALL
              SELECT
                idx,
                \'place\' AS content_type,
                name,
                address1,
                address2,
                state,
                like_cnt,
                reg_date
              FROM busan_place
              UNION ALL
              SELECT
                idx,
                \'event\' AS content_type,
                name,
                address1,
                address2,
                state,
                like_cnt,
                reg_date
              FROM busan_event
        ');
    }

    public function down(): void
    {
        $this->db->query('DROP VIEW IF EXISTS v_region_content');
    }
}
