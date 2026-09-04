# 환불 요청 프로세스 구현 계획

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 고객이 주문 상세 페이지에서 환불을 요청하고, 백오피스 관리자가 확인 후 승인/반려하는 수동 처리 방식의 환불 프로세스를 구현한다.

**Architecture:** 별도 `refund_requests` / `refund_request_items` / `refund_request_images` 테이블로 환불 요청을 관리한다. 프론트는 AJAX로 요청을 접수하고, 백오피스는 목록 페이지의 "상세" 버튼 클릭 시 JS 모달로 상세 정보를 표시하고 승인/반려 처리한다.

**Tech Stack:** CodeIgniter 4, PHP 8.1+, MySQL, Vanilla JS (AJAX), 기존 프로젝트 CSS 패턴

---

## 파일 맵

| 파일 | 작업 |
|------|------|
| `busan_onna/app/Database/Migrations/2026-09-04-000001_AddDeliveredAtToOrders.php` | 신규 |
| `busan_onna/app/Database/Migrations/2026-09-04-000002_CreateRefundRequests.php` | 신규 |
| `busan_onna/app/Database/Migrations/2026-09-04-000003_CreateRefundRequestItems.php` | 신규 |
| `busan_onna/app/Database/Migrations/2026-09-04-000004_CreateRefundRequestImages.php` | 신규 |
| `busan_onna/app/Models/RefundRequestModel.php` | 신규 |
| `busan_onna/app/Models/RefundRequestItemModel.php` | 신규 |
| `busan_onna/app/Models/RefundRequestImageModel.php` | 신규 |
| `busan_onna/app/Models/OrderModel.php` | 수정 — allowedFields에 `delivered_at` 추가 |
| `busan_onna/app/Config/Routes.php` | 수정 — 라우트 5개 추가 |
| `busan_onna/app/Controllers/Order.php` | 수정 — `requestRefund()` 추가 |
| `busan_onna/app/Controllers/BackofficeOrders.php` | 수정 — 메서드 5개 추가/수정 |
| `busan_onna/app/Views/service/mypage/order_detail.php` | 수정 — 버튼 + 모달 추가 |
| `busan_onna/app/Views/backoffice/refunds/list.php` | 신규 |

---

## Task 1: DB Migrations

**Files:**
- Create: `busan_onna/app/Database/Migrations/2026-09-04-000001_AddDeliveredAtToOrders.php`
- Create: `busan_onna/app/Database/Migrations/2026-09-04-000002_CreateRefundRequests.php`
- Create: `busan_onna/app/Database/Migrations/2026-09-04-000003_CreateRefundRequestItems.php`
- Create: `busan_onna/app/Database/Migrations/2026-09-04-000004_CreateRefundRequestImages.php`

- [ ] **Step 1: Migration 1 생성 — orders에 delivered_at 추가**

```php
<?php
// 배송완료 시각 기록 — 환불 요청 시 7일 경과 여부 계산에 사용
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddDeliveredAtToOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('orders', [
            'delivered_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'after'   => 'paid_at',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('orders', 'delivered_at');
    }
}
```

- [ ] **Step 2: Migration 2 생성 — refund_requests 테이블**

```php
<?php
// 환불 요청 헤더 — 요청 단위로 사유·상태·관리자 메모를 관리
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateRefundRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_idx'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_idx'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reason'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'reason_text'  => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'admin_memo'   => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'processed_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'created_at'   => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('order_idx');
        $this->forge->addKey('user_idx');
        $this->forge->addKey('status');
        $this->forge->createTable('refund_requests');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_requests');
    }
}
```

- [ ] **Step 3: Migration 3 생성 — refund_request_items 테이블**

```php
<?php
// 환불 요청 대상 상품 — 체크박스로 선택한 order_items를 연결
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateRefundRequestItems extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'refund_request_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'order_item_idx'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('refund_request_idx');
        $this->forge->createTable('refund_request_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_request_items');
    }
}
```

- [ ] **Step 4: Migration 4 생성 — refund_request_images 테이블**

```php
<?php
// 환불 요청 첨부 이미지 — public/uploads/refunds/ 에 저장된 파일 경로
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateRefundRequestImages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'refund_request_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_path'          => ['type' => 'VARCHAR', 'constraint' => 300],
            'created_at'         => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('refund_request_idx');
        $this->forge->createTable('refund_request_images');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_request_images');
    }
}
```

- [ ] **Step 5: 마이그레이션 실행**

```bash
php spark migrate
```

예상 출력: `All migrations are up to date.` 포함 4개 마이그레이션 실행 완료 메시지

---

## Task 2: Models

**Files:**
- Create: `busan_onna/app/Models/RefundRequestModel.php`
- Create: `busan_onna/app/Models/RefundRequestItemModel.php`
- Create: `busan_onna/app/Models/RefundRequestImageModel.php`
- Modify: `busan_onna/app/Models/OrderModel.php`

- [ ] **Step 1: RefundRequestModel 생성**

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 환불 요청 헤더 모델 — CRUD, 목록(order/user JOIN), 승인/반려 처리
 */
class RefundRequestModel extends Model
{
    protected $table      = 'refund_requests';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_idx', 'user_idx', 'reason', 'reason_text',
        'status', 'admin_memo', 'processed_at',
    ];

    public const STATUS_LABELS = [
        'pending'  => '대기중',
        'approved' => '승인',
        'rejected' => '반려',
    ];

    public const REASON_LABELS = [
        'change_of_mind'   => '단순 변심',
        'defective'        => '상품 불량 / 파손',
        'wrong_item'       => '상품 오배송 (다른 상품 도착)',
        'delay'            => '배송 지연',
        'not_as_described' => '상품 설명과 다름',
        'duplicate'        => '중복 주문',
        'direct'           => '직접 입력',
    ];

    /**
     * 백오피스 목록 — orders·user_info JOIN, status 필터, 페이지네이션
     */
    public function getAdminList(string $status = ''): array
    {
        $this->select('refund_requests.*, orders.order_no, orders.status AS order_status, ui.name AS user_name, ui.id AS user_id')
             ->join('orders', 'orders.idx = refund_requests.order_idx', 'left')
             ->join('user_info ui', 'ui.idx = refund_requests.user_idx', 'left');
        if ($status !== '') {
            $this->where('refund_requests.status', $status);
        }
        return $this->orderBy('refund_requests.idx', 'DESC')->paginate(20) ?? [];
    }

    /**
     * 단건 상세 — orders·user_info JOIN
     */
    public function getDetail(int $idx): ?array
    {
        return $this->select('refund_requests.*, orders.order_no, orders.status AS order_status, ui.name AS user_name, ui.id AS user_id')
                    ->join('orders', 'orders.idx = refund_requests.order_idx', 'left')
                    ->join('user_info ui', 'ui.idx = refund_requests.user_idx', 'left')
                    ->where('refund_requests.idx', $idx)
                    ->first();
    }

    /**
     * 승인 처리 — status=approved, processed_at 기록
     */
    public function approve(int $idx, string $adminMemo = ''): bool
    {
        return $this->update($idx, [
            'status'       => 'approved',
            'admin_memo'   => $adminMemo !== '' ? $adminMemo : null,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 반려 처리 — status=rejected, admin_memo 필수
     */
    public function reject(int $idx, string $adminMemo): bool
    {
        return $this->update($idx, [
            'status'       => 'rejected',
            'admin_memo'   => $adminMemo,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
```

- [ ] **Step 2: RefundRequestItemModel 생성**

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 환불 요청 대상 상품 모델 — 다건 INSERT, 요청별 조회(order_items JOIN)
 */
class RefundRequestItemModel extends Model
{
    protected $table      = 'refund_request_items';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['refund_request_idx', 'order_item_idx'];

    /**
     * 체크박스로 선택된 상품 idx 배열을 일괄 INSERT
     */
    public function insertItems(int $refundRequestIdx, array $orderItemIdxList): void
    {
        $rows = array_map(
            fn($itemIdx) => [
                'refund_request_idx' => $refundRequestIdx,
                'order_item_idx'     => (int) $itemIdx,
            ],
            $orderItemIdxList
        );
        $this->insertBatch($rows);
    }

    /**
     * 환불 요청별 대상 상품 목록 — order_items JOIN으로 상품명·옵션·금액 포함
     */
    public function getByRefundRequest(int $refundRequestIdx): array
    {
        return $this->select('refund_request_items.order_item_idx, oi.goods_name, oi.option_label, oi.quantity, oi.unit_price')
                    ->join('order_items oi', 'oi.idx = refund_request_items.order_item_idx', 'left')
                    ->where('refund_request_idx', $refundRequestIdx)
                    ->findAll();
    }
}
```

- [ ] **Step 3: RefundRequestImageModel 생성**

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 환불 요청 첨부 이미지 모델 — file_path는 '/uploads/refunds/{filename}' 형태
 */
class RefundRequestImageModel extends Model
{
    protected $table      = 'refund_request_images';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['refund_request_idx', 'file_path'];

    /**
     * 환불 요청별 이미지 목록
     */
    public function getByRefundRequest(int $refundRequestIdx): array
    {
        return $this->where('refund_request_idx', $refundRequestIdx)->findAll();
    }
}
```

- [ ] **Step 4: OrderModel.php — allowedFields에 delivered_at 추가**

`busan_onna/app/Models/OrderModel.php` 의 `$allowedFields` 배열에 `'delivered_at'` 추가:

```php
protected $allowedFields = [
    'status', 'order_no', 'user_idx', 'total_price',
    'delivery_type', 'recipient_name', 'recipient_phone',
    'delivery_address', 'delivery_address2', 'pickup_location_idx',
    'payment_key', 'payment_method', 'pay_kind', 'paid_at',
    'delivered_at',   // 추가
];
```

---

## Task 3: Routes 추가

**Files:**
- Modify: `busan_onna/app/Config/Routes.php`

- [ ] **Step 1: 프론트 환불 요청 라우트 추가**

`/mypage/orders/(:num)` 라우트 아래에 추가:

```php
$routes->post('/mypage/orders/(:num)/refund', 'Order::requestRefund/$1');
```

- [ ] **Step 2: 백오피스 환불 요청 관리 라우트 추가**

백오피스 group 블록 안의 payments 라우트 아래에 추가:

```php
$routes->get('refunds',                         'BackofficeOrders::refundList');
$routes->get('refunds/(:num)/detail',           'BackofficeOrders::refundDetail/$1');
$routes->post('refunds/(:num)/approve',         'BackofficeOrders::approveRefund/$1');
$routes->post('refunds/(:num)/reject',          'BackofficeOrders::rejectRefund/$1');
```

---

## Task 4: Order::requestRefund 구현

**Files:**
- Modify: `busan_onna/app/Controllers/Order.php`

- [ ] **Step 1: use 구문 추가 (파일 상단 namespace 아래)**

```php
use App\Models\RefundRequestModel;
use App\Models\RefundRequestItemModel;
use App\Models\RefundRequestImageModel;
```

- [ ] **Step 2: requestRefund 메서드 추가**

`Order` 클래스 마지막 메서드 뒤에 추가:

```php
/**
 * POST /mypage/orders/{idx}/refund — 환불 요청 접수 (AJAX)
 * 허용 상태: paid / preparing / delivered
 * 이미지: 최대 3장, jpg/png/gif, 파일당 10MB 이하
 */
public function requestRefund(int $orderIdx): void
{
    $userIdx = $this->userIdxForAjax();
    if ($userIdx === false) return;

    $order = (new OrderModel())->getDetail($orderIdx, $userIdx);
    if (!$order) {
        echo json_encode(['success' => false, 'message' => '주문을 찾을 수 없습니다.']);
        return;
    }

    if (!in_array($order['status'], ['paid', 'preparing', 'delivered'], true)) {
        echo json_encode(['success' => false, 'message' => '환불 요청이 불가능한 주문 상태입니다.']);
        return;
    }

    // 선택 상품 검증
    $itemIdxList = $this->request->getPost('item_idxs') ?? [];
    if (empty($itemIdxList) || !is_array($itemIdxList)) {
        echo json_encode(['success' => false, 'message' => '환불할 상품을 1개 이상 선택해주세요.']);
        return;
    }

    $validIdxs = array_column((new OrderItemModel())->getByOrder($orderIdx), 'idx');
    foreach ($itemIdxList as $itemIdx) {
        if (!in_array((int) $itemIdx, $validIdxs, true)) {
            echo json_encode(['success' => false, 'message' => '잘못된 상품 정보입니다.']);
            return;
        }
    }

    // 환불 사유 검증
    $reason = trim($this->request->getPost('reason') ?? '');
    $validReasons = ['change_of_mind', 'defective', 'wrong_item', 'delay', 'not_as_described', 'duplicate', 'direct'];
    if (!in_array($reason, $validReasons, true)) {
        echo json_encode(['success' => false, 'message' => '환불 사유를 선택해주세요.']);
        return;
    }

    $reasonText = null;
    if ($reason === 'direct') {
        $reasonText = trim($this->request->getPost('reason_text') ?? '');
        if ($reasonText === '') {
            echo json_encode(['success' => false, 'message' => '직접 입력 사유를 작성해주세요.']);
            return;
        }
        if (mb_strlen($reasonText) > 200) {
            echo json_encode(['success' => false, 'message' => '사유는 200자 이하로 입력해주세요.']);
            return;
        }
    }

    // 이미지 업로드 처리
    $uploadedPaths = [];
    $images = $this->request->getFiles()['images'] ?? [];
    if (!empty($images) && is_array($images)) {
        $validImages = array_filter($images, fn($f) => $f->isValid() && !$f->hasMoved());
        if (count($validImages) > 3) {
            echo json_encode(['success' => false, 'message' => '이미지는 최대 3장까지 첨부 가능합니다.']);
            return;
        }
        $uploadDir = FCPATH . 'uploads/refunds/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        foreach ($validImages as $img) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($img->getMimeType(), $allowedMimes, true)) {
                echo json_encode(['success' => false, 'message' => 'jpg, png, gif 형식만 첨부 가능합니다.']);
                return;
            }
            if ($img->getSizeByUnit('mb') > 10) {
                echo json_encode(['success' => false, 'message' => '이미지 1장당 최대 10MB까지 업로드 가능합니다.']);
                return;
            }
            $newName = $img->getRandomName();
            $img->move($uploadDir, $newName);
            $uploadedPaths[] = '/uploads/refunds/' . $newName;
        }
    }

    // 트랜잭션으로 DB 저장
    $db = \Config\Database::connect();
    $db->transStart();

    $refundModel = new RefundRequestModel();
    $refundModel->insert([
        'order_idx'   => $orderIdx,
        'user_idx'    => $userIdx,
        'reason'      => $reason,
        'reason_text' => $reasonText,
        'status'      => 'pending',
    ]);
    $refundIdx = (int) $refundModel->getInsertID();

    (new RefundRequestItemModel())->insertItems($refundIdx, $itemIdxList);

    if (!empty($uploadedPaths)) {
        $imageModel = new RefundRequestImageModel();
        foreach ($uploadedPaths as $path) {
            $imageModel->insert(['refund_request_idx' => $refundIdx, 'file_path' => $path]);
        }
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        echo json_encode(['success' => false, 'message' => '환불 요청 중 오류가 발생했습니다.']);
        return;
    }

    echo json_encode(['success' => true, 'message' => '환불 요청이 접수되었습니다.']);
}
```

---

## Task 5: BackofficeOrders 수정

**Files:**
- Modify: `busan_onna/app/Controllers/BackofficeOrders.php`

- [ ] **Step 1: use 구문 추가**

파일 상단 `use App\Models\OrderModel;` 아래에 추가:

```php
use App\Models\RefundRequestModel;
use App\Models\RefundRequestItemModel;
use App\Models\RefundRequestImageModel;
```

- [ ] **Step 2: updatePaymentStatus() — delivered 변경 시 delivered_at 기록**

기존 `updatePaymentStatus()` 메서드 안의 `$this->model->update($idx, ['status' => $next]);` 부분을 다음으로 교체:

```php
$updateData = ['status' => $next];
if ($next === 'delivered') {
    $updateData['delivered_at'] = date('Y-m-d H:i:s');
}
$this->model->update($idx, $updateData);
```

- [ ] **Step 3: refundList() 추가**

```php
/**
 * GET /backoffice/refunds — 환불 요청 목록 (status 필터, 페이지네이션)
 */
public function refundList(): string
{
    $status      = trim($this->request->getGet('status') ?? '');
    $refundModel = new RefundRequestModel();
    $refunds     = $refundModel->getAdminList($status);

    return view('backoffice/refunds/list', $this->base('환불 요청 관리', [
        'refunds'      => $refunds,
        'pager'        => $refundModel->pager,
        'status'       => $status,
        'statusLabels' => RefundRequestModel::STATUS_LABELS,
    ]));
}
```

- [ ] **Step 4: refundDetail() 추가 — 모달용 JSON API**

```php
/**
 * GET /backoffice/refunds/{idx}/detail — 모달용 환불 요청 상세 JSON
 */
public function refundDetail(int $idx): void
{
    $refund = (new RefundRequestModel())->getDetail($idx);
    if (!$refund) {
        echo json_encode(['success' => false, 'message' => '환불 요청을 찾을 수 없습니다.']);
        return;
    }

    $items  = (new RefundRequestItemModel())->getByRefundRequest($idx);
    $images = (new RefundRequestImageModel())->getByRefundRequest($idx);

    echo json_encode([
        'success' => true,
        'refund'  => $refund,
        'items'   => $items,
        'images'  => $images,
    ]);
}
```

- [ ] **Step 5: approveRefund() 추가**

```php
/**
 * POST /backoffice/refunds/{idx}/approve — 환불 승인 (AJAX)
 */
public function approveRefund(int $idx): void
{
    $adminMemo = trim($this->request->getPost('admin_memo') ?? '');
    (new RefundRequestModel())->approve($idx, $adminMemo);
    echo json_encode(['success' => true, 'message' => '환불 요청이 승인되었습니다.']);
}
```

- [ ] **Step 6: rejectRefund() 추가**

```php
/**
 * POST /backoffice/refunds/{idx}/reject — 환불 반려 (AJAX), admin_memo 필수
 */
public function rejectRefund(int $idx): void
{
    $adminMemo = trim($this->request->getPost('admin_memo') ?? '');
    if ($adminMemo === '') {
        echo json_encode(['success' => false, 'message' => '반려 사유를 입력해주세요.']);
        return;
    }
    (new RefundRequestModel())->reject($idx, $adminMemo);
    echo json_encode(['success' => true, 'message' => '환불 요청이 반려되었습니다.']);
}
```

---

## Task 6: order_detail.php 수정 (프론트 환불 버튼 + 모달)

**Files:**
- Modify: `busan_onna/app/Views/service/mypage/order_detail.php`

- [ ] **Step 1: PHP 변수 계산 블록 추가**

`<style>` 태그 바로 위(PHP 구문 사용 가능한 위치)에 추가:

```php
<?php
$canRefund  = in_array($order['status'] ?? '', ['paid', 'preparing', 'delivered']);
$isShipped  = ($order['status'] ?? '') === 'shipped';
$isOver7Days = false;
if (($order['status'] ?? '') === 'delivered' && !empty($order['delivered_at'])) {
    $diff = (new \DateTime())->diff(new \DateTime($order['delivered_at']))->days;
    $isOver7Days = ($diff >= 7);
}
?>
```

- [ ] **Step 2: CSS 추가 — 기존 `<style>` 블록 맨 끝에 추가**

```css
/* 환불 요청 버튼 */
.btn-refund-request {
    display: inline-block;
    padding: 12px 28px;
    background: #fff;
    color: #e55039;
    font-size: 15px;
    font-weight: 700;
    border: 2px solid #e55039;
    border-radius: 10px;
    cursor: pointer;
    transition: background .2s, color .2s;
}
.btn-refund-request:hover { background: #e55039; color: #fff; }

/* 배송중 안내 텍스트 */
.refund-ship-notice {
    font-size: 14px;
    color: #868e96;
    padding: 10px 0;
}

/* 환불 모달 */
.refund-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9000;
    align-items: center;
    justify-content: center;
}
.refund-overlay.active { display: flex; }
.refund-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 520px;
    max-height: 88vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    margin: 16px;
}
.refund-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #e9ecef;
    position: sticky; top: 0;
    background: #fff; z-index: 1;
}
.refund-modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; }
.refund-modal-close { font-size: 24px; color: #adb5bd; cursor: pointer; background: none; border: none; line-height: 1; }
.refund-modal-body { padding: 22px 24px; }
.refund-modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 24px; border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    position: sticky; bottom: 0;
}
.rm-section { margin-bottom: 20px; }
.rm-section h4 {
    font-size: 12px; font-weight: 700; color: #868e96;
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 10px; padding-bottom: 6px;
    border-bottom: 1px solid #e9ecef;
}
.rm-item-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px;
    border: 1.5px solid #dee2e6; border-radius: 8px;
    margin-bottom: 8px;
}
.rm-item-row label { display: flex; align-items: center; gap: 10px; cursor: pointer; flex: 1; }
.rm-item-row input[type=checkbox] { width: 16px; height: 16px; accent-color: #e55039; flex-shrink: 0; }
.rm-item-name { font-size: 14px; font-weight: 600; }
.rm-item-opt  { font-size: 12px; color: #868e96; }
.rm-item-price { font-size: 14px; font-weight: 700; color: #e55039; white-space: nowrap; }
.rm-warn {
    background: #fff3cd; border: 1.5px solid #ffc107;
    border-radius: 8px; padding: 10px 14px;
    font-size: 13px; color: #856404;
    display: flex; gap: 8px; align-items: flex-start;
    margin-bottom: 16px;
}
.rm-select, .rm-textarea, .rm-file {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #dee2e6; border-radius: 8px;
    font-size: 14px; color: #343a40;
    background: #f8f9fa; box-sizing: border-box;
    font-family: inherit;
}
.rm-textarea { resize: vertical; min-height: 72px; margin-top: 8px; }
.rm-file { background: #fff; padding: 6px 10px; }
.rm-label { display: block; font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; }
.rm-hint  { font-size: 12px; color: #868e96; margin-top: 4px; }
.btn-rm-cancel  { padding: 10px 22px; border: 1.5px solid #dee2e6; background: #fff; color: #495057; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.btn-rm-submit  { padding: 10px 24px; border: none; background: #e55039; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-rm-submit:disabled { opacity: .5; cursor: not-allowed; }
```

- [ ] **Step 3: 하단 버튼 영역 교체**

기존 코드:
```php
<!-- 목록으로 버튼 -->
<a href="/mypage/orders" class="btn-back-list">← 목록으로</a>
```

다음으로 교체:
```php
<!-- 하단 버튼 영역 -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
    <a href="/mypage/orders" class="btn-back-list">← 목록으로</a>
    <?php if ($canRefund): ?>
    <button type="button" class="btn-refund-request" id="btnOpenRefundModal">환불 요청</button>
    <?php elseif ($isShipped): ?>
    <span class="refund-ship-notice">배송 완료 후 환불 요청이 가능합니다</span>
    <?php endif; ?>
</div>
```

- [ ] **Step 4: 환불 모달 HTML 추가 — `<?= view('service/partials/footer') ?>` 바로 위에 삽입**

```php
<?php if ($canRefund): ?>
<!-- ===== 환불 요청 모달 ===== -->
<div class="refund-overlay" id="refundOverlay">
  <div class="refund-modal">
    <div class="refund-modal-header">
      <h3>환불 요청</h3>
      <button type="button" class="refund-modal-close" id="btnCloseRefundModal">✕</button>
    </div>
    <div class="refund-modal-body">

      <!-- 상품 선택 -->
      <div class="rm-section">
        <h4>환불할 상품 선택 <span style="color:#e55039">*</span></h4>
        <?php foreach ($items as $item): ?>
        <div class="rm-item-row">
          <label>
            <input type="checkbox" class="refund-item-check"
                   value="<?= (int)$item['idx'] ?>" checked>
            <div>
              <div class="rm-item-name"><?= esc($item['goods_name']) ?></div>
              <?php if (!empty($item['option_label'])): ?>
              <div class="rm-item-opt"><?= esc($item['option_label']) ?></div>
              <?php endif; ?>
            </div>
          </label>
          <span class="rm-item-price"><?= number_format((int)$item['unit_price'] * (int)$item['quantity']) ?>원</span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- 7일 경과 경고 -->
      <?php if ($isOver7Days): ?>
      <div class="rm-warn">
        <span style="font-size:16px">⚠️</span>
        <div>
          배송완료일로부터 <strong>7일 이상</strong> 경과한 상품이 포함되어 있습니다.<br>
          상품 확인 과정에서 <strong>패널티가 발생할 수 있습니다.</strong>
        </div>
      </div>
      <?php endif; ?>

      <!-- 환불 사유 -->
      <div class="rm-section">
        <h4>환불 사유 <span style="color:#e55039">*</span></h4>
        <select class="rm-select" id="refundReason">
          <option value="">-- 사유를 선택해주세요 --</option>
          <option value="change_of_mind">단순 변심</option>
          <option value="defective">상품 불량 / 파손</option>
          <option value="wrong_item">상품 오배송 (다른 상품 도착)</option>
          <option value="delay">배송 지연</option>
          <option value="not_as_described">상품 설명과 다름</option>
          <option value="duplicate">중복 주문</option>
          <option value="direct">직접 입력 ✏️</option>
        </select>
        <textarea class="rm-textarea" id="refundReasonText"
                  placeholder="환불 사유를 직접 입력해주세요 (최대 200자)"
                  maxlength="200"
                  style="display:none"></textarea>
        <p class="rm-hint" id="refundDirectHint" style="display:none">
          "직접 입력" 선택 시 이 입력란을 작성해주세요.
        </p>
      </div>

      <!-- 이미지 첨부 -->
      <div class="rm-section" style="margin-bottom:0">
        <h4>이미지 첨부 (선택)</h4>
        <input type="file" class="rm-file" id="refundImages"
               accept="image/jpeg,image/png,image/gif" multiple>
        <p class="rm-hint">최대 3장, jpg/png/gif, 파일당 10MB 이하</p>
      </div>

    </div>
    <div class="refund-modal-footer">
      <button type="button" class="btn-rm-cancel" id="btnCancelRefund">취소</button>
      <button type="button" class="btn-rm-submit" id="btnSubmitRefund">환불 요청 제출</button>
    </div>
  </div>
</div>
<?php endif; ?>
```

- [ ] **Step 5: JS 추가 — `</body>` 바로 위에 삽입**

```php
<?php if ($canRefund): ?>
<script>
(function () {
  var overlay   = document.getElementById('refundOverlay');
  var btnOpen   = document.getElementById('btnOpenRefundModal');
  var btnClose  = document.getElementById('btnCloseRefundModal');
  var btnCancel = document.getElementById('btnCancelRefund');
  var btnSubmit = document.getElementById('btnSubmitRefund');
  var selReason = document.getElementById('refundReason');
  var txtReason = document.getElementById('refundReasonText');
  var hintDirect= document.getElementById('refundDirectHint');
  var fileInput = document.getElementById('refundImages');
  var orderIdx  = <?= (int)$order['idx'] ?>;

  function openModal()  { overlay.classList.add('active'); }
  function closeModal() { overlay.classList.remove('active'); }

  if (btnOpen)   btnOpen.addEventListener('click', openModal);
  if (btnClose)  btnClose.addEventListener('click', closeModal);
  if (btnCancel) btnCancel.addEventListener('click', closeModal);
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) closeModal();
  });

  // 직접 입력 선택 시 textarea 토글
  selReason.addEventListener('change', function () {
    var isDirect = this.value === 'direct';
    txtReason.style.display  = isDirect ? 'block' : 'none';
    hintDirect.style.display = isDirect ? 'block' : 'none';
  });

  // 이미지 3장 초과 방지
  fileInput.addEventListener('change', function () {
    if (this.files.length > 3) {
      alert('이미지는 최대 3장까지 첨부 가능합니다.');
      this.value = '';
    }
  });

  // AJAX 제출
  btnSubmit.addEventListener('click', function () {
    var checked = document.querySelectorAll('.refund-item-check:checked');
    if (checked.length === 0) {
      alert('환불할 상품을 1개 이상 선택해주세요.');
      return;
    }
    if (!selReason.value) {
      alert('환불 사유를 선택해주세요.');
      return;
    }
    if (selReason.value === 'direct' && !txtReason.value.trim()) {
      alert('직접 입력 사유를 작성해주세요.');
      return;
    }

    var fd = new FormData();
    checked.forEach(function (el) { fd.append('item_idxs[]', el.value); });
    fd.append('reason', selReason.value);
    if (selReason.value === 'direct') fd.append('reason_text', txtReason.value.trim());
    Array.from(fileInput.files).forEach(function (f) { fd.append('images[]', f); });

    btnSubmit.disabled = true;
    btnSubmit.textContent = '처리 중...';

    fetch('/mypage/orders/' + orderIdx + '/refund', {
      method: 'POST',
      body: fd,
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        alert('환불 요청이 접수되었습니다.');
        closeModal();
        location.reload();
      } else {
        alert(data.message || '오류가 발생했습니다.');
        btnSubmit.disabled = false;
        btnSubmit.textContent = '환불 요청 제출';
      }
    })
    .catch(function () {
      alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
      btnSubmit.disabled = false;
      btnSubmit.textContent = '환불 요청 제출';
    });
  });
})();
</script>
<?php endif; ?>
```

---

## Task 7: 백오피스 환불 요청 목록 뷰 신규

**Files:**
- Create: `busan_onna/app/Views/backoffice/refunds/list.php`

- [ ] **Step 1: 뷰 파일 생성**

```php
<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">환불 요청 관리</h1>
            <p class="bo-page-desc">고객의 환불 요청을 확인하고 승인·반려 처리합니다.</p>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<style>
/* 상태 필터 탭 */
.refund-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.refund-tab {
    padding: 6px 18px; border-radius: 20px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; border: 1.5px solid #dee2e6;
    color: #495057; background: #fff;
    transition: all .15s;
}
.refund-tab.active, .refund-tab:hover { background: #343a40; color: #fff; border-color: #343a40; }
.refund-tab.tab-pending  { border-color: #ffc107; color: #856404; }
.refund-tab.tab-pending.active, .refund-tab.tab-pending:hover { background: #ffc107; color: #fff; border-color: #ffc107; }
.refund-tab.tab-approved { border-color: #28a745; color: #155724; }
.refund-tab.tab-approved.active, .refund-tab.tab-approved:hover { background: #28a745; color: #fff; border-color: #28a745; }
.refund-tab.tab-rejected { border-color: #dc3545; color: #721c24; }
.refund-tab.tab-rejected.active, .refund-tab.tab-rejected:hover { background: #dc3545; color: #fff; border-color: #dc3545; }

/* 상태 뱃지 */
.rs-badge {
    display: inline-block; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
}
.rs-pending  { background: #fff3cd; color: #856404; }
.rs-approved { background: #d4edda; color: #155724; }
.rs-rejected { background: #f8d7da; color: #721c24; }

/* 상세 모달 */
.ro-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 9000;
    align-items: center; justify-content: center;
}
.ro-overlay.active { display: flex; }
.ro-modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 620px;
    max-height: 88vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,0.3);
    margin: 16px;
}
.ro-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 24px; border-bottom: 1px solid #e9ecef;
    position: sticky; top: 0; background: #fff; z-index: 1;
}
.ro-header h3 { margin: 0; font-size: 16px; font-weight: 700; color: #212529; }
.ro-close { font-size: 22px; color: #adb5bd; cursor: pointer; background: none; border: none; }
.ro-body { padding: 22px 24px; }
.ro-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 24px; border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    position: sticky; bottom: 0;
}
.ro-section { margin-bottom: 20px; }
.ro-section h4 {
    font-size: 12px; font-weight: 700; color: #868e96;
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e9ecef;
}
.ro-row {
    display: flex; gap: 8px; font-size: 13px;
    padding: 6px 0; border-bottom: 1px solid #f8f9fa;
}
.ro-row:last-child { border-bottom: none; }
.ro-label { color: #868e96; white-space: nowrap; width: 90px; flex-shrink: 0; }
.ro-val   { font-weight: 600; color: #212529; }
.ro-item-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; background: #f8f9fa;
    border-radius: 8px; margin-bottom: 6px; font-size: 13px;
}
.ro-item-name  { flex: 1; font-weight: 600; color: #212529; }
.ro-item-opt   { font-size: 11px; color: #868e96; display: block; }
.ro-item-price { color: #e55039; font-weight: 700; white-space: nowrap; }
.ro-img-gallery { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
.ro-img-thumb {
    width: 80px; height: 80px; border-radius: 8px; overflow: hidden;
    border: 1.5px solid #dee2e6; cursor: pointer;
}
.ro-img-thumb img { width: 100%; height: 100%; object-fit: cover; }
.ro-no-img { font-size: 13px; color: #adb5bd; }
.ro-memo {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #dee2e6; border-radius: 8px;
    font-size: 13px; font-family: inherit;
    resize: vertical; min-height: 64px;
    background: #f8f9fa; box-sizing: border-box;
}
.btn-ro-close   { padding: 10px 22px; border: 1.5px solid #dee2e6; background: #fff; color: #495057; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.btn-ro-reject  { padding: 10px 22px; border: 1.5px solid #dc3545; background: #fff; color: #dc3545; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-ro-approve { padding: 10px 24px; border: none; background: #28a745; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-ro-approve:disabled, .btn-ro-reject:disabled { opacity: .5; cursor: not-allowed; }

/* 이미지 라이트박스 */
.lightbox-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.85); z-index: 10000;
    align-items: center; justify-content: center;
}
.lightbox-overlay.active { display: flex; }
.lightbox-overlay img {
    max-width: 90vw; max-height: 90vh;
    border-radius: 8px; object-fit: contain;
}
.lightbox-close {
    position: absolute; top: 16px; right: 20px;
    font-size: 32px; color: #fff; cursor: pointer; background: none; border: none;
}
</style>

<!-- 상태 필터 탭 -->
<div class="refund-tabs">
    <?php
    $tabList = ['' => '전체', 'pending' => '대기중', 'approved' => '승인', 'rejected' => '반려'];
    $tabClass = ['' => '', 'pending' => 'tab-pending', 'approved' => 'tab-approved', 'rejected' => 'tab-rejected'];
    foreach ($tabList as $val => $label):
        $isActive = ($status === $val) ? ' active' : '';
    ?>
    <a href="?status=<?= esc($val) ?>"
       class="refund-tab <?= $tabClass[$val] ?><?= $isActive ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- 목록 테이블 -->
<?php if (empty($refunds)): ?>
    <p style="color:#868e96; padding:20px 0;">환불 요청이 없습니다.</p>
<?php else: ?>
<div class="bo-table-wrap">
    <table class="bo-table">
        <thead>
            <tr>
                <th>접수일시</th>
                <th>주문번호</th>
                <th>신청자</th>
                <th>환불 사유</th>
                <th>상태</th>
                <th>처리</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($refunds as $r): ?>
        <?php
            $badgeClass = match($r['status']) {
                'pending'  => 'rs-pending',
                'approved' => 'rs-approved',
                'rejected' => 'rs-rejected',
                default    => '',
            };
            $statusLabel = $statusLabels[$r['status']] ?? $r['status'];
        ?>
        <tr>
            <td><?= esc(substr($r['created_at'], 0, 16)) ?></td>
            <td><?= esc($r['order_no'] ?? '-') ?></td>
            <td><?= esc($r['user_name'] ?? $r['user_id'] ?? '-') ?></td>
            <td><?= esc(\App\Models\RefundRequestModel::REASON_LABELS[$r['reason']] ?? $r['reason']) ?></td>
            <td><span class="rs-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
            <td>
                <button type="button" class="bo-btn bo-btn-sm"
                        onclick="openRefundDetail(<?= (int)$r['idx'] ?>)">
                    상세
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 페이지네이션 -->
<?php if ($pager): ?>
<div class="bo-pager"><?= $pager->links() ?></div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== 환불 요청 상세 모달 ===== -->
<div class="ro-overlay" id="roOverlay">
  <div class="ro-modal">
    <div class="ro-header">
      <h3>환불 요청 상세</h3>
      <div style="display:flex;align-items:center;gap:12px;">
        <span id="roStatusBadge" class="rs-badge"></span>
        <button type="button" class="ro-close" id="roClose">✕</button>
      </div>
    </div>
    <div class="ro-body" id="roBody">
      <p style="color:#868e96;text-align:center;padding:30px 0;">불러오는 중...</p>
    </div>
    <div class="ro-footer" id="roFooter" style="display:none;">
      <button type="button" class="btn-ro-close" id="roCloseBtn">닫기</button>
      <button type="button" class="btn-ro-reject"  id="roBtnReject">반려</button>
      <button type="button" class="btn-ro-approve" id="roBtnApprove">승인</button>
    </div>
  </div>
</div>

<!-- ===== 이미지 라이트박스 ===== -->
<div class="lightbox-overlay" id="lightboxOverlay">
  <button type="button" class="lightbox-close" id="lightboxClose">✕</button>
  <img id="lightboxImg" src="" alt="첨부 이미지">
</div>

<?= view('backoffice/partials/footer', $this->data) ?>

<script>
(function () {
  var currentRefundIdx = null;
  var overlay   = document.getElementById('roOverlay');
  var body      = document.getElementById('roBody');
  var footer    = document.getElementById('roFooter');
  var badge     = document.getElementById('roStatusBadge');
  var btnApprove= document.getElementById('roBtnApprove');
  var btnReject = document.getElementById('roBtnReject');
  var lbOverlay = document.getElementById('lightboxOverlay');
  var lbImg     = document.getElementById('lightboxImg');

  var statusLabels = <?= json_encode(\App\Models\RefundRequestModel::STATUS_LABELS) ?>;
  var reasonLabels = <?= json_encode(\App\Models\RefundRequestModel::REASON_LABELS) ?>;

  function closeModal() { overlay.classList.remove('active'); }

  document.getElementById('roClose').addEventListener('click', closeModal);
  document.getElementById('roCloseBtn').addEventListener('click', closeModal);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });

  document.getElementById('lightboxClose').addEventListener('click', function () {
    lbOverlay.classList.remove('active');
  });
  lbOverlay.addEventListener('click', function(e) {
    if (e.target === lbOverlay) lbOverlay.classList.remove('active');
  });

  window.openRefundDetail = function (idx) {
    currentRefundIdx = idx;
    overlay.classList.add('active');
    body.innerHTML = '<p style="color:#868e96;text-align:center;padding:30px 0;">불러오는 중...</p>';
    footer.style.display = 'none';
    badge.textContent = '';
    badge.className = 'rs-badge';

    fetch('/backoffice/refunds/' + idx + '/detail')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) { body.innerHTML = '<p style="color:#dc3545;padding:20px 0;">불러오기 실패: ' + (data.message || '') + '</p>'; return; }
        renderDetail(data);
      })
      .catch(function () {
        body.innerHTML = '<p style="color:#dc3545;padding:20px 0;">네트워크 오류</p>';
      });
  };

  function renderDetail(data) {
    var r = data.refund;
    var items  = data.items  || [];
    var images = data.images || [];

    // 뱃지
    var badgeClasses = { pending: 'rs-pending', approved: 'rs-approved', rejected: 'rs-rejected' };
    badge.textContent = statusLabels[r.status] || r.status;
    badge.className = 'rs-badge ' + (badgeClasses[r.status] || '');

    // 상품 목록 HTML
    var itemsHtml = items.map(function (it) {
      var opt = it.option_label ? '<span class="ro-item-opt">' + esc(it.option_label) + '</span>' : '';
      var price = Number(it.unit_price) * Number(it.quantity);
      return '<div class="ro-item-row"><span class="ro-item-name">' + esc(it.goods_name) + opt + '</span><span class="ro-item-price">' + price.toLocaleString() + '원</span></div>';
    }).join('');

    // 이미지 HTML
    var imgsHtml = images.length
      ? images.map(function (img) {
          return '<div class="ro-img-thumb" onclick="openLightbox(\'' + img.file_path + '\')"><img src="' + img.file_path + '" alt="첨부이미지"></div>';
        }).join('')
      : '<span class="ro-no-img">첨부 이미지 없음</span>';

    // 사유
    var reasonLabel = reasonLabels[r.reason] || r.reason;
    var reasonDetail = (r.reason === 'direct' && r.reason_text)
      ? '<div class="ro-row"><span class="ro-label">상세 내용</span><span class="ro-val">' + esc(r.reason_text) + '</span></div>'
      : '';

    // 처리 내역 (승인/반려 완료 시)
    var processedHtml = '';
    if (r.status !== 'pending') {
      processedHtml = '<div class="ro-section">'
        + '<h4>처리 내역</h4>'
        + '<div class="ro-row"><span class="ro-label">처리일시</span><span class="ro-val">' + esc(r.processed_at || '-') + '</span></div>'
        + (r.admin_memo ? '<div class="ro-row"><span class="ro-label">관리자 메모</span><span class="ro-val">' + esc(r.admin_memo) + '</span></div>' : '')
        + '</div>';
    }

    body.innerHTML = ''
      + '<div class="ro-section"><h4>주문 정보</h4>'
      + '<div class="ro-row"><span class="ro-label">주문번호</span><span class="ro-val">' + esc(r.order_no) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">신청자</span><span class="ro-val">' + esc(r.user_name || r.user_id) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">접수일시</span><span class="ro-val">' + esc(r.created_at) + '</span></div>'
      + '<div class="ro-row"><span class="ro-label">주문 상태</span><span class="ro-val">' + esc(r.order_status) + '</span></div>'
      + '</div>'
      + '<div class="ro-section"><h4>환불 요청 상품</h4>' + itemsHtml + '</div>'
      + '<div class="ro-section"><h4>환불 사유</h4>'
      + '<div class="ro-row"><span class="ro-label">선택 사유</span><span class="ro-val">' + esc(reasonLabel) + '</span></div>'
      + reasonDetail
      + '</div>'
      + '<div class="ro-section"><h4>고객 첨부 이미지</h4><div class="ro-img-gallery">' + imgsHtml + '</div></div>'
      + processedHtml
      + '<div class="ro-section" style="margin-bottom:0"><h4>관리자 메모</h4>'
      + '<textarea class="ro-memo" id="roMemo" placeholder="승인 시 선택, 반려 시 필수입력"></textarea>'
      + '</div>';

    // 대기중이면 승인/반려 버튼 표시, 완료면 닫기만
    footer.style.display = 'flex';
    if (r.status === 'pending') {
      btnApprove.style.display = '';
      btnReject.style.display  = '';
    } else {
      btnApprove.style.display = 'none';
      btnReject.style.display  = 'none';
    }
  }

  function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  window.openLightbox = function (src) {
    lbImg.src = src;
    lbOverlay.classList.add('active');
  };

  function postAction(url, memo, successMsg) {
    var fd = new FormData();
    fd.append('admin_memo', memo);
    btnApprove.disabled = true;
    btnReject.disabled  = true;

    fetch(url, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          alert(successMsg);
          closeModal();
          location.reload();
        } else {
          alert(data.message || '처리 중 오류가 발생했습니다.');
          btnApprove.disabled = false;
          btnReject.disabled  = false;
        }
      })
      .catch(function () {
        alert('네트워크 오류가 발생했습니다.');
        btnApprove.disabled = false;
        btnReject.disabled  = false;
      });
  }

  btnApprove.addEventListener('click', function () {
    if (!confirm('이 환불 요청을 승인하시겠습니까?')) return;
    var memo = document.getElementById('roMemo').value.trim();
    postAction('/backoffice/refunds/' + currentRefundIdx + '/approve', memo, '환불 요청이 승인되었습니다.');
  });

  btnReject.addEventListener('click', function () {
    var memo = document.getElementById('roMemo').value.trim();
    if (!memo) { alert('반려 사유를 입력해주세요.'); return; }
    if (!confirm('이 환불 요청을 반려하시겠습니까?')) return;
    postAction('/backoffice/refunds/' + currentRefundIdx + '/reject', memo, '환불 요청이 반려되었습니다.');
  });
})();
</script>
```

---

## 셀프 리뷰 체크리스트

**스펙 커버리지:**
- [x] 주문 상세 하단 우측 주황색 테두리 환불 버튼 — Task 6 Step 3
- [x] shipped 상태 안내 텍스트 — Task 6 Step 3
- [x] 체크박스 부분 환불 — Task 6 Step 4
- [x] 환불 사유 드롭다운 + 직접 입력 — Task 6 Step 4, 5
- [x] 배송완료 7일 경과 경고 박스 — Task 6 Step 4
- [x] 이미지 첨부 (최대 3장) — Task 4 Step 2, Task 6 Step 4, 5
- [x] 백오피스 환불 목록 (status 필터) — Task 7
- [x] 백오피스 상세 모달 + 이미지 라이트박스 — Task 7
- [x] 백오피스 승인/반려 (반려 시 메모 필수) — Task 5 Step 5, 6 / Task 7
- [x] delivered_at 자동 기록 — Task 5 Step 2

**타입 일관성:**
- `RefundRequestModel::approve()` / `reject()` — Task 2, 5 모두 동일 시그니처
- `RefundRequestItemModel::insertItems()` — Task 2, 4 모두 동일 이름
- `refundDetail()` JSON 키 (`refund`, `items`, `images`) — Task 5 Step 4, Task 7 JS 모두 일치
