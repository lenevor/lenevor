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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.5.0
 */

namespace Syscode\Routing;

use Closure;
use Syscode\Routing\Exceptions\{
	ActionNotFoundException,
	ClassNotFoundException,
	NamespaceNotFoundException,
	RouteNotFoundException
};
use Syscode\Contracts\Routing\Routable;

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
	 * @param  \Syscode\Contracts\Routing\Routable  $router 
	 * @param  string                               $uri 
	 * @param  string  								$method 
	 *
	 * @return mixed
	 *
	 * @throws \Syscode\Routing\Exceptions\RouteNotFoundException
	 * @throws \Syscode\Routing\Exceptions\NamespaceNotFoundException
	 */
	public function resolve(Routable $router, $uri, $method)
	{
		// Get all register routes with the same request method
		$routes = $router->getRoutesByMethod($method);
		
		// Remove trailing and leading slash
		$requestedUri = trim(preg_replace('/\?.*/', '', $uri), '/');

		// Loop trough the posible routes
		foreach ($routes as $route) 
		{
			$matches = [];

			// If the requested route matches one of the defined routes
			if ($route->getRoute() === $requestedUri || preg_match_all('~^'.$route->getRoute().'$~', $requestedUri, $matches)) 
			{	
				$arguments = [];
				$params    = (new RouteParams($matches))->toEachCountItems();
				
				if (is_array($route->getArguments()) && count($route->getArguments()) > 0)
				{
					foreach ($route->getArguments() as $key => $args) 
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
				
				if (is_object($route->getAction()['uses']) && ($route->getAction()['uses'] instanceof Closure)) 
				{
					return call_user_func_array($route->getAction()['uses'], $arguments);
				}
				
				if ($route->isControllerAction()) 
				{
					$controller = $route->getController();
					$method     = $route->getControllerMethod();
					
					// If exist the namespace
					if (strrpos($namespace = $route->getcontroller(), 's') === false)
					{
						throw new NamespaceNotFoundException(__('route.namespaceNotFound', ['namespace' => $namespace]));
					}

					if ( ! class_exists($controller))
					{
						// If exist the controller
						throw new ClassNotFoundException(__('route.classNotFound', ['class' => $controller]));  
					}

					// If exist the method
					if ( ! method_exists($controller, $method))
					{
						throw new ActionNotFoundException(__('route.methodNotFound', [
								'method' => $method, 
								'class' => $controller
						])); 
					}

					return (new $controller)->{$method}(...$arguments);
				}
			}
		}

		throw new RouteNotFoundException;
	}
}