<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>주문 완료 | 부산온나</title>
    <meta name="description" content="부산온나 주문이 완료되었습니다.">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 주문 완료 페이지 전용 스타일 ---- */
        .complete-section {
            padding: 128px 0 100px; /* 68px 고정 네비바 + 60px 여백 */
            text-align: center;
        }

        /* 완료 아이콘 + 타이틀 */
        .complete-icon {
            font-size: 72px;
            margin-bottom: 20px;
        }
        .complete-title {
            font-size: 28px;
            font-weight: 800;
            color: #212529;
            margin: 0 0 10px;
        }
        .complete-sub {
            font-size: 16px;
            color: #868e96;
            margin-bottom: 40px;
        }

        /* 주문 정보 카드 */
        .complete-card {
            max-width: 560px;
            margin: 0 auto 40px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 32px;
            text-align: left;
        }
        .complete-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        /* 주문 정보 목록 */
        .info-list {
            list-style: none;
            margin: 0 0 20px;
            padding: 0;
        }
        .info-list li {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f5;
            gap: 12px;
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #868e96;
            white-space: nowrap;
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

        /* 주문 상품 테이블 */
        .complete-items {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .complete-items h4 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 12px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f5;
            gap: 12px;
        }
        .item-row:last-child {
            border-bottom: none;
        }
        .item-name-wrap {
            flex: 1;
        }
        .item-goods-name {
            font-weight: 600;
            margin: 0 0 3px;
        }
        .item-option {
            font-size: 12px;
            color: #868e96;
        }
        .item-amount {
            white-space: nowrap;
            font-weight: 700;
            color: #e55039;
        }

        /* 버튼 영역 */
        .complete-btns {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-block;
            padding: 14px 32px;
            background: #e55039;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            border-radius: 10px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-primary:hover { background: #c0392b; }
        .btn-outline {
            display: inline-block;
            padding: 14px 32px;
            background: #fff;
            color: #495057;
            font-size: 15px;
            font-weight: 600;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            text-decoration: none;
            transition: border-color .2s, color .2s;
        }
        .btn-outline:hover {
            border-color: #e55039;
            color: #e55039;
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'goods']) ?>

<!-- ===================== 주문 완료 본문 ===================== -->
<section class="complete-section">
    <div class="container">

        <!-- 완료 메시지 -->
        <div class="complete-icon">🎉</div>
        <h1 class="complete-title">주문이 완료되었습니다!</h1>
        <p class="complete-sub">주문해주셔서 감사합니다. 주문 내역은 마이페이지에서 확인하실 수 있습니다.</p>

        <!-- 주문 정보 카드 -->
        <div class="complete-card">
            <h3>주문 정보</h3>
            <ul class="info-list">
                <li>
                    <span class="info-label">주문번호</span>
                    <span class="info-value"><?= esc($order['order_no']) ?></span>
                </li>
                <li>
                    <span class="info-label">결제금액</span>
                    <span class="info-value accent"><?= number_format((int)$order['total_price']) ?>원</span>
                </li>
                <li>
                    <span class="info-label">배송 방법</span>
                    <span class="info-value">
                        <?php if ((int)$order['delivery_type'] === 1): ?>
                            택배 배송
                        <?php else: ?>
                            픽업
                        <?php endif; ?>
                    </span>
                </li>
                <?php if ((int)$order['delivery_type'] === 1 && !empty($order['delivery_address'])): ?>
                <li>
                    <span class="info-label">배송지</span>
                    <span class="info-value"><?= esc($order['delivery_address']) ?></span>
                </li>
                <?php endif; ?>
                <?php if ((int)$order['delivery_type'] === 1 && !empty($order['recipient_name'])): ?>
                <li>
                    <span class="info-label">수령인</span>
                    <span class="info-value">
                        <?= esc($order['recipient_name']) ?>
                        <?php if (!empty($order['recipient_phone'])): ?>
                        / <?= esc($order['recipient_phone']) ?>
                        <?php endif; ?>
                    </span>
                </li>
                <?php endif; ?>
            </ul>

            <!-- 주문 상품 목록 -->
            <?php if (!empty($items)): ?>
            <div class="complete-items">
                <h4>주문 상품</h4>
                <?php foreach ($items as $item): ?>
                <?php $itemTotal = (int)$item['unit_price'] * (int)$item['quantity']; ?>
                <div class="item-row">
                    <div class="item-name-wrap">
                        <p class="item-goods-name"><?= esc($item['goods_name']) ?></p>
                        <?php if (!empty($item['option_label'])): ?>
                        <span class="item-option"><?= esc($item['option_label']) ?></span>
                        <?php endif; ?>
                        <span class="item-option">수량: <?= (int)$item['quantity'] ?>개</span>
                    </div>
                    <span class="item-amount"><?= number_format($itemTotal) ?>원</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- 하단 버튼 -->
        <div class="complete-btns">
            <a href="/mypage/orders" class="btn-primary">주문 내역 보기</a>
            <a href="/goods" class="btn-outline">계속 쇼핑하기</a>
        </div>

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
