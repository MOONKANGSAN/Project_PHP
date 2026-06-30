<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FAQ 더미 데이터 시더 (5건)
 * 실행: php spark db:seed FaqSeeder
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        $existing = $db->table('faq')->countAllResults();
        if ($existing > 0) {
            echo "FAQ 데이터가 이미 존재합니다. 건너뜁니다.\n";
            return;
        }

        // 관리자 아이디 조회 (없으면 'admin' 기본값 사용)
        $admin = $db->table('backoffice_user')->select('id')->limit(1)->get()->getRowArray();
        $regId = $admin['id'] ?? 'admin';

        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'state'      => 1,
                'faq_type'   => 1,   // 서비스 이용
                'title'      => '부산온나는 어떤 서비스인가요?',
                'content'    => '<p><strong>부산온나</strong>는 부산을 방문하는 국내외 여행객을 위한 <strong>부산 특화 여행 정보 플랫폼</strong>입니다.</p><p>관광지, 맛집, 축제·행사, 여행코스 정보를 한 곳에서 제공하여 여행객이 보다 편리하게 부산 여행을 계획하고 즐길 수 있도록 돕습니다.</p><ul><li>부산 관광지 및 맛집 정보 제공</li><li>축제·행사 일정 안내</li><li>지역별 인터랙티브 지도 서비스</li><li>추천 여행 코스 큐레이션</li></ul>',
                'view_cnt'   => 128,
                'sort_order' => 1,
                'reg_id'     => $regId,
                'reg_date'   => date('Y-m-d H:i:s', strtotime('-30 days')),
                'edit_date'  => null,
            ],
            [
                'state'      => 1,
                'faq_type'   => 2,   // 계정/로그인
                'title'      => '회원가입은 어떻게 하나요?',
                'content'    => '<p>회원가입은 아래 순서로 진행하시면 됩니다.</p><ol><li>메인 페이지 우측 상단의 <strong>[회원가입]</strong> 버튼을 클릭합니다.</li><li>아이디, 비밀번호, 이메일, 연락처를 입력합니다.</li><li><strong>[가입하기]</strong> 버튼을 눌러 완료합니다.</li></ol><p>가입 후 바로 로그인하여 서비스를 이용하실 수 있습니다.</p><blockquote><p>💡 아이디는 영문·숫자 조합 4자 이상, 비밀번호는 8자 이상으로 설정해 주세요.</p></blockquote>',
                'view_cnt'   => 97,
                'sort_order' => 2,
                'reg_id'     => $regId,
                'reg_date'   => date('Y-m-d H:i:s', strtotime('-28 days')),
                'edit_date'  => null,
            ],
            [
                'state'      => 1,
                'faq_type'   => 2,   // 계정/로그인
                'title'      => '비밀번호를 잊어버렸어요. 어떻게 하나요?',
                'content'    => '<p>현재 이메일을 통한 비밀번호 재설정 기능을 준비 중입니다.</p><p>당분간은 고객문의를 통해 본인 확인 후 <strong>임시 비밀번호</strong>를 발급해 드리고 있습니다.</p><p><strong>문의 시 아래 정보를 함께 알려주세요.</strong></p><ul><li>가입 아이디</li><li>가입 시 등록한 이메일 주소</li><li>가입 시 등록한 연락처</li></ul>',
                'view_cnt'   => 214,
                'sort_order' => 3,
                'reg_id'     => $regId,
                'reg_date'   => date('Y-m-d H:i:s', strtotime('-25 days')),
                'edit_date'  => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'state'      => 1,
                'faq_type'   => 1,   // 서비스 이용
                'title'      => '맛집·관광지 정보는 얼마나 자주 업데이트되나요?',
                'content'    => '<p>맛집, 관광지, 행사 정보는 <strong>운영팀이 주 1~2회</strong> 직접 검수하여 업데이트하고 있습니다.</p><p>정보 오류를 발견하셨다면 <a href="/inquiry">고객문의</a>로 알려주시면 빠르게 수정하겠습니다.</p><table><thead><tr><th>카테고리</th><th>업데이트 주기</th></tr></thead><tbody><tr><td>맛집</td><td>주 1회</td></tr><tr><td>관광지</td><td>월 2회</td></tr><tr><td>행사·축제</td><td>수시 (행사 일정 기준)</td></tr></tbody></table>',
                'view_cnt'   => 63,
                'sort_order' => 4,
                'reg_id'     => $regId,
                'reg_date'   => date('Y-m-d H:i:s', strtotime('-20 days')),
                'edit_date'  => null,
            ],
            [
                'state'      => 0,   // 비활성 (작성 중)
                'faq_type'   => 3,   // 결제
                'title'      => '유료 서비스나 결제 기능이 있나요?',
                'content'    => '<p>현재 부산온나의 모든 서비스는 <strong>완전 무료</strong>로 제공됩니다.</p><p>향후 프리미엄 여행 코스 추천, 로컬 가이드 예약 등 유료 서비스를 검토 중이며, 도입 시 사전 공지 후 적용할 예정입니다.</p>',
                'view_cnt'   => 0,
                'sort_order' => 5,
                'reg_id'     => $regId,
                'reg_date'   => $now,
                'edit_date'  => null,
            ],
        ];

        $db->table('faq')->insertBatch($rows);
        echo "FAQ 더미 데이터 5건이 삽입되었습니다.\n";
    }
}
