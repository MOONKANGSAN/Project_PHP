<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>내 주문 내역 | 부산온나</title>
    <meta name="description" content="부산온나 마이페이지 - 내 주문 내역을 확인하세요.">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 마이페이지 주문 목록 전용 스타일 ---- */
        .mypage-section {
            padding: 116px 0 80px; /* 68px 고정 네비바 + 48px 여백 */
        }
        .mypage-section h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        /* 주문 없음 안내 */
        .orders-empty {
            text-align: center;
            padding: 80px 20px;
            color: #adb5bd;
        }
        .orders-empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        .orders-empty h3 {
            font-size: 20px;
            color: #868e96;
            margin: 0 0 24px;
        }
        .btn-go-goods {
            display: inline-block;
            padding: 12px 28px;
            background: #e55039;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-go-goods:hover { background: #c0392b; }

        /* 주문 테이블 */
        .orders-table-wrap {
            overflow-x: auto;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .orders-table thead th {
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        .orders-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: background .1s;
        }
        .orders-table tbody tr:hover {
            background: #fafafa;
        }
        .orders-table td {
            padding: 16px;
            vertical-align: middle;
        }

        /* 주문번호 링크 */
        .order-no-link {
            font-weight: 600;
            color: #e55039;
            text-decoration: none;
        }
        .order-no-link:hover {
            text-decoration: underline;
        }

        /* 금액 */
        .order-price {
            font-weight: 700;
            color: #212529;
            white-space: nowrap;
        }

        /* 상태 뱃지 */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-pending  { background: #fff3cd; color: #856404; }
        .status-paid     { background: #d1ecf1; color: #0c5460; }
        .status-shipping { background: #d4edda; color: #155724; }
        .status-done     { background: #e2e3e5; color: #383d41; }
        .status-cancel   { background: #f8d7da; color: #721c24; }
        .status-default  { background: #f8f9fa; color: #495057; }

        /* 상세 보기 버튼 */
        .btn-detail {
            display: inline-block;
            padding: 6px 16px;
            background: #fff;
            color: #495057;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-decoration: none;
            transition: border-color .15s, color .15s;
            white-space: nowrap;
        }
        .btn-detail:hover {
            border-color: #e55039;
            color: #e55039;
        }

        /* 페이저 */
        .pager-wrap {
            margin-top: 32px;
            display: flex;
            justify-content: center;
        }
        .pager-wrap nav {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        .pager-wrap a,
        .pager-wrap span {
            display: inline-block;
            padding: 8px 14px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            color: #495057;
            transition: background .15s, color .15s;
        }
        .pager-wrap a:hover {
            background: #e55039;
            color: #fff;
            border-color: #e55039;
        }
        .pager-wrap .active {
            background: #e55039;
            color: #fff;
            border-color: #e55039;
            font-weight: 700;
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'mypage']) ?>

<!-- ===================== 페이지 히어로 ===================== -->
<section class="page-hero">
    <div class="container">
        <h1>내 주문 내역</h1>
        <p>지금까지 주문하신 내역을 확인하세요</p>
    </div>
</section>

<!-- ===================== 주문 목록 본문 ===================== -->
<section class="mypage-section">
    <div class="container">

        <?php if (empty($orders)): ?>
        <!-- 주문 내역 없음 -->
        <div class="orders-empty">
            <div class="orders-empty-icon">📦</div>
            <h3>주문 내역이 없습니다</h3>
            <a href="/goods" class="btn-go-goods">굿즈 보러가기</a>
        </div>

        <?php else: ?>

        <!-- 주문 목록 테이블 -->
        <div class="orders-table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>주문일</th>
                        <th>결제금액</th>
                        <th>배송 방법</th>
                        <th>상태</th>
                        <th>상세</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                <?php
                /* 상태에 따른 CSS 클래스 결정 */
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
                <tr>
                    <!-- 주문번호 (상세 페이지 링크) -->
                    <td>
                        <a href="/mypage/orders/<?= (int)$order['idx'] ?>" class="order-no-link">
                            <?= esc($order['order_no']) ?>
                        </a>
                    </td>

                    <!-- 주문일 -->
                    <td><?= esc(substr($order['created_at'] ?? '', 0, 10)) ?></td>

                    <!-- 결제금액 -->
                    <td class="order-price"><?= number_format((int)$order['total_price']) ?>원</td>

                    <!-- 배송 방법 -->
                    <td>
                        <?= (int)($order['delivery_type'] ?? 1) === 1 ? '택배' : '픽업' ?>
                    </td>

                    <!-- 상태 뱃지 -->
                    <td>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </td>

                    <!-- 상세 보기 -->
                    <td>
                        <a href="/mypage/orders/<?= (int)$order['idx'] ?>" class="btn-detail">상세 보기</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 페이저 -->
        <?php if (!empty($pager)): ?>
        <div class="pager-wrap">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>

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
