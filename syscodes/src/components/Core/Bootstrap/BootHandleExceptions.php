<?php

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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Bootstrap;

use Closure;
use Exception;
use Throwable;
use ErrorException;
use Syscodes\Components\Log\LogManager;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Error\FatalError;

/**
 * It is an integrated exception handler that allows you to report and 
 * generate exceptions in a simple and friendly way.
 */
class BootHandleExceptions
{
    /**
     * The application implementation.
     *
     * @var \Syscodes\Components\Contracts\Core\Application
     */
    protected static $app;
    
    /**
     * Bootstrap the given application.
     *
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function bootstrap(Application $app)
    {
        static::$app = $app;

        error_reporting(-1);
        
        set_error_handler($this->forwardsTo('handleError'));

        set_exception_handler($this->forwardsTo('handleException'));

        register_shutdown_function($this->forwardsTo('handleShutdown'));

        if ( ! $app->isUnitTests()) {
            ini_set('display_errors', 'off');
        }
    }

    /**
     * Handle a PHP error for the application.
     * 
     * @param  int  $level
     * @param  string  $message
     * @param  string|null  $file
     * @param  int  $line
     * @param  array  $context
     * 
     * @return void
     * 
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if ($this->isDeprecation($level)) {
            $this->handleDeprecationError($message, $file, $line, $level);
        } elseif (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Reports a deprecation to the "deprecations" logger.
     *
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  int  $level
     * 
     * @return void
     */
    public function handleDeprecationError($message, $file, $line, $level = E_DEPRECATED)
    {
        try {
            $logger = static::$app->make(LogManager::class);
        } catch (Exception) {
            return;
        }

        with($logger->store(), function ($log) use ($message, $file, $line, $level) {
            if ($level ?? false) {
                $log->warning((string) new ErrorException($message, 0, $level, $file, $line));
            } else {
                $log->warning(sprintf('%s in %s on line %s', $message, $file, $line));
            }
        });   
    }

    /**
     * Handle an exception for the application.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    public function handleException(Throwable $e)
    {
        try {
            $this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            $exceptionHandlerFailed = true;
        }

        if ($this->app->runningInConsole()) { 
            $this->renderForConsole($e);
            
            if ($exceptionHandlerFailed ?? false) {
                exit(1);
            }
        } else {
            $this->renderHttpResponse($e);
        }
    }

    /**
     * Render an exception to the console.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    protected function renderForConsole(Throwable $e)
    {
        $this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
    }

    /**
     * Render an exception as an HTTP response and send it.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    protected function renderHttpResponse(Throwable $e)
    {
        $this->getExceptionHandler()->render(static::$app['request'], $e)->send();
    }
    
    /**
     * Handle the PHP shutdown event.
     * 
     * @return void
     */
    public function handleShutdown()
    {
        if ( ! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }
    
    /**
     * Forward a method call to the given method if an application instance exists.
     * 
     * @return callable
     */
    protected function forwardsTo($method): Closure
    {
        return fn (...$arguments) => static::$app
            ? $this->{$method}(...$arguments)
            : false;
    }
    
    /**
     * Determine if the error level is a deprecation.
     * 
     * @param  int  $level
     * 
     * @return bool
     */
    protected function isDeprecation($level): bool
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }
    
    /**
     * Determine if the error type is fatal.
     * 
     * @param  int  $type
     * 
     * @return bool
     */
    protected function isFatal($type): bool
    {
        return in_array($type, array(E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE));
    }
    
    /**
     * Create a new fatal exception instance from an error array.
     * 
     * @param  array  $error
     * @param  int|null  $traceOffset
     * 
     * @return \Symfony\Component\ErrorHandler\Error\FatalError
     */
    protected function fatalExceptionFromError(array $error, ?int $traceOffset = null)
    {
        return new FatalError($error['message'], 0, $error, $traceOffset);
    }
    
    /**
     * Get an instance of the exception handler.
     *
     * @return \Syscodes\Components\Contracts\Debug\ExceptionHandler
     */
    protected function getExceptionHandler()
    {    
        return static::$app->make(ExceptionHandler::class);
    }
}