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

/**
 * The Mapper trait.
 */
trait Mapper
{
	/**
	 * All of the verbs supported by the router.
	 * 
	 * @var array $verbs
	 */
	public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];

	/**
	 * Add a route to the underlying route collection.
	 *
	 * @param  string  $method
	 * @param  string  $route
	 * @param  mixed  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	abstract public function addRoute($method, $route, $action);

	/**
	 * Add a route for all posible methods.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function any($route, $action = null) 
	{		
		return $this->addRoute(self::$verbs, $route, $action);
	}
	
	/**
	 * Add a route with delete method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function delete($route, $action = null) 
	{
		return $this->addRoute('DELETE', $route, $action);
	}

	/**
	 * Add a route with get method.
	 * 
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function get($route, $action = null) 
	{
		return $this->addRoute(['GET', 'HEAD'], $route, $action);
	}

	/**
	 * Add a route with head method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function head($route, $action = null)
	{
		return $this->addRoute('HEAD', $route, $action);
	}

	/**
	 * Register a new route with the given methods.
	 * 
	 * @param  array|string  $methods
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function match($methods, $route, $action = null)
	{
		return $this->addRoute(array_map('strtoupper', (array) $methods), $route, $action);
	}

	/**
	 * Add a route with options method
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function options($route, $action = null) 
	{
		return $this->addRoute('OPTIONS', $route, $action);
	}

	/**
	 * Add a route with patch method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function patch($route, $action = null)
	{
		return $this->addRoute('PATCH', $route, $action);
	}

	/**
	 * Add a route with post method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function post($route, $action = null) 
	{
		return $this->addRoute('POST', $route, $action);
	}

	/**
	 * Add a route with put method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string|null  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function put($route, $action = null) 
	{
		return $this->addRoute('PUT', $route, $action);
	}  
}