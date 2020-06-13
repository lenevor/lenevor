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
 * @since       0.7.0
 */

namespace Syscodes\Routing;

/**
 * 
 */
class RouteParamBinding
{
    /**
	 * Find the route matching a given request.
	 * 
	 * @param  \Syscodes\Contracts\Routing\Routable  $router 
	 * @param  \Syscodes\Http\Request  $request
	 * 
	 * @return \Syscodes\Routing\Route
	 */
	protected function findRoute($router, $request)
	{
		$route = $router->routes->match($request);

		return $this->sustituteBindings($route);
	}
	
	/**
	 * Substitute the route bindings onto the route.
	 * 
	 * @param  \Syscodes\Routing\Route  $route
	 * 
	 * @return \Syscodes\Routing\Route
	 */
	protected function sustituteBindings($route)
	{
		foreach ($route->parameters() as $key => $value)
		{
			if (isset($this->binders[$key]))
			{
				$route->setParameter($key, $this->performBinding($key, $value, $route));				
			}
		}

		return $route;
	}

	/**
	 * Call the binding callback for the given key.
	 * 
	 * @param  string  $key
	 * @param  string  $value
	 * @param  \Syscodes\Routing\Route  $route
	 * 
	 * @return mixed
	 */
	protected function performBinding($key, $value, $route)
	{
		$callback = $this->binders[$key];

		return call_user_func($key, $value, $route);
	}
}