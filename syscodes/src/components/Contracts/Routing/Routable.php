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
 * @since       0.1.2
 */

namespace Syscodes\Contracts\Routing;

/**
 * All Lenevor routes are defined in your route files, which are located in the routes 
 * directory and called depending on the HTTP verbs used by the user.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface Routable
{
	/**
	 * Add a route for all posible methods.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function any($route, $action);

	/**
	 * Add a route with delete method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function delete($route, $action);

	/**
	 * Add a route with get method.
	 * 
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function get($route, $action);

	/**
	 * Add a route with head method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function head($route, $action);

	/**
	 * Add a route with options method
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function options($route, $action);

	/**
	 * Add a route with patch method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function patch($route, $action);

	/**
	 * Add a route with post method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function post($route, $action);

	/**
	 * Add a route with put method.
	 *
	 * @param  string  $route
	 * @param  \Closure|array|string  $action
	 *
	 * @return void
	 */
	public function put($route, $action);

	/**
	 * Group a series of routes under a single URL segment. This is handy
	 * for grouping items into an admin area.
	 * 
	 * @param  array  $attributes
	 * @param  \Closure|string  $callback
	 *
	 * @return void
	 */
	public function group(array $attributes, $parameters);
}