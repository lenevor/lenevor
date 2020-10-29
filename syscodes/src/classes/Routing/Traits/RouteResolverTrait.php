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
 * @since       0.7.3
 */

namespace Syscodes\Routing\Traits;

use Closure;
use JsonSerializable;
use Syscodes\Http\Response;
use Syscodes\Http\JsonResponse;
use Syscodes\Pipeline\Pipeline;
use Syscodes\Routing\RouteCollection;
use Syscodes\Contracts\Routing\Routable;
use Syscodes\Routing\Exceptions\RouteNotFoundException;

/**
 * This trait resolve the given route and called the method that belongs to the route.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait RouteResolverTrait
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
			   is_array($response)))
		{
			$response = new JsonResponse($response);
		}
		elseif ( ! $response instanceof Response)
		{
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
	 * 
	 * @throws \Syscodes\Routing\Exceptions\RouteNotFoundException
	 */
	protected function findRoute($request, $route)
	{
		// Get all register routes with the same request method
		$routes = $route->match($request);
		
		$this->container->instance(Route::class, $routes);

		// Remove trailing and leading slash
		$requestedUri = $request->url();

		// Loop trough the possible routes
		foreach ($routes as $route) 
		{
			// Variable assignment by route
			$this->current = $route;

			if (isset($requestedUri))
			{
				$host = $route->getHost();
				
				if ($host !== null && $host != $request->getHost())
				{
					continue;
				}
				
				$scheme = $route->getScheme();
				
				if ($scheme !== null && $scheme !== $request->getScheme())
				{
					continue;
				}
				
				$port = $route->getPort();
				
				if ($port !== null && $port !== $request->getPort())
				{
					continue;
				}
			}
			
			// If the requested route one of the defined routes
			if ($this->compareUri($route->getRoute(), $requestedUri)) 
			{					
				return $route->bind($request);
			}
		}

		throw new RouteNotFoundException;
	}

	/**
	 * Check if given request uri matches given uri method.
	 * 
	 * @param  string  $route
	 * @param  string  $requestedUri
	 * 
	 * @return bool
	 */
	protected function compareUri(string $route, string $requestedUri)
	{
		$pattern = '~^'.$this->regexUri($route).'$~';

		return preg_match($pattern, $requestedUri);
	}

	/**
	 * Get the currently dispatched route instance.
	 * 
	 * @return \Syscodes\Routing\Route|null
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Determine if the current route matches a pattern.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function currentRouteNamed(...$patterns)
	{
		return $this->current() && $this->current()->named(...$patterns);
	}

	/**
	 * Convert route to regex.
	 * 
	 * @param  string  $route
	 * 
	 * @return string
	 */
	protected function regexUri(string $route)
	{
		return preg_replace_callback('~\{([^/]+)\}~', function (array $match) 
		{
			return $this->regexParameter($match[1]);
		}, $route);
	}

	/**
	 * Convert route parameter to regex.
	 * 
	 * @param  string  $name
	 * 
	 * @return string
	 */
	protected function regexParameter(string $name)
	{
		$pattern = $this->current->wheres[$name] ?? '[^/]+';

		return '(?<'.$name.'>'.$pattern.')';
	}
}