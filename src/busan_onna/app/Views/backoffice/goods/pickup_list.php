<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">픽업 장소 관리</h1>
            <p class="bo-page-desc">픽업 수령 가능 장소를 등록하고 활성/비활성 상태를 관리합니다.</p>
        </div>
        <a href="/backoffice/goods" class="bo-btn bo-btn-ghost">← 굿즈 목록</a>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- 픽업 장소 인라인 등록 폼 -->
<div class="bo-card" style="margin-bottom:20px;">
    <h2 style="font-size:15px;font-weight:600;color:#374151;margin:0 0 16px;">새 픽업 장소 추가</h2>

    <form method="post" action="/backoffice/pickup-locations/store">
        <?= csrf_field() ?>

        <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:12px;align-items:flex-end;">

            <div>
                <label class="bo-form-label">
                    장소명 <span style="color:#ef4444">*</span>
                </label>
                <input type="text" name="name"
                       class="bo-form-input" style="width:100%"
                       placeholder="예) 해운대 비치 센터" required>
            </div>

            <div>
                <label class="bo-form-label">
                    주소 <span style="color:#ef4444">*</span>
                </label>
                <input type="text" name="address"
                       class="bo-form-input" style="width:100%"
                       placeholder="예) 부산광역시 해운대구 해운대해변로 264" required>
            </div>

            <div>
                <button type="submit" class="bo-btn bo-btn-primary">추가</button>
            </div>

        </div>
    </form>
</div>

<!-- 픽업 장소 목록 테이블 -->
<div class="bo-card">
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="text-align:center">장소명</th>
                    <th style="text-align:center">주소</th>
                    <th style="width:80px;text-align:center">상태</th>
                    <th style="width:120px;text-align:center">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($pickups)): ?>
                <tr><td colspan="5" class="bo-table-empty">등록된 픽업 장소가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($pickups as $row): ?>
                <?php $isActive = (int)$row['state'] === 1; ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>

                    <!-- 장소명 — 비활성 시 회색 처리 -->
                    <td style="<?= !$isActive ? 'color:#9ca3af;' : '' ?>">
                        <?= esc($row['name']) ?>
                    </td>

                    <!-- 주소 -->
                    <td style="<?= !$isActive ? 'color:#9ca3af;' : '' ?>">
                        <?= esc($row['address']) ?>
                    </td>

                    <!-- 상태 배지 -->
                    <td class="text-center">
                        <span class="bo-badge <?= $isActive ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $isActive ? '활성' : '비활성' ?>
                        </span>
                    </td>

                    <!-- 상태 토글 버튼 (POST 폼) -->
                    <td class="text-center">
                        <form method="post" action="/backoffice/pickup-locations/<?= $row['idx'] ?>/state"
                              style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit"
                                    class="bo-btn-action <?= $isActive ? 'deactivate' : 'activate' ?>">
                                <?= $isActive ? '비활성' : '활성화' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= view('backoffice/partials/footer') ?>
