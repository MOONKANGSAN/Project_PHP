<?php

declare(strict_types=1);

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
        // CI4 테스트 환경에서는 autoload가 동작하지 않으므로 수동 로드
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

    public function testHandleError500HasCorrectCode(): void
    {
        try {
            handle_error(500, '서버 오류');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(HTTPExceptionInterface::class, $e);
            $this->assertSame(500, $e->getCode());
            return;
        }
        $this->fail('예외가 발생하지 않았습니다.');
    }

    public function testHandleErrorThrowsInvalidArgumentExceptionForOutOfRangeCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        handle_error(200);
    }

    public function testHandleError400UsesDefaultMessageWhenEmpty(): void
    {
        try {
            handle_error(400);
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
        $this->fail('예외가 발생하지 않았습니다.');
    }
}
