<?php 

namespace Syscode\Contracts\Routing;

use Syscode\Routing\Route;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.4.0
 */
interface Routable
{
	/**
	 * Add a route 
	 *
	 * @access  public
	 * @param   Route  $route
	 *
	 * @return  route
	 */
	public function addRoute(Route $route);

	/**
	 * Add a route for all posible methods
	 *
	 * @access  public 
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function any($route, $action);

	/**
	 * Add a route with delete method
	 *
	 * @access  public
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function delete($route, $action);

	/**
	 * Add a route with get method
	 *
	 * @access  public 
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function get($route, $action);

	/**
	 * Add a route with head method
	 *
	 * @access  public
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function head($route, $action);

	/**
	 * Add a route with options method
	 *
	 * @access  public
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function options($route, $action);

	/**
	 * Add a route with patch method
	 *
	 * @access  public
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function patch($route, $action);

	/**
	 * Add a route with post method
	 *
	 * @access  public
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function post($route, $action);

	/**
	 * Add a route with put method
	 *
	 * @access  public
	 * @param   string                 $route
	 * @param   \Closure|array|string  $action
	 *
	 * @return  void
	 */
	public function put($route, $action);
}