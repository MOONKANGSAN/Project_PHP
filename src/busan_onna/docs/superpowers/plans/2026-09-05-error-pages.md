# 부산온나 400/404 에러 페이지 & handle_error 헬퍼 구현 계획

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** `handle_error(int $code)` 헬퍼 함수를 제공하고, 404/400 에러 발생 시 부산온나 브랜드 디자인(라이트 클린)의 커스텀 에러 뷰를 표시한다.

**Architecture:** CI4 예외 throw 방식을 사용한다. `handle_error(404)`는 `PageNotFoundException`을, `handle_error(400)`은 `HTTPExceptionInterface`를 구현하는 익명 예외 클래스를 throw한다. CI4 ExceptionHandler가 예외 코드를 HTTP 상태 코드로 매핑하고 `app/Views/errors/html/error_{code}.php`를 렌더링한다. `Config/Exceptions.php`의 DB 에러 로그가 자동으로 작동한다.

**Tech Stack:** PHP 8.x, CodeIgniter 4, PHPUnit (CIUnitTestCase), Noto Sans KR (Google Fonts)

---

## 파일 구조

| 작업 | 경로 | 역할 |
|------|------|------|
| 신규 | `app/Helpers/error_helper.php` | `handle_error()` 함수 정의 |
| 신규 | `tests/unit/ErrorHelperTest.php` | 헬퍼 함수 단위 테스트 |
| 수정 | `app/Config/Autoload.php` | `$helpers`에 `'error'` 추가 |
| 수정 | `app/Views/errors/html/error_404.php` | 라이트 클린 404 뷰 |
| 수정 | `app/Views/errors/html/error_400.php` | 라이트 클린 400 뷰 |

---

## Task 1: handle_error() 헬퍼 함수 — TDD

**Files:**
- Create: `app/Helpers/error_helper.php`
- Create: `tests/unit/ErrorHelperTest.php`

- [ ] **Step 1: 실패 테스트 작성**

`tests/unit/ErrorHelperTest.php`를 아래 내용으로 새로 만든다.

```php
<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ErrorHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 헬퍼가 아직 없으므로 수동 로드
        helper('error');
    }

    public function testHandleError404ThrowsPageNotFoundException(): void
    {
        $this->expectException(PageNotFoundException::class);
        handle_error(404);
    }

    public function testHandleError404WithMessagePassesMessageToException(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('해당 상품 없음');
        handle_error(404, '해당 상품 없음');
    }

    public function testHandleError400ThrowsHTTPExceptionInterface(): void
    {
        $this->expectException(HTTPExceptionInterface::class);
        handle_error(400);
    }

    public function testHandleError400HasCorrectCode(): void
    {
        try {
            handle_error(400, '잘못된 요청');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(HTTPExceptionInterface::class, $e);
            $this->assertSame(400, $e->getCode());
            return;
        }
        $this->fail('예외가 발생하지 않았습니다.');
    }
}
```

- [ ] **Step 2: 테스트 실패 확인**

```bash
cd C:\project_php\src\busan_onna
vendor/bin/phpunit tests/unit/ErrorHelperTest.php --testdox
```

예상 결과: `FAIL` — `helper('error')` 로드 실패 또는 `handle_error` 함수 미정의

- [ ] **Step 3: 헬퍼 함수 구현**

`app/Helpers/error_helper.php`를 아래 내용으로 새로 만든다.

```php
<?php

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Exceptions\RuntimeException;

/**
 * 지정한 HTTP 에러 코드에 해당하는 CI4 예외를 throw한다.
 * CI4 ExceptionHandler가 HTTP 상태 코드를 설정하고 에러 뷰를 렌더링한다.
 * Config/Exceptions::saveToDatabase()가 자동으로 DB에 에러를 기록한다.
 *
 * @throws PageNotFoundException 코드가 404인 경우
 * @throws HTTPExceptionInterface 그 외 HTTP 에러 코드인 경우
 */
function handle_error(int $code, string $message = ''): never
{
    if ($code === 404) {
        throw PageNotFoundException::forPageNotFound(
            $message !== '' ? $message : null
        );
    }

    // HTTPExceptionInterface를 구현해야 CI4 ExceptionHandler가
    // $exception->getCode()를 HTTP 상태 코드로 인식한다.
    throw new class($message ?: 'HTTP Error', $code)
        extends RuntimeException
        implements HTTPExceptionInterface {};
}
```

- [ ] **Step 4: 테스트 통과 확인**

```bash
vendor/bin/phpunit tests/unit/ErrorHelperTest.php --testdox
```

예상 결과:
```
ErrorHelper (ErrorHelper)
 ✔ Handle error 404 throws page not found exception
 ✔ Handle error 404 with message passes message to exception
 ✔ Handle error 400 throws h t t p exception interface
 ✔ Handle error 400 has correct code
```

- [ ] **Step 5: 커밋**

```bash
git add app/Helpers/error_helper.php tests/unit/ErrorHelperTest.php
git commit -m "feat: add handle_error() helper for 404/400 CI4 exception dispatch"
```

---

## Task 2: Autoload에 error 헬퍼 등록

**Files:**
- Modify: `app/Config/Autoload.php:91`

- [ ] **Step 1: $helpers 배열에 'error' 추가**

`app/Config/Autoload.php` 91번 줄의 `$helpers`를 수정한다.

변경 전:
```php
public $helpers = [];
```

변경 후:
```php
public $helpers = ['error'];
```

- [ ] **Step 2: 전체 테스트 실행으로 기존 테스트 깨지지 않음 확인**

```bash
vendor/bin/phpunit --testdox
```

예상 결과: 모든 테스트 PASS (헬퍼 자동 로드로 `helper('error')` 수동 호출 없어도 동작)

- [ ] **Step 3: 커밋**

```bash
git add app/Config/Autoload.php
git commit -m "chore: auto-load error helper globally via Autoload config"
```

---

## Task 3: 404 에러 뷰 재디자인 (라이트 클린)

**Files:**
- Modify: `app/Views/errors/html/error_404.php`

- [ ] **Step 1: 404 뷰 전체 교체**

`app/Views/errors/html/error_404.php`를 아래 내용으로 완전히 교체한다.

```php
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — 페이지를 찾을 수 없어요 | 부산온나</title>
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

        /* 아이콘 원 */
        .error-icon {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(10, 31, 60, 0.22);
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
                <!-- 앵커 아이콘: 길을 잃었을 때의 방향 상실 표현 -->
                <svg viewBox="0 0 24 24" fill="none" stroke="#f39c12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="5" r="3"/>
                    <line x1="12" y1="8" x2="12" y2="21"/>
                    <path d="M5 16l7 5 7-5"/>
                    <path d="M5 12h14"/>
                </svg>
            </div>

            <div class="error-code">404</div>
            <div class="error-divider"></div>
            <div class="error-title">페이지를 찾을 수 없어요</div>

            <?php if (ENVIRONMENT !== 'production') : ?>
                <div class="error-dev-msg"><?= nl2br(esc($message)) ?></div>
            <?php else : ?>
                <div class="error-desc">
                    요청하신 페이지가 이동되었거나 삭제되었을 수 있습니다.<br>
                    주소를 다시 확인해 주세요.
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
        © 2025 부산온나 · VISIT BUSAN
    </footer>

</body>
</html>
```

- [ ] **Step 2: 커밋**

```bash
git add app/Views/errors/html/error_404.php
git commit -m "design: redesign 404 error page with light-clean brand style"
```

---

## Task 4: 400 에러 뷰 재디자인 (라이트 클린)

**Files:**
- Modify: `app/Views/errors/html/error_400.php`

- [ ] **Step 1: 400 뷰 전체 교체**

`app/Views/errors/html/error_400.php`를 아래 내용으로 완전히 교체한다.

```php
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
        © 2025 부산온나 · VISIT BUSAN
    </footer>

</body>
</html>
```

- [ ] **Step 2: 커밋**

```bash
git add app/Views/errors/html/error_400.php
git commit -m "design: redesign 400 error page with light-clean brand style"
```

---

## 성공 기준 체크리스트

- [ ] `vendor/bin/phpunit tests/unit/ErrorHelperTest.php` 전체 PASS
- [ ] `handle_error(404)` 호출 시 HTTP 404 + 커스텀 뷰 렌더링
- [ ] `handle_error(400)` 호출 시 HTTP 400 + 커스텀 뷰 렌더링
- [ ] `Config/Exceptions.php` DB 에러 로그가 400 에러에 대해 정상 저장
- [ ] 홈으로 돌아가기 → `/` 이동
- [ ] 이전 페이지 버튼 → `history.back()` 또는 히스토리 없으면 `/` 이동
- [ ] production 환경에서 에러 메시지 노출 없음
