# 부산온나 400/404 에러 페이지 & handle_error 헬퍼 함수 설계

**작성일**: 2026-09-05  
**브랜치**: item_view  
**작성자**: Claude (브레인스토밍 스킬)

---

## 1. 목표

사용자가 404(페이지 없음) 또는 400(잘못된 요청) 에러를 만났을 때, 기본 CI4 에러 페이지 대신 부산온나 브랜드에 맞는 커스텀 에러 뷰를 표시한다. 컨트롤러에서 `handle_error(404)` 형태로 간편하게 호출할 수 있는 헬퍼 함수를 제공한다.

---

## 2. 범위

- **에러 코드**: 404, 400 두 가지만 대상
- **대상 파일**:
  - `app/Helpers/error_helper.php` (신규)
  - `app/Views/errors/html/error_404.php` (재디자인)
  - `app/Views/errors/html/error_400.php` (재디자인)
  - `app/Config/Autoload.php` (헬퍼 자동 로드 추가)
- **비범위**: 500, 503 등 서버 에러, CLI 에러 뷰, 백오피스 에러 처리

---

## 3. 구현 방식: CI4 예외 throw 방식

### 왜 이 방식인가?

| 항목 | CI4 예외 throw | 직접 뷰 렌더 + exit | 302 리다이렉트 |
|------|---------------|-------------------|--------------|
| HTTP 상태 코드 보존 | ✅ | ✅ | ❌ (200이 됨) |
| DB 에러 로그 자동 저장 | ✅ | ❌ | ❌ |
| CI4 기존 파이프라인 유지 | ✅ | ❌ | △ |
| 구현 단순성 | ✅ | △ | △ |

`Config/Exceptions.php`의 `saveToDatabase()`가 자동 동작하므로 에러 로그 기능을 깨지 않는다.

### handle_error() 함수 시그니처

```php
function handle_error(int $code, string $message = ''): never
```

- `$code = 404` → `PageNotFoundException` throw
- `$code = 400` → `HTTPException::forHTTPCode(400)` throw
- 그 외 코드 → `HTTPException::forHTTPCode($code)` throw

---

## 4. 에러 뷰 디자인 스펙

### 공통 디자인 언어

| 속성 | 값 |
|------|-----|
| 배경 | `#f4f7fb` (라이트 클린) |
| 헤더 | `rgba(8,15,30,0.92)` 미니 네이비 바 |
| 에러 코드 폰트 | 80px / 900 weight / `#0a1f3c` |
| 포인트 구분선 | `#f39c12` (골든) |
| 버튼 1 (홈) | 네이비 채움 `#0a1f3c` / 화이트 텍스트 |
| 버튼 2 (이전) | 화이트 배경 / 네이비 보더 |
| 폰트 | Noto Sans KR |
| 푸터 | 네이비 `#0a1f3c` / 저작권 텍스트 |

### 404 전용

- 원형 아이콘: `#0a1f3c` 배경 + 앵커 SVG (`#f39c12`)
- 제목: "페이지를 찾을 수 없어요"
- 설명: "요청하신 페이지가 이동되었거나 삭제되었을 수 있습니다."

### 400 전용

- 원형 아이콘: `#1a6b9a` 배경 + 경고 삼각형 SVG (`#f39c12`)
- 제목: "잘못된 요청입니다"
- 설명: "올바르지 않은 요청이 감지되었습니다."

### 버튼 액션

- **홈으로 돌아가기**: `href="/"`
- **이전 페이지**: `onclick="history.back()"` (JS) → JS 비활성 시 `href="/"` fallback

---

## 5. 파일 구조

```
app/
├── Helpers/
│   └── error_helper.php          ← 신규: handle_error() 정의
├── Config/
│   └── Autoload.php              ← 수정: 'error' 헬퍼 자동 로드 추가
└── Views/
    └── errors/
        └── html/
            ├── error_404.php     ← 재디자인 (라이트 클린)
            └── error_400.php     ← 재디자인 (라이트 클린)
```

---

## 6. 사용 예시

```php
// 컨트롤러에서 직접 호출
if (!$item) {
    handle_error(404, '해당 상품을 찾을 수 없습니다.');
}

// 잘못된 파라미터
if ($amount <= 0) {
    handle_error(400, '유효하지 않은 요청입니다.');
}
```

CI4의 예외 파이프라인이 처리하므로 `Config/Exceptions.php`의 DB 로그, HTTP 상태 코드 반환이 모두 정상 작동한다.

---

## 7. Autoload 수정 사항

`app/Config/Autoload.php`의 `$helpers` 배열에 `'error'` 추가:

```php
public array $helpers = ['error'];
```

---

## 8. 성공 기준

- [ ] 404 발생 시 커스텀 뷰가 렌더링되고 HTTP 404 반환
- [ ] 400 발생 시 커스텀 뷰가 렌더링되고 HTTP 400 반환
- [ ] `handle_error(404)` 호출 시 동일하게 동작
- [ ] `Config/Exceptions.php`의 DB 에러 로그가 정상 저장됨
- [ ] 홈으로 돌아가기 버튼 → `/` 이동
- [ ] 이전 페이지 버튼 → `history.back()` 동작
