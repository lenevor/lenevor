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
 * @since       0.5.1
 */

namespace Syscodes\Routing;

use Closure;
use Syscodes\Contracts\Routing\Routable;
use Syscodes\Routing\Exceptions\RouteNotFoundException;

/**
 * This class resolve the given route and called the method that belongs to the route.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteResolver
{
	/**
	 * Resolve the given route and call the method that belongs to the route.
	 *
	 * @param  \Syscodes\Contracts\Routing\Routable  $router 
	 * @param  \Syscodes\Http\Request  $request
	 *
	 * @return mixed
	 *
	 * @throws \Syscodes\Routing\Exceptions\RouteNotFoundException
	 */
	public function resolve(Routable $router, $request)
	{
		// Get all register routes with the same request method
		$routes = $router->getRoutesByMethod($request->method());
		
		// Remove trailing and leading slash
		$requestedUri = trim(preg_replace('/\?.*/', '', $request->getUri()), '/');

		// Loop trough the posible routes
		foreach ($routes as $key => $route) 
		{
			$matches = [];

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
			
			// If the requested route matches one of the defined routes
			if ($route->getRoute() === $requestedUri || preg_match_all('~^'.$route->getRoute().'$~', $requestedUri, $matches)) 
			{	
				$arguments = [];
				$paramArgs = $route->getArguments();
				$params    = (new RouteParams($matches))->toEachCountItems();
			
				if (is_array($paramArgs) && count($paramArgs) > 0)
				{
					foreach ($paramArgs as $key => $args)
					{
						if (isset($params[$key]))
						{
							$arguments[$args] = $params[$key];
						}
						else
						{
							$arguments[$args] = null;
						}						
					}
				}

				return $route->runResolver($arguments);
			}
		}

		throw new RouteNotFoundException;
	}	
}