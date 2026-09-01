# 지역별 탐색 SQL View 전환 — 인수인계 문서

> 작성일: 2026-09-01  
> 브랜치: `SQL_View`  
> 작업자: Claude Code (claude-sonnet-4-6)

---

## 1. 작업 개요

기존 지역별 탐색 기능이 `RestaurantModel`, `PlaceModel`, `EventModel`을 개별 호출하는 3-쿼리 방식이었던 것을,
MySQL View(`v_region_content`)를 활용한 단일 쿼리 방식으로 전환한 작업이다.

**전환 동기:**
- 3개 쿼리 중 하나라도 실패하면 결과가 불완전해질 수 있는 리스크 제거
- 검색·조회 로직을 단일 쿼리로 단순화하여 유지보수성 향상

---

## 2. 변경된 파일 목록 (전체 완료)

| 파일 | 상태 | 변경 내용 |
|------|------|-----------|
| `app/Database/Migrations/2026-08-31-000001_CreateViewRegionContent.php` | ✅ 신규 생성 | `v_region_content` View 생성 Migration |
| `app/Models/RegionContentModel.php` | ✅ 신규 생성 | View 전용 읽기 모델 |
| `app/Models/BusanMapsTop5Model.php` | ✅ 메서드 추가 | `getTop5WithContent()` 추가 |
| `app/Controllers/BackofficeRegionExplore.php` | ✅ 수정 완료 | 3-쿼리 → View 단일 쿼리 전환 |

---

## 3. 아키텍처 설계

### View 구조: `v_region_content`

```sql
CREATE OR REPLACE VIEW v_region_content AS
  SELECT idx, 'restaurant' AS content_type, name, address1, address2, state, like_cnt, reg_date
  FROM busan_restaurant
  UNION ALL
  SELECT idx, 'place'      AS content_type, name, address1, address2, state, like_cnt, reg_date
  FROM busan_place
  UNION ALL
  SELECT idx, 'event'      AS content_type, name, address1, address2, state, like_cnt, reg_date
  FROM busan_event;
```

**설계 원칙:**
- 공통 컬럼(name, address1, address2, state, like_cnt, reg_date)만 포함 — 테이블별 고유 필드는 제외
- `content_type` 리터럴 문자열로 출처 테이블 구분
- `idx`는 content_type별로 독립적이므로 View 단독으로는 PK가 없음

---

## 4. 각 파일 상세 설명

### 4-1. Migration: `2026-08-31-000001_CreateViewRegionContent.php`

- `up()`: `CREATE OR REPLACE VIEW v_region_content` 실행
- `down()`: `DROP VIEW IF EXISTS v_region_content` 실행
- **실행 방법:** `php spark migrate` (미적용 시)

---

### 4-2. RegionContentModel: `app/Models/RegionContentModel.php`

```php
class RegionContentModel extends Model
{
    protected $table         = 'v_region_content';
    protected $allowedFields = [];   // View는 쓰기 불가
    protected $useTimestamps = false;

    public function searchByRegion(string $q, string $regionName, string $type): array
```

**주요 설계 결정:**
- `$primaryKey` 미설정 — idx가 content_type별로 독립적이므로 단독 유일성 없음
- `$q === ''` 가드: 빈 검색어로 LIKE 쿼리 실행 시 full table scan 방지
- `type` 지정 시 해당 타입 10건, 전체 조회 시 최대 30건 반환 (컨트롤러에서 타입별 10건 재제한)

---

### 4-3. BusanMapsTop5Model: `getTop5WithContent()` 추가

```php
public function getTop5WithContent(int $mainIdx): array
{
    return \Config\Database::connect()
        ->table('busan_maps_top5 t')
        ->select('t.*, v.name AS content_name, v.address1 AS content_address1')
        ->join('v_region_content v', 'v.content_type = t.content_type AND v.idx = t.content_idx', 'left')
        ->where('t.main_idx', $mainIdx)
        ->where('t.state', 1)
        ->orderBy('t.sort_order', 'ASC')
        ->orderBy('t.idx', 'ASC')
        ->get()
        ->getResultArray();
}
```

**응답 컬럼:** 기존 `busan_maps_top5` 컬럼 전체 + `content_name`, `content_address1`  
**LEFT JOIN 이유:** 원본 콘텐츠가 삭제되어도 TOP5 레코드는 유지 (NULL로 표시)

---

### 4-4. BackofficeRegionExplore.php 변경 요약

| 항목 | 이전 | 이후 |
|------|------|------|
| use 선언 | RestaurantModel, PlaceModel, EventModel | RegionContentModel |
| 프로퍼티 | (없음) | `private RegionContentModel $contentModel` |
| `search()` | 3개 모델 개별 쿼리 후 병합 | `contentModel->searchByRegion()` 단일 쿼리 |
| `formatSearchResult()` | (없음) | 신규 private 헬퍼 추가 |
| `getTop5()` | `getTop5ByRegion()` | `getTop5WithContent()` |
| `apiTop5()` | `getActiveByRegion()` | `getTop5WithContent()` |

---

## 5. API 응답 형식 변경

### `GET /backoffice/region-explore/search`

```json
{
  "success": true,
  "region_name": "해운대구",
  "results": [
    {
      "content_type": "restaurant",
      "content_idx": 42,
      "type_name": "맛집",
      "title": "OO 맛집",
      "link_url": "/restaurants/42",
      "address": "부산광역시 해운대구 OO로 1"
    }
  ],
  "total": 3
}
```

### `GET /backoffice/region-explore/(:num)/top5` 및 `/api/region-explore/(:num)/top5`

```json
{
  "success": true,
  "region": { "idx": 1, "name": "해운대구", ... },
  "items": [
    {
      "idx": 10,
      "main_idx": 1,
      "content_type": "restaurant",
      "content_idx": 42,
      "content_name": "OO 맛집",
      "content_address1": "부산광역시 해운대구 OO로",
      ...
    }
  ]
}
```

---

## 6. Git 커밋 이력 (SQL_View 브랜치)

| SHA | 내용 |
|-----|------|
| `3868cd9` | feat: v_region_content View 생성 Migration 추가 |
| `2252e84` | feat: RegionContentModel 추가 (v_region_content View 전용) |
| `02703ef` | fix: RegionContentModel — 빈 검색어 LIKE 가드 추가, primaryKey 제거 |
| `07476ea` | feat: BusanMapsTop5Model에 getTop5WithContent() 추가 |
| (미커밋) | BackofficeRegionExplore.php 수정 완료 (로컬에만 있음) |

> **주의:** `BackofficeRegionExplore.php` 수정분은 아직 커밋되지 않았음. git add/commit은 사용자가 직접 처리.

---

## 7. 배포 전 체크리스트

- [ ] `php spark migrate` 실행하여 `v_region_content` View 생성 확인
- [ ] 검색 API `GET /backoffice/region-explore/search?q=OO&region_idx=1` 정상 동작 확인
- [ ] TOP5 조회 API `GET /backoffice/region-explore/1/top5` 응답에 `content_name` 포함 확인
- [ ] 공개 API `GET /api/region-explore/1/top5` 정상 동작 확인
- [ ] `BackofficeRegionExplore.php` 수정분 커밋 후 `main` 브랜치로 PR 생성

---

## 8. 미완료 / 향후 고려 사항

- **미완료 없음** — 코드 수정은 모두 완료된 상태
- **테이블별 고유 필드**: 각 테이블(busan_restaurant 등)에만 있는 컬럼(예: 영업시간, 카테고리 등)은 View에 포함하지 않음. 상세 페이지는 기존 개별 모델을 그대로 사용.
- **성능 고려**: View는 인덱스를 직접 생성할 수 없으므로, 원본 테이블의 `name`, `address1`, `state` 컬럼에 인덱스가 있는지 확인 권장.

---

## 9. 운영 중 문제 발생 시 롤백 방법

```sql
-- View 삭제 (롤백)
php spark migrate:rollback --batch 최신배치번호
-- 또는 직접 실행
DROP VIEW IF EXISTS v_region_content;
```

컨트롤러는 git으로 이전 커밋(`3af3f9f` 이전)으로 체크아웃하면 3-쿼리 방식으로 복원됨.
