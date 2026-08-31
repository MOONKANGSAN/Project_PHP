# 지역별 탐색 SQL View 전환 구현 계획

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** `v_region_content` View를 도입하여 지역별 탐색 검색·TOP5 조회에서 발생하는 3회 개별 쿼리를 단일 쿼리로 교체한다.

**Architecture:** MySQL View(`v_region_content`)가 `busan_restaurant`, `busan_place`, `busan_event` 세 테이블을 UNION ALL로 통합한다. `RegionContentModel`이 이 View를 읽기 전용으로 감싸고, `BusanMapsTop5Model`에 View JOIN 메서드를 추가한다. `BackofficeRegionExplore` 컨트롤러는 기존 3개 모델 직접 호출을 제거하고 두 신규 메서드로 교체한다.

**Tech Stack:** PHP 8.x, CodeIgniter 4, MySQL 8.x

---

## Task 1: View 생성 Migration 작성

**Files:**
- Create: `app/Database/Migrations/2026-08-31-000001_CreateViewRegionContent.php`

- [ ] **Step 1: Migration 파일 생성**

아래 내용으로 파일을 작성한다.

```php
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
```

- [ ] **Step 2: Migration 실행**

```bash
cd /c/project_php/src/busan_onna
php spark migrate
```

Expected 출력 예시:
```
Migrating Once...
Running: App\Database\Migrations\2026-08-31-000001_CreateViewRegionContent
Done!
```

- [ ] **Step 3: View 생성 확인**

MySQL에서 직접 확인:
```sql
SHOW FULL TABLES IN busan_onna WHERE TABLE_TYPE = 'VIEW';
-- v_region_content 가 VIEW 타입으로 나와야 함

SELECT content_type, COUNT(*) AS cnt
FROM v_region_content
GROUP BY content_type;
-- restaurant / place / event 세 행이 각각 카운트와 함께 출력되어야 함
```

- [ ] **Step 4: 커밋**

```bash
git add app/Database/Migrations/2026-08-31-000001_CreateViewRegionContent.php
git commit -m "feat: v_region_content View 생성 Migration 추가"
```

---

## Task 2: RegionContentModel 작성

**Files:**
- Create: `app/Models/RegionContentModel.php`

- [ ] **Step 1: RegionContentModel 파일 생성**

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * v_region_content View 전용 읽기 모델
 * busan_restaurant · busan_place · busan_event UNION View를 단일 인터페이스로 조회한다.
 */
class RegionContentModel extends Model
{
    protected $table         = 'v_region_content';
    protected $primaryKey    = 'idx';
    protected $allowedFields = [];   // View는 쓰기 불가
    protected $useTimestamps = false;

    /**
     * 지역별 탐색 통합 검색
     *
     * @param string $q          검색어 (name LIKE 매칭)
     * @param string $regionName 지역명 (address1 LIKE 필터, 빈 문자열이면 전체)
     * @param string $type       'restaurant' | 'place' | 'event' | '' (빈 값이면 전체)
     * @return array             검색 결과 행 배열 (content_type별 최대 10건, 전체 최대 30건)
     */
    public function searchByRegion(string $q, string $regionName, string $type): array
    {
        $this->where('state', 1)
             ->like('name', $q, 'both');

        if ($regionName !== '') {
            $this->like('address1', $regionName, 'both');
        }

        if ($type !== '') {
            $this->where('content_type', $type);
            return $this->orderBy('name', 'ASC')->findAll(10);
        }

        // 타입 전체 검색: 최대 30건 반환 (컨트롤러에서 타입별 10건 재제한)
        return $this->orderBy('content_type', 'ASC')
                    ->orderBy('name', 'ASC')
                    ->findAll(30);
    }
}
```

- [ ] **Step 2: 커밋**

```bash
git add app/Models/RegionContentModel.php
git commit -m "feat: RegionContentModel 추가 (v_region_content View 전용)"
```

---

## Task 3: BusanMapsTop5Model에 getTop5WithContent() 추가

**Files:**
- Modify: `app/Models/BusanMapsTop5Model.php`

- [ ] **Step 1: `getTop5WithContent()` 메서드 추가**

`BusanMapsTop5Model.php`의 마지막 메서드(`getActiveByRegion`) 아래, 닫는 중괄호(`}`) 바로 위에 삽입한다.

```php
    /**
     * 특정 지역의 TOP5와 연결 콘텐츠 상세를 JOIN하여 반환
     * busan_maps_top5 LEFT JOIN v_region_content
     * content_name, content_address1 컬럼이 추가된다 (콘텐츠가 삭제된 경우 NULL).
     */
    public function getTop5WithContent(int $mainIdx): array
    {
        return \Config\Database::connect()
            ->table('busan_maps_top5 t')
            ->select('t.*, v.name AS content_name, v.address1 AS content_address1')
            ->join(
                'v_region_content v',
                'v.content_type = t.content_type AND v.idx = t.content_idx',
                'left'
            )
            ->where('t.main_idx', $mainIdx)
            ->where('t.state', 1)
            ->orderBy('t.sort_order', 'ASC')
            ->orderBy('t.idx', 'ASC')
            ->get()
            ->getResultArray();
    }
```

- [ ] **Step 2: 커밋**

```bash
git add app/Models/BusanMapsTop5Model.php
git commit -m "feat: BusanMapsTop5Model에 getTop5WithContent() 추가"
```

---

## Task 4: BackofficeRegionExplore — search() 교체

**Files:**
- Modify: `app/Controllers/BackofficeRegionExplore.php`

- [ ] **Step 1: use 구문 교체**

파일 상단의 `use` 블록을 아래와 같이 교체한다 (`RestaurantModel`, `PlaceModel`, `EventModel` 제거, `RegionContentModel` 추가).

```php
use App\Models\BusanMapsModel;
use App\Models\BusanMapsTop5Model;
use App\Models\RegionContentModel;
```

- [ ] **Step 2: 클래스 프로퍼티 및 initController() 교체**

기존:
```php
private BusanMapsModel    $mapsModel;
private BusanMapsTop5Model $top5Model;

public function initController(...): void
{
    parent::initController($request, $response, $logger);
    $this->mapsModel = new BusanMapsModel();
    $this->top5Model = new BusanMapsTop5Model();
}
```

변경 후:
```php
private BusanMapsModel     $mapsModel;
private BusanMapsTop5Model $top5Model;
private RegionContentModel $contentModel;

public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                               \CodeIgniter\HTTP\ResponseInterface $response,
                               \Psr\Log\LoggerInterface $logger): void
{
    parent::initController($request, $response, $logger);
    $this->mapsModel    = new BusanMapsModel();
    $this->top5Model    = new BusanMapsTop5Model();
    $this->contentModel = new RegionContentModel();
}
```

- [ ] **Step 3: formatSearchResult() private 메서드 추가**

`search()` 메서드 바로 아래(또는 `// 공개 API` 주석 위)에 삽입한다.

```php
    /**
     * v_region_content 행을 검색 결과 응답 형식으로 변환
     */
    private function formatSearchResult(array $row): array
    {
        static $urlMap  = ['restaurant' => '/restaurants/', 'place' => '/spots/', 'event' => '/festivals/'];
        static $nameMap = ['restaurant' => '맛집',         'place' => '관광지',  'event' => '행사/축제'];

        $type = $row['content_type'];

        return [
            'content_type' => $type,
            'content_idx'  => (int) $row['idx'],
            'type_name'    => $nameMap[$type] ?? $type,
            'title'        => $row['name'],
            'link_url'     => ($urlMap[$type] ?? '/') . $row['idx'],
            'address'      => trim(($row['address1'] ?? '') . ' ' . ($row['address2'] ?? '')),
        ];
    }
```

- [ ] **Step 4: search() 메서드 전체 교체**

기존 `search()` 메서드 전체를 아래로 교체한다.

```php
    /**
     * GET /backoffice/region-explore/search
     * 맛집·관광지·행사를 통합 검색 (TOP5 추가용, JSON)
     * Query:
     *   q          = 검색어
     *   type       = restaurant | place | event (생략 시 전체)
     *   region_idx = busan_maps.idx (설정 시 해당 지역구로 address1 필터링)
     */
    public function search(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q         = trim((string) $this->request->getGet('q'));
        $type      = (string) $this->request->getGet('type');
        $regionIdx = (int) $this->request->getGet('region_idx');

        if (strlen($q) < 1) {
            return $this->json(['success' => false, 'message' => '검색어를 입력해주세요.'], 422);
        }

        // 선택된 지역명 조회 (region_idx가 있을 때만)
        $regionName = '';
        if ($regionIdx > 0) {
            $region     = $this->mapsModel->find($regionIdx);
            $regionName = $region['name'] ?? '';
        }

        // v_region_content View 단일 쿼리로 통합 검색
        $rows = $this->contentModel->searchByRegion($q, $regionName, $type);

        // 타입 전체 검색 시 content_type별 10건 제한 적용
        $grouped = [];
        $results = [];
        foreach ($rows as $row) {
            $t = $row['content_type'];
            if (!isset($grouped[$t])) {
                $grouped[$t] = 0;
            }
            if ($grouped[$t] < 10) {
                $grouped[$t]++;
                $results[] = $this->formatSearchResult($row);
            }
        }

        // debug_sql 제거: View 단일 쿼리로 통합되어 개별 SQL 추적이 불필요해짐
        return $this->json([
            'success'     => true,
            'region_name' => $regionName ?: null,
            'results'     => $results,
            'total'       => count($results),
        ]);
    }
```

- [ ] **Step 5: 커밋**

```bash
git add app/Controllers/BackofficeRegionExplore.php
git commit -m "feat: search() — 3개 모델 개별 쿼리를 RegionContentModel 단일 쿼리로 교체"
```

---

## Task 5: BackofficeRegionExplore — getTop5() / apiTop5() 교체

**Files:**
- Modify: `app/Controllers/BackofficeRegionExplore.php`

- [ ] **Step 1: getTop5() 내부 교체**

기존:
```php
$items = $this->top5Model->getTop5ByRegion($regionIdx);
```

변경 후 (해당 한 줄만 교체):
```php
$items = $this->top5Model->getTop5WithContent($regionIdx);
```

- [ ] **Step 2: apiTop5() 내부 교체**

기존:
```php
$items = $this->top5Model->getActiveByRegion($regionIdx);
```

변경 후 (해당 한 줄만 교체):
```php
$items = $this->top5Model->getTop5WithContent($regionIdx);
```

- [ ] **Step 3: 커밋**

```bash
git add app/Controllers/BackofficeRegionExplore.php
git commit -m "feat: getTop5() · apiTop5() — getTop5WithContent() JOIN 쿼리로 교체"
```

---

## Task 6: 통합 검증

- [ ] **Step 1: PHP 문법 검사**

```bash
php -l app/Controllers/BackofficeRegionExplore.php
php -l app/Models/RegionContentModel.php
php -l app/Models/BusanMapsTop5Model.php
```

Expected: 세 파일 모두 `No syntax errors detected`

- [ ] **Step 2: 통합 검색 API 검증**

브라우저에서 백오피스 로그인 후 아래 URL을 직접 호출하여 확인한다.

```
# 전체 타입 검색
/backoffice/region-explore/search?q=해운대&type=&region_idx=9

# 예상 응답 구조
{
  "success": true,
  "region_name": "해운대구",
  "results": [
    {
      "content_type": "restaurant",
      "content_idx": 123,
      "type_name": "맛집",
      "title": "...",
      "link_url": "/restaurants/123",
      "address": "부산 해운대구 ..."
    },
    ...
  ],
  "total": 숫자
}

# 단일 타입 검색
/backoffice/region-explore/search?q=해운대&type=restaurant&region_idx=9
```

확인 항목:
- `success: true` 반환 여부
- `results` 배열에 `content_type`, `content_idx`, `title`, `link_url`, `address` 포함 여부
- `type=restaurant`일 때 `content_type`이 `restaurant`만 반환되는지 확인
- `region_idx` 지정 시 해당 지역 결과만 반환되는지 확인

- [ ] **Step 3: TOP5 조회 API 검증**

```
# 백오피스 TOP5 조회
/backoffice/region-explore/9/top5    (region_idx=9 예시)

# 예상 응답 구조
{
  "success": true,
  "region": { "idx": 9, "name": "해운대구", ... },
  "items": [
    {
      "idx": 1,
      "title": "...",
      "link_url": "...",
      "content_type": "restaurant",
      "content_idx": 123,
      "content_name": "현재 콘텐츠명",     ← 신규 JOIN 컬럼
      "content_address1": "부산 해운대구", ← 신규 JOIN 컬럼
      ...
    }
  ]
}

# 공개 API
/api/region-explore/9/top5
```

확인 항목:
- 기존 필드(`title`, `link_url`, `content_type`, `content_idx`) 정상 반환 여부
- 신규 필드(`content_name`, `content_address1`) 추가 반환 여부
- 프론트엔드 백오피스 페이지에서 TOP5 목록이 정상 렌더링되는지 확인

- [ ] **Step 4: 백오피스 페이지 전체 흐름 확인**

브라우저에서 `/backoffice/region-explore` 접속 후:
1. 지역 클릭 → TOP5 목록 로딩 확인
2. 검색창에 키워드 입력 → 결과 반환 확인
3. 검색 결과에서 항목 추가 → TOP5 저장 확인

- [ ] **Step 5: 최종 커밋**

```bash
git status
# 변경 파일이 없으면(이미 개별 커밋 완료) 아래 스킵
git add -A
git commit -m "chore: 지역별 탐색 SQL View 전환 작업 완료"
```
