<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>400 — 잘못된 요청입니다 | 부산온나</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:       #0a1f3c;
            --primary-light: #1a6b9a;
            --accent:        #f39c12;
            --sea:           #74b9ff;
            --light-bg:      #f4f7fb;
            --white:         #ffffff;
            --text:          #1a2a3a;
            --text-muted:    #6c7a89;
            --border:        #e4eaf1;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Noto Sans KR', -apple-system, sans-serif;
            background: var(--light-bg);
            color: var(--text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* 미니 헤더 */
        .error-header {
            background: rgba(8, 15, 30, 0.92);
            backdrop-filter: blur(14px);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 28px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            flex-shrink: 0;
        }
        .logo-link { text-decoration: none; display: flex; flex-direction: column; line-height: 1; }
        .logo-main { font-size: 18px; font-weight: 900; color: #fff; letter-spacing: -0.5px; }
        .logo-sub  { font-size: 8px;  font-weight: 400; color: var(--sea); letter-spacing: 3px; margin-top: 2px; }

        /* 본문 */
        .error-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
        }
        .error-card {
            text-align: center;
            max-width: 480px;
            width: 100%;
        }

        /* 아이콘 원 — 400은 primary-light 배경 */
        .error-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(26, 107, 154, 0.22);
        }
        .error-icon svg { width: 36px; height: 36px; }

        /* 에러 코드 */
        .error-code {
            font-size: 88px;
            font-weight: 900;
            color: var(--primary);
            line-height: 1;
            letter-spacing: -6px;
            margin-bottom: 12px;
        }

        /* 골드 구분선 */
        .error-divider {
            width: 48px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
            margin: 0 auto 20px;
        }

        /* 제목 */
        .error-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }

        /* 설명 */
        .error-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.9;
            margin-bottom: 12px;
        }

        /* 개발 환경 메시지 */
        .error-dev-msg {
            font-size: 12px;
            color: var(--text-muted);
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 32px;
            text-align: left;
            word-break: break-all;
        }
        .error-gap { margin-bottom: 32px; }

        /* 버튼 그룹 */
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #fff;
            padding: 13px 28px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(10, 31, 60, 0.22);
            transition: background 0.2s, transform 0.15s;
        }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            color: var(--primary);
            padding: 12px 28px;
            border-radius: 24px;
            border: 1.5px solid var(--border);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            transition: border-color 0.2s, transform 0.15s;
        }
        .btn-secondary:hover { border-color: var(--primary); transform: translateY(-1px); }

        /* 미니 푸터 */
        .error-footer {
            background: var(--primary);
            padding: 16px 28px;
            text-align: center;
            color: rgba(255, 255, 255, 0.35);
            font-size: 11px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <header class="error-header">
        <a href="/" class="logo-link">
            <span class="logo-main">부산온나</span>
            <span class="logo-sub">VISIT BUSAN</span>
        </a>
    </header>

    <main class="error-body">
        <div class="error-card">

            <div class="error-icon">
                <!-- 경고 삼각형 아이콘: 잘못된 요청 표현 -->
                <svg viewBox="0 0 24 24" fill="none" stroke="#f39c12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>

            <div class="error-code">400</div>
            <div class="error-divider"></div>
            <div class="error-title">잘못된 요청입니다</div>

            <?php if (ENVIRONMENT !== 'production') : ?>
                <div class="error-dev-msg"><?= nl2br(esc($message)) ?></div>
            <?php else : ?>
                <div class="error-desc">
                    올바르지 않은 요청이 감지되었습니다.<br>
                    다시 시도하시거나 홈으로 돌아가 주세요.
                </div>
                <div class="error-gap"></div>
            <?php endif; ?>

            <div class="btn-group">
                <a href="/" class="btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    홈으로 돌아가기
                </a>
                <button type="button" class="btn-secondary" onclick="history.length > 1 ? history.back() : location.href='/'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    이전 페이지
                </button>
            </div>

        </div>
    </main>

    <footer class="error-footer">
        © <?= date('Y') ?> 부산온나 · VISIT BUSAN
    </footer>

</body>
</html>
