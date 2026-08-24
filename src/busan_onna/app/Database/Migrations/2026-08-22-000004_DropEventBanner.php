<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 이벤트 배너(event_banner) 테이블 롤백
 * 별도의 "이벤트 배너 관리" 화면 대신 기존 배너 관리(main_banner)를 그대로 사용하기로
 * 결정함에 따라, 이전에 추가했던 event_banner 테이블을 제거한다.
 * (2026-08-22-000003_CreateEventBanner 마이그레이션을 이미 실행했든 안 했든
 *  이 마이그레이션만 실행하면 event_banner 테이블이 존재하지 않는 상태로 정리된다.)
 */
class DropEventBanner extends Migration
{
    public function up(): void
    {
        $this->forge->dropTable('event_banner', true);
    }

    public function down(): void
    {
        // 롤백의 롤백은 지원하지 않음 (event_banner 기능 자체를 폐기)
    }
}
