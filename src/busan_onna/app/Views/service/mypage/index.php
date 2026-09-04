<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마이페이지 | 부산온나</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/busan.css">
    <link rel="stylesheet" href="/css/modules/auth-common.css">
    <link rel="stylesheet" href="/css/modules/login.css">
    <link rel="stylesheet" href="/css/modules/signup.css">

    <style>
        /* ---- 마이페이지 전체 레이아웃 ---- */
        .mypage-body {
            background: #f4f6f9;
            min-height: calc(100vh - 68px);
            padding: 100px 0 80px;
        }
        .mypage-wrap {
            width: 50%;
            min-width: 600px;
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ---- 카드 공통 ---- */
        .mypage-card {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        /* ---- 프로필 영역 ---- */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            border: 3px solid #dee2e6;
        }
        .profile-avatar svg {
            width: 44px;
            height: 44px;
            color: #adb5bd;
        }
        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .profile-greeting {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
        }
        .profile-greeting span { color: #e55039; }
        .btn-profile-edit {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: #868e96;
            text-decoration: none;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 4px 14px;
            width: fit-content;
            transition: border-color .15s, color .15s;
        }
        .btn-profile-edit:hover { border-color: #e55039; color: #e55039; }

        /* ---- 주문 현황 영역 ---- */
        .order-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .order-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }
        .btn-all-orders {
            font-size: 12px;
            color: #868e96;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: color .15s;
        }
        .btn-all-orders:hover { color: #e55039; }

        /* 탭 버튼 */
        .order-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 28px;
        }
        .order-tab-btn {
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #adb5bd;
            cursor: pointer;
            transition: color .15s, border-color .15s;
        }
        .order-tab-btn.active { color: #e55039; border-bottom-color: #e55039; }
        .order-tab-btn:hover:not(.active) { color: #495057; }

        /* 탭 패널 */
        .order-tab-panel { display: none; }
        .order-tab-panel.active { display: block; }

        /* 주문 진행 단계 (스텝 바) */
        .order-steps {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
        }
        .order-steps::before {
            content: '';
            position: absolute;
            top: 16px;
            left: calc(100% / 8);
            right: calc(100% / 8);
            height: 2px;
            background: #dee2e6;
            z-index: 0;
        }
        .order-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            flex: 1;
            position: relative;
            z-index: 1;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .step-circle.has-count { background: #e55039; }
        .step-circle span {
            font-size: 13px;
            font-weight: 700;
            color: #adb5bd;
        }
        .step-circle.has-count span { color: #fff; }
        .step-label {
            font-size: 12px;
            color: #868e96;
            text-align: center;
            white-space: nowrap;
        }
        .step-arrow {
            font-size: 14px;
            color: #dee2e6;
            margin-top: 8px;
            flex-shrink: 0;
        }

        /* 주문 리스트 (주문내역 탭) */
        .order-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .order-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f3f5;
            text-decoration: none;
            color: inherit;
            transition: background .15s;
            cursor: pointer;
        }
        .order-list-item:last-child { border-bottom: none; }
        .order-list-item:hover { background: #fafafa; border-radius: 8px; padding-left: 8px; padding-right: 8px; margin: 0 -8px; }
        .order-list-left { flex: 1; min-width: 0; }
        .order-list-goods {
            font-size: 14px;
            font-weight: 600;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }
        .order-list-meta {
            font-size: 12px;
            color: #adb5bd;
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .order-list-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
            flex-shrink: 0;
        }
        .order-list-price {
            font-size: 15px;
            font-weight: 700;
            color: #e55039;
        }
        /* 상태 배지 */
        .order-status-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .badge-paid      { background: #dbeafe; color: #1d4ed8; }
        .badge-preparing { background: #fef3c7; color: #92400e; }
        .badge-shipped   { background: #ede9fe; color: #6d28d9; }
        .badge-delivered { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .badge-pending   { background: #f1f3f5; color: #868e96; }

        .order-list-empty {
            text-align: center;
            padding: 36px 0;
            color: #adb5bd;
            font-size: 14px;
        }
        .order-chevron {
            color: #ced4da;
            flex-shrink: 0;
        }

        /* ---- 좋아요 명소 영역 ---- */
        .likes-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
        }
        .likes-card-list {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        .likes-card-list::-webkit-scrollbar { height: 4px; }
        .likes-card-list::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }
        .place-card {
            flex-shrink: 0;
            width: 140px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e9ecef;
            text-decoration: none;
            color: inherit;
            transition: box-shadow .2s, transform .2s;
        }
        .place-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.12); transform: translateY(-2px); }
        .place-card-thumb {
            width: 100%;
            height: 110px;
            object-fit: cover;
            background: #e9ecef;
            display: block;
        }
        .place-card-thumb-placeholder {
            width: 100%;
            height: 110px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .place-card-thumb-placeholder svg { width: 32px; height: 32px; color: #ced4da; }
        .place-card-body { padding: 10px 12px; }
        .place-card-name {
            font-size: 13px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .place-card-type { font-size: 11px; color: #adb5bd; }
        .likes-empty { text-align: center; padding: 40px 20px; color: #adb5bd; }
        .likes-empty p { font-size: 14px; margin-bottom: 12px; }
        .btn-explore {
            display: inline-block;
            padding: 8px 20px;
            background: #e55039;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-explore:hover { background: #c0392b; }

        @media (max-width: 768px) {
            .mypage-wrap { width: 100%; min-width: 0; padding: 0 16px; }
        }
    </style>
</head>
<body>

<?= view('service/partials/header', ['activeNav' => 'mypage']) ?>

<!-- ===================== 마이페이지 본문 ===================== -->
<div class="mypage-body">
    <div class="mypage-wrap">

        <!-- 프로필 카드 -->
        <div class="mypage-card">
            <div class="profile-section">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="/uploads/profile/<?= esc($user['profile_image']) ?>"
                             alt="프로필 이미지"
                             style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <p class="profile-greeting">
                        <span><?= esc(!empty($user['name']) ? $user['name'] : $user['id']) ?></span>님 안녕하세요!
                    </p>
                    <a href="/mypage/profile" class="btn-profile-edit">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        프로필 수정
                    </a>
                </div>
            </div>
        </div>

        <!-- 주문 현황 카드 -->
        <div class="mypage-card">
            <?php
            /* 상태 레이블·배지 클래스 매핑 */
            $statusLabels = [
                'paid'      => '결제완료',
                'preparing' => '상품준비중',
                'shipped'   => '배송중',
                'delivered' => '배송완료',
                'cancelled' => '취소됨',
                'pending'   => '결제대기',
            ];
            $statusBadges = [
                'paid'      => 'badge-paid',
                'preparing' => 'badge-preparing',
                'shipped'   => 'badge-shipped',
                'delivered' => 'badge-delivered',
                'cancelled' => 'badge-cancelled',
                'pending'   => 'badge-pending',
            ];
            ?>

            <div class="order-card-header">
                <p class="order-card-title">주문 현황</p>
            </div>

            <!-- 탭 버튼 -->
            <div class="order-tabs">
                <button class="order-tab-btn active" type="button" data-tab="tab-progress">진행중인 주문</button>
                <button class="order-tab-btn"        type="button" data-tab="tab-history">주문 내역 보기</button>
            </div>

            <!-- 탭 1: 진행중인 주문 스텝바 (실제 데이터) -->
            <div class="order-tab-panel active" id="tab-progress">
                <div class="order-steps">

                    <?php
                    $steps = [
                        'paid'      => '결제완료',
                        'preparing' => '상품준비중',
                        'shipped'   => '배송중',
                        'delivered' => '배송완료',
                    ];
                    $stepKeys = array_keys($steps);
                    ?>
                    <?php foreach ($steps as $key => $label): ?>
                        <?php
                        $cnt      = $orderCounts[$key] ?? 0;
                        $hasCount = $cnt > 0;
                        ?>
                        <div class="order-step">
                            <div class="step-circle <?= $hasCount ? 'has-count' : '' ?>">
                                <span><?= $cnt ?></span>
                            </div>
                            <p class="step-label"><?= $label ?></p>
                        </div>
                        <?php if ($key !== 'delivered'): ?>
                            <span class="step-arrow">›</span>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- 탭 2: 최근 3개월 주문 내역 (최대 5개) -->
            <div class="order-tab-panel" id="tab-history">

                <!-- 전체 주문내역 보기 링크 -->
                <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
                    <a href="/mypage/orders" class="btn-all-orders">
                        전체 주문내역 보기
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="order-list-empty">최근 3개월간 주문 내역이 없습니다.</div>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($recentOrders as $ord): ?>
                        <?php
                            $sKey   = $ord['status'] ?? '';
                            $sLabel = $statusLabels[$sKey] ?? $sKey;
                            $sBadge = $statusBadges[$sKey] ?? '';
                            $paidAt = !empty($ord['paid_at']) ? substr($ord['paid_at'], 0, 10) : '—';
                            $goodsName = $ord['goods_names'] ?? '상품 정보 없음';
                        ?>
                        <a href="/mypage/orders/<?= (int)$ord['idx'] ?>" class="order-list-item">

                            <div class="order-list-left">
                                <p class="order-list-goods"><?= esc($goodsName) ?></p>
                                <div class="order-list-meta">
                                    <span><?= esc($ord['order_no']) ?></span>
                                    <span>·</span>
                                    <span><?= esc($paidAt) ?></span>
                                </div>
                            </div>

                            <div class="order-list-right">
                                <span class="order-list-price"><?= number_format((int)$ord['total_price']) ?>원</span>
                                <span class="order-status-badge <?= esc($sBadge) ?>"><?= esc($sLabel) ?></span>
                            </div>

                            <!-- 우측 화살표 -->
                            <svg class="order-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>

                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- 좋아요 누른 부산 명소 카드 -->
        <div class="mypage-card">
            <p class="likes-section-title">좋아요 누른 부산 명소</p>

            <?php if (empty($likedPlaces)): ?>
                <div class="likes-empty">
                    <p>아직 좋아요한 명소가 없습니다.</p>
                    <a href="/spots" class="btn-explore">부산 명소 둘러보기</a>
                </div>
            <?php else: ?>
                <?php
                $urlMap   = ['restaurant' => '/restaurants', 'place' => '/spots', 'event' => '/festivals'];
                $labelMap = ['restaurant' => '맛집', 'place' => '관광지', 'event' => '축제·행사'];
                ?>
                <div class="likes-card-list">
                <?php foreach ($likedPlaces as $place): ?>
                    <?php
                    $type      = $place['content_type'];
                    $detailUrl = ($urlMap[$type] ?? '/spots') . '/' . (int)$place['target_idx'];
                    $typeLabel = $labelMap[$type] ?? $type;
                    ?>
                    <a href="<?= esc($detailUrl) ?>" class="place-card">
                        <?php if (!empty($place['thumbnail'])): ?>
                            <img src="<?= esc($place['thumbnail']) ?>"
                                 alt="<?= esc($place['name']) ?>"
                                 class="place-card-thumb">
                        <?php else: ?>
                            <div class="place-card-thumb-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="m21 15-5-5L5 21"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="place-card-body">
                            <p class="place-card-name"><?= esc($place['name']) ?></p>
                            <p class="place-card-type"><?= esc($typeLabel) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div><!-- /.mypage-wrap -->
</div><!-- /.mypage-body -->

<?= view('service/partials/footer') ?>

<?= view('modules/auth/login_modal') ?>
<?= view('modules/auth/signup_modal') ?>

<script src="/js/busan.js"></script>
<script src="/js/modules/login.js"></script>
<script src="/js/modules/signup.js"></script>
<script src="/js/service-common.js"></script>

<script>
/* ---- 주문 탭 전환 ---- */
document.querySelectorAll('.order-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        /* 탭 버튼 active 전환 */
        document.querySelectorAll('.order-tab-btn').forEach(function (b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');

        /* 탭 패널 전환 */
        var target = btn.dataset.tab;
        document.querySelectorAll('.order-tab-panel').forEach(function (panel) {
            panel.classList.toggle('active', panel.id === target);
        });
    });
});
</script>

</body>
</html>
