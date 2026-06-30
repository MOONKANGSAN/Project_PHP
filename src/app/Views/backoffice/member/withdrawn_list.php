<?= view('backoffice/partials/header', $this->data) ?>

<div class="bo-page-header">
    <div class="bo-page-header-row">
        <div>
            <h1 class="bo-page-title">탈퇴회원 관리</h1>
            <p class="bo-page-desc">탈퇴 처리된 회원 목록입니다. (state = 5)</p>
        </div>
        <a href="/backoffice/members" class="bo-btn bo-btn-ghost">← 회원 목록으로</a>
    </div>
</div>

<!-- 플래시 메시지 -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bo-alert bo-alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="bo-alert bo-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="bo-card">
    <!-- 검색 바 -->
    <form method="get" class="bo-list-toolbar">
        <div class="bo-search-wrap">
            <input type="text" name="q" value="<?= esc($q) ?>"
                   class="bo-form-input bo-search-input" placeholder="아이디 / 이메일 / 전화번호 검색...">
            <button type="submit" class="bo-btn bo-btn-primary">검색</button>
            <a href="/backoffice/withdrawn-members" class="bo-btn bo-btn-ghost">초기화</a>
        </div>
    </form>

    <!-- 탈퇴회원 목록 테이블 -->
    <div class="bo-table-wrap">
        <table class="bo-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">IDX</th>
                    <th style="text-align:center">이메일</th>
                    <th style="width:130px;text-align:center">아이디</th>
                    <th style="width:130px;text-align:center">전화번호</th>
                    <th style="width:140px;text-align:center">가입일</th>
                    <th style="width:220px;text-align:center">탈퇴 사유</th>
                    <th style="width:90px;text-align:center">상태</th>
                    <th style="width:90px;text-align:center">복원</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="8" class="bo-table-empty">탈퇴한 회원이 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                <?php $log = $state5Map[$row['idx']] ?? null; ?>
                <tr>
                    <td class="text-center text-muted"><?= $row['idx'] ?></td>
                    <td><?= esc($row['email']) ?></td>
                    <td class="text-center"><?= esc($row['id']) ?></td>
                    <td class="text-center text-muted"><?= esc($row['phone'] ?: '-') ?></td>
                    <td class="text-center text-muted text-sm"><?= substr($row['reg_date'], 0, 10) ?></td>

                    <!-- 탈퇴 사유: 최대 30자 · 2줄 말줄임 -->
                    <td style="width:220px;padding:10px 12px;">
                        <?php if ($log && $log['reason']): ?>
                            <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:13px;color:#374151;line-height:1.55;word-break:break-all;"
                                  title="<?= esc($log['reason']) ?>">
                                <?= esc($log['reason']) ?>
                            </span>
                        <?php else: ?>
                            <span style="font-size:13px;color:#d1d5db;">사유 없음</span>
                        <?php endif; ?>
                    </td>

                    <!-- 상태 배지 -->
                    <td class="text-center">
                        <span style="display:inline-block;background:#fef2f2;color:#ef4444;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600">
                            탈퇴
                        </span>
                    </td>

                    <!-- 복원 버튼 -->
                    <td class="text-center">
                        <form method="post" action="/backoffice/members/<?= $row['idx'] ?>/restore"
                              onsubmit="return confirm('[<?= esc($row['id']) ?>] 회원을 복원하시겠습니까?\nuser_info.state가 1(활성)로 변경됩니다.')">
                            <?= csrf_field() ?>
                            <button type="submit"
                                    class="bo-btn-action activate"
                                    style="min-width:64px">
                                복원
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($pager): ?>
    <div class="bo-pagination">
        <?= $pager->links('default', 'bo_pager') ?>
    </div>
    <?php endif; ?>
</div>

<?= view('backoffice/partials/footer') ?>
