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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Concerns;

/**
 * The RouteMap trait.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait RouteMap
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
	 * {@inheritdoc}
	 */
	public function any($route, $action = null) 
	{		
		return $this->addRoute(self::$verbs, $route, $action);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function delete($route, $action = null) 
	{
		return $this->addRoute('DELETE', $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($route, $action = null) 
	{
		return $this->addRoute(['GET', 'HEAD'], $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function head($route, $action = null)
	{
		return $this->addRoute('HEAD', $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function match($methods, $route, $action = null)
	{
		return $this->addRoute(array_map('strtoupper', (array) $methods), $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function options($route, $action = null) 
	{
		return $this->addRoute('OPTIONS', $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function patch($route, $action = null)
	{
		return $this->addRoute('PATCH', $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function post($route, $action = null) 
	{
		return $this->addRoute('POST', $route, $action);
	}

	/**
	 * {@inheritdoc}
	 */
	public function put($route, $action = null) 
	{
		return $this->addRoute('PUT', $route, $action);
	}  
}