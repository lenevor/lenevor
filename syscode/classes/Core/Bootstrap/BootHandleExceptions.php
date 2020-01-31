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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Core\Bootstrap;

use Exception;
use ErrorException;
use Syscode\Contracts\Core\Application;
use Syscode\Contracts\Debug\ExceptionHandler;
use Syscode\Debug\FatalExceptions\FatalErrorException;
use Syscode\Debug\FatalExceptions\FatalThrowableError;

/**
 * It is an integrated exception handler that allows you to report and 
 * generate exceptions in a simple and friendly way.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class BootHandleExceptions
{
    /**
     * The application implementation.
     *
     * @var \Syscode\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * Bootstrap the given application.
     *
     * @param  \Syscode\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;
        
        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);
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
        if (error_reporting() & $level)
        {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an exception for the application.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    public function handleException($e)
    {
        if ( ! $e instanceof Exception)
        {
            $e = new FatalThrowableError($e);
        }

        try
        {
            $this->getExceptionHandler()->report($e);
        }
        catch (Exception $e)
        {
            //
        }

        $this->renderHttpResponse($e);
    }

    /**
     * Render an exception as an HTTP response and send it.
     * 
     * @param  \Exception  $e
     * 
     * @return void
     */
    protected function renderHttpResponse(Exception $e)
    {
        $this->getExceptionHandler()->render($this->app['request'], $e)->send(true);
    }
    
    /**
     * Handle the PHP shutdown event.
     * 
     * @return void
     */
    public function handleShutdown()
    {
        if ( ! is_null($error = error_get_last()) && $this->isFatal($error['type']))
        {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }
    
    /**
     * Determine if the error type is fatal.
     * 
     * @param  int  $type
     * 
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, array(E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE));
    }
    
    /**
     * Create a new fatal exception instance from an error array.
     * 
     * @param  array  $error
     * @param  int|null  $traceOffset
     * 
     * @return \Syscode\Debug\FatalExceptions\FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }
    
    /**
     * Get an instance of the exception handler.
     *
     * @return \Syscode\Contracts\Debug\Handler
     */
    protected function getExceptionHandler()
    {    
        return $this->app->make(ExceptionHandler::class);
    }
}