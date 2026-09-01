<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>주문 상세 | 부산온나</title>
    <meta name="description" content="부산온나 마이페이지 - 주문 상세 내역을 확인하세요.">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 주문 상세 페이지 전용 스타일 ---- */
        .order-detail-section {
            padding: 48px 0 80px;
        }

        /* 뒤로 가기 링크 */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #868e96;
            text-decoration: none;
            margin-bottom: 28px;
            transition: color .15s;
        }
        .back-link:hover { color: #e55039; }

        /* 페이지 타이틀 */
        .order-detail-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 28px;
        }

        /* 정보 카드 공통 */
        .detail-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 20px;
        }
        .detail-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        /* 정보 목록 */
        .info-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .info-list li {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 9px 0;
            border-bottom: 1px solid #f1f3f5;
            gap: 12px;
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #868e96;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .info-value {
            font-weight: 600;
            color: #212529;
            text-align: right;
            word-break: break-all;
        }
        .info-value.accent {
            color: #e55039;
            font-size: 16px;
        }

        /* 상태 뱃지 */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending  { background: #fff3cd; color: #856404; }
        .status-paid     { background: #d1ecf1; color: #0c5460; }
        .status-shipping { background: #d4edda; color: #155724; }
        .status-done     { background: #e2e3e5; color: #383d41; }
        .status-cancel   { background: #f8d7da; color: #721c24; }
        .status-default  { background: #f8f9fa; color: #495057; }

        /* 주문 상품 테이블 */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .items-table thead th {
            padding: 10px 14px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        .items-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        .items-table tbody tr:last-child {
            border-bottom: none;
        }
        .items-table td {
            padding: 14px;
            vertical-align: middle;
        }
        .item-goods-name {
            font-weight: 600;
            margin: 0 0 4px;
        }
        .item-option {
            font-size: 12px;
            color: #868e96;
        }
        .item-price {
            font-weight: 700;
            color: #e55039;
            white-space: nowrap;
        }

        /* 총 결제금액 합계 행 */
        .items-total-row td {
            padding-top: 16px;
            border-top: 2px solid #dee2e6;
        }

        /* 목록으로 버튼 */
        .btn-back-list {
            display: inline-block;
            padding: 12px 28px;
            background: #fff;
            color: #495057;
            font-size: 15px;
            font-weight: 600;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            text-decoration: none;
            margin-top: 8px;
            transition: border-color .2s, color .2s;
        }
        .btn-back-list:hover {
            border-color: #e55039;
            color: #e55039;
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'mypage']) ?>

<!-- ===================== 페이지 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>주문 상세</h1>
        <p>주문 상세 내역을 확인하세요</p>
    </div>
</section>

<!-- ===================== 주문 상세 본문 ===================== -->
<section class="order-detail-section">
    <div class="container">

        <!-- 목록으로 돌아가기 -->
        <a href="/mypage/orders" class="back-link">
            ← 주문 목록으로
        </a>

        <h2 class="order-detail-title">주문번호: <?= esc($order['order_no']) ?></h2>

        <?php
        /* 상태 CSS 클래스 결정 */
        $statusClass = match($order['status'] ?? '') {
            'pending'  => 'status-pending',
            'paid'     => 'status-paid',
            'shipping' => 'status-shipping',
            'done'     => 'status-done',
            'cancel'   => 'status-cancel',
            default    => 'status-default',
        };
        $statusLabel = $labels[$order['status'] ?? ''] ?? esc($order['status'] ?? '-');
        ?>

        <!-- 주문 기본 정보 -->
        <div class="detail-card">
            <h3>주문 정보</h3>
            <ul class="info-list">
                <li>
                    <span class="info-label">주문번호</span>
                    <span class="info-value"><?= esc($order['order_no']) ?></span>
                </li>
                <li>
                    <span class="info-label">주문일시</span>
                    <span class="info-value"><?= esc($order['created_at'] ?? '-') ?></span>
                </li>
                <li>
                    <span class="info-label">결제금액</span>
                    <span class="info-value accent"><?= number_format((int)$order['total_price']) ?>원</span>
                </li>
                <li>
                    <span class="info-label">주문 상태</span>
                    <span class="info-value">
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </span>
                </li>
                <li>
                    <span class="info-label">배송 방법</span>
                    <span class="info-value">
                        <?= (int)($order['delivery_type'] ?? 1) === 1 ? '택배 배송' : '픽업' ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- 택배 배송지 정보 (택배 주문에만 표시) -->
        <?php if ((int)($order['delivery_type'] ?? 1) === 1): ?>
        <div class="detail-card">
            <h3>배송지 정보</h3>
            <ul class="info-list">
                <?php if (!empty($order['recipient_name'])): ?>
                <li>
                    <span class="info-label">수령인</span>
                    <span class="info-value"><?= esc($order['recipient_name']) ?></span>
                </li>
                <?php endif; ?>
                <?php if (!empty($order['recipient_phone'])): ?>
                <li>
                    <span class="info-label">연락처</span>
                    <span class="info-value"><?= esc($order['recipient_phone']) ?></span>
                </li>
                <?php endif; ?>
                <?php if (!empty($order['delivery_address'])): ?>
                <li>
                    <span class="info-label">배송지</span>
                    <span class="info-value"><?= esc($order['delivery_address']) ?></span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- 주문 상품 목록 -->
        <?php if (!empty($items)): ?>
        <div class="detail-card">
            <h3>주문 상품</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>상품명 / 옵션</th>
                        <th>단가</th>
                        <th>수량</th>
                        <th>금액</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <?php $itemTotal = (int)$item['unit_price'] * (int)$item['quantity']; ?>
                <tr>
                    <td>
                        <p class="item-goods-name"><?= esc($item['goods_name']) ?></p>
                        <?php if (!empty($item['option_label'])): ?>
                        <span class="item-option"><?= esc($item['option_label']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((int)$item['unit_price']) ?>원</td>
                    <td><?= (int)$item['quantity'] ?>개</td>
                    <td class="item-price"><?= number_format($itemTotal) ?>원</td>
                </tr>
                <?php endforeach; ?>
                <!-- 총 합계 행 -->
                <tr class="items-total-row">
                    <td colspan="3" style="text-align: right; font-weight: 700; color: #495057;">총 결제금액</td>
                    <td class="item-price" style="font-size: 16px;">
                        <?= number_format((int)$order['total_price']) ?>원
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- 목록으로 버튼 -->
        <a href="/mypage/orders" class="btn-back-list">← 목록으로</a>

    </div>
</section>

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

</body>
</html>
