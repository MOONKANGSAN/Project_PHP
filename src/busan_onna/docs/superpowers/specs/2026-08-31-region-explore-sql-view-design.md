# 지역별 탐색 기능 SQL View 전환 설계

- **작성일**: 2026-08-31
- **브랜치**: SQL_View
- **작성자**: Moonkangsan

---

## 1. 개요

### 배경

`BackofficeRegionExplore::search()` 메서드는 맛집(`busan_restaurant`), 관광지(`busan_place`), 행사·축제(`busan_event`) 세 테이블을 **각각 별도의 쿼리로 3번** 호출한 뒤 PHP에서 결과를 수동으로 병합하는 구조다. 이 방식은 다음 두 가지 문제를 갖는다.

1. **잠재적 오류 위험**: 개별 쿼리 실행 중 하나라도 실패하면 부분 결과만 반환되거나 예외가 비일관적으로 처리될 수 있다.
2. **코드 복잡성**: 동일한 필터(지역명, 검색어, state) 로직이 세 블록에 반복되어 수정 시 누락 위험이 있다.

### 목표

MySQL View(`v_region_content`)를 도입하여 세 테이블을 DB 수준에서 통합하고, PHP 코드에서는 단일 쿼리로 교체한다.

---

## 2. 범위

| 대상 | 변경 여부 |
|---|---|
| `search()` — 통합 검색 | 변경 (View 단일 쿼리로 교체) |
| `getTop5()` — 백오피스 TOP5 조회 | 변경 (View JOIN 적용) |
| `apiTop5()` — 공개 TOP5 API | 변경 (View JOIN 적용) |
| `apiRegions()` — 지역 목록 API | 변경 없음 (단일 테이블, 이미 단순) |
| `saveTop5()`, `toggleState()` | 변경 없음 (쓰기 작업, View 무관) |
| `RestaurantModel`, `PlaceModel`, `EventModel` | 변경 없음 (백오피스·서비스 페이지에서 계속 사용) |

---

## 3. 아키텍처

### 3-1. View 정의

```sql
CREATE OR REPLACE VIEW v_region_content AS
  SELECT
    idx,
    'restaurant'  AS content_type,
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
    'place'       AS content_type,
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
    'event'       AS content_type,
    name,
    address1,
    address2,
    state,
    like_cnt,
    reg_date
  FROM busan_event;
```

**포함 컬럼 선정 근거**

| 컬럼 | 이유 |
|---|---|
| `idx` | 링크 URL 생성에 필요 (`/restaurants/{idx}` 등) |
| `content_type` | 리터럴로 타입 구분, PHP에서 타입별 분기 제거 |
| `name` | 검색어 LIKE 매칭, 결과 제목 표시 |
| `address1`, `address2` | 지역명 필터링, 주소 표시 |
| `state` | 활성 항목(`state=1`) 필터 |
| `like_cnt` | 정렬 기준 |
| `reg_date` | 정렬 기준 |

테이블별 고유 컬럼(`phone`, `open_time`, `start_date`, `host` 등)은 View에서 제외한다. 이 정보는 상세 뷰 페이지에서 각 원본 테이블을 직접 조회하므로 목록·검색 단계에서는 불필요하다.

### 3-2. content_type별 링크 URL 매핑

| content_type | 링크 URL 형식 | 타입명 표시 |
|---|---|---|
| `restaurant` | `/restaurants/{idx}` | 맛집 |
| `place` | `/spots/{idx}` | 관광지 |
| `event` | `/festivals/{idx}` | 행사/축제 |

---

## 4. 추가·변경 파일 목록

### 신규 추가

| 파일 | 역할 |
|---|---|
| `app/Database/Migrations/2026-08-31-000001_CreateViewRegionContent.php` | View 생성 Migration (`up`: CREATE VIEW, `down`: DROP VIEW) |
| `app/Models/RegionContentModel.php` | `v_region_content` View 전용 읽기 모델 |

### 변경

| 파일 | 변경 내용 |
|---|---|
| `app/Controllers/BackofficeRegionExplore.php` | `search()`, `getTop5()`, `apiTop5()` 내부 로직 교체 |

---

## 5. 상세 설계

### 5-1. RegionContentModel

- `$table = 'v_region_content'`
- View는 읽기 전용이므로 `$allowedFields = []` (쓰기 불가 명시)
- 공개 메서드 1개: `searchByRegion(string $q, string $regionName, string $type): array`
  - `$q`: 검색어 (name LIKE)
  - `$regionName`: 지역명 (address1 LIKE, 빈 문자열이면 전체)
  - `$type`: `'restaurant'` | `'place'` | `'event'` | `''` (빈 값이면 전체)
  - 항상 `state = 1` 필터 적용
  - Model은 최대 **30건**까지 반환 (type 전체 검색 시 세 타입 합산 상한)
  - 컨트롤러에서 `content_type`별로 그룹핑 후 각 타입당 10건으로 재제한하여 기존 동작 유지

### 5-2. BackofficeRegionExplore::search() 변경 요약

**변경 전**: `RestaurantModel`, `PlaceModel`, `EventModel` 개별 인스턴스화 → 3회 쿼리 → PHP 병합

**변경 후**: `RegionContentModel::searchByRegion()` 1회 호출 → 결과 바로 반환

응답 JSON 구조(`success`, `region_name`, `results`, `total`)는 그대로 유지한다. 프론트엔드 JS 변경 없음.

### 5-3. getTop5() / apiTop5() 변경 요약

`busan_maps_top5`의 `content_type`, `content_idx`를 이용해 `v_region_content`를 JOIN하여 콘텐츠의 현재 `name`, `address1` 등을 함께 반환한다.

JOIN 방식: `busan_maps_top5.content_type = v.content_type AND busan_maps_top5.content_idx = v.idx`

---

## 6. 에러 처리 방침

- View 자체의 쿼리 오류는 CodeIgniter4 기본 예외 처리에 위임 (기존 방식과 동일)
- `content_type`이 View에 없는 값인 경우 JOIN 결과가 NULL → 컨트롤러에서 해당 항목 필터링

---

## 7. 마이그레이션 전략

- `up()`: `CREATE OR REPLACE VIEW v_region_content AS ...`
- `down()`: `DROP VIEW IF EXISTS v_region_content`
- 기존 테이블 스키마 변경 없음 — 롤백 시 원본 코드로만 복구하면 됨

---

## 8. 변경하지 않는 것

- 세 원본 테이블(`busan_restaurant`, `busan_place`, `busan_event`) 스키마
- `RestaurantModel`, `PlaceModel`, `EventModel` (서비스 페이지, 백오피스 개별 관리에서 계속 사용)
- 프론트엔드 JS 및 View 파일 (`region_explore/index.php`)
- 응답 JSON 구조
