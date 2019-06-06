<?php 

namespace Syscode\Core\Exceptions;

use Exception;
use Syscode\Debug\GDebug;
use Syscode\Http\Response;
use Syscode\Core\Http\Exceptions\{
    HttpException,
    NotFoundHttpException
};
use Syscode\Debug\ExceptionHandler;
use Syscode\Http\Exceptions\HttpResponseException;
use Syscode\Debug\FatalExceptions\FlattenException;
use Syscode\Routing\Exceptions\RouteNotFoundException;
use Syscode\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

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
 * @since       0.1.0
 */
class Handler implements ExceptionHandlerContract
{
    /**
     * Render an exception into a response.
     *
     * @param  \Syscode\Http\Request  $request
     * @param  \Exception             $e
     * 
     * @return \Syscode\Http\Response
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
        if ($e instanceof RouteNotFoundException)
        {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }
     
    /**
     * Prepare a response for the given exception.
     * 
     * @param  \Syscode\Http\Request  $request
     * @param  \Exception             $e
     * 
     * @return \Syscode\Http\Response
     * 
     * @uses   \Syscode\Core\Http\Exceptions\HttpException
     */
    protected function prepareResponse($request, Exception $e)
    {
        if ( ! $this->isHttpException($e) && config('app.debug'))
        {
            return $this->toSyscodeResponse($this->convertExceptionToResponse($e), $e);
        }

        // When the debug is not active, the HTTP 500 code view is throw
        if ( ! $this->isHttpException($e)) 
        {
            $e = new HttpException(500, $e->getMessage());
        }

        return $this->toSyscodeResponse($this->renderHttpException($e), $e);
    }

    /**
     * Render the given HttpException.
     * 
     * @param  \Syscode\Core\Http\Exceptions\HttpException  $e
     * 
     * @return \Syscode\Http\Response
     */
    protected function renderHttpException(HttpException $e)
    {
        if (view()->viewExists($view = "errors::{$e->getStatusCode()}"))
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
     * Create a response for the given exception.
     * 
     * @param  \Exception  $e
     * 
     * @return \Syscode\Http\Response
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
     * Handle an incoming HTTP request.
     * 
     * @param  \Exception  $e
     * 
     * @return void
     * 
     * @uses   \Syscode\Debug\GDebug
     */
    protected function renderExceptionWithGDebug(Exception $e)
    {
        return take(new GDebug, function ($debug) {
            
            $debug->pushHandler($this->DebugHandler());

        })->handleException($e);
    }

    /**
     * Get the Debug handler for the application.
     *
     * @return \Syscode\Debug\Handlers\MainHandler
     */
    protected function DebugHandler()
    {
        return (new DebugHandler)->initDebug();
    }

    /**
     * Render an exception to a string using Flat Design Debug.
     * 
     * @param  \Exception  $e
     * @param  bool        $debug
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
     * Map the given exception into an Syscode response.
     * 
     * @param  \Syscode\Http\Response  $response
     * @param  \Exception              $e 
     * 
     * @return $\Syscode\Http\Response
     */
    protected function toSyscodeResponse($response, Exception $e)
    {
        $response = new Response(
            $response->content(),
            $response->status(),
            $response->header()
        );

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