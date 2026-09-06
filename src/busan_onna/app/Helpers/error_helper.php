<?php

declare(strict_types=1);

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
    // 400~599 범위 외의 코드는 HTTP 에러가 아니므로 거부한다
    if ($code < 400 || $code > 599) {
        throw new \InvalidArgumentException(
            "handle_error()는 400~599 HTTP 에러 코드만 허용합니다. 받은 값: {$code}"
        );
    }

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
