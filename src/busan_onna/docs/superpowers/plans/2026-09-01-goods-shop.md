# 굿즈샵 (Goods Shop) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** busan_onna에 부산 굿즈 실물 상품 판매 기능 추가 — 상품 목록→장바구니→주문서→PortOne 결제→완료, 백오피스 관리까지 완성.

**Architecture:** CodeIgniter 4 MVC 준수. PortOne REST API로 서버사이드 결제 검증 후 주문 확정. 배송은 택배/픽업 2종. 판매자(vendor)는 백오피스 승인 후 상품 등록 가능. 운영자 직판(vendor_idx=NULL)도 지원.

**Tech Stack:** PHP 8.x, CodeIgniter 4, MySQL 8.0, PortOne REST API v1, Vanilla JS + Fetch API

---

## File Structure

### 신규 생성

```
app/Database/Migrations/
  2026-09-01-000001_CreatePickupLocations.php
  2026-09-01-000002_CreateVendors.php
  2026-09-01-000003_CreateGoods.php
  2026-09-01-000004_CreateGoodsImages.php
  2026-09-01-000005_CreateGoodsOptions.php
  2026-09-01-000006_CreateGoodsOptionValues.php
  2026-09-01-000007_CreateCart.php
  2026-09-01-000008_CreateOrders.php
  2026-09-01-000009_CreateOrderItems.php
  2026-09-01-000010_CreateDeliveries.php

app/Models/
  GoodsModel.php            ← 상품 목록/상세/재고
  GoodsOptionModel.php      ← 옵션 그룹 (색상, 사이즈)
  GoodsOptionValueModel.php ← 옵션 값 + 재고 + 추가금액
  PickupLocationModel.php   ← 픽업 고정 장소
  VendorModel.php           ← 판매자
  CartModel.php             ← 장바구니
  OrderModel.php            ← 주문 헤더
  OrderItemModel.php        ← 주문 라인 아이템
  DeliveryModel.php         ← 배송 정보

app/Libraries/
  PortOnePayment.php        ← PortOne REST API 결제 검증

app/Controllers/
  Goods.php                 ← GET /goods, GET /goods/{idx}
  Cart.php                  ← 장바구니 CRUD (AJAX)
  Order.php                 ← 주문서 / 결제 / 완료 / 마이페이지
  BackofficeGoods.php       ← 상품 CRUD + 이미지/옵션 관리
  BackofficeOrders.php      ← 주문 상태 변경, 송장번호
  BackofficeVendors.php     ← 판매자 승인/거절

app/Views/service/goods/
  index.php    ← 상품 목록 (카드 그리드, 정렬)
  detail.php   ← 상품 상세 (이미지, 옵션, 담기 버튼)

app/Views/service/cart/
  index.php    ← 장바구니 (수량 조절, 합계, 주문 진행)

app/Views/service/order/
  form.php     ← 주문서 (배송유형 분기 폼)
  complete.php ← 주문 완료

app/Views/service/mypage/
  orders.php       ← 주문 내역
  order_detail.php ← 주문 상세 + 배송 추적

app/Views/backoffice/goods/
  list.php  ← 상품 목록
  form.php  ← 상품 등록/수정

app/Views/backoffice/orders/
  list.php   ← 주문 목록
  detail.php ← 주문 상세 + 배송 처리

app/Views/backoffice/vendors/
  list.php   ← 판매자 목록
  detail.php ← 판매자 상세 + 승인/거절
```

### 수정

```
app/Config/Routes.php  ← 굿즈샵 전체 라우트 추가
```

---

## Task 1: DB 마이그레이션 — 10개 테이블 생성

**Files:**
- Create: `app/Database/Migrations/2026-09-01-000001_CreatePickupLocations.php`
- Create: `app/Database/Migrations/2026-09-01-000002_CreateVendors.php`
- Create: `app/Database/Migrations/2026-09-01-000003_CreateGoods.php`
- Create: `app/Database/Migrations/2026-09-01-000004_CreateGoodsImages.php`
- Create: `app/Database/Migrations/2026-09-01-000005_CreateGoodsOptions.php`
- Create: `app/Database/Migrations/2026-09-01-000006_CreateGoodsOptionValues.php`
- Create: `app/Database/Migrations/2026-09-01-000007_CreateCart.php`
- Create: `app/Database/Migrations/2026-09-01-000008_CreateOrders.php`
- Create: `app/Database/Migrations/2026-09-01-000009_CreateOrderItems.php`
- Create: `app/Database/Migrations/2026-09-01-000010_CreateDeliveries.php`

- [ ] **Step 1: pickup_locations 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000001_CreatePickupLocations.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePickupLocations extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'name'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'address'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'reg_date' => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable('pickup_locations');
    }

    public function down(): void
    {
        $this->forge->dropTable('pickup_locations');
    }
}
```

- [ ] **Step 2: vendors 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000002_CreateVendors.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateVendors extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            // 0:대기, 1:승인, 2:거절
            'state'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'user_idx'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'shop_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'note'      => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'reg_date'  => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable('vendors');
    }

    public function down(): void
    {
        $this->forge->dropTable('vendors');
    }
}
```

- [ ] **Step 3: goods 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000003_CreateGoods.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateGoods extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            // 1:판매중, 0:중지, 2:품절
            'state'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            // NULL = 운영자 직판
            'vendor_idx'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 200],
            'description' => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'price'       => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'stock'       => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            // 1:택배, 2:픽업
            'delivery_type' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'thumbnail'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
            'reg_date'    => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'edit_date'   => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable('goods');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods');
    }
}
```

- [ ] **Step 4: goods_images 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000004_CreateGoodsImages.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateGoodsImages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'goods_idx'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'sort_order' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('goods_idx');
        $this->forge->createTable('goods_images');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_images');
    }
}
```

- [ ] **Step 5: goods_options + goods_option_values 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000005_CreateGoodsOptions.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateGoodsOptions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'goods_idx'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'option_name' => ['type' => 'VARCHAR', 'constraint' => 50], // 색상, 사이즈
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('goods_idx');
        $this->forge->createTable('goods_options');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_options');
    }
}
```

```php
<?php
// app/Database/Migrations/2026-09-01-000006_CreateGoodsOptionValues.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateGoodsOptionValues extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'option_idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'value'            => ['type' => 'VARCHAR', 'constraint' => 100], // 빨강, L
            'additional_price' => ['type' => 'INT', 'default' => 0],          // 추가금액 (음수 가능)
            'stock'            => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('option_idx');
        $this->forge->createTable('goods_option_values');
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_option_values');
    }
}
```

- [ ] **Step 6: cart 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000007_CreateCart.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateCart extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'goods_idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            // 옵션 선택 시 option_value idx, 없으면 NULL
            'option_value_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'quantity'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'reg_date'         => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey(['user_idx', 'goods_idx']);
        $this->forge->createTable('cart');
    }

    public function down(): void
    {
        $this->forge->dropTable('cart');
    }
}
```

- [ ] **Step 7: orders 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000008_CreateOrders.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            // pending / paid / preparing / shipped / delivered / cancelled
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'order_no'            => ['type' => 'VARCHAR', 'constraint' => 30], // BO-20260901-0001
            'user_idx'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'total_price'         => ['type' => 'INT', 'unsigned' => true],
            // 1:택배, 2:픽업
            'delivery_type'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            // 택배 시 배송지
            'recipient_name'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'recipient_phone'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'default' => null],
            'delivery_address'    => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true, 'default' => null],
            // 픽업 시 장소 idx
            'pickup_location_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            // PortOne imp_uid
            'payment_key'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'payment_method'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'paid_at'             => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'reg_date'            => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('order_no');
        $this->forge->addKey('user_idx');
        $this->forge->createTable('orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('orders');
    }
}
```

- [ ] **Step 8: order_items + deliveries 마이그레이션 생성**

```php
<?php
// app/Database/Migrations/2026-09-01-000009_CreateOrderItems.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateOrderItems extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'goods_idx'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'vendor_idx'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'option_value_idx' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            // 주문 시점 스냅샷
            'goods_name'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'option_label'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'quantity'         => ['type' => 'SMALLINT', 'unsigned' => true],
            'unit_price'       => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('order_idx');
        $this->forge->createTable('order_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('order_items');
    }
}
```

```php
<?php
// app/Database/Migrations/2026-09-01-000010_CreateDeliveries.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateDeliveries extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'idx'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_idx'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'courier'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],  // CJ대한통운 등
            'tracking_no' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            // ready / shipped / in_transit / delivered
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ready'],
            'updated_at'  => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->addKey('order_idx');
        $this->forge->createTable('deliveries');
    }

    public function down(): void
    {
        $this->forge->dropTable('deliveries');
    }
}
```

- [ ] **Step 9: 마이그레이션 실행**

```bash
php spark migrate
```

Expected: `All migrations complete.`

테이블 10개가 생성됐는지 확인:
```bash
php spark db:table goods
php spark db:table orders
```

---

## Task 2: 모델 구현 — 상품/옵션/픽업/판매자

**Files:**
- Create: `app/Models/GoodsModel.php`
- Create: `app/Models/GoodsOptionModel.php`
- Create: `app/Models/GoodsOptionValueModel.php`
- Create: `app/Models/PickupLocationModel.php`
- Create: `app/Models/VendorModel.php`

- [ ] **Step 1: GoodsModel 작성**

```php
<?php
// app/Models/GoodsModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 상품 모델
 */
class GoodsModel extends Model
{
    protected $table      = 'goods';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'state', 'vendor_idx', 'name', 'description',
        'price', 'stock', 'delivery_type', 'thumbnail',
        'reg_date', 'edit_date',
    ];

    public function getList(string $q = '', string $deliveryType = '', string $sort = 'latest'): array
    {
        $this->where('state', 1);

        if ($q !== '') {
            $this->like('name', $q);
        }
        if ($deliveryType !== '') {
            $this->where('delivery_type', (int) $deliveryType);
        }

        match ($sort) {
            'price_asc'  => $this->orderBy('price', 'ASC'),
            'price_desc' => $this->orderBy('price', 'DESC'),
            default      => $this->orderBy('idx', 'DESC'),
        };

        return $this->paginate(12) ?? [];
    }

    public function getDetail(int $idx): ?array
    {
        return $this->where('idx', $idx)->where('state', 1)->first();
    }

    /**
     * 구매 시 재고 차감 (트랜잭션 내에서 호출)
     */
    public function decreaseStock(int $idx, int $qty): bool
    {
        return $this->db->table('goods')
            ->where('idx', $idx)
            ->where('stock >=', $qty)
            ->update(['stock' => "stock - {$qty}"], null, false) > 0;
    }
}
```

- [ ] **Step 2: GoodsOptionModel + GoodsOptionValueModel 작성**

```php
<?php
// app/Models/GoodsOptionModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 옵션 그룹 모델 (색상, 사이즈 등)
 */
class GoodsOptionModel extends Model
{
    protected $table      = 'goods_options';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['goods_idx', 'option_name'];

    public function getByGoods(int $goodsIdx): array
    {
        return $this->where('goods_idx', $goodsIdx)->findAll();
    }
}
```

```php
<?php
// app/Models/GoodsOptionValueModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 옵션 값 모델 (빨강/L 등, 개별 재고·추가금액 포함)
 */
class GoodsOptionValueModel extends Model
{
    protected $table      = 'goods_option_values';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['option_idx', 'value', 'additional_price', 'stock'];

    public function getByOption(int $optionIdx): array
    {
        return $this->where('option_idx', $optionIdx)->findAll();
    }

    public function decreaseStock(int $idx, int $qty): bool
    {
        return $this->db->table('goods_option_values')
            ->where('idx', $idx)
            ->where('stock >=', $qty)
            ->update(['stock' => "stock - {$qty}"], null, false) > 0;
    }
}
```

- [ ] **Step 3: PickupLocationModel 작성**

```php
<?php
// app/Models/PickupLocationModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 픽업 고정 장소 모델
 */
class PickupLocationModel extends Model
{
    protected $table      = 'pickup_locations';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['state', 'name', 'address'];

    public function getActive(): array
    {
        return $this->where('state', 1)->orderBy('idx', 'ASC')->findAll();
    }
}
```

- [ ] **Step 4: VendorModel 작성**

```php
<?php
// app/Models/VendorModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 판매자(vendor) 모델 — 0:대기, 1:승인, 2:거절
 */
class VendorModel extends Model
{
    protected $table      = 'vendors';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['state', 'user_idx', 'shop_name', 'contact', 'note'];

    public function getList(string $state = ''): array
    {
        if ($state !== '') {
            $this->where('state', (int) $state);
        }
        return $this->orderBy('idx', 'DESC')->paginate(20) ?? [];
    }

    public function getApprovedByUser(int $userIdx): ?array
    {
        return $this->where('user_idx', $userIdx)->where('state', 1)->first();
    }
}
```

---

## Task 3: 모델 구현 — 거래 (장바구니/주문/배송)

**Files:**
- Create: `app/Models/CartModel.php`
- Create: `app/Models/OrderModel.php`
- Create: `app/Models/OrderItemModel.php`
- Create: `app/Models/DeliveryModel.php`

- [ ] **Step 1: CartModel 작성**

```php
<?php
// app/Models/CartModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 장바구니 모델
 */
class CartModel extends Model
{
    protected $table      = 'cart';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['user_idx', 'goods_idx', 'option_value_idx', 'quantity'];

    /**
     * 사용자 장바구니 전체 조회 (상품+옵션 JOIN)
     */
    public function getCartItems(int $userIdx): array
    {
        return $this->db->table('cart c')
            ->select('c.idx, c.goods_idx, c.option_value_idx, c.quantity,
                      g.name AS goods_name, g.price, g.thumbnail, g.stock,
                      g.delivery_type,
                      gov.value AS option_value, gov.additional_price,
                      go.option_name')
            ->join('goods g', 'g.idx = c.goods_idx')
            ->join('goods_option_values gov', 'gov.idx = c.option_value_idx', 'left')
            ->join('goods_options go', 'go.idx = gov.option_idx', 'left')
            ->where('c.user_idx', $userIdx)
            ->where('g.state', 1)
            ->orderBy('c.idx', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * 동일 상품+옵션 조합이 이미 있으면 수량 증가, 없으면 신규 삽입
     */
    public function addOrIncrement(int $userIdx, int $goodsIdx, ?int $optionValueIdx, int $qty): void
    {
        $existing = $this->where('user_idx', $userIdx)
                         ->where('goods_idx', $goodsIdx)
                         ->where('option_value_idx', $optionValueIdx)
                         ->first();

        if ($existing) {
            $this->update($existing['idx'], ['quantity' => $existing['quantity'] + $qty]);
        } else {
            $this->insert([
                'user_idx'         => $userIdx,
                'goods_idx'        => $goodsIdx,
                'option_value_idx' => $optionValueIdx,
                'quantity'         => $qty,
            ]);
        }
    }

    public function clearByUser(int $userIdx): void
    {
        $this->where('user_idx', $userIdx)->delete();
    }
}
```

- [ ] **Step 2: OrderModel 작성**

```php
<?php
// app/Models/OrderModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 주문 헤더 모델
 */
class OrderModel extends Model
{
    protected $table      = 'orders';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'status', 'order_no', 'user_idx', 'total_price',
        'delivery_type', 'recipient_name', 'recipient_phone',
        'delivery_address', 'pickup_location_idx',
        'payment_key', 'payment_method', 'paid_at',
    ];

    public const STATUS_LABELS = [
        'pending'   => '결제대기',
        'paid'      => '결제완료',
        'preparing' => '상품준비중',
        'shipped'   => '배송중',
        'delivered' => '배송완료',
        'cancelled' => '취소됨',
    ];

    /**
     * 주문번호 생성: BO-YYYYMMDD-NNNN (당일 순번)
     */
    public function generateOrderNo(): string
    {
        $date  = date('Ymd');
        $count = $this->where("order_no LIKE 'BO-{$date}-%'")->countAllResults() + 1;
        return sprintf('BO-%s-%04d', $date, $count);
    }

    public function getMyOrders(int $userIdx): array
    {
        return $this->where('user_idx', $userIdx)
                    ->orderBy('idx', 'DESC')
                    ->paginate(10) ?? [];
    }

    public function getDetail(int $idx, int $userIdx): ?array
    {
        return $this->where('idx', $idx)->where('user_idx', $userIdx)->first();
    }

    public function getAdminList(string $status = '', string $q = ''): array
    {
        if ($status !== '') $this->where('status', $status);
        if ($q !== '')      $this->like('order_no', $q);
        return $this->orderBy('idx', 'DESC')->paginate(20) ?? [];
    }

    public function markPaid(int $idx, string $paymentKey, string $method): bool
    {
        return $this->update($idx, [
            'status'         => 'paid',
            'payment_key'    => $paymentKey,
            'payment_method' => $method,
            'paid_at'        => date('Y-m-d H:i:s'),
        ]);
    }
}
```

- [ ] **Step 3: OrderItemModel + DeliveryModel 작성**

```php
<?php
// app/Models/OrderItemModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 주문 라인 아이템 모델 (가격은 주문 시점 스냅샷)
 */
class OrderItemModel extends Model
{
    protected $table      = 'order_items';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_idx', 'goods_idx', 'vendor_idx',
        'option_value_idx', 'goods_name', 'option_label',
        'quantity', 'unit_price',
    ];

    public function getByOrder(int $orderIdx): array
    {
        return $this->where('order_idx', $orderIdx)->findAll();
    }
}
```

```php
<?php
// app/Models/DeliveryModel.php
namespace App\Models;

use CodeIgniter\Model;

/**
 * 배송 정보 모델
 */
class DeliveryModel extends Model
{
    protected $table      = 'deliveries';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['order_idx', 'courier', 'tracking_no', 'status', 'updated_at'];

    public const COURIERS = ['CJ대한통운', '한진택배', '롯데택배', '우체국택배', '로젠택배'];

    public function getByOrder(int $orderIdx): ?array
    {
        return $this->where('order_idx', $orderIdx)->first();
    }

    public function upsert(int $orderIdx, string $courier, string $trackingNo): void
    {
        $existing = $this->getByOrder($orderIdx);
        $data = [
            'courier'     => $courier,
            'tracking_no' => $trackingNo,
            'status'      => 'shipped',
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        if ($existing) {
            $this->update($existing['idx'], $data);
        } else {
            $this->insert(array_merge($data, ['order_idx' => $orderIdx]));
        }
    }
}
```

---

## Task 4: PortOne 결제 검증 라이브러리

**Files:**
- Create: `app/Libraries/PortOnePayment.php`

- [ ] **Step 1: .env에 PortOne 키 추가**

`.env` 파일에 추가:
```
PORTONE_IMP_KEY = your_imp_key_here
PORTONE_IMP_SECRET = your_imp_secret_here
```

- [ ] **Step 2: PortOnePayment 라이브러리 작성**

```php
<?php
// app/Libraries/PortOnePayment.php
namespace App\Libraries;

/**
 * PortOne(구 아임포트) REST API v1 결제 검증 헬퍼
 * 결제 후 imp_uid로 서버에서 실제 결제 금액을 조회·검증한다.
 */
class PortOnePayment
{
    private string $impKey;
    private string $impSecret;
    private string $baseUrl = 'https://api.iamport.kr';

    public function __construct()
    {
        $this->impKey    = $_ENV['PORTONE_IMP_KEY']    ?? env('PORTONE_IMP_KEY');
        $this->impSecret = $_ENV['PORTONE_IMP_SECRET'] ?? env('PORTONE_IMP_SECRET');
    }

    /**
     * 액세스 토큰 발급
     */
    private function getToken(): string
    {
        $response = $this->request('POST', '/users/getToken', [
            'imp_key'    => $this->impKey,
            'imp_secret' => $this->impSecret,
        ]);

        if ($response['code'] !== 0) {
            throw new \RuntimeException('PortOne 토큰 발급 실패: ' . $response['message']);
        }

        return $response['response']['access_token'];
    }

    /**
     * imp_uid로 결제 정보 조회 후 금액 검증
     * @return array ['valid' => bool, 'data' => array|null, 'error' => string]
     */
    public function verify(string $impUid, int $expectedAmount): array
    {
        try {
            $token    = $this->getToken();
            $response = $this->request('GET', "/payments/{$impUid}", [], $token);

            if ($response['code'] !== 0) {
                return ['valid' => false, 'data' => null, 'error' => $response['message']];
            }

            $payment = $response['response'];

            if ((int) $payment['amount'] !== $expectedAmount) {
                return ['valid' => false, 'data' => $payment, 'error' => '결제 금액 불일치'];
            }

            if ($payment['status'] !== 'paid') {
                return ['valid' => false, 'data' => $payment, 'error' => '결제 미완료 상태'];
            }

            return ['valid' => true, 'data' => $payment, 'error' => ''];
        } catch (\Throwable $e) {
            return ['valid' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    private function request(string $method, string $path, array $body = [], string $token = ''): array
    {
        $url  = $this->baseUrl . $path;
        $curl = curl_init($url);

        $headers = ['Content-Type: application/json'];
        if ($token !== '') {
            $headers[] = "Authorization: Bearer {$token}";
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $method !== 'GET' ? json_encode($body) : null,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true) ?? ['code' => -1, 'message' => 'curl 실패'];
    }
}
```

---

## Task 5: 라우트 등록

**Files:**
- Modify: `app/Config/Routes.php`

- [ ] **Step 1: Routes.php에 굿즈샵 라우트 추가**

`// 이벤트` 블록 바로 아래에 삽입:

```php
// 굿즈샵 — 프론트
$routes->get('/goods',               'Goods::index');
$routes->get('/goods/(:num)',        'Goods::detail/$1');

// 장바구니 (로그인 필요)
$routes->get('/cart',                'Cart::index');
$routes->post('/cart/add',           'Cart::add');
$routes->post('/cart/update',        'Cart::update');
$routes->post('/cart/remove',        'Cart::remove');

// 주문 + 결제 (로그인 필요)
$routes->get('/order',               'Order::form');
$routes->post('/order/store',        'Order::store');
$routes->post('/order/verify',       'Order::verify');
$routes->get('/order/complete/(:num)', 'Order::complete/$1');

// 마이페이지 — 주문내역
$routes->get('/mypage/orders',           'Order::myOrders');
$routes->get('/mypage/orders/(:num)',    'Order::myOrderDetail/$1');
```

백오피스 group 안에 추가:

```php
    // 굿즈 관리
    $routes->get('goods',                    'BackofficeGoods::list');
    $routes->get('goods/register',           'BackofficeGoods::register');
    $routes->post('goods/register',          'BackofficeGoods::store');
    $routes->get('goods/(:num)/edit',        'BackofficeGoods::edit/$1');
    $routes->post('goods/(:num)/edit',       'BackofficeGoods::update/$1');
    $routes->post('goods/(:num)/state',      'BackofficeGoods::toggleState/$1');
    $routes->post('goods/(:num)/delete',     'BackofficeGoods::delete/$1');

    // 주문 관리
    $routes->get('orders',                   'BackofficeOrders::list');
    $routes->get('orders/(:num)',            'BackofficeOrders::detail/$1');
    $routes->post('orders/(:num)/status',    'BackofficeOrders::updateStatus/$1');
    $routes->post('orders/(:num)/delivery',  'BackofficeOrders::saveDelivery/$1');

    // 픽업 장소 관리
    $routes->get('pickup-locations',             'BackofficeGoods::pickupList');
    $routes->post('pickup-locations/store',      'BackofficeGoods::pickupStore');
    $routes->post('pickup-locations/(:num)/state', 'BackofficeGoods::pickupToggle/$1');

    // 판매자 관리
    $routes->get('vendors',                  'BackofficeVendors::list');
    $routes->get('vendors/(:num)',           'BackofficeVendors::detail/$1');
    $routes->post('vendors/(:num)/approve',  'BackofficeVendors::approve/$1');
    $routes->post('vendors/(:num)/reject',   'BackofficeVendors::reject/$1');
```

---

## Task 6: 굿즈 목록/상세 컨트롤러 + 뷰

**Files:**
- Create: `app/Controllers/Goods.php`
- Create: `app/Views/service/goods/index.php`
- Create: `app/Views/service/goods/detail.php`

- [ ] **Step 1: Goods 컨트롤러 작성**

```php
<?php
// app/Controllers/Goods.php
namespace App\Controllers;

use App\Models\GoodsModel;
use App\Models\GoodsOptionModel;
use App\Models\GoodsOptionValueModel;

/**
 * 굿즈 목록/상세 컨트롤러
 */
class Goods extends BaseController
{
    /**
     * GET /goods — 상품 목록
     */
    public function index(): string
    {
        $model = new GoodsModel();

        $q            = trim($this->request->getGet('q') ?? '');
        $deliveryType = trim($this->request->getGet('delivery_type') ?? '');
        $sort         = trim($this->request->getGet('sort') ?? 'latest');

        $items = $model->getList($q, $deliveryType, $sort);

        return view('service/goods/index', [
            'items'        => $items,
            'pager'        => $model->pager,
            'q'            => $q,
            'deliveryType' => $deliveryType,
            'sort'         => $sort,
        ]);
    }

    /**
     * GET /goods/{idx} — 상품 상세
     */
    public function detail(int $idx): string
    {
        $goodsModel       = new GoodsModel();
        $optionModel      = new GoodsOptionModel();
        $optionValueModel = new GoodsOptionValueModel();

        $goods = $goodsModel->getDetail($idx);
        if ($goods === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 옵션 그룹 + 각 값 조합
        $rawOptions = $optionModel->getByGoods($idx);
        $options    = array_map(function ($opt) use ($optionValueModel) {
            $opt['values'] = $optionValueModel->getByOption($opt['idx']);
            return $opt;
        }, $rawOptions);

        return view('service/goods/detail', [
            'goods'   => $goods,
            'options' => $options,
        ]);
    }
}
```

- [ ] **Step 2: 굿즈 목록 뷰 작성 (`app/Views/service/goods/index.php`)**

```php
<?php
// app/Views/service/goods/index.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="goods-list-section">
  <div class="container">
    <h2 class="section-title">부산굿즈</h2>

    <form method="get" action="/goods" class="goods-filter">
      <input type="text" name="q" value="<?= esc($q) ?>" placeholder="상품 검색">
      <select name="delivery_type">
        <option value="">전체 배송</option>
        <option value="1" <?= $deliveryType==='1'?'selected':'' ?>>택배</option>
        <option value="2" <?= $deliveryType==='2'?'selected':'' ?>>픽업</option>
      </select>
      <select name="sort">
        <option value="latest"     <?= $sort==='latest'?'selected':'' ?>>최신순</option>
        <option value="price_asc"  <?= $sort==='price_asc'?'selected':'' ?>>낮은가격순</option>
        <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>높은가격순</option>
      </select>
      <button type="submit">검색</button>
    </form>

    <div class="goods-grid">
      <?php foreach ($items as $item): ?>
      <a href="/goods/<?= $item['idx'] ?>" class="goods-card">
        <div class="goods-thumb">
          <?php if ($item['thumbnail']): ?>
            <img src="<?= esc($item['thumbnail']) ?>" alt="<?= esc($item['name']) ?>">
          <?php else: ?>
            <div class="no-image">이미지 없음</div>
          <?php endif; ?>
          <?php if ($item['delivery_type'] == 2): ?>
            <span class="badge badge-pickup">픽업</span>
          <?php endif; ?>
        </div>
        <div class="goods-info">
          <p class="goods-name"><?= esc($item['name']) ?></p>
          <p class="goods-price"><?= number_format($item['price']) ?>원</p>
          <?php if ($item['stock'] == 0): ?>
            <span class="badge badge-soldout">품절</span>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (empty($items)): ?>
        <p class="empty-msg">상품이 없습니다.</p>
      <?php endif; ?>
    </div>

    <?= $pager?->links() ?>
  </div>
</section>
<?php $this->endSection(); ?>
```

- [ ] **Step 3: 굿즈 상세 뷰 작성 (`app/Views/service/goods/detail.php`)**

```php
<?php
// app/Views/service/goods/detail.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="goods-detail-section">
  <div class="container">
    <div class="goods-detail-wrap">

      <div class="goods-images">
        <img src="<?= esc($goods['thumbnail'] ?? '/assets/img/no-image.png') ?>" alt="<?= esc($goods['name']) ?>" id="main-image">
      </div>

      <div class="goods-purchase">
        <h1 class="goods-title"><?= esc($goods['name']) ?></h1>
        <p class="goods-price" id="display-price"><?= number_format($goods['price']) ?>원</p>
        <p class="delivery-badge">
          <?= $goods['delivery_type'] == 1 ? '📦 택배 배송' : '🏪 픽업 수령' ?>
        </p>

        <?php foreach ($options as $opt): ?>
        <div class="option-group">
          <label><?= esc($opt['option_name']) ?></label>
          <select name="option[<?= $opt['idx'] ?>]" class="option-select" data-base-price="<?= $goods['price'] ?>">
            <option value="">선택하세요</option>
            <?php foreach ($opt['values'] as $val): ?>
            <option value="<?= $val['idx'] ?>"
                    data-additional="<?= $val['additional_price'] ?>"
                    data-stock="<?= $val['stock'] ?>"
                    <?= $val['stock'] == 0 ? 'disabled' : '' ?>>
              <?= esc($val['value']) ?>
              <?= $val['additional_price'] > 0 ? '(+'.number_format($val['additional_price']).'원)' : '' ?>
              <?= $val['stock'] == 0 ? '(품절)' : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endforeach; ?>

        <div class="quantity-wrap">
          <label>수량</label>
          <input type="number" id="quantity" value="1" min="1" max="<?= $goods['stock'] ?>">
        </div>

        <?php if ($goods['stock'] > 0): ?>
        <button class="btn-cart" onclick="addToCart(<?= $goods['idx'] ?>)">장바구니 담기</button>
        <?php else: ?>
        <button class="btn-cart" disabled>품절</button>
        <?php endif; ?>

        <div class="goods-description">
          <?= nl2br(esc($goods['description'] ?? '')) ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// 옵션 선택 시 가격 업데이트
document.querySelectorAll('.option-select').forEach(sel => {
  sel.addEventListener('change', () => {
    const basePrice = parseInt(sel.dataset.basePrice);
    let additional  = 0;
    document.querySelectorAll('.option-select').forEach(s => {
      const opt = s.options[s.selectedIndex];
      additional += parseInt(opt.dataset.additional || 0);
    });
    document.getElementById('display-price').textContent =
      (basePrice + additional).toLocaleString() + '원';
  });
});

function addToCart(goodsIdx) {
  const optionValueIdx = document.querySelector('.option-select')?.value || null;
  const quantity       = parseInt(document.getElementById('quantity').value);

  fetch('/cart/add', {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
    body: JSON.stringify({ goods_idx: goodsIdx, option_value_idx: optionValueIdx, quantity })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      if (confirm('장바구니에 담겼습니다. 장바구니로 이동하시겠습니까?')) {
        location.href = '/cart';
      }
    } else {
      alert(data.message || '오류가 발생했습니다.');
    }
  });
}
</script>
<?php $this->endSection(); ?>
```

---

## Task 7: 장바구니 컨트롤러 + 뷰

**Files:**
- Create: `app/Controllers/Cart.php`
- Create: `app/Views/service/cart/index.php`

- [ ] **Step 1: Cart 컨트롤러 작성**

```php
<?php
// app/Controllers/Cart.php
namespace App\Controllers;

use App\Models\CartModel;

/**
 * 장바구니 컨트롤러 — 로그인 필요
 */
class Cart extends BaseController
{
    private function requireLogin(): bool
    {
        if (!session()->get('user.idx')) {
            if ($this->request->isAJAX()) {
                $this->response->setStatusCode(401);
                echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
                return false;
            }
            return redirect()->to('/auth/login')->withCookies();
        }
        return true;
    }

    /** GET /cart */
    public function index(): string
    {
        if (!$this->requireLogin()) exit;
        $model     = new CartModel();
        $userIdx   = session()->get('user.idx');
        $cartItems = $model->getCartItems($userIdx);
        $total     = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $cartItems
        ));
        return view('service/cart/index', ['cartItems' => $cartItems, 'total' => $total]);
    }

    /** POST /cart/add (AJAX JSON) */
    public function add(): void
    {
        if (!$this->requireLogin()) return;
        $body            = $this->request->getJSON(true);
        $goodsIdx        = (int) ($body['goods_idx'] ?? 0);
        $optionValueIdx  = isset($body['option_value_idx']) && $body['option_value_idx'] ? (int)$body['option_value_idx'] : null;
        $quantity        = max(1, (int) ($body['quantity'] ?? 1));

        if ($goodsIdx === 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        $model = new CartModel();
        $model->addOrIncrement(session()->get('user.idx'), $goodsIdx, $optionValueIdx, $quantity);
        echo json_encode(['success' => true]);
    }

    /** POST /cart/update (AJAX JSON) — { cart_idx, quantity } */
    public function update(): void
    {
        if (!$this->requireLogin()) return;
        $body     = $this->request->getJSON(true);
        $cartIdx  = (int) ($body['cart_idx'] ?? 0);
        $quantity = max(1, (int) ($body['quantity'] ?? 1));
        $model    = new CartModel();
        $item     = $model->find($cartIdx);
        if (!$item || $item['user_idx'] !== session()->get('user.idx')) {
            echo json_encode(['success' => false, 'message' => '권한 없음']);
            return;
        }
        $model->update($cartIdx, ['quantity' => $quantity]);
        echo json_encode(['success' => true]);
    }

    /** POST /cart/remove (AJAX JSON) — { cart_idx } */
    public function remove(): void
    {
        if (!$this->requireLogin()) return;
        $cartIdx = (int) ($this->request->getJSON(true)['cart_idx'] ?? 0);
        $model   = new CartModel();
        $item    = $model->find($cartIdx);
        if (!$item || $item['user_idx'] !== session()->get('user.idx')) {
            echo json_encode(['success' => false, 'message' => '권한 없음']);
            return;
        }
        $model->delete($cartIdx);
        echo json_encode(['success' => true]);
    }
}
```

- [ ] **Step 2: 장바구니 뷰 작성 (`app/Views/service/cart/index.php`)**

```php
<?php
// app/Views/service/cart/index.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="cart-section">
  <div class="container">
    <h2>장바구니</h2>

    <?php if (empty($cartItems)): ?>
      <p class="empty-msg">장바구니가 비어있습니다. <a href="/goods">굿즈 보러가기</a></p>
    <?php else: ?>
    <table class="cart-table">
      <thead>
        <tr><th>상품</th><th>옵션</th><th>수량</th><th>금액</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($cartItems as $item):
          $unitPrice = $item['price'] + ($item['additional_price'] ?? 0);
        ?>
        <tr data-cart-idx="<?= $item['idx'] ?>">
          <td>
            <?php if ($item['thumbnail']): ?>
              <img src="<?= esc($item['thumbnail']) ?>" width="60" alt="">
            <?php endif; ?>
            <?= esc($item['goods_name']) ?>
          </td>
          <td><?= $item['option_name'] ? esc($item['option_name'].': '.$item['option_value']) : '-' ?></td>
          <td>
            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1"
                   onchange="updateQty(<?= $item['idx'] ?>, this.value)">
          </td>
          <td class="item-total"><?= number_format($unitPrice * $item['quantity']) ?>원</td>
          <td><button onclick="removeItem(<?= $item['idx'] ?>)">삭제</button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="cart-summary">
      <strong>합계: <span id="total-price"><?= number_format($total) ?></span>원</strong>
      <a href="/order" class="btn-order">주문하기</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
function updateQty(cartIdx, qty) {
  fetch('/cart/update', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ cart_idx: cartIdx, quantity: parseInt(qty) })
  }).then(() => location.reload());
}

function removeItem(cartIdx) {
  if (!confirm('삭제하시겠습니까?')) return;
  fetch('/cart/remove', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ cart_idx: cartIdx })
  }).then(() => location.reload());
}
</script>
<?php $this->endSection(); ?>
```

---

## Task 8: 주문서/결제/완료 컨트롤러 + 뷰

**Files:**
- Create: `app/Controllers/Order.php`
- Create: `app/Views/service/order/form.php`
- Create: `app/Views/service/order/complete.php`
- Create: `app/Views/service/mypage/orders.php`
- Create: `app/Views/service/mypage/order_detail.php`

- [ ] **Step 1: Order 컨트롤러 작성**

```php
<?php
// app/Controllers/Order.php
namespace App\Controllers;

use App\Models\CartModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PickupLocationModel;
use App\Models\GoodsModel;
use App\Models\GoodsOptionValueModel;
use App\Libraries\PortOnePayment;

/**
 * 주문/결제/마이페이지 컨트롤러
 */
class Order extends BaseController
{
    private function userIdx(): int
    {
        $idx = session()->get('user.idx');
        if (!$idx) {
            redirect()->to('/auth/login')->send();
            exit;
        }
        return (int) $idx;
    }

    /** GET /order — 주문서 폼 */
    public function form(): string
    {
        $userIdx = $this->userIdx();
        $cart    = new CartModel();
        $items   = $cart->getCartItems($userIdx);

        if (empty($items)) {
            return redirect()->to('/cart')->with('error', '장바구니가 비어있습니다.');
        }

        $total     = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));
        $pickups   = (new PickupLocationModel())->getActive();

        return view('service/order/form', [
            'cartItems' => $items,
            'total'     => $total,
            'pickups'   => $pickups,
        ]);
    }

    /**
     * POST /order/store — 주문 생성 (결제 전, pending 상태)
     * 프론트에서 PortOne 결제창을 띄우기 전에 주문 레코드를 먼저 생성한다.
     */
    public function store(): void
    {
        $userIdx = $this->userIdx();
        $post    = $this->request->getPost();

        $deliveryType = (int) ($post['delivery_type'] ?? 1);
        $cart         = new CartModel();
        $items        = $cart->getCartItems($userIdx);

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => '장바구니가 비어있습니다.']);
            return;
        }

        $total = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $items
        ));

        $orderModel = new OrderModel();
        $db         = \Config\Database::connect();
        $db->transStart();

        $orderData = [
            'status'       => 'pending',
            'order_no'     => $orderModel->generateOrderNo(),
            'user_idx'     => $userIdx,
            'total_price'  => $total,
            'delivery_type' => $deliveryType,
        ];

        if ($deliveryType === 1) {
            $orderData['recipient_name']   = trim($post['recipient_name'] ?? '');
            $orderData['recipient_phone']  = trim($post['recipient_phone'] ?? '');
            $orderData['delivery_address'] = trim($post['delivery_address'] ?? '');
        } else {
            $orderData['pickup_location_idx'] = (int) ($post['pickup_location_idx'] ?? 0);
        }

        $orderModel->insert($orderData);
        $orderIdx = $orderModel->getInsertID();

        $itemModel = new OrderItemModel();
        foreach ($items as $item) {
            $unitPrice = $item['price'] + ($item['additional_price'] ?? 0);
            $itemModel->insert([
                'order_idx'        => $orderIdx,
                'goods_idx'        => $item['goods_idx'],
                'vendor_idx'       => null,
                'option_value_idx' => $item['option_value_idx'],
                'goods_name'       => $item['goods_name'],
                'option_label'     => $item['option_name'] ? $item['option_name'].': '.$item['option_value'] : null,
                'quantity'         => $item['quantity'],
                'unit_price'       => $unitPrice,
            ]);
        }

        $db->transComplete();

        echo json_encode([
            'success'     => $db->transStatus(),
            'order_idx'   => $orderIdx,
            'order_no'    => $orderData['order_no'],
            'total_price' => $total,
        ]);
    }

    /**
     * POST /order/verify — PortOne 결제 완료 검증
     * 프론트에서 imp_uid + order_idx 전송 → 서버 금액 검증 → 주문 확정
     */
    public function verify(): void
    {
        $body     = $this->request->getJSON(true);
        $impUid   = trim($body['imp_uid'] ?? '');
        $orderIdx = (int) ($body['order_idx'] ?? 0);
        $userIdx  = $this->userIdx();

        $orderModel = new OrderModel();
        $order      = $orderModel->where('idx', $orderIdx)->where('user_idx', $userIdx)->first();

        if (!$order || $order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => '유효하지 않은 주문입니다.']);
            return;
        }

        $portone = new PortOnePayment();
        $result  = $portone->verify($impUid, (int) $order['total_price']);

        if (!$result['valid']) {
            echo json_encode(['success' => false, 'message' => $result['error']]);
            return;
        }

        $orderModel->markPaid($orderIdx, $impUid, $result['data']['pay_method'] ?? 'card');

        // 재고 차감
        $goodsModel       = new GoodsModel();
        $optionValueModel = new GoodsOptionValueModel();
        $itemModel        = new OrderItemModel();

        foreach ($itemModel->getByOrder($orderIdx) as $item) {
            $goodsModel->decreaseStock($item['goods_idx'], $item['quantity']);
            if ($item['option_value_idx']) {
                $optionValueModel->decreaseStock($item['option_value_idx'], $item['quantity']);
            }
        }

        // 장바구니 비우기
        (new CartModel())->clearByUser($userIdx);

        echo json_encode(['success' => true, 'order_idx' => $orderIdx]);
    }

    /** GET /order/complete/{idx} — 주문 완료 페이지 */
    public function complete(int $idx): string
    {
        $userIdx    = $this->userIdx();
        $orderModel = new OrderModel();
        $order      = $orderModel->getDetail($idx, $userIdx);

        if (!$order || $order['status'] === 'pending') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $items = (new OrderItemModel())->getByOrder($idx);
        return view('service/order/complete', ['order' => $order, 'items' => $items]);
    }

    /** GET /mypage/orders — 내 주문 목록 */
    public function myOrders(): string
    {
        $userIdx    = $this->userIdx();
        $orderModel = new OrderModel();
        $orders     = $orderModel->getMyOrders($userIdx);
        return view('service/mypage/orders', [
            'orders' => $orders,
            'pager'  => $orderModel->pager,
            'labels' => OrderModel::STATUS_LABELS,
        ]);
    }

    /** GET /mypage/orders/{idx} — 내 주문 상세 */
    public function myOrderDetail(int $idx): string
    {
        $userIdx  = $this->userIdx();
        $order    = (new OrderModel())->getDetail($idx, $userIdx);
        if (!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $items    = (new OrderItemModel())->getByOrder($idx);
        return view('service/mypage/order_detail', [
            'order'  => $order,
            'items'  => $items,
            'labels' => OrderModel::STATUS_LABELS,
        ]);
    }
}
```

- [ ] **Step 2: 주문서 뷰 작성 (`app/Views/service/order/form.php`)**

```php
<?php
// app/Views/service/order/form.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="order-section">
  <div class="container">
    <h2>주문서</h2>

    <div class="order-items-summary">
      <h3>주문 상품</h3>
      <?php foreach ($cartItems as $item): ?>
      <div class="order-item-row">
        <span><?= esc($item['goods_name']) ?></span>
        <?php if ($item['option_name']): ?>
          <span class="option-label"><?= esc($item['option_name'].': '.$item['option_value']) ?></span>
        <?php endif; ?>
        <span>수량: <?= $item['quantity'] ?></span>
        <span><?= number_format(($item['price'] + ($item['additional_price'] ?? 0)) * $item['quantity']) ?>원</span>
      </div>
      <?php endforeach; ?>
      <div class="order-total">총 결제금액: <strong><?= number_format($total) ?>원</strong></div>
    </div>

    <form id="order-form">
      <h3>배송 방법</h3>
      <label><input type="radio" name="delivery_type" value="1" checked onchange="toggleDelivery(1)"> 택배 배송</label>
      <label><input type="radio" name="delivery_type" value="2" onchange="toggleDelivery(2)"> 픽업 수령</label>

      <!-- 택배 배송지 -->
      <div id="section-delivery">
        <input type="text" name="recipient_name" placeholder="받는 분 이름" required>
        <input type="text" name="recipient_phone" placeholder="연락처 (010-xxxx-xxxx)" required>
        <input type="text" name="delivery_address" placeholder="배송 주소" required>
      </div>

      <!-- 픽업 장소 -->
      <div id="section-pickup" style="display:none">
        <select name="pickup_location_idx">
          <option value="">픽업 장소 선택</option>
          <?php foreach ($pickups as $p): ?>
          <option value="<?= $p['idx'] ?>"><?= esc($p['name']) ?> — <?= esc($p['address']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="button" onclick="requestOrder()">결제하기 (<?= number_format($total) ?>원)</button>
    </form>
  </div>
</section>

<!-- PortOne SDK -->
<script src="https://cdn.iamport.kr/v1/iamport.js"></script>
<script>
const TOTAL = <?= $total ?>;
// 발급받은 가맹점 식별코드 (imp_uid 아님, 가맹점코드)
const IMP_CODE = 'imp_yourcode';

function toggleDelivery(type) {
  document.getElementById('section-delivery').style.display = type === 1 ? '' : 'none';
  document.getElementById('section-pickup').style.display   = type === 2 ? '' : 'none';
}

async function requestOrder() {
  const form         = document.getElementById('order-form');
  const deliveryType = form.querySelector('[name="delivery_type"]:checked').value;
  const body         = { delivery_type: deliveryType };

  if (deliveryType === '1') {
    body.recipient_name   = form.recipient_name.value;
    body.recipient_phone  = form.recipient_phone.value;
    body.delivery_address = form.delivery_address.value;
    if (!body.recipient_name || !body.delivery_address) {
      return alert('배송지를 입력해주세요.');
    }
  } else {
    body.pickup_location_idx = form.pickup_location_idx.value;
    if (!body.pickup_location_idx) return alert('픽업 장소를 선택해주세요.');
  }

  // 1단계: 주문 레코드 생성
  const orderRes  = await fetch('/order/store', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(body),
  }).then(r => r.json());

  if (!orderRes.success) return alert(orderRes.message);

  // 2단계: PortOne 결제창 호출
  IMP.init(IMP_CODE);
  IMP.request_pay({
    pg:           'html5_inicis',
    pay_method:   'card',
    merchant_uid: orderRes.order_no,
    name:         '부산굿즈 주문',
    amount:       TOTAL,
    buyer_name:   body.recipient_name || '픽업 구매자',
    buyer_tel:    body.recipient_phone || '',
  }, async (rsp) => {
    if (!rsp.success) return alert('결제 실패: ' + rsp.error_msg);

    // 3단계: 서버 결제 검증
    const verifyRes = await fetch('/order/verify', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ imp_uid: rsp.imp_uid, order_idx: orderRes.order_idx }),
    }).then(r => r.json());

    if (verifyRes.success) {
      location.href = '/order/complete/' + verifyRes.order_idx;
    } else {
      alert('결제 검증 실패: ' + verifyRes.message);
    }
  });
}
</script>
<?php $this->endSection(); ?>
```

- [ ] **Step 3: 주문 완료 뷰 (`app/Views/service/order/complete.php`)**

```php
<?php
// app/Views/service/order/complete.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="order-complete-section">
  <div class="container text-center">
    <h2>주문이 완료되었습니다!</h2>
    <p>주문번호: <strong><?= esc($order['order_no']) ?></strong></p>
    <p>결제금액: <strong><?= number_format($order['total_price']) ?>원</strong></p>
    <?php if ($order['delivery_type'] == 1): ?>
      <p>배송지: <?= esc($order['delivery_address']) ?></p>
    <?php else: ?>
      <p>픽업 장소에서 수령해주세요.</p>
    <?php endif; ?>
    <div class="ordered-items">
      <?php foreach ($items as $item): ?>
      <div><?= esc($item['goods_name']) ?> × <?= $item['quantity'] ?></div>
      <?php endforeach; ?>
    </div>
    <a href="/mypage/orders" class="btn">주문 내역 보기</a>
    <a href="/goods" class="btn btn-outline">계속 쇼핑하기</a>
  </div>
</section>
<?php $this->endSection(); ?>
```

- [ ] **Step 4: 마이페이지 주문 목록 + 상세 뷰**

`app/Views/service/mypage/orders.php`:
```php
<?php
// app/Views/service/mypage/orders.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="mypage-section">
  <div class="container">
    <h2>주문 내역</h2>
    <?php if (empty($orders)): ?>
      <p class="empty-msg">주문 내역이 없습니다.</p>
    <?php else: ?>
    <table class="order-table">
      <thead><tr><th>주문번호</th><th>날짜</th><th>금액</th><th>상태</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= esc($o['order_no']) ?></td>
          <td><?= date('Y.m.d', strtotime($o['reg_date'])) ?></td>
          <td><?= number_format($o['total_price']) ?>원</td>
          <td><?= esc($labels[$o['status']] ?? $o['status']) ?></td>
          <td><a href="/mypage/orders/<?= $o['idx'] ?>">상세</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?= $pager?->links() ?>
    <?php endif; ?>
  </div>
</section>
<?php $this->endSection(); ?>
```

`app/Views/service/mypage/order_detail.php`:
```php
<?php
// app/Views/service/mypage/order_detail.php
$this->extend('service/partials/layout');
$this->section('content');
?>
<section class="mypage-section">
  <div class="container">
    <h2>주문 상세</h2>
    <p>주문번호: <strong><?= esc($order['order_no']) ?></strong></p>
    <p>상태: <strong><?= esc($labels[$order['status']] ?? $order['status']) ?></strong></p>
    <p>결제금액: <?= number_format($order['total_price']) ?>원</p>
    <p>배송방법: <?= $order['delivery_type'] == 1 ? '택배' : '픽업' ?></p>
    <?php if ($order['delivery_type'] == 1): ?>
      <p>배송지: <?= esc($order['delivery_address']) ?></p>
    <?php endif; ?>
    <h3>주문 상품</h3>
    <?php foreach ($items as $item): ?>
    <div class="order-item-row">
      <span><?= esc($item['goods_name']) ?></span>
      <?php if ($item['option_label']): ?>
        <span class="option-label"><?= esc($item['option_label']) ?></span>
      <?php endif; ?>
      <span>× <?= $item['quantity'] ?></span>
      <span><?= number_format($item['unit_price'] * $item['quantity']) ?>원</span>
    </div>
    <?php endforeach; ?>
    <a href="/mypage/orders" class="btn">목록으로</a>
  </div>
</section>
<?php $this->endSection(); ?>
```

---

## Task 9: 백오피스 — 상품 관리

**Files:**
- Create: `app/Controllers/BackofficeGoods.php`
- Create: `app/Views/backoffice/goods/list.php`
- Create: `app/Views/backoffice/goods/form.php`

- [ ] **Step 1: BackofficeGoods 컨트롤러 작성**

```php
<?php
// app/Controllers/BackofficeGoods.php
namespace App\Controllers;

use App\Models\GoodsModel;
use App\Models\PickupLocationModel;
use App\Models\VendorModel;

/**
 * 백오피스 — 굿즈 상품 관리 컨트롤러
 */
class BackofficeGoods extends BaseController
{
    private GoodsModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $req,
                                   \CodeIgniter\HTTP\ResponseInterface $res,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($req, $res, $logger);
        $this->model = new GoodsModel();
    }

    private function base(string $title, array $extra = []): array
    {
        return array_merge([
            'page_title'  => $title,
            'admin'       => ['idx' => session()->get('backoffice.idx'), 'id' => session()->get('backoffice.id')],
            'current_uri' => '/' . uri_string(),
        ], $extra);
    }

    /** GET backoffice/goods */
    public function list(): string
    {
        $q     = $this->request->getGet('q') ?? '';
        $state = $this->request->getGet('state') ?? '';
        if ($q !== '') $this->model->like('name', $q);
        if ($state !== '') $this->model->where('state', (int) $state);
        $items = $this->model->orderBy('idx', 'DESC')->paginate(20) ?? [];
        return view('backoffice/goods/list', $this->base('굿즈 관리', [
            'items' => $items, 'pager' => $this->model->pager, 'q' => $q, 'state' => $state,
        ]));
    }

    /** GET backoffice/goods/register */
    public function register(): string
    {
        $pickups = (new PickupLocationModel())->getActive();
        return view('backoffice/goods/form', $this->base('상품 등록', [
            'goods' => null, 'pickups' => $pickups,
        ]));
    }

    /** POST backoffice/goods/register */
    public function store()
    {
        $data = $this->buildData();
        $this->model->insert($data);
        return redirect()->to('/backoffice/goods')->with('success', '상품이 등록되었습니다.');
    }

    /** GET backoffice/goods/{idx}/edit */
    public function edit(int $idx): string
    {
        $goods   = $this->model->find($idx);
        $pickups = (new PickupLocationModel())->getActive();
        return view('backoffice/goods/form', $this->base('상품 수정', [
            'goods' => $goods, 'pickups' => $pickups,
        ]));
    }

    /** POST backoffice/goods/{idx}/edit */
    public function update(int $idx)
    {
        $this->model->update($idx, $this->buildData());
        return redirect()->to('/backoffice/goods')->with('success', '수정되었습니다.');
    }

    /** POST backoffice/goods/{idx}/state */
    public function toggleState(int $idx)
    {
        $goods    = $this->model->find($idx);
        $newState = $goods['state'] == 1 ? 0 : 1;
        $this->model->update($idx, ['state' => $newState]);
        return redirect()->back();
    }

    /** POST backoffice/goods/{idx}/delete */
    public function delete(int $idx)
    {
        $this->model->update($idx, ['state' => 9]);
        return redirect()->to('/backoffice/goods')->with('success', '삭제되었습니다.');
    }

    private function buildData(): array
    {
        return [
            'name'          => trim($this->request->getPost('name') ?? ''),
            'description'   => trim($this->request->getPost('description') ?? ''),
            'price'         => (int) ($this->request->getPost('price') ?? 0),
            'stock'         => (int) ($this->request->getPost('stock') ?? 0),
            'delivery_type' => (int) ($this->request->getPost('delivery_type') ?? 1),
            'thumbnail'     => trim($this->request->getPost('thumbnail') ?? ''),
            'state'         => 1,
            'edit_date'     => date('Y-m-d H:i:s'),
        ];
    }

    /** GET backoffice/pickup-locations */
    public function pickupList(): string
    {
        $model   = new PickupLocationModel();
        $pickups = $model->findAll();
        return view('backoffice/goods/pickup_list', $this->base('픽업 장소 관리', ['pickups' => $pickups]));
    }

    /** POST backoffice/pickup-locations/store */
    public function pickupStore()
    {
        $model = new PickupLocationModel();
        $model->insert([
            'name'    => trim($this->request->getPost('name') ?? ''),
            'address' => trim($this->request->getPost('address') ?? ''),
            'state'   => 1,
        ]);
        return redirect()->to('/backoffice/pickup-locations')->with('success', '장소가 추가되었습니다.');
    }

    /** POST backoffice/pickup-locations/{idx}/state */
    public function pickupToggle(int $idx)
    {
        $model    = new PickupLocationModel();
        $item     = $model->find($idx);
        $model->update($idx, ['state' => $item['state'] == 1 ? 0 : 1]);
        return redirect()->back();
    }
}
```

- [ ] **Step 2: 백오피스 상품 목록 뷰 (`app/Views/backoffice/goods/list.php`)**

기존 `backoffice/notice/list.php` 패턴을 따른다:
```php
<?php
// app/Views/backoffice/goods/list.php
$this->extend('backoffice/partials/layout');
$this->section('content');
?>
<div class="content-header">
  <h1>굿즈 관리</h1>
  <a href="/backoffice/goods/register" class="btn btn-primary">상품 등록</a>
</div>

<form method="get" class="search-form">
  <input type="text" name="q" value="<?= esc($q) ?>" placeholder="상품명 검색">
  <select name="state">
    <option value="">전체</option>
    <option value="1" <?= $state==='1'?'selected':'' ?>>판매중</option>
    <option value="0" <?= $state==='0'?'selected':'' ?>>중지</option>
  </select>
  <button type="submit">검색</button>
</form>

<table class="table">
  <thead><tr><th>idx</th><th>상품명</th><th>가격</th><th>재고</th><th>배송</th><th>상태</th><th>관리</th></tr></thead>
  <tbody>
    <?php foreach ($items as $item): ?>
    <tr>
      <td><?= $item['idx'] ?></td>
      <td><?= esc($item['name']) ?></td>
      <td><?= number_format($item['price']) ?>원</td>
      <td><?= $item['stock'] ?></td>
      <td><?= $item['delivery_type'] == 1 ? '택배' : '픽업' ?></td>
      <td>
        <form method="post" action="/backoffice/goods/<?= $item['idx'] ?>/state" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm <?= $item['state']==1?'btn-success':'btn-secondary' ?>">
            <?= $item['state']==1?'판매중':'중지' ?>
          </button>
        </form>
      </td>
      <td>
        <a href="/backoffice/goods/<?= $item['idx'] ?>/edit" class="btn btn-sm btn-info">수정</a>
        <form method="post" action="/backoffice/goods/<?= $item['idx'] ?>/delete" style="display:inline"
              onsubmit="return confirm('삭제하시겠습니까?')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-danger">삭제</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $pager?->links() ?>
<?php $this->endSection(); ?>
```

- [ ] **Step 3: 백오피스 상품 등록/수정 폼 뷰 (`app/Views/backoffice/goods/form.php`)**

```php
<?php
// app/Views/backoffice/goods/form.php
$this->extend('backoffice/partials/layout');
$this->section('content');
$isEdit = $goods !== null;
$action = $isEdit ? "/backoffice/goods/{$goods['idx']}/edit" : '/backoffice/goods/register';
?>
<div class="content-header">
  <h1><?= $isEdit ? '상품 수정' : '상품 등록' ?></h1>
</div>

<form method="post" action="<?= $action ?>">
  <?= csrf_field() ?>
  <div class="form-group">
    <label>상품명</label>
    <input type="text" name="name" value="<?= esc($goods['name'] ?? '') ?>" required>
  </div>
  <div class="form-group">
    <label>가격 (원)</label>
    <input type="number" name="price" value="<?= $goods['price'] ?? 0 ?>" min="0" required>
  </div>
  <div class="form-group">
    <label>재고</label>
    <input type="number" name="stock" value="<?= $goods['stock'] ?? 0 ?>" min="0" required>
  </div>
  <div class="form-group">
    <label>배송 방법</label>
    <select name="delivery_type">
      <option value="1" <?= ($goods['delivery_type'] ?? 1)==1?'selected':'' ?>>택배</option>
      <option value="2" <?= ($goods['delivery_type'] ?? 1)==2?'selected':'' ?>>픽업</option>
    </select>
  </div>
  <div class="form-group">
    <label>썸네일 URL</label>
    <input type="text" name="thumbnail" value="<?= esc($goods['thumbnail'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label>상품 설명</label>
    <textarea name="description" rows="6"><?= esc($goods['description'] ?? '') ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary"><?= $isEdit ? '수정' : '등록' ?></button>
  <a href="/backoffice/goods" class="btn">취소</a>
</form>
<?php $this->endSection(); ?>
```

---

## Task 10: 백오피스 — 주문 관리

**Files:**
- Create: `app/Controllers/BackofficeOrders.php`
- Create: `app/Views/backoffice/orders/list.php`
- Create: `app/Views/backoffice/orders/detail.php`

- [ ] **Step 1: BackofficeOrders 컨트롤러 작성**

```php
<?php
// app/Controllers/BackofficeOrders.php
namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\DeliveryModel;

/**
 * 백오피스 — 주문 관리 컨트롤러
 */
class BackofficeOrders extends BaseController
{
    private OrderModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $req,
                                   \CodeIgniter\HTTP\ResponseInterface $res,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($req, $res, $logger);
        $this->model = new OrderModel();
    }

    private function base(string $title, array $extra = []): array
    {
        return array_merge([
            'page_title'  => $title,
            'admin'       => ['idx' => session()->get('backoffice.idx')],
            'current_uri' => '/' . uri_string(),
        ], $extra);
    }

    /** GET backoffice/orders */
    public function list(): string
    {
        $status = $this->request->getGet('status') ?? '';
        $q      = $this->request->getGet('q') ?? '';
        $orders = $this->model->getAdminList($status, $q);
        return view('backoffice/orders/list', $this->base('주문 관리', [
            'orders'  => $orders,
            'pager'   => $this->model->pager,
            'status'  => $status,
            'q'       => $q,
            'labels'  => OrderModel::STATUS_LABELS,
        ]));
    }

    /** GET backoffice/orders/{idx} */
    public function detail(int $idx): string
    {
        $order    = $this->model->find($idx);
        $items    = (new OrderItemModel())->getByOrder($idx);
        $delivery = (new DeliveryModel())->getByOrder($idx);
        return view('backoffice/orders/detail', $this->base('주문 상세', [
            'order'    => $order,
            'items'    => $items,
            'delivery' => $delivery,
            'labels'   => OrderModel::STATUS_LABELS,
            'couriers' => DeliveryModel::COURIERS,
        ]));
    }

    /** POST backoffice/orders/{idx}/status — { status } */
    public function updateStatus(int $idx)
    {
        $status = $this->request->getPost('status') ?? '';
        if (array_key_exists($status, OrderModel::STATUS_LABELS)) {
            $this->model->update($idx, ['status' => $status]);
        }
        return redirect()->to("/backoffice/orders/{$idx}");
    }

    /** POST backoffice/orders/{idx}/delivery — { courier, tracking_no } */
    public function saveDelivery(int $idx)
    {
        $courier    = trim($this->request->getPost('courier') ?? '');
        $trackingNo = trim($this->request->getPost('tracking_no') ?? '');
        (new DeliveryModel())->upsert($idx, $courier, $trackingNo);
        $this->model->update($idx, ['status' => 'shipped']);
        return redirect()->to("/backoffice/orders/{$idx}")->with('success', '송장이 저장되었습니다.');
    }
}
```

- [ ] **Step 2: 백오피스 주문 목록 뷰 (`app/Views/backoffice/orders/list.php`)**

```php
<?php
// app/Views/backoffice/orders/list.php
$this->extend('backoffice/partials/layout');
$this->section('content');
?>
<div class="content-header"><h1>주문 관리</h1></div>

<form method="get" class="search-form">
  <input type="text" name="q" value="<?= esc($q) ?>" placeholder="주문번호 검색">
  <select name="status">
    <option value="">전체 상태</option>
    <?php foreach ($labels as $key => $label): ?>
    <option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">검색</button>
</form>

<table class="table">
  <thead><tr><th>주문번호</th><th>일자</th><th>금액</th><th>배송</th><th>상태</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= esc($o['order_no']) ?></td>
      <td><?= date('Y.m.d H:i', strtotime($o['reg_date'])) ?></td>
      <td><?= number_format($o['total_price']) ?>원</td>
      <td><?= $o['delivery_type']==1?'택배':'픽업' ?></td>
      <td><?= esc($labels[$o['status']] ?? $o['status']) ?></td>
      <td><a href="/backoffice/orders/<?= $o['idx'] ?>" class="btn btn-sm">상세</a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $pager?->links() ?>
<?php $this->endSection(); ?>
```

- [ ] **Step 3: 백오피스 주문 상세 뷰 (`app/Views/backoffice/orders/detail.php`)**

```php
<?php
// app/Views/backoffice/orders/detail.php
$this->extend('backoffice/partials/layout');
$this->section('content');
?>
<div class="content-header"><h1>주문 상세 — <?= esc($order['order_no']) ?></h1></div>

<p>상태: <strong><?= esc($labels[$order['status']] ?? $order['status']) ?></strong></p>
<p>결제금액: <?= number_format($order['total_price']) ?>원</p>
<p>배송방법: <?= $order['delivery_type']==1?'택배':'픽업' ?></p>
<?php if ($order['delivery_type']==1): ?>
<p>배송지: <?= esc($order['delivery_address']) ?> / <?= esc($order['recipient_name']) ?> <?= esc($order['recipient_phone']) ?></p>
<?php endif; ?>

<h3>주문 상품</h3>
<table class="table">
  <thead><tr><th>상품</th><th>옵션</th><th>수량</th><th>단가</th></tr></thead>
  <tbody>
    <?php foreach ($items as $item): ?>
    <tr>
      <td><?= esc($item['goods_name']) ?></td>
      <td><?= esc($item['option_label'] ?? '-') ?></td>
      <td><?= $item['quantity'] ?></td>
      <td><?= number_format($item['unit_price']) ?>원</td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- 상태 변경 -->
<form method="post" action="/backoffice/orders/<?= $order['idx'] ?>/status" class="inline-form">
  <?= csrf_field() ?>
  <select name="status">
    <?php foreach ($labels as $key => $label): ?>
    <option value="<?= $key ?>" <?= $order['status']===$key?'selected':'' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-primary">상태 변경</button>
</form>

<?php if ($order['delivery_type'] == 1): ?>
<!-- 송장번호 입력 -->
<h3>배송 정보</h3>
<?php if ($delivery): ?>
<p>택배사: <?= esc($delivery['courier']) ?> / 송장: <?= esc($delivery['tracking_no']) ?></p>
<?php endif; ?>
<form method="post" action="/backoffice/orders/<?= $order['idx'] ?>/delivery">
  <?= csrf_field() ?>
  <select name="courier">
    <?php foreach ($couriers as $c): ?>
    <option value="<?= $c ?>" <?= ($delivery['courier'] ?? '')===$c?'selected':'' ?>><?= $c ?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="tracking_no" value="<?= esc($delivery['tracking_no'] ?? '') ?>" placeholder="송장번호">
  <button type="submit" class="btn btn-success">송장 저장</button>
</form>
<?php endif; ?>
<?php $this->endSection(); ?>
```

---

## Task 11: 백오피스 — 판매자 관리

**Files:**
- Create: `app/Controllers/BackofficeVendors.php`
- Create: `app/Views/backoffice/vendors/list.php`
- Create: `app/Views/backoffice/vendors/detail.php`

- [ ] **Step 1: BackofficeVendors 컨트롤러 작성**

```php
<?php
// app/Controllers/BackofficeVendors.php
namespace App\Controllers;

use App\Models\VendorModel;

/**
 * 백오피스 — 판매자 관리 컨트롤러
 */
class BackofficeVendors extends BaseController
{
    private VendorModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $req,
                                   \CodeIgniter\HTTP\ResponseInterface $res,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($req, $res, $logger);
        $this->model = new VendorModel();
    }

    private function base(string $title, array $extra = []): array
    {
        return array_merge([
            'page_title'  => $title,
            'admin'       => ['idx' => session()->get('backoffice.idx')],
            'current_uri' => '/' . uri_string(),
        ], $extra);
    }

    public static array $STATE_LABELS = [0 => '대기', 1 => '승인', 2 => '거절'];

    /** GET backoffice/vendors */
    public function list(): string
    {
        $state   = $this->request->getGet('state') ?? '';
        $vendors = $this->model->getList($state);
        return view('backoffice/vendors/list', $this->base('판매자 관리', [
            'vendors' => $vendors,
            'pager'   => $this->model->pager,
            'state'   => $state,
            'labels'  => self::$STATE_LABELS,
        ]));
    }

    /** GET backoffice/vendors/{idx} */
    public function detail(int $idx): string
    {
        $vendor = $this->model->find($idx);
        return view('backoffice/vendors/detail', $this->base('판매자 상세', [
            'vendor' => $vendor,
            'labels' => self::$STATE_LABELS,
        ]));
    }

    /** POST backoffice/vendors/{idx}/approve */
    public function approve(int $idx)
    {
        $this->model->update($idx, ['state' => 1]);
        return redirect()->to("/backoffice/vendors/{$idx}")->with('success', '승인되었습니다.');
    }

    /** POST backoffice/vendors/{idx}/reject */
    public function reject(int $idx)
    {
        $this->model->update($idx, ['state' => 2]);
        return redirect()->to("/backoffice/vendors/{$idx}")->with('success', '거절되었습니다.');
    }
}
```

- [ ] **Step 2: 판매자 목록/상세 뷰 작성**

`app/Views/backoffice/vendors/list.php`:
```php
<?php
// app/Views/backoffice/vendors/list.php
$this->extend('backoffice/partials/layout');
$this->section('content');
?>
<div class="content-header"><h1>판매자 관리</h1></div>
<form method="get" class="search-form">
  <select name="state">
    <option value="">전체</option>
    <?php foreach ($labels as $k => $v): ?>
    <option value="<?= $k ?>" <?= $state==(string)$k?'selected':'' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">필터</button>
</form>
<table class="table">
  <thead><tr><th>idx</th><th>상점명</th><th>연락처</th><th>상태</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($vendors as $v): ?>
    <tr>
      <td><?= $v['idx'] ?></td>
      <td><?= esc($v['shop_name']) ?></td>
      <td><?= esc($v['contact'] ?? '-') ?></td>
      <td><?= esc($labels[$v['state']] ?? $v['state']) ?></td>
      <td><a href="/backoffice/vendors/<?= $v['idx'] ?>" class="btn btn-sm">상세</a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $pager?->links() ?>
<?php $this->endSection(); ?>
```

`app/Views/backoffice/vendors/detail.php`:
```php
<?php
// app/Views/backoffice/vendors/detail.php
$this->extend('backoffice/partials/layout');
$this->section('content');
?>
<h1>판매자 상세</h1>
<p>상점명: <?= esc($vendor['shop_name']) ?></p>
<p>연락처: <?= esc($vendor['contact'] ?? '-') ?></p>
<p>메모: <?= nl2br(esc($vendor['note'] ?? '')) ?></p>
<p>현재 상태: <strong><?= esc($labels[$vendor['state']]) ?></strong></p>

<?php if ($vendor['state'] == 0): ?>
<form method="post" action="/backoffice/vendors/<?= $vendor['idx'] ?>/approve" style="display:inline">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-success">승인</button>
</form>
<form method="post" action="/backoffice/vendors/<?= $vendor['idx'] ?>/reject" style="display:inline">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-danger">거절</button>
</form>
<?php endif; ?>

<a href="/backoffice/vendors" class="btn">목록</a>
<?php $this->endSection(); ?>
```

---

## Self-Review

### Spec Coverage

| 요구사항 | Task |
|---------|------|
| 실물 굿즈 상품 목록/상세 | Task 6 |
| 장바구니 CRUD | Task 7 |
| 주문서 (택배/픽업 분기) | Task 8 |
| PortOne PG 결제 + 서버 검증 | Task 4, 8 |
| 재고 차감 | Task 2 (GoodsModel.decreaseStock), Task 8 (Order.verify) |
| 마이페이지 주문내역 | Task 8 |
| 백오피스 상품 관리 | Task 9 |
| 백오피스 주문 관리 + 송장 | Task 10 |
| 백오피스 판매자 승인 | Task 11 |
| 픽업 고정 장소 관리 | Task 9 (pickupList/pickupStore) |
| 라우트 등록 | Task 5 |

### Placeholder Scan

- PortOne `IMP_CODE` 상수 (`order/form.php` 30번째줄): 실제 가맹점 코드로 교체 필요 — `.env`에 `PORTONE_IMP_CODE`를 추가하고 view에서 `env('PORTONE_IMP_CODE')`로 출력하도록 변경하면 완벽.
- 이미지 업로드: 현재 썸네일은 URL 직접 입력. 파일 업로드는 2단계 작업으로 별도 계획.

### Type Consistency

- `CartModel.getCartItems()` 반환: `goods_idx`, `option_value_idx`, `quantity`, `price`, `additional_price`, `goods_name`, `option_name`, `option_value`, `delivery_type` — `Cart::index()`와 `Order::store()`에서 동일 키 사용 ✓
- `OrderModel.generateOrderNo()` 반환 형식 `BO-YYYYMMDD-NNNN` — `order/form.php`의 `merchant_uid`와 일치 ✓
- `DeliveryModel.upsert()` 파라미터 `(int, string, string)` — `BackofficeOrders.saveDelivery()`에서 동일하게 호출 ✓
