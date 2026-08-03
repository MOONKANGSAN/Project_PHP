# 부산온나 (BUSAN ONNA)
> 부산 여행의 시작과 끝 — 부산 특화 여행 정보 플랫폼
부산을 방문하는 국내외 여행객을 위해 관광지·맛집·축제·여행코스 정보를 한 곳에서 제공하는 웹 서비스입니다.

---

## 주요 기능

### 서비스 (프론트)
| 기능 | URL | 설명 |
|------|-----|------|
| 메인 페이지 | `/` | 배너 슬라이더, 지역별 인터랙티브 지도, 추천 콘텐츠 |
| 관광지 | `/spots` | 구·군·카테고리 필터링, 목록/상세, 추천/비추천 반응 |
| 맛집 | `/restaurants` | 지역·음식 종류 필터링, 목록/상세, 해시태그 검색 |
| 축제·행사 | `/festivals` | 진행 중/예정/종료 상태 표시, 목록/상세 |
| 여행코스 | `/travel-courses` | 테마별 큐레이션 코스, 경유지 순서 표시 |
| 지역별 핫플레이스 | `/hotplace` | 구·군 클릭 기반 핫플레이스 탐색 |
| 고객센터 | `/customer` | 공지사항, FAQ, 1:1 문의 |
| 회원 인증 | `/auth/login` 외 | 이메일 기반 회원가입/로그인/로그아웃 |

### 백오피스 (관리자)
| 기능 | URL |
|------|-----|
| 대시보드 | `backoffice/dashboard` |
| 회원 관리 | `backoffice/members` |
| 탈퇴회원 관리 | `backoffice/withdrawn-members` |
| 관광지 관리 | `backoffice/spots` |
| 맛집 관리 | `backoffice/restaurants` |
| 축제·행사 관리 | `backoffice/festivals` |
| 여행코스 관리 | `backoffice/travel-courses` |
| 지역별 탐색 관리 | `backoffice/region-explore` |
| 배너 관리 | `backoffice/banners` |
| 공지사항 관리 | `backoffice/notices` |
| FAQ 관리 | `backoffice/faqs` |
| 고객문의 관리 | `backoffice/inquiries` |
| 에러 로그 관리 | `backoffice/error-logs` |
| 휴지통 | `backoffice/trash` |
| 관리자 계정 추가 | `backoffice/site/admins/add` |

---

## 기술 스택

| 구분 | 기술 |
|------|------|
| Backend | PHP 8.2+, CodeIgniter 4 |
| Frontend | HTML5, CSS3, Vanilla JS |
| Database | MySQL 8.0 |
| 지도 | SVG 인터랙티브 맵 (자체 구현) + Naver Maps API |
| 인증 | 세션 기반 인증 (bcrypt 해싱) |
| 배포환경 | 리눅스(Ubuntu)
| 서버 | nginx |

---

## 디렉토리 구조

```
busan_onna/
├── app/
│   ├── Config/         # 라우팅, DB, 필터 등 설정
│   ├── Controllers/    # 서비스·백오피스 컨트롤러
│   │   ├── Service.php           # 서비스 프론트 통합 컨트롤러
│   │   ├── Auth.php              # 회원 인증
│   │   ├── Customer.php          # 고객센터
│   │   ├── Reaction.php          # 추천/비추천 API
│   │   ├── GeoController.php     # 네이버 Geocoding 프록시
│   │   ├── Backoffice.php        # 백오피스 공통/대시보드
│   │   └── Backoffice*.php       # 백오피스 각 기능별 컨트롤러
│   ├── Models/         # 데이터 모델
│   ├── Views/
│   │   ├── service/    # 서비스 뷰 (관광지·맛집·축제·코스·고객센터)
│   │   ├── backoffice/ # 백오피스 뷰
│   │   └── modules/    # 공통 모듈 (로그인·회원가입 모달)
│   └── Database/
│       └── Migrations/ # DB 마이그레이션 파일
├── public/             # 웹 루트 (index.php, assets)
├── writable/           # 세션, 캐시, 업로드 파일
├── .env.example        # 환경변수 예시
└── composer.json
```

---

## 설치 방법

### 1. 요구 사항

- PHP 8.2 이상
- Composer
- MySQL 8.0 이상
- Apache 또는 Nginx

PHP 필수 확장:
- `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`

### 2. 저장소 클론 및 의존성 설치

```bash
git clone https://github.com/your-username/busan-onna.git
cd busan-onna/busan_onna
composer install
```

### 3. 환경변수 설정

```bash
cp .env.example .env
```

`.env` 파일을 열어 아래 항목을 환경에 맞게 수정합니다.
```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/'

database.default.hostname = localhost
database.default.database = busan_onna
database.default.username = DB_사용자명
database.default.password = DB_비밀번호

NAVER_MAP_CLIENT_ID     = 네이버클라우드_클라이언트_ID
NAVER_MAP_CLIENT_SECRET = 네이버클라우드_클라이언트_시크릿
```

> Naver Maps API 키는 [네이버 클라우드 플랫폼](https://console.ncloud.com)에서 발급받습니다.

### 4. 데이터베이스 생성 및 마이그레이션

```bash
# MySQL에서 데이터베이스 생성
CREATE DATABASE busan_onna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 마이그레이션 실행 (CI4 CLI)
php spark migrate
```

### 5. 웹 서버 설정
웹 서버의 Document Root를 `busan_onna/public/` 폴더로 지정합니다.

**Nginx**

```nginx
server {
    listen 80;
    server_name https://busan-onna.duckdns.org;
    root /path/to/busan_onna/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:8080;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## DB 주요 테이블

| 테이블 | 설명 |
|--------|------|
| `user_info` | 일반 회원 정보 |
| `backoffice_user` | 관리자 계정 |
| `busan_restaurant` | 맛집 |
| `busan_place` | 관광지 |
| `busan_event` | 축제·행사 |
| `hashtag` | 해시태그 |
| `hashtag_number` | 콘텐츠별 해시태그 매핑 |

> 전체 스키마는 `app/Database/Migrations/` 파일을 참고하세요.

---

## 개발 환경 실행 (로컬)

```bash
# CI4 내장 개발 서버 실행 (포트 8080)
php spark serve

# 이후 브라우저에서 접속
http://localhost:8080
```

---

## 기여 방법

1. 이 저장소를 Fork합니다.
2. 기능 브랜치를 생성합니다. (`git checkout -b feature/기능명`)
3. 변경 사항을 커밋합니다. (`git commit -m '기능 설명'`)
4. 브랜치에 Push합니다. (`git push origin feature/기능명`)
5. Pull Request를 생성합니다.

---

## 라이선스

이 프로젝트는 개인/학습 목적으로 개발되었습니다.
