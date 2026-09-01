<?= view('backoffice/partials/header', $this->data) ?>

<?php
/* 등록 모드: $goods === null, 수정 모드: $goods = 기존 데이터 배열 */
$isEdit = !empty($goods);
$action = $isEdit
    ? '/backoffice/goods/' . $goods['idx'] . '/edit'
    : '/backoffice/goods/register';
?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title"><?= $isEdit ? '상품 수정' : '상품 등록' ?></h1>
            <p class="bo-page-desc"><?= $isEdit ? '굿즈 상품 정보를 수정합니다.' : '새로운 굿즈 상품을 등록합니다.' ?></p>
        </div>
        <a href="/backoffice/goods" class="bo-btn bo-btn-ghost">← 목록으로</a>
    </div>
</div>

<!-- 유효성 오류 메시지 -->
<?php if (session()->getFlashdata('form_errors')): ?>
    <div class="bo-alert bo-alert-error">
        <?php foreach ((array) session()->getFlashdata('form_errors') as $err): ?>
            <div><?= esc($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
/* old() 헬퍼 — 유효성 실패 후 재입력 값 우선, 없으면 기존 데이터 사용 */
$val = fn(string $field, mixed $default = '') =>
    old($field) ?? ($goods[$field] ?? $default);
?>

<form method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="bo-card" style="margin-bottom:20px;">

        <!-- 상품명 -->
        <div style="margin-bottom:20px;">
            <label class="bo-form-label">
                상품명 <span style="color:#ef4444">*</span>
            </label>
            <input type="text" name="name"
                   value="<?= esc($val('name')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="굿즈 상품명을 입력하세요." required>
        </div>

        <!-- 가격 / 재고 / 배송 유형 -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">

            <div>
                <label class="bo-form-label">
                    가격 (원) <span style="color:#ef4444">*</span>
                </label>
                <input type="number" name="price" min="0"
                       value="<?= esc($val('price', 0)) ?>"
                       class="bo-form-input" style="width:100%"
                       placeholder="0" required>
            </div>

            <div>
                <label class="bo-form-label">
                    재고 <span style="color:#ef4444">*</span>
                </label>
                <input type="number" name="stock" min="0"
                       value="<?= esc($val('stock', 0)) ?>"
                       class="bo-form-input" style="width:100%"
                       placeholder="0" required>
            </div>

            <div>
                <label class="bo-form-label">배송 유형</label>
                <select name="delivery_type" class="bo-form-select" style="width:100%">
                    <!-- 1: 택배, 2: 픽업 -->
                    <option value="1" <?= (string)$val('delivery_type', 1) === '1' ? 'selected' : '' ?>>택배</option>
                    <option value="2" <?= (string)$val('delivery_type', 1) === '2' ? 'selected' : '' ?>>픽업</option>
                </select>
            </div>
        </div>

        <!-- 픽업 장소 선택 (배송 유형이 픽업일 때 참고용 안내) -->
        <?php if (!empty($pickups)): ?>
        <div style="margin-bottom:20px;padding:14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#374151;">
                등록된 픽업 장소 (배송 유형을 "픽업"으로 선택 시 참고)
            </p>
            <ul style="margin:0;padding-left:18px;font-size:13px;color:#6b7280;">
                <?php foreach ($pickups as $p): ?>
                <li><?= esc($p['name']) ?> — <?= esc($p['address']) ?></li>
                <?php endforeach; ?>
            </ul>
            <p style="margin:8px 0 0;font-size:12px;color:#9ca3af;">
                픽업 장소 추가·관리는
                <a href="/backoffice/pickup-locations" style="color:#6366f1;">픽업 장소 관리</a> 페이지에서 할 수 있습니다.
            </p>
        </div>
        <?php endif; ?>

        <!-- 썸네일 URL -->
        <div style="margin-bottom:20px;">
            <label class="bo-form-label">썸네일 이미지 URL</label>
            <input type="url" name="thumbnail"
                   value="<?= esc($val('thumbnail')) ?>"
                   class="bo-form-input" style="width:100%"
                   placeholder="https://example.com/image.jpg">
            <?php if ($val('thumbnail')): ?>
            <div style="margin-top:10px;">
                <img src="<?= esc($val('thumbnail')) ?>" alt="썸네일 미리보기"
                     style="max-width:200px;max-height:200px;border-radius:6px;border:1px solid #e2e8f0;">
            </div>
            <?php endif; ?>
        </div>

        <!-- 상품 설명 -->
        <div>
            <label class="bo-form-label">상품 설명</label>
            <textarea name="description" rows="6"
                      class="bo-form-input" style="width:100%;resize:vertical;"
                      placeholder="상품 상세 설명을 입력하세요."><?= esc($val('description')) ?></textarea>
        </div>

    </div>

    <!-- 저장·취소 버튼 -->
    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="/backoffice/goods" class="bo-btn bo-btn-ghost">취소</a>
        <button type="submit" class="bo-btn bo-btn-primary">
            <?= $isEdit ? '저장하기' : '등록하기' ?>
        </button>
    </div>
</form>

<?= view('backoffice/partials/footer') ?>
