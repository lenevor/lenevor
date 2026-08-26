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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Exceptions;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Syscodes\Components\Auth\Access\Exceptions\AuthorizationException;
use Syscodes\Components\Auth\Exceptions\AuthenticationException;
use Syscodes\Components\Console\View\Components\BulletList;
use Syscodes\Components\Console\View\Components\Error;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Core\ExceptionRender;
use Syscodes\Components\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Syscodes\Components\Core\Http\Exceptions\BadRequestHttpException;
use Syscodes\Components\Core\Http\Exceptions\HttpException;
use Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException;
use Syscodes\Components\Core\Http\Exceptions\AccessDeniedHttpException;
use Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException;
use Syscodes\Components\Database\Exceptions\RecordNotFoundException;
use Syscodes\Components\Database\Exceptions\RecordsNotFoundException;
use Syscodes\Components\Http\Exceptions\HttpResponseException;
use Syscodes\Components\Http\RedirectResponse;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Routing\Router;
use Syscodes\Components\Session\Exceptions\TokenMismatchException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Stringable;
use Syscodes\Components\Support\ViewErrorBag;
use Syscodes\Components\Validation\Exceptions\ValidationException;
use Throwable;

/**
 * The system's main exception class is loaded for activate the render method of debugging.
*/
class Handler implements ExceptionHandlerContract
{
    /**
     * The container implementation.
     * 
     * @var \Syscodes\Components\Contracts\Container\Container 
     */
    protected $container;

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * A list of the exception types that should not be reported.
     * 
     * @var array 
     */
    protected $dontReport = [];

    /**
     * A list of the Core exception types that should not be reported.
     * 
     * @var array 
     */
    protected $coreDontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        RecordNotFoundException::class,
        RecordsNotFoundException::class,
        RequestExceptionInterface::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * The callback that prepares responses to be returned to the browser.
     *
     * @var callable|null
     */
    protected $finalizeResponseCallback;

    /**
     * The callbacks that should be used during reporting.
     * 
     * @var array 
     */
    protected $reportCallbacks = [];

    /**
     * The callbacks that should be used during rendering.
     * 
     * @var array 
     */
    protected $renderCallbacks = [];

    /**
     * The callback that determines if the exception handler response should be JSON.
     *
     * @var callable|null
     */
    protected $shouldRenderJsonWhenCallback;

    /**
     * Constructor. Create a new exception handler instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container 
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->register();
    }

    /**
     * Register the exception handling with callbacks for the application.
     * 
     * @return void
     */
    public function register() {}

    /**
     * Register a reportable callback.
     * 
     * @param  callable  $callback 
     * @return static
     */
    public function reportable(callable $callback): static
    {
        $this->reportCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a renderable callback.
     * 
     * @param  callable  $callback 
     * @return static
     */
    public function renderable(callable $callback): static
    {
        $this->renderCallbacks[] = $callback;

        return $this;
    }
    
    /**
     * Report or log an exception.
     * 
     * @param  \Throwable  $e 
     * @return mixed
     * 
     * @throws \Exception
     */
    public function report(Throwable $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (method_exists($e, 'report')) {
            return $e->report($e);
        }
        
        foreach ($this->reportCallbacks as $reportCallback) {
            if ($reportCallback($e) === false) {
                return;
            }
        }

        try {
            $logger = $this->newLogger();
        } catch (Exception $e) {
            throw $e;
        }
        
        $logger->error($e->getMessage());
    }

    /**
     * Determine if the exception should be reported.
     * 
     * @param  \Throwable  $e 
     * @return bool
     */
    public function shouldReport(Throwable $e): bool
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     * 
     * @param  \Throwable  $e 
     * @return bool
     */
    public function shouldntReport(Throwable $e): bool
    {
        $dontReport = array_merge($this->dontReport, $this->coreDontReport);
        
        return ! is_null(Arr::first($dontReport, fn ($type) => $e instanceof $type));
    }

    /**
     * Render an exception into an HTTP response.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Throwable  $e 
     * @return \Syscodes\Components\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return $this->finalizeRenderedResponse(
                $request,
                Router::toResponse($request, $response),
                $e
            );
        }
        
        $e = $this->prepareException($e);
        
        if ($response = $this->renderViaCallbacks($request, $e)) {
            return $this->finalizeRenderedResponse($request, $response, $e);
        }

        return $this->finalizeRenderedResponse($request, match (true) {
            $e instanceof HttpResponseException => $e->getResponse(),
            $e instanceof AuthenticationException => $this->unauthenticated($request, $e),
            $e instanceof ValidationException => $this->convertValidationExceptionToResponse($e, $request),
            default => $this->renderExceptionResponse($request, $e),
        }, $e);
    }

    /**
     * Prepare the final, rendered response to be returned to the browser.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function finalizeRenderedResponse($request, $response, Throwable $e)
    {
        return $this->finalizeResponseCallback
            ? call_user_func($this->finalizeResponseCallback, $response, $e, $request)
            : $response;
    }

    /**
     * Prepare the final, rendered response for an exception using the given callback.
     *
     * @param  callable  $callback
     * @return static
     */
    public function respondUsing($callback): static
    {
        $this->finalizeResponseCallback = $callback;

        return $this;
    }

    /**
     * Prepare exception for rendering.
     * 
     * @param  Throwable  $e 
     * @return Throwable
     */
    protected function prepareException(Throwable $e): Throwable
    {
        return match (true) {
            $e instanceof ModelNotFoundException => new NotFoundHttpException($e->getMessage(), $e),
            $e instanceof AuthorizationException && $e->hasStatus() => new HttpException(
                $e->status(), $e->response()?->message() ?: (Response::$statusTexts[$e->status()] ?? 'Whoops, looks like something went wrong.'), $e
            ),
            $e instanceof AuthorizationException && ! $e->hasStatus() => new AccessDeniedHttpException($e->getMessage(), $e),
            $e instanceof TokenMismatchException => new HttpException(419, $e->getMessage(), $e),
            $e instanceof RequestExceptionInterface => new BadRequestHttpException('Bad request.', $e),
            $e instanceof RecordNotFoundException => new NotFoundHttpException('Not found.', $e),
            $e instanceof RecordsNotFoundException => new NotFoundHttpException('Not found.', $e),
            default => $e,
        };
    }

    /**
     * Try to render a response from request and exception via render callbacks.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function renderViaCallbacks($request, Throwable $e)
    {
        foreach ($this->renderCallbacks as $renderCallback) {
            foreach ($this->firstClosureParameterTypes($renderCallback) as $type) {
                if (is_a($e, $type)) {
                    $response = $renderCallback($e, $request);

                    if ( ! is_null($response)) {
                        return $response;
                    }
                }
            }
        }
    }
    
    /**
     * Render a default exception response if any.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Syscodes\Components\Http\Response|\Syscodes\Components\Http\JsonResponse|\Syscodes\Components\Http\RedirectResponse
     */
    protected function renderExceptionResponse($request, Throwable $e)
    {
        return $this->shouldReturnJson($request, $e)
            ? $this->prepareJsonResponse($request, $e)
            : $this->prepareResponse($request, $e);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Auth\Exceptions\AuthenticationException  $exception
     * @return \Syscodes\Components\Http\Response|\Syscodes\Components\Http\JsonResponse|\Syscodes\Components\Http\RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        $redirectTo = $exception->redirectTo($request);

        if ( ! $redirectTo) {
            return response()->noContent(401);
        }

        return redirect()->guest($redirectTo);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Syscodes\Components\Validation\Exceptions\ValidationException  $e
     * @param  \Syscodes\Components\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return $this->shouldReturnJson($request, $e)
            ? $this->invalidJson($request, $e)
            : $this->invalid($request, $e);
    }

    /**
     * Convert a validation exception into a response.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Validation\Exceptions\ValidationException  $exception
     * @return \Syscodes\Components\Http\Response|\Syscodes\Components\Http\JsonResponse|\Syscodes\Components\Http\RedirectResponse
     */
    protected function invalid($request, ValidationException $exception)
    {
        return redirect($exception->redirectTo ?? url()->previous())
            ->withInput(Arr::except($request->input(), $this->dontFlash))
            ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Validation\Exceptions\ValidationException  $exception
     * @return \Syscodes\Components\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Determine if the exception handler response should be JSON.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Throwable  $e
     * @return bool
     */
    protected function shouldReturnJson($request, $e)
    {
        return $this->shouldRenderJsonWhenCallback
            ? call_user_func($this->shouldRenderJsonWhenCallback, $request, $e)
            : $request->expectsJson();
    }

    /**
     * Register the callable that determines if the exception handler response should be JSON.
     *
     * @param  callable(\Syscodes\Components\Http\Request $request, \Throwable): bool  $callback
     * @return static
     */
    public function shouldRenderJsonWhen($callback): static
    {
        $this->shouldRenderJsonWhenCallback = $callback;

        return $this;
    }

    /**
     * Prepare a response for the given exception.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  Throwable  $e 
     * @return \Syscodes\Components\Http\Response
     * 
     * @uses   \Syscodes\Components\Core\Http\Exceptions\HttpException
     */
    protected function prepareResponse($request, Throwable $e)
    {
        if ( ! $this->isHttpException($e) && config('app.debug')) {
            return $this->toSyscodesResponse($this->convertExceptionToResponse($e), $e)->prepare($request);
        }

        // When the debug is not active, the HTTP 500 code view is throw
        if ( ! $this->isHttpException($e)) {
            $e = new HttpException(500, $e->getMessage());
        }

        return $this->toSyscodesResponse(
            $this->renderHttpException($e), $e
        )->prepare($request);
    }

    /**
     * Render the given HttpException.
     * 
     * @param  \Syscodes\Components\Core\Http\Exceptions\HttpException  $e 
     * @return \Syscodes\Components\Http\Response
     */
    protected function renderHttpException(HttpException $e)
    {
        $this->registerViewErrorPaths();

        if ($view = $this->getHttpExceptionView($e)) {
            try {
                return response()->view($view, [
                    'errors' => new ViewErrorBag,
                    'exception' => $e,
                ], $e->getStatusCode(), $e->getHeaders());
            } catch (Throwable $th) {
                config('app.debug') && throw $th;

                $this->report($th);
            }
        }

        return $this->convertExceptionToResponse($e);
    }

    /**
     * Register the error view paths.
     * 
     * @return void
     */
    protected function registerViewErrorPaths(): void
    {
        (new RegisterErrorViewPaths)();
    }

    /**
     * Get the view used to render HTTP exceptions.
     * 
     * @param  \Syscodes\Components\Core\Http\Exceptions\HttpException  $e 
     * @return string|null
     */
    protected function getHttpExceptionView(HttpException $e): string|null
    {
        $view = 'errors::'.$e->getStatusCode();

        if (view()->exists($view)) {
            return $view;
        }

        return null;
    }

    /**
     * Create a response for the given exception.
     * 
     * @param  Throwable  $e 
     * @return \Syscodes\Components\Http\Response
     */
    protected function convertExceptionToResponse(Throwable $e)
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
     * @param  Throwable  $e 
     * @return string
     */
    protected function renderExceptionContent(Throwable $e)
    {
        try {
            if (config('app.debug')) {
                if (app()->has(ExceptionRender::class)) {
                    return $this->renderExceptionWithCustomDebug($e);
                } 
            }
            
            return $this->renderExceptionWithSymfony($e, config('app.debug'));
        } catch (Throwable $e) {
            return $this->renderExceptionWithSymfony($e, config('app.debug'));
        }
    }

    /**
     * Render an exception to a string of debug.
     * 
     * @param  Throwable  $e 
     * @return void
     * 
     * @uses   \Syscodes\Components\Contracts\Core\ExceptionRender  
     */
    protected function renderExceptionWithCustomDebug(Throwable $e)
    {
        return app(ExceptionRender::class)->render($e);
    }

    /**
     * Render an exception to a string using Symfony.
     * 
     * @param  Throwable  $e
     * @param  bool  $debug 
     * @return string
     */
    protected function renderExceptionWithSymfony(Throwable $e, $debug)
    {
        $renderer = new HtmlErrorRenderer($debug);
        
        return $renderer->render($e)->getAsString();
    }

    /**
     * Map the given exception into an Syscodes response.
     * 
     * @param  \Syscodes\Components\Http\Response  $response
     * @param  Throwable  $e 
     * @return \Syscodes\Components\Http\Response
     */
    protected function toSyscodesResponse($response, Throwable $e)
    {
        if ($response instanceof RedirectResponse) {
            $response = new RedirectResponse(
                $response->getTargetUrl(), $response->status(), $response->headers->all()
            );
        } else {
            $response = new Response(
                $response->content(), $response->status(), $response->headers->all()
            );
        }

        return $response->withException($e);
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Syscodes\Components\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        return response()->json(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => (new Collection($e->getTrace()))->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * Render an exception to the console.
     * 
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  Throwable  $e 
     * @return void
     */
    public function renderForConsole($output, Throwable $e)
    {
        if ($e instanceof CommandNotFoundException) {
            $message = (new Stringable($e->getMessage()))->explode('.')->first();

            if ( ! empty($alternatives = $e->getAlternatives())) {
                $message .= '. Did you mean one of these?';

                (new Error($output))->render($message);
                (new BulletList($output))->render($alternatives);

                $output->writeln('');
            } else {
                (new Error($output))->render($message);
            }

            return;
        }

        (new ConsoleApplication)->renderThrowable($e, $output);
    }

    /**
     * Determine if the given exception is an HTTP exception.
     * 
     * @param  Throwable  $e 
     * @return bool
     */
    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
    
    /**
     * Create a new logger instance.
     * 
     * @return \Psr\Log\LoggerInterface
     */
    protected function newLogger()
    {
        return $this->container->make(LoggerInterface::class);
    }
}