<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Setup how the exception handler works.
 */
class Exceptions extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * LOG EXCEPTIONS?
     * --------------------------------------------------------------------------
     * If true, then exceptions will be logged
     * through Services::Log.
     *
     * Default: true
     */
    public bool $log = true;

    /**
     * --------------------------------------------------------------------------
     * DO NOT LOG STATUS CODES
     * --------------------------------------------------------------------------
     * Any status codes here will NOT be logged if logging is turned on.
     * By default, only 404 (Page Not Found) exceptions are ignored.
     *
     * @var list<int>
     */
    public array $ignoreCodes = [404];

    /**
     * --------------------------------------------------------------------------
     * Error Views Path
     * --------------------------------------------------------------------------
     * This is the path to the directory that contains the 'cli' and 'html'
     * directories that hold the views used to generate errors.
     *
     * Default: APPPATH.'Views/errors'
     */
    public string $errorViewPath = APPPATH . 'Views/errors';

    /**
     * --------------------------------------------------------------------------
     * HIDE FROM DEBUG TRACE
     * --------------------------------------------------------------------------
     * Any data that you would like to hide from the debug trace.
     * In order to specify 2 levels, use "/" to separate.
     * ex. ['server', 'setup/password', 'secret_token']
     *
     * @var list<string>
     */
    public array $sensitiveDataInTrace = [];

    /**
     * --------------------------------------------------------------------------
     * WHETHER TO THROW AN EXCEPTION ON DEPRECATED ERRORS
     * --------------------------------------------------------------------------
     * If set to `true`, DEPRECATED errors are only logged and no exceptions are
     * thrown. This option also works for user deprecations.
     */
    public bool $logDeprecations = true;

    /**
     * --------------------------------------------------------------------------
     * LOG LEVEL THRESHOLD FOR DEPRECATIONS
     * --------------------------------------------------------------------------
     * If `$logDeprecations` is set to `true`, this sets the log level
     * to which the deprecation will be logged. This should be one of the log
     * levels recognized by PSR-3.
     *
     * The related `Config\Logger::$threshold` should be adjusted, if needed,
     * to capture logging the deprecations.
     */
    public string $deprecationLogLevel = LogLevel::WARNING;

    /*
     * DEFINE THE HANDLERS USED
     * --------------------------------------------------------------------------
     * Given the HTTP status code, returns exception handler that
     * should be used to deal with this error. By default, it will run CodeIgniter's
     * default handler and display the error information in the expected format
     * for CLI, HTTP, or AJAX requests, as determined by is_cli() and the expected
     * response format.
     *
     * Custom handlers can be returned if you want to handle one or more specific
     * error codes yourself like:
     *
     *      if (in_array($statusCode, [400, 404, 500])) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     *      if ($exception instanceOf PageNotFoundException) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     */
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        // ignoreCodes(기본 404)는 DB 저장 제외
        if (!in_array($statusCode, $this->ignoreCodes, true)) {
            $this->saveToDatabase($statusCode, $exception);
        }

        return new ExceptionHandler($this);
    }

    /**
     * 에러를 error_log 테이블에 저장
     * try-catch로 감싸 저장 실패 시 무한 루프 방지
     */
    private function saveToDatabase(int $statusCode, Throwable $exception): void
    {
        try {
            $request = service('request');

            \Config\Database::connect()->table('error_log')->insert([
                'state'      => 0,
                'error_type' => $this->resolveErrorType($statusCode, $exception),
                'error_code' => (string) $statusCode,
                'message'    => mb_substr($exception->getMessage(), 0, 500),
                'file'       => mb_substr($exception->getFile(), 0, 500),
                'line'       => $exception->getLine(),
                'url'        => mb_substr((string) current_url(), 0, 500),
                'method'     => strtoupper((string) $request->getMethod()),
                'ip'         => $request->getIPAddress(),
                'reg_date'   => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            // DB 저장 자체가 실패하면 파일 로그에만 기록
            log_message('critical', '[ErrorLog DB 저장 실패] ' . $e->getMessage());
        }
    }

    /**
     * 예외 유형 → error_type 코드 변환
     * 1=PHP Exception, 2=DB Error, 3=HTTP 4xx, 4=HTTP 5xx, 5=기타
     */
    private function resolveErrorType(int $statusCode, Throwable $exception): int
    {
        if ($exception instanceof \CodeIgniter\Database\Exceptions\DatabaseException) {
            return 2;
        }
        if ($statusCode >= 500) {
            return 4;
        }
        if ($statusCode >= 400) {
            return 3;
        }
        return 1;
    }
}
