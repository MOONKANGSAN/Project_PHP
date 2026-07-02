<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 에러 로그 더미 데이터 시더 (8건)
 * 실행: php spark db:seed ErrorLogSeeder
 */
class ErrorLogSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        if ($db->table('error_log')->countAllResults() > 0) {
            echo "에러 로그 데이터가 이미 존재합니다. 건너뜁니다.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'state'       => 0,
                'error_type'  => 1,
                'error_code'  => 'E_PARSE',
                'message'     => 'Parse error: syntax error, unexpected token "}" in /app/Controllers/Home.php on line 87',
                'file'        => '/app/Controllers/Home.php',
                'line'        => 87,
                'url'         => '/busan/spots/detail/12',
                'method'      => 'GET',
                'ip'          => '192.168.1.101',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-2 days')),
                'resolved_at' => null,
                'feedback'    => null,
            ],
            [
                'state'       => 1,
                'error_type'  => 2,
                'error_code'  => '1045',
                'message'     => 'SQLSTATE[HY000] [1045] Access denied for user \'root\'@\'localhost\' (using password: YES)',
                'file'        => '/app/Models/UserInfoModel.php',
                'line'        => 42,
                'url'         => '/auth/login',
                'method'      => 'POST',
                'ip'          => '10.0.0.55',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-5 days')),
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'feedback'    => 'DB 계정 비밀번호 재설정으로 해결',
            ],
            [
                'state'       => 0,
                'error_type'  => 3,
                'error_code'  => '404',
                'message'     => 'Controller or its method is not found: \App\Controllers\Events::festival',
                'file'        => null,
                'line'        => null,
                'url'         => '/events/festival/99',
                'method'      => 'GET',
                'ip'          => '203.0.113.42',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-1 day')),
                'resolved_at' => null,
                'feedback'    => null,
            ],
            [
                'state'       => 0,
                'error_type'  => 4,
                'error_code'  => '500',
                'message'     => 'Uncaught RuntimeException: Unable to connect to the database. Database: busan_onna. Error: Connection refused.',
                'file'        => '/system/Database/Connection.php',
                'line'        => 213,
                'url'         => '/backoffice/restaurants',
                'method'      => 'GET',
                'ip'          => '127.0.0.1',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'resolved_at' => null,
                'feedback'    => null,
            ],
            [
                'state'       => 1,
                'error_type'  => 3,
                'error_code'  => '403',
                'message'     => 'BackofficeAuthFilter: Unauthorized access attempt to /backoffice/dashboard',
                'file'        => '/app/Filters/BackofficeAuthFilter.php',
                'line'        => 28,
                'url'         => '/backoffice/dashboard',
                'method'      => 'GET',
                'ip'          => '58.229.10.4',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-7 days')),
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'feedback'    => 'IP 차단 처리 완료',
            ],
            [
                'state'       => 0,
                'error_type'  => 1,
                'error_code'  => 'E_WARNING',
                'message'     => 'Warning: Trying to access array offset on value of type null in /app/Models/RestaurantModel.php on line 59',
                'file'        => '/app/Models/RestaurantModel.php',
                'line'        => 59,
                'url'         => '/backoffice/restaurants/15/edit',
                'method'      => 'GET',
                'ip'          => '127.0.0.1',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'resolved_at' => null,
                'feedback'    => null,
            ],
            [
                'state'       => 1,
                'error_type'  => 5,
                'error_code'  => null,
                'message'     => 'Session: the configured save path "/var/lib/php/sessions" is not writable by the PHP process.',
                'file'        => null,
                'line'        => null,
                'url'         => '/',
                'method'      => 'GET',
                'ip'          => '172.16.0.1',
                'reg_date'    => date('Y-m-d H:i:s', strtotime('-10 days')),
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-10 days', strtotime('+2 hours'))),
                'feedback'    => 'writable/session 권한 755 변경 완료',
            ],
            [
                'state'       => 0,
                'error_type'  => 4,
                'error_code'  => '500',
                'message'     => 'Maximum execution time of 30 seconds exceeded in /app/Controllers/BackofficeRestaurant.php on line 198',
                'file'        => '/app/Controllers/BackofficeRestaurant.php',
                'line'        => 198,
                'url'         => '/backoffice/restaurants/register',
                'method'      => 'POST',
                'ip'          => '127.0.0.1',
                'reg_date'    => $now,
                'resolved_at' => null,
                'feedback'    => null,
            ],
        ];

        $db->table('error_log')->insertBatch($rows);
        echo "에러 로그 더미 데이터 " . count($rows) . "건이 삽입되었습니다.\n";
    }
}
