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
use Syscodes\Http\Response;
use Syscodes\Routing\Pipeline;
use Syscodes\Http\JsonResponse;
use Syscodes\Routing\RouteCollection;
use Syscodes\Contracts\Routing\Routable;

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
	 * @param  \Syscodes\Routing\RouteCollection  $route 
	 *
	 * @return \Syscodes\Http\Response
	 */
	public function resolve($request, Routecollection $route)
	{
		return $this->dispatchToRoute($request, $route);
	}

	/**
	 * Dispatch the request to a route and return the response.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * @param  \Syscodes\Routing\RouteCollection  $route 
	 *
	 * @return \Syscodes\Http\Response
	 */
	protected function dispatchToRoute($request, $route)
	{
		return $this->runRoute($request, 
			$this->findRoute($request, $route)
		);
	}

	/**
	 * Return the response for the given route.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * @param  \Syscodes\Routing\Route  $route
	 * 
	 * @return \Syscodes\Http\Response 
	 */
	protected function runRoute($request, $route)
	{		
		return (new Pipeline($this->container))
				->send($request)
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

	/**
	 * Find the route matching a given request.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * @param  \Syscodes\Routing\RouteCollection  $route
	 * 
	 * @return \Syscodes\Routing\Route
	 */
	protected function findRoute($request, $route)
	{
		// Get all register routes with the same request method
		$this->current = $route = $route->match($request);

		$this->container->instance(Route::class, $route);

		return $route;
	}
}