<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // 부산 관광 하드코딩 데이터 (DB 연결 전 임시)
        $data = [
            'banners' => [
                [
                    'title'    => '부산의 푸른 바다',
                    'subtitle' => '해운대에서 시작하는 완벽한 여름 휴가',
                    'location' => '해운대구',
                    'bg'       => 'banner-haeundae',
                ],
                [
                    'title'    => '알록달록 감천문화마을',
                    'subtitle' => '골목마다 피어난 예술과 이야기',
                    'location' => '사하구',
                    'bg'       => 'banner-gamcheon',
                ],
                [
                    'title'    => '광안대교의 밤',
                    'subtitle' => '빛나는 부산의 야경을 만나다',
                    'location' => '수영구',
                    'bg'       => 'banner-gwangan',
                ],
            ],
            'spots' => [
                ['name' => '해운대해수욕장', 'district' => '해운대구', 'category' => '해변', 'desc' => '부산을 대표하는 명품 해수욕장으로 여름이면 수백만 명이 찾는 핫플레이스', 'emoji' => '🏖️', 'color' => '#74b9ff'],
                ['name' => '광안리해수욕장', 'district' => '수영구',  'category' => '해변', 'desc' => '광안대교를 배경으로 펼쳐지는 낭만적인 해변, 야경 명소', 'emoji' => '🌉', 'color' => '#a29bfe'],
                ['name' => '감천문화마을',   'district' => '사하구',  'category' => '문화', 'desc' => '부산의 마추픽추! 알록달록 벽화와 골목길이 가득한 예술 마을', 'emoji' => '🎨', 'color' => '#fd79a8'],
                ['name' => '자갈치시장',     'district' => '중구',    'category' => '시장', 'desc' => '싱싱한 해산물과 활기찬 상인들이 가득한 부산 최대 수산시장', 'emoji' => '🐟', 'color' => '#fdcb6e'],
                ['name' => '태종대',         'district' => '영도구',  'category' => '자연', 'desc' => '천혜의 기암절벽과 탁 트인 남해 전망을 자랑하는 절경 명소', 'emoji' => '⛰️', 'color' => '#55efc4'],
                ['name' => '용두산공원',     'district' => '중구',    'category' => '공원', 'desc' => '부산타워에서 내려다보는 시내 전경이 아름다운 도심 공원', 'emoji' => '🗼', 'color' => '#81ecec'],
            ],
            'restaurants' => [
                ['name' => '돼지국밥', 'area' => '서면 / 남포동',  'price' => '₩9,000~',  'desc' => '부산의 소울푸드! 구수하고 진한 돼지국밥 한 그릇', 'emoji' => '🍲', 'color' => '#e17055'],
                ['name' => '씨앗호떡', 'area' => '남포동',         'price' => '₩1,500~',  'desc' => '견과류 씨앗이 가득 들어간 부산의 국민 간식', 'emoji' => '🥞', 'color' => '#fdcb6e'],
                ['name' => '밀면',     'area' => '부산 전역',      'price' => '₩8,000~',  'desc' => '쫄깃한 밀면 면발과 시원한 육수의 조화', 'emoji' => '🍜', 'color' => '#74b9ff'],
                ['name' => '어묵(오뎅)', 'area' => '자갈치 / 남포동', 'price' => '₩500~', 'desc' => '부산식 어묵으로 겨울 길거리 필수 간식', 'emoji' => '🍢', 'color' => '#a29bfe'],
                ['name' => '낙곱새',   'area' => '부산 전역',      'price' => '₩15,000~', 'desc' => '낙지+곱창+새우를 함께 즐기는 매콤한 부산식 볶음', 'emoji' => '🦑', 'color' => '#fd79a8'],
                ['name' => '복국',     'area' => '부산 전역',      'price' => '₩12,000~', 'desc' => '시원하고 깔끔한 복어탕, 해장으로도 최고', 'emoji' => '🐡', 'color' => '#55efc4'],
            ],
            'courses' => [
                [
                    'title'    => '해안 드라이브 코스',
                    'theme'    => '🌊 바다',
                    'duration' => '1일 코스',
                    'spots'    => ['해운대해수욕장', '광안리해수욕장', '이기대공원', '태종대'],
                    'desc'     => '부산의 아름다운 해안선을 따라 달리는 드라이브 코스',
                    'color'    => '#2e86de',
                ],
                [
                    'title'    => '역사·문화 탐방 코스',
                    'theme'    => '🏛️ 문화',
                    'duration' => '반일 코스',
                    'spots'    => ['용두산공원', '보수동 책방골목', '감천문화마을', '자갈치시장'],
                    'desc'     => '부산의 역사와 예술이 살아 숨쉬는 문화 탐방 코스',
                    'color'    => '#8854d0',
                ],
                [
                    'title'    => '미식 탐방 코스',
                    'theme'    => '🍽️ 맛집',
                    'duration' => '1일 코스',
                    'spots'    => ['돼지국밥(서면)', '씨앗호떡(남포동)', '어묵(자갈치)', '서면 낙곱새'],
                    'desc'     => '부산의 대표 향토 음식을 한 번에 맛보는 미식 기행',
                    'color'    => '#e67e22',
                ],
            ],
        ];

        // 아이디 저장 쿠키가 있으면 로그인 모달 ID 필드에 미리 채워준다
        $data['saved_id'] = $this->request->getCookie('saved_id') ?? '';

        return view('home/index', $data);
    }
}
