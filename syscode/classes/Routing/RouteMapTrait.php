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
 * @since       0.1.0
 */

namespace Syscode\Routing;

/**
 * The RouteMap trait.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait RouteMapTrait
{
	/**
	 * Add new route to routes array.
	 *
	 * @param  string  $method
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return \Syscode\Routing\Route
	 */
	abstract public function map($method, $route, $action);

	/**
	 * Add a route for all posible methods.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function any($route, $action = null) 
	{
		$methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];
		
		return $this->map($methods, $route, $action);
	}
	
	/**
	 * Add a route with delete method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function delete($route, $action = null) 
	{
		return $this->map(['DELETE'], $route, $action);
	}

	/**
	 * Add a route with get method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function get($route, $action = null) 
	{
		return $this->map(['GET', 'HEAD'], $route, $action);
	}

	/**
	 * Add a route with head method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function head($route, $action = null)
	{
		return $this->map(['HEAD'], $route, $action);
	}

	/**
	 * Register a new route with the given methods.
	 * 
	 * @param  array|string  $methods
	 * @param  string  $route
	 * @param  string|null|callable  $action
	 * 
	 * @return void
	 */
	public function match($methods, $route, $action = null)
	{
		return $this->map($methods, $route, $action);
	}

	/**
	 * Add a route with options method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function options($route, $action = null) 
	{
		return $this->map(['OPTIONS'], $route, $action);
	}

	/**
	 * Add a route with patch method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function patch($route, $action = null)
	{
		return $this->map(['PATCH'], $route, $action);
	}

	/**
	 * Add a route with post method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function post($route, $action = null) 
	{
		return $this->map(['POST'], $route, $action);
	}

	/**
	 * Add a route with put method.
	 *
	 * @param  string  $route
	 * @param  string|callable  $action
	 *
	 * @return void
	 */
	public function put($route, $action = null) 
	{
		return $this->map(['PUT'], $route, $action);
	}  
}