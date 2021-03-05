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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Routing\Concerns;

use Closure;
use JsonSerializable;
use Syscodes\Http\Request;
use Syscodes\Http\Response;
use Syscodes\Routing\Route;
use Syscodes\Routing\Pipeline;
use Syscodes\Http\JsonResponse;

/**
 * This trait resolve the given route and called the method that belongs to the route.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait RouteResolver
{
	/**
	 * The currently dispatched route instance.
	 * 
	 * @var \Syscodes\Routing\Route|null
	 */
	protected $current;

	/**
	 * Resolve the given route and call the method that belongs to the route.
	 *
	 * @param  \Syscodes\Http\Request  $request
	 *
	 * @return \Syscodes\Http\Response
	 */
	public function resolve(Request $request)
	{
		return $this->dispatchToRoute($request);
	}

	/**
	 * Dispatch the request to a route and return the response.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 *
	 * @return \Syscodes\Http\Response
	 */
	protected function dispatchToRoute(Request $request)
	{
		return $this->runRoute($request, $this->findRoute($request));
	}

	/**
	 * Find the route matching a given request.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * 
	 * @return \Syscodes\Routing\Route
	 */
	protected function findRoute($request)
	{
		// Get all register routes with the same request method
		$this->current = $route = $this->routes->match($request);

		$this->container->instance(Route::class, $route);

		return $route;
	}

	/**
	 * Return the response for the given route.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * @param  \Syscodes\Routing\Route  $route
	 * 
	 * @return \Syscodes\Http\Response
	 */
	protected function runRoute(Request $request, Route $route)
	{
		$request->setRouteResolver(function () use ($route) {
			return $route;
		});

		return $this->callResponse($request, 
			$this->runRouteStack($route, $request)
		); 
	}

	/**
	 * Run the given route through a stack response instance.
	 * 
	 * @param  \Syscodes\Routing\Route  $route
	 * @param  \Syscodes\Http\Request  $request
	 * 
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
				->then(function ($request) use ($route) {
					return $this->callResponse(
						$request, $route->runResolver()
					); 
				});
	}

	/**
	 * Create a response instance from the given value.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * @param  mixed  $response
	 * 
	 * @return \Syscodes\Http\Response
	 */
	protected function callResponse($request, $response)
	{
		if ( ! $response instanceof Response && 
		      ($response instanceof Jsonserializable || 
			   is_array($response))) {
			$response = new JsonResponse($response);
		} elseif ( ! $response instanceof Response) {
			$response = new Response($response, 200, ['Content-Type' => 'text/html']);
		}

		return $response->prepare($request);
	}
}