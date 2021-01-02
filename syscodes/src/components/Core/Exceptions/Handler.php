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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.2
 */

namespace Syscodes\Core\Exceptions;

use Exception;
use Syscodes\Debug\GDebug;
use Syscodes\Http\Response;
use Psr\Log\LoggerInterface;
use Syscodes\Collections\Arr;
use Syscodes\Debug\ExceptionHandler;
use Syscodes\Contracts\Container\Container;
use Syscodes\Core\Http\Exceptions\HttpException;
use Syscodes\Http\Exceptions\HttpResponseException;
use Syscodes\Debug\FatalExceptions\FlattenException;
use Syscodes\Database\Exceptions\ModelNotFoundException;
use Syscodes\Core\Http\Exceptions\NotFoundHttpException;
use Syscodes\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

/**
 * The system's main exception class is loaded for activate the render method of debugging.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Handler implements ExceptionHandlerContract
{
    /**
     * The container implementation.
     * 
     * @var \Syscodes\Contracts\Container\Container $container
     */
    protected $container;

    /**
     * A list of the exception types that should not be reported.
     * 
     * @var array $dontReport
     */
    protected $dontReport = [];

    /**
     * A list of the Core exception types that should not be reported.
     * 
     * @var array $coreDontReport
     */
    protected $coreDontReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Constructor. Create a new exception handler instance.
     * 
     * @param  \Syscodes\Contracts\Container\Container  $container
     * 
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Report or log an exception.
     * 
     * @param  \Exception  $e
     * 
     * @return mixed
     * 
     * @throws \Exception
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e))
        {
            return;
        }

        if (method_exists($e, 'report'))
        {
            return $e->report($e);
        }

        try
        {
            $logger = $this->container->make(LoggerInterface::class);
        }
        catch (Exception $e)
        {
            throw $e;
        }
        
        $logger->error($e->getMessage());
    }

    /**
     * Determine if the exception should be reported.
     * 
     * @param  \Exception  $e
     * 
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     * 
     * @param  \Exception  $e
     * 
     * @return bool
     */
    public function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, $this->coreDontReport);

        foreach ($dontReport as $type) 
        {
            if ($e instanceof $type) 
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Render an exception into a response.
     *
     * @param  \Syscodes\Http\Request  $request
     * @param  \Exception  $e
     * 
     * @return \Syscodes\Http\Response
     */
    public function render($request, Exception $e)
    {
        $e = $this->prepareException($e);

        if ($e instanceof HttpResponseException)
        {
            $e->getResponse();
        }

        return $this->prepareResponse($request, $e);
    }

    /**
     * Prepare exception for rendering.
     * 
     * @param  \Exception  $e
     * 
     * @return \Exception
     */
    protected function prepareException(Exception $e)
    {
        if ($e instanceof ModelNotFoundException)
        {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }
     
    /**
     * Prepare a response for the given exception.
     * 
     * @param  \Syscodes\Http\Request  $request
     * @param  \Exception  $e
     * 
     * @return \Syscodes\Http\Response
     * 
     * @uses   \Syscodes\Core\Http\Exceptions\HttpException
     */
    protected function prepareResponse($request, Exception $e)
    {
        if ( ! $this->isHttpException($e) && config('app.debug'))
        {
            return $this->toSyscodesResponse($this->convertExceptionToResponse($e), $e);
        }

        // When the debug is not active, the HTTP 500 code view is throw
        if ( ! $this->isHttpException($e)) 
        {
            $e = new HttpException(500, $e->getMessage());
        }

        return $this->toSyscodesResponse($this->renderHttpException($e), $e);
    }

    /**
     * Render the given HttpException.
     * 
     * @param  \Syscodes\Core\Http\Exceptions\HttpException  $e
     * 
     * @return \Syscodes\Http\Response
     */
    protected function renderHttpException(HttpException $e)
    {
        $this->registerViewErrorPaths();

        if (view()->viewExists($view = $this->getHttpExceptionView($e)))
        {
            return response()->view(
                $view, 
                ['exception' => $e],
                $e->getStatusCode(),
                $e->getHeaders()
            );
        }

        return $this->convertExceptionToResponse($e);
    }

    /**
     * Register the error view paths.
     * 
     * @return void
     */
    protected function registerViewErrorPaths()
    {
        (new RegisterErrorViewPaths)();
    }

    /**
     * Get the view used to render HTTP exceptions.
     * 
     * @param  \Syscodes\Core\Http\Exceptions\HttpException  $e
     * 
     * @return string
     */
    protected function getHttpExceptionView(HttpException $e)
    {
        return "errors::{$e->getStatusCode()}";
    }

    /**
     * Create a response for the given exception.
     * 
     * @param  \Exception  $e
     * 
     * @return \Syscodes\Http\Response
     */
    protected function convertExceptionToResponse(Exception $e)
    {
        return Response::render(
            $this->renderExceptionContent($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }

    /**
     * Gets the response content for the given exception.
     * 
     * @param  \Exception  $e
     * 
     * @return string
     */
    protected function renderExceptionContent(Exception $e)
    {
        try 
        {
            return config('app.debug') && class_exists(GDebug::class)
                        ? $this->renderExceptionWithGDebug($e) 
                        : $this->renderExceptionWithFlatDesignDebug($e, config('app.debug'));
        }
        catch (Exception $e)
        {
            $this->renderExceptionWithFlatDesignDebug($e, config('app.debug'));
        }
    }

    /**
     * Render an exception to a string using "GDebug".
     * 
     * @param  \Exception  $e
     * 
     * @return void
     * 
     * @uses   \Syscodes\Debug\GDebug
     */
    protected function renderExceptionWithGDebug(Exception $e)
    {
        return take(new GDebug, function ($debug) {
            
            $debug->pushHandler($this->DebugHandler());

            $debug->writeToOutput(false);

            $debug->allowQuit(false);

        })->handleException($e);
    }

    /**
     * Get the Debug handler for the application.
     *
     * @return \Syscodes\Debug\Handlers\MainHandler
     */
    protected function DebugHandler()
    {
        return (new DebugHandler)->initDebug();
    }

    /**
     * Render an exception to a string using Flat Design Debug.
     * 
     * @param  \Exception  $e
     * @param  bool  $debug
     * 
     * @return string
     */
    protected function renderExceptionWithFlatDesignDebug(Exception $e, $debug)
    {
        return (new ExceptionHandler($debug))->getHtmlResponse(
            FlattenException::make($e)
        );
    }

    /**
     * Map the given exception into an Syscodes response.
     * 
     * @param  \Syscodes\Http\Response  $response
     * @param  \Exception  $e 
     * 
     * @return \Syscodes\Http\Response
     */
    protected function toSyscodesResponse($response, Exception $e)
    {
        if ($response instanceof RedirectResponse)
        {
            $response = new RedirectResponse(
                $response->getTargetUrl(), $response->status(), $response->headers->all()
            );
        }
        else
        {
            $response = new Response(
                $response->content(), $response->status(), $response->headers->all()
            );
        }

        return $response->withException($e);
    }

    /**
     * Determine if the given exception is an HTTP exception.
     * 
     * @param  \Exception  $e
     * 
     * @return bool
     */
    protected function isHttpException(Exception $e)
    {
        return $e instanceof HttpException;
    }
}