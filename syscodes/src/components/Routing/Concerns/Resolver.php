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

namespace Syscodes\Components\Routing\Concerns;

use ArrayObject;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Http\JsonResponse;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Routing\Events\PreparingResponse;
use Syscodes\Components\Routing\Events\ResponsePrepared;
use Syscodes\Components\Routing\Events\RouteMatched;
use Syscodes\Components\Routing\Events\Routing;
use Syscodes\Components\Routing\Resources\Pipeline;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Support\Stringable;

/**
 * This trait resolve the given route and called the method that belongs to the route.
 */
Trait Resolver
{
	/**
	 * Resolve the given route and call the method that belongs to the route.
	 *
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function resolve(Request $request)
	{
		return $this->dispatchToRoute($request);
	}

	/**
	 * Dispatch the request to a route and return the response.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function dispatchToRoute(Request $request)
	{
		return $this->runRoute($request, $this->findRoute($request));
	}

	/**
	 * Find the route matching a given request.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request 
	 * @return \Syscodes\Components\Routing\Route
	 */
	protected function findRoute($request): Route
	{
		$this->events->dispatch(new Routing($request));
		
		// Get all register routes with the same request method
		$this->current = $route = $this->routes->match($request);

		$route->setContainer($this->container);

		$this->container->instance(Route::class, $route);

		return $route;
	}

	/**
	 * Return the response for the given route.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @param  \Syscodes\Components\Routing\Route  $route 
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function runRoute(Request $request, Route $route)
	{
		$request->setRouteResolver(fn () => $route);

		$this->events->dispatch(new RouteMatched($route, $request));

		return $this->callResponse($request, 
			$this->runRouteStack($route, $request)
		); 
	}

	/**
	 * Run the given route through a stack response instance.
	 * 
	 * @param  \Syscodes\Components\Routing\Route  $route
	 * @param  \Syscodes\Components\Http\Request  $request 
	 * @return mixed
	 */
	protected function runRouteStack(Route $route, Request $request)
	{
		$skipMiddleware = $this->container->bound('middleware.disable') &&
		    ($this->container->make('middleware.disable') === true);						  
		
		$middleware = $skipMiddleware ? [] : $this->gatherRouteMiddleware($route);

		return (new Pipeline($this->container))
		    ->send($request)
		    ->through($middleware)
		    ->then(fn ($request) => $this->callResponse(
				$request, $route->runResolver()
			));
	}

	/**
	 * Create a response instance from the given value.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @param  mixed  $response 
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function callResponse($request, $response)
	{
		$this->events->dispatch(new PreparingResponse($request, $response));

		return take(static::toResponse($request, $response), function ($response) use ($request) {
            $this->events->dispatch(new ResponsePrepared($request, $response));
        });
	}

	/**
	 * Static version of callResponse.
	 * 
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @param  mixed  $response 
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function toResponse($request, $response)
	{
		if ($response instanceof Stringable) {
            $response = new Response($response->__toString(), 200, ['Content-Type' => 'text/html']);
        } elseif ( ! $response instanceof SymfonyResponse &&
			    ($response instanceof Arrayable ||
			    $response instanceof Jsonable ||
			    $response instanceof ArrayObject ||
			    $response instanceof JsonSerializable ||
			    $response instanceof \stdClass ||
			    is_array($response))) {
            $response = new JsonResponse($response);
        } elseif ( ! $response instanceof SymfonyResponse) {
            $response = new Response($response, 200, ['Content-Type' => 'text/html']);
        }
		
		if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
			$response->setNotModified();
		}

		return $response->prepare($request);
	}
}