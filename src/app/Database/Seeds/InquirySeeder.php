<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 고객문의 더미 데이터 시더 (5건)
 * 실행: php spark db:seed InquirySeeder
 */
class InquirySeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        $existing = $db->table('inquiry')->countAllResults();
        if ($existing > 0) {
            echo "고객문의 데이터가 이미 존재합니다. 건너뜁니다.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'state'        => 1,   // 접수(미답변)
                'id'           => 'testuser01',
                'inquiry_type' => 1,   // 계정/로그인
                'title'        => '로그인이 안 됩니다.',
                'content'      => '어제까지 정상 로그인이 됐는데 오늘 갑자기 비밀번호가 틀리다고 나옵니다. 비밀번호를 바꾼 적이 없는데 무슨 문제인지 확인 부탁드립니다.',
                'is_public'    => 1,
                'reg_date'     => date('Y-m-d H:i:s', strtotime('-5 days')),
                'answer'       => null,
                'answer_date'  => null,
            ],
            [
                'state'        => 2,   // 답변완료
                'id'           => 'testuser02',
                'inquiry_type' => 2,   // 서비스 이용
                'title'        => '맛집 즐겨찾기 기능은 언제 생기나요?',
                'content'      => '마음에 드는 맛집을 저장해두고 싶은데 즐겨찾기 기능이 없는 것 같더라고요. 추가 예정이 있는지 궁금합니다.',
                'is_public'    => 1,
                'reg_date'     => date('Y-m-d H:i:s', strtotime('-10 days')),
                'answer'       => "안녕하세요, 부산온나 운영팀입니다.\n\n즐겨찾기(찜하기) 기능은 현재 개발 중이며 2026년 3분기 내 오픈 예정입니다.\n조금만 기다려 주시면 빠르게 만나볼 수 있을 예정입니다. 감사합니다!",
                'answer_date'  => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],
            [
                'state'        => 1,   // 접수(미답변)
                'id'           => 'testuser03',
                'inquiry_type' => 3,   // 오류/버그
                'title'        => '부산 지도 클릭 시 페이지가 멈춥니다.',
                'content'      => '모바일 크롬 브라우저에서 메인 페이지의 부산 SVG 지도를 클릭하면 페이지 반응이 없어집니다. 새로고침해야 다시 작동합니다. 갤럭시 S24 사용 중입니다.',
                'is_public'    => 1,
                'reg_date'     => date('Y-m-d H:i:s', strtotime('-3 days')),
                'answer'       => null,
                'answer_date'  => null,
            ],
            [
                'state'        => 2,   // 답변완료
                'id'           => 'testuser04',
                'inquiry_type' => 4,   // 기타
                'title'        => '부산 여행 코스 추천 요청드립니다.',
                'content'      => '부산 2박 3일 여행을 계획 중인데 해운대, 광안리, 감천문화마을을 모두 넣고 싶습니다. 효율적인 동선을 추천해주실 수 있을까요?',
                'is_public'    => 0,   // 비공개
                'reg_date'     => date('Y-m-d H:i:s', strtotime('-7 days')),
                'answer'       => "안녕하세요! 2박 3일 코스를 아래와 같이 추천드립니다.\n\n【1일차】광안리 해수욕장 → 민락수변공원 (야경) → 광안대교 뷰포인트\n【2일차】감천문화마을 → 송도해수욕장 → 암남공원\n【3일차】해운대 해수욕장 → 동백섬 → APEC 나루공원\n\n이동 시 지하철+도보 조합을 추천드립니다. 즐거운 여행 되세요!",
                'answer_date'  => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'state'        => 1,   // 접수(미답변)
                'id'           => 'testuser05',
                'inquiry_type' => 2,   // 서비스 이용
                'title'        => '행사 정보가 실제와 다릅니다.',
                'content'      => '해운대 모래축제 일정이 사이트에는 6월 말로 나와 있는데 실제로는 이미 종료됐습니다. 정보 업데이트가 늦는 것 같으니 확인 부탁드립니다.',
                'is_public'    => 1,
                'reg_date'     => $now,
                'answer'       => null,
                'answer_date'  => null,
            ],
        ];

        $db->table('inquiry')->insertBatch($rows);
        echo "고객문의 더미 데이터 5건이 삽입되었습니다.\n";
    }
}
