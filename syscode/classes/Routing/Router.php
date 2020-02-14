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
 * @since       0.5.0
 */

namespace Syscode\Routing;

use Closure;
use Syscode\Support\Arr;
use BadMethodCallException;
use InvalidArgumentException;
use Syscode\Contracts\Routing\Routable;

/**
 * The Router class allows the integration of an easy-to-use routing system.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Router implements Routable
{
	use RouteMapTrait;

	/**
	 * Variable of group route.
	 *  
	 * @var array $groupStack
	 */
	protected $groupStack = [];
	
	/**
	 * The registered string macros.
	 * 
	 * @var array $macros
	 */
	protected $macros = [];

	/**
	 * Middleware for function of filters
	 *  
	 * @var string[] $middleware
	 */
	protected $middleware = [];

	/**
	 * Default namespace.
	 * 
	 * @var string $namecepace
	 */
	protected $namespace;
	
	/**
	 * The globally available parameter patterns.
	 * 
	 * @var array $regex
	 */
	protected $regex = [];

	/**
	 * Default resolver.
	 * 
	 * @var string $resolver
	 */
	protected $resolver;

	/**
	 * An array with all routes by method.
	 *
	 * @var array $routesByMethod
	 */
	protected $routesByMethod = [];

	/** 
	 * Currently registered routes. 
	 * 
	 * @var array $routes
	 */
	protected $routes = [];

	/**
	 * Constructor initialize namespace.
	 *
	 * @param  string  $namespace
	 * @param  \Syscode\Routing\RouteResolver  $resolver
	 * 
	 * @return void
	 */
	public function __construct($namespace = null, RouteResolver $resolver)
	{
		$this->namespace = $namespace;
		$this->resolver  = $resolver;
	}

	/**
	 * Add a route. 
	 *
	 * @param  string  $route
	 *
	 * @return \Syscode\Routing\Route
	 */
	public function addRoute(Route $route)
	{
		$this->routes[] = $route;
	}

	/**
	 * Get all routes.
	 *
	 * @return array   
	 */
	public function getAllRoutes()
	{
		return $this->routes;
	}

	/**
	 * Get the prefix from the group on the stack.
	 *
	 * @return string
	 *
	 * @uses   RouteGroup->prefix
	 */
	public function getGroupPrefix()
	{
		if ( ! empty($this->groupStack))
		{
			$last = end($this->groupStack);

			return $last['prefix'] ?? '';
		}

		return '';
	}

	/**
	 * Get routes by method.
	 *
	 * @param  array|string  $method
	 *
	 * @return array 
	 */
	public function getRoutesByMethod($method)
	{
		return ($this->routesByMethod && isset($this->routesByMethod[$method])) ? $this->routesByMethod[$method] : [];
	}

	/**
	 * Group a series of routes under a single URL segment. This is handy
	 * for grouping items into an admin area, like:
	 *
	 *   Example:
	 *      // Creates route: /admin show the word 'User'
	 *      Route::group(['prefix' => 'admin'], function() {	 
	 *
	 *          Route::get('/user', function() {
	 *	            echo 'Hello world..!';
	 *          });
	 *
	 *      }); /admin/user
	 *
	 * @param  array            $attributes
	 * @param  \Closure|string  $callback
	 *
	 * @return void
	 */
	public function group(array $attributes, $callback) 
	{
		$this->updateGroupStack($attributes);

		$this->loadRoutes($callback);

		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 * 
	 * @param  array  $attributes
	 * 
	 * @return void
	 */
	protected function updateGroupStack(array $attributes)
	{
		if ( ! empty($this->groupStack))
		{
			$attributes = $this->mergeGroup($attributes);
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given group attributes.
	 * 
	 * @param  array  $new
	 * 
	 * @return array
	 */
	protected function mergeGroup($new)
	{
		return RouteGroup::mergeGroup($new, end($this->groupStack));
	}
	
	/**
	 * Load the provided routes.
	 * 
	 * @param  \Closure|string  $callback
	 * 
	 * @return void
	 */
	protected function loadRoutes($callback)
	{
		if ($callback instanceof Closure) 
		{
			$callback($this);
		}
		else
		{
			(new RouteFileRegister($this))->register($callback);
		}
	}

	/**
	 * Add new route to routes array.
	 *
	 * @param  array|string  $method
	 * @param  string  $route
	 * @param  mixed  $action
	 *
	 * @return void
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function map($method, $route, $action) 
	{
		if ($this->actionReferencesController($action))
		{
			$action = $this->convertToControllerAction($action);
		}
		
		$method = array_map('strtoupper', (array) $method);

		$route = $this->newRoute(
						$method, 
						$this->prefix($route), 
						$action
		);

		$this->addRoute($route);

		if ($this->hasGroupStack())
		{
			$this->mergeGroupAttributesIntoRoute($route);			
		}

		$this->addWhereClausesToRoute($route);

		foreach ($route->getMethod() as $method)
		{			
			$this->routesByMethod[$method][] = $route;
		}
		
		return $route;
	}
	
	/**
	 * Determine if the action is routing to a controller.
	 * 
	 * @param  array  $action
	 * 
	 * @return bool
	 */
	protected function actionReferencesController($action)
	{
		if ($action instanceof Closure)
		{
			return false;
		}
		
		return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
	}
	
	/**
	 * Add a controller based route action to the action array.
	 * 
	 * @param  array|string  $action
	 * 
	 * @return array
	 */
	protected function convertToControllerAction($action)
	{
		if (is_string($action))
		{
			$action = ['uses' => $action];
		}
		
		if (! empty($this->groupStack))
		{
			$action['uses'] = $this->prependGroupUses($action['uses']);
		}
		
		$action['controller'] = $action['uses'];
		
		return $action;
	}
	
	/**
	 * Prepend the last group uses onto the use clause.
	 * 
	 * @param  string  $uses
	 * 
	 * @return string
	 */
	protected function prependGroupUses($uses)
	{
		$group = end($this->groupStack);
		
		return isset($group['namespace']) ? $group['namespace'].'\\'.$uses : $uses;
	}

	/**
	 * Create a new Route object.
	 * 
	 * @param  array|string  $method
	 * @param  string        $uri
	 * @param  mixed         $action
	 * 
	 * @return \Syscode\Routing\Route
	 */
	public function newRoute($method, $uri, $action)
	{
		return take(new Route($method, $uri, $action))
		            ->parseArgs($uri)
		            ->setNamespace($this->namespace);
	}
	
	/**
	 * Determine if the router currently has a group stack.
	 * 
	 * @return bool
	 */
	public function hasGroupStack()
	{
		return ! empty($this->groupStack);
	}
	
	/**
	 * Merge the group stack with the controller action.
	 * 
	 * @param  \Syscpde\Routing\Route  $route
	 * 
	 * @return void
	 */
	protected function mergeGroupAttributesIntoRoute($route)
	{
		$action = static::mergeGroup($route->getAction(), end($this->groupStack));
		
		$route->setAction($action);
	}
	
	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 * 
	 * @param  \Syscode\Routing\Route  $route
	 * 
	 * @return \Syscode\Routing\Route
	 */
	protected function addWhereClausesToRoute($route)
	{
		$route->where(array_merge(
			$this->regex, $route->getAction()['where'] ?? []
		));
		
		return $route;
	}

	/**
	 * Add a prefix to the route URI.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 */
	protected function prefix($uri)
	{
		$uri = is_null($uri) ? '' : trim($uri, '/').'/';

		$uri = filter_var($uri, FILTER_SANITIZE_STRING);

		// While we want to add a route within a group of '/',
		// it doens't work with matching, so remove them...
		if ($uri != '/')
		{
			$uri = ltrim($uri, '/');
		}

		return trim(trim($this->getGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
	}

	/**
	 * Set a global where pattern on all routes.
	 * 
	 * @param  string  $name
	 * @param  string  $regex
	 * 
	 * @return void
	 */
	public function pattern($name, $regex)
	{
		return $this->regex[$name] = $regex;
	}

	/**
	 * Set a group of global where patterns on all routes.
	 * 
	 * @param  array  $patterns
	 * 
	 * @return void
	 */
	public function patterns($patterns)
	{
		foreach ($patterns as $key => $pattern)
		{
			$this->regex[$key] = $pattern;
		}
	}

	/**
	 * Called the namespace and controller.
	 *
	 * @param  string  $route
	 * @param  \Closure|string  $classname
	 *
	 * @return string
	 */
	public function namespaces($route, $classname)
	{
		return $this;		
	}

	/**
	 * Resolve the given url and call the method that belongs to the route.
	 *
	 * @param  \Syscode\Http\Request  $request
	 *
	 * @return array
	 */
	public function resolve($request)
	{
		return $this->resolver->resolve($this, $request);
	}

	/**
	 * 
	 *
	 * @param   
	 */
	public static function resources() 
	{

	} 

	/**
	 * Set namespace.
	 *
	 * @param  string|null  $namespace
	 *
	 * @return string|bool 
	 */
	public function namespace($namespace = null)
	{
		if (is_null($namespace))
		{
			throw new InvalidArgumentException(__('route.namespaceNotExist'));
		}

		$this->namespace = $namespace;

		return $this;
	}
	
	/**
	 * Register a custom macro.
	 * 
	 * @param  string  $name
	 * @param  callable  $callback
	 * 
	 * @return void
	 */
	public function macro($name, callable $callback)
	{
		$this->macros[$name] = $callback;
	}
	
	/**
	 * Checks if macro is registered.
	 * @param  string  $name
	 * 
	 * @return boolean
	 */
	public function hasMacro($name)
	{
		return isset($this->macros[$name]);
	}
	
	/**
	 * Dynamically handle calls into the router instance.
	 * 
	 * @param  string  $method
	 * @param  array  $parameters
	 * 
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (isset($this->macros[$method]))
		{
			$callback = $this->macros[$method];

			return call_user_func_array($callback, $parameters);
		}
		
		throw new BadMethodCallException("Method [ {$method} ] does not exist");
	}
}