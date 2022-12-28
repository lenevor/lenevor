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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Http;

use Closure;
use Throwable; 
use Syscodes\Components\Routing\Router;
use Syscodes\Components\Support\Facades\Facade;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Routing\Supported\Pipeline;
use Syscodes\Components\Contracts\Debug\ExceptionHandler;
use Syscodes\Components\Contracts\Http\Lenevor as LenevorContract;

/**
 * The Lenevor class is the heart of the system framework.
 */
class Lenevor implements LenevorContract
{
	/**
	 * The application implementation.
	 * 
	 * @var \Syscodes\Components\Contracts\Core\Application $app
	 */
	protected $app;
	
	/**
	 * The bootstrap classes for the application.
	 * 
	 * @var array $bootstrappers
	 */
	protected $bootstrappers = [
		\Syscodes\Components\Core\Bootstrap\BootDetectEnvironment::class,
		\Syscodes\Components\Core\Bootstrap\BootConfiguration::class,
		\Syscodes\Components\Core\Bootstrap\BootHandleExceptions::class,
		\Syscodes\Components\Core\Bootstrap\BootRegisterFacades::class,
		\Syscodes\Components\Core\Bootstrap\BootRegisterProviders::class,
		\Syscodes\Components\Core\Bootstrap\BootProviders::class,
	];

	/**
	 * Get the application's middleware.
	 * 
	 * @var array $middleware
	 */
	protected $middleware = [];

	/**
	 * Get the application's middleware groups.
	 * 
	 * @var array $middlewareGroups
	 */
	protected $middlewareGroups = [];

	/**
	 * The priority list of middleware.
	 * 
	 * @var string[] $middlwarePriority
	 */
	protected $middlwarePriority = [];

	/**
	 * The router instance.
	 * 
	 * @var \Syscodes\Components\Routing\Router $router
	 */
	protected $router;

	/**
	 * Get the application's route middleware.
	 * 
	 * @var array $routeMiddleware
	 */
	protected $routeMiddleware = [];

	/**
	 * Total app execution time.
	 * 
	 * @var float $totalTime
	 */
	protected $totalTime;

	/**
	 * Constructor. Lenevor class instance.
	 * 
	 * @param  \Syscodes\Components\Contracts\Core\Application  $app
	 * @param  \Syscodes\Components\Routing\Router  $router
	 * 
	 * @return void
	 */
	public function __construct(Application $app, Router $router)
	{
		$this->app    = $app;
		$this->router = $router;

		$this->syncMiddlewareRoute();
	}
	 
	/**
	 * Initializes the framework, this can only be called once.
	 * Launch the application.
	 * 
	 * @param  \Syscodes\Components\http\Request  $request
	 *
	 * @return void
	 */
	public function handle($request)
	{
		try {
			$response = $this->sendRequestThroughRouter($request);
		} catch (Throwable $e) {
			$this->reportException($e);

			$response = $this->renderException($request, $e);
		}		

		return $response;
	}

	/**
	 * Send the given request through the router.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return \Syscodes\Components\Http\Response
	 */
	protected function sendRequestThroughRouter($request)
	{
		$this->app->instance('request', $request);  

		Facade::clearResolvedInstance('request');
		
		// Load configuration system
		$this->bootstrap();
		
		return (new Pipeline($this->app))
				->send($request)
				->through($this->app->skipGoingMiddleware() ? [] : $this->middleware)
				->then($this->dispatchToRouter());
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 * 
	 * @return void
	 */
	protected function bootstrap(): void
	{		
		if ( ! $this->app->hasBeenBootstrapped()) {
			$this->app->bootstrapWith($this->bootstrappers());
		}
	}

	/**
	 * Get the bootstrap classes for the application.
	 * 
	 * @return array
	 */
	protected function bootstrappers(): array
	{
		return $this->bootstrappers;
	}

	/**
	 * Sync the current state of the middleware to the router.
	 * 
	 * @return void
	 */
	protected function syncMiddlewareRoute(): void
	{
		$this->router->middlewarePriority = $this->middlwarePriority;
		
		foreach ($this->middlewareGroups as $key => $middleware) {
			$this->router->middlewareGroup($key, $middleware);
		}

		foreach ($this->routeMiddleware as $key => $middleware) {
			$this->router->aliasMiddleware($key, $middleware);
		}
	}

	/**
	 * Get the dispatcher of routes.
	 * 	  
	 * @return \Closure
 	 */
	protected function dispatchToRouter()
	{
		return function ($request) {
			$this->app->instance('request', $request);

			return $this->router->dispatch($request);
		};
	}

	/**
	 * Call the shutdown method on any terminable middleware.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @param  \Syscodes\Components\Http\Response  $response
	 * 
	 * @return void
	 */
	public function shutdown($request, $response): void
	{
		$this->shutdownMiddleware($request, $response);
	}

	/**
	 * Call the terminate method on any terminable middleware.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @param  \Syscodes\Components\Http\Response  $response
	 * 
	 * @return void
	 */
	protected function shutdownMiddleware($request, $response): void
	{
		$middlewares = $this->app->skipGoingMiddleware() ? [] : array_merge(
			$this->gatherRouteMiddleware($request),
			$this->middleware
		);

		foreach ($middlewares as $middleware) {
			if ( ! is_string($middleware)) {
				continue;
			}
			
			[$name] = $this->parseMiddleware($middleware);
			
			$instance = $this->app->make($name);
			
			if (method_exists($instance, 'shutdown')) {
				$instance->shutdown($request, $response);
			}
		}
	}

	/**
	 * Gather the route middleware for the given request.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return array
	 */
	protected function gatherRouteMiddleware($request): array
	{
		if ($route = $request->route()) {
			return $this->router->gatherRouteMiddleware($route);
		}

		return [];
	}
	
	/**
	 * Parse a middleware string to get the name and parameters.
	 * 
	 * @param  string  $middleware
	 * 
	 * @return array
	 */
	protected function parseMiddleware($middleware): array
	{
		[$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, []);
		
		if (is_string($parameters)) {
			$parameters = explode(',', $parameters);
		}
		
		return [$name, $parameters];
    }

	/**
	 * Gets the Lenevor application instance.
	 * 
	 * @return void
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Report the exception to the exception handler.
	 * 
	 * @param  \Throwable  $e
	 * 
	 * @return void
	 */
	protected function reportException(Throwable $e)
	{
		return $this->app[ExceptionHandler::class]->report($e);
	}
	
	/**
	 * Render the exception to a response.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @param  \Throwable  $e
	 * 
	 * @return \Syscodes\Components\Http\Response
	 */
	protected function renderException($request, Throwable $e)
	{
		return $this->app[ExceptionHandler::class]->render($request, $e);
	}
}