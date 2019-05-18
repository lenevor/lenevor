<?php 

namespace Syscode\Core\Exceptions\FatalExceptions;

use Exception;
use Throwable;
use Syscode\Core\Http\Exceptions\HttpException;

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.1
 */
class FlattenException
{
    /**
     * Gets the class name.
     * 
     * @var string $class
     */
    protected $class;

    /**
     * Gets the code of error.
     * 
     * @var int $code
     */
    protected $code;

    /**
     * Gets the file path.
     * @var string $file
     */
    protected $file;

    /**
     * Gets the headers HTTP.
     * 
     * @var array $headers
     */
    protected $headers;

    /**
     * Gets the line where specifice the line number and code in happened an error.
     *  
     * @var int $line
     */
    protected $line;

    /**
     * Gets the message of exception.
     * 
     * @var string $message
     */
    protected $message;

    /**
     * Gets the previous exception.
     * 
     * @var string $previous
     */
    protected $previous;

    /**
     * Gets the status code response.
     * 
     * @var int $statusCode
     */
    protected $statusCode;

    /**
     * Gets the trace.
     * 
     * @var array $trace
     */
    protected $trace;

    /**
     * Load the exception with their respective status code and headers.
     * 
     * @param  \Exception  $exception
     * @param  int|null    $statusCode
     * @param  array       $headers
     * 
     * @return void
     */
    public static function make(Exception $exception, $statusCode = null, array $headers = [])
    {
        return static::makeFromThrowable($exception, $statusCode, $headers);
    }

    /**
     * An exception is loaded to change the initial value in set methods.
     * 
     * @param  Throwable  $exception
     * @param  int|null   $statusCode
     * @param  array      $headers
     * 
     * @return new static
     */
    public static function makeFromThrowable(Throwable $exception, ?int $statusCode = null, array $headers = [])
    {
        $e = new static;
        $e->setMessage($exception->getMessage());
        $e->setCode($exception->getCode());

        if ($exception instanceof HttpException)
        {
            $statusCode = $exception->getStatusCode();
            $headers    = array_merge($headers, $exception->getHeaders());
        }

        if ($statusCode === null)
        {
            $statusCode = 500;
        }

        $e->setStatusCode($statusCode);
        $e->setHeaders($headers);
        $e->setClass($exception instanceof FatalThrowableError ? $exception->getOriginalClassName() : get_class($exception));
        $e->setFile($exception->getFile());
        $e->setLine($exception->getLine());
        $e->setTraceFromThrowable($exception);

        $previous = $exception->getPrevious();

        if ($previous instanceof Throwable)
        {
            $e->setPrevious(static::makeFromThrowable($previous));
        }

        return $e;
    }
    
    
}