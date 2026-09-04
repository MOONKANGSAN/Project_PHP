# 부산 온나 — 환불 요청 프로세스 설계

**작성일**: 2026-09-04  
**브랜치**: cancel_process  
**작성자**: Claude (brainstorming)

---

## 1. 개요

고객이 주문 상세 페이지에서 직접 환불을 요청하고, 백오피스 관리자가 확인 후 승인/반려하는 수동 처리 방식의 환불 프로세스를 구현한다.

---

## 2. 핵심 결정 사항

| 항목 | 결정 |
|------|------|
| 환불 처리 방식 | 요청 접수 → 백오피스 수동 처리 (PortOne API 즉시 환불 X) |
| 환불 버튼 표시 상태 | paid / preparing / delivered |
| shipped 상태 처리 | 버튼 대신 "배송 완료 후 환불 요청이 가능합니다" 안내 텍스트 |
| 부분 환불 | 체크박스로 일부 상품만 선택 가능 |
| 이미지 첨부 | 고객이 최대 3장 업로드, 백오피스 모달에서 확인 |
| 배송완료 7일 초과 경고 | 모달 안에 경고 박스 표시 (요청 자체는 가능) |
| 백오피스 UI | 목록에 "상세" 버튼만 → 모달에서 전체 정보 확인 후 승인/반려 |

---

## 3. DB 스키마 변경

### 3-1. Migration 1 — orders 테이블 컬럼 추가

```sql
ALTER TABLE orders ADD COLUMN delivered_at TIMESTAMP NULL DEFAULT NULL;
```

- 백오피스에서 status를 `delivered`로 변경할 때 자동 기록
- 7일 경과 여부 계산 기준

### 3-2. Migration 2 — refund_requests 테이블 생성

```
refund_requests
├── idx            INT AI PK
├── order_idx      INT FK → orders.idx
├── user_idx       INT FK → user_info.idx
├── reason         VARCHAR(50)       사유 코드
├── reason_text    TEXT NULL         직접 입력 사유
├── status         VARCHAR(20)       pending / approved / rejected
├── admin_memo     TEXT NULL         관리자 처리 메모
├── processed_at   TIMESTAMP NULL    승인/반려 처리 시각
└── created_at     TIMESTAMP         요청 접수 시각
```

### 3-3. Migration 3 — refund_request_items 테이블 생성

```
refund_request_items
├── idx                   INT AI PK
├── refund_request_idx    INT FK → refund_requests.idx
└── order_item_idx        INT FK → order_items.idx
```

### 3-4. Migration 4 — refund_request_images 테이블 생성

```
refund_request_images
├── idx                   INT AI PK
├── refund_request_idx    INT FK → refund_requests.idx
├── file_path             VARCHAR(300)   서버 저장 경로
└── created_at            TIMESTAMP
```

- 최대 3장 제한 (서버 유효성 검사)
- 업로드 경로: `/uploads/refunds/`

---

## 4. 환불 사유 목록

| 코드 | 표시 텍스트 |
|------|------------|
| change_of_mind | 단순 변심 |
| defective | 상품 불량 / 파손 |
| wrong_item | 상품 오배송 (다른 상품 도착) |
| delay | 배송 지연 |
| not_as_described | 상품 설명과 다름 |
| duplicate | 중복 주문 |
| direct | 직접 입력 ✏️ |

`direct` 선택 시 `reason_text` 입력란 활성화 (최대 200자)

---

## 5. 라우트 추가

```php
// 프론트 — 환불 요청 접수 (AJAX POST)
$routes->post('/mypage/orders/(:num)/refund', 'Order::requestRefund/$1');

// 백오피스 — 환불 요청 관리
$routes->get('refunds',                     'BackofficeOrders::refundList');
$routes->post('refunds/(:num)/approve',     'BackofficeOrders::approveRefund/$1');
$routes->post('refunds/(:num)/reject',      'BackofficeOrders::rejectRefund/$1');
$routes->get('refunds/(:num)/images/(:num)','BackofficeOrders::refundImage/$1/$2');
```

---

## 6. 백엔드 구조

### 6-1. Order 컨트롤러 — 신규 메서드

**`requestRefund(int $orderIdx)`**
- 로그인 체크 (userIdxForAjax)
- 본인 주문 검증: `orders.user_idx === session user_idx`
- 상태 검증: paid / preparing / delivered 만 허용, 그 외 JSON 400 반환
- 이미지 업로드 처리 (최대 3장, jpg/png/gif, 10MB 이하)
- `refund_requests` INSERT → `refund_request_items` 다건 INSERT → `refund_request_images` INSERT
- JSON 응답: `{ success: true }`

### 6-2. BackofficeOrders 컨트롤러 — 신규/수정 메서드

| 메서드 | 설명 |
|--------|------|
| `refundList()` | 환불 요청 목록 (status 필터, 페이지네이션, user JOIN) |
| `approveRefund(int $idx)` | status = approved, processed_at 기록, admin_memo 저장 |
| `rejectRefund(int $idx)` | status = rejected, processed_at 기록, admin_memo 저장 (필수) |
| `refundImage(int $refundIdx, int $imageIdx)` | 이미지 파일 서빙 (백오피스 권한 체크 후) |
| `updatePaymentStatus()` *(수정)* | status → delivered 변경 시 `orders.delivered_at = NOW()` 기록 |

### 6-3. 신규 모델

| 파일 | 역할 |
|------|------|
| `RefundRequestModel.php` | refund_requests CRUD, 목록 조회 (order/user JOIN) |
| `RefundRequestItemModel.php` | refund_request_items 다건 INSERT / 요청별 조회 |
| `RefundRequestImageModel.php` | refund_request_images INSERT / 요청별 조회 |

**`OrderModel.php` 수정**: `allowedFields`에 `delivered_at` 추가

---

## 7. 프론트 UI — order_detail.php 수정

### 7-1. 하단 버튼 영역 조건부 렌더링

```
status === 'paid' | 'preparing' | 'delivered'
  → 우측에 주황색 테두리 "환불 요청" 버튼 표시

status === 'shipped'
  → 버튼 없이 안내 텍스트: "배송 완료 후 환불 요청이 가능합니다"

status === 'pending' | 'cancelled'
  → 아무것도 표시하지 않음
```

### 7-2. 환불 요청 모달 구성

1. **환불할 상품 선택** — 체크박스 (1개 이상 필수)
2. **7일 경과 경고 박스** — `delivered_at`이 있고 7일 초과 시 조건부 표시
   > "배송완료일로부터 7일 이상 경과한 상품이 포함되어 있습니다. 상품 확인 과정에서 패널티가 발생할 수 있습니다."
3. **환불 사유 선택** — `<select>` 드롭다운
4. **직접 입력란** — `direct` 선택 시만 표시 (최대 200자)
5. **이미지 첨부** — 최대 3장, jpg/png/gif
6. **제출 버튼** — AJAX POST `/mypage/orders/{idx}/refund`

---

## 8. 백오피스 UI

### 8-1. 환불 요청 목록 (`backoffice/refunds/list.php`)

- 상태 필터 탭: 전체 / 대기중 / 승인 / 반려
- 컬럼: 접수일시 / 주문번호 / 신청자 / 환불상품 / 사유 / 상태 / **상세 버튼**
- 승인/반려 버튼은 목록에 없음 — 상세 모달에서만 처리

### 8-2. 환불 요청 상세 모달 (JS 모달, 페이지 중앙)

구성 섹션:
1. **주문 정보** — 주문번호 / 신청자 / 접수일시 / 주문 상태
2. **환불 요청 상품 목록** — 상품명 / 옵션 / 금액
3. **환불 사유** — 선택 코드 + 직접 입력 내용
4. **고객 첨부 이미지** — 썸네일 갤러리, 클릭 시 원본 크기 확인
5. **관리자 메모 입력란** — 반려 시 사유 입력 (반려 시 필수, 승인 시 선택)
6. **하단 버튼** — 닫기 / 반려 / 승인

---

## 9. 구현 파일 목록 요약

| 파일 | 작업 |
|------|------|
| `Database/Migrations/2026-09-04-000001_AddDeliveredAtToOrders.php` | 신규 |
| `Database/Migrations/2026-09-04-000002_CreateRefundRequests.php` | 신규 |
| `Database/Migrations/2026-09-04-000003_CreateRefundRequestItems.php` | 신규 |
| `Database/Migrations/2026-09-04-000004_CreateRefundRequestImages.php` | 신규 |
| `Models/RefundRequestModel.php` | 신규 |
| `Models/RefundRequestItemModel.php` | 신규 |
| `Models/RefundRequestImageModel.php` | 신규 |
| `Models/OrderModel.php` | 수정 (allowedFields + delivered_at) |
| `Controllers/Order.php` | 수정 (requestRefund 추가) |
| `Controllers/BackofficeOrders.php` | 수정 (4개 메서드 추가 + updatePaymentStatus 수정) |
| `Views/service/mypage/order_detail.php` | 수정 (버튼 + 모달) |
| `Views/backoffice/refunds/list.php` | 신규 |
| `Config/Routes.php` | 수정 (5개 라우트 추가) |
