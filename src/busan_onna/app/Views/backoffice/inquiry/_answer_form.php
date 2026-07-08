<form method="post" action="/backoffice/inquiries/<?= $item['idx'] ?>/answer">
    <?= csrf_field() ?>
    <div style="margin-bottom:12px;">
        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">답변 내용</label>
        <textarea name="answer"
                  rows="7"
                  placeholder="답변 내용을 입력하세요."
                  style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:8px;padding:12px 14px;font-size:14px;font-family:inherit;color:#111;line-height:1.7;resize:vertical;outline:none;transition:border-color .15s;"
                  onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.1)'"
                  onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'"><?= esc($item['answer'] ?? '') ?></textarea>
    </div>
    <div style="display:flex;justify-content:flex-end;">
        <button type="submit"
                style="padding:9px 26px;border:none;border-radius:8px;background:#3b82f6;color:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;"
                onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
            <?= $item['answer'] ? '답변 수정' : '답변 등록' ?>
        </button>
    </div>
</form>
