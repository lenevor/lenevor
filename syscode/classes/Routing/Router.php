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
 * @since       0.1.0
 */

namespace Syscode\Routing;

use Closure;
use Syscode\Support\Arr;
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
	 * @var string $groupStack
	 */
	protected $groupStack;

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
	* Patterns that should be replaced.
	*
	* @var array $patterns 
	*/
	protected $patterns = [
		'~/~'             		 =>  '\/',				 // Slash
		'~{an:[^\/{}]+}~' 		 => '([0-9a-zA-Z]++)',   // Placeholder accepts alphabetic and numeric chars
		'~{n:[^\/{}]+}~'  		 => '([0-9]++)',         // Placeholder accepts only numeric
		'~{a:[^\/{}]+}~'  		 => '([a-zA-Z]++)',      // Placeholder accepts only alphabetic chars
		'~{w:[^\/{}]+}~'  		 => '([0-9a-zA-Z-_]++)', // Placeholder accepts alphanumeric and underscore
		'~{\*:[^\/{}]+}~' 		 => '(.++)',             // Placeholder match rest of url
		'~(\\\/)?{\?:[^\/{}]+}~' => '\/?([^\/]*)', 		 // Optional placeholder
		'~{[^\/{}]+}~'           => '([^\/]++)'	 		 // Normal placeholder
	];
	//protected $patterns = [];

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
	 * Get the group of routes.
	 * 
	 * @var string $routeGroup
	 */
	protected $routeGroup;

	/** 
	 * Currently registered routes. 
	 * 
	 * @var array $routes
	 */
	protected $routes = [];

	/**
	 * Constructor initialize namespace.
	 *
	 * @param  string                          $namespace
	 * @param  \Syscode\Routing\RouteGroup     $group
	 * @param  \Syscode\Routing\RouteResolver  $resolver
	 * 
	 * @return void
	 */
	public function __construct($namespace = null, RouteGroup $group, RouteResolver $resolver)
	{
		$this->namespace = $namespace;

		// Instance the RouteGroup class 
		$this->routeGroup = $group;

		// Instance the RouteDispatcher class 
		$this->resolver = $resolver;
	}

	/**
	 * Add a route. 
	 *
	 * @param   string $route
	 *
	 * @return  route
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
		$this->groupStack = $this->routeGroup->prefix ?: '';

		if ( ! empty($this->groupStack))
		{
			return $this->groupStack;
		}
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
	 *   Example 1:
	 *      // Creates route: /admin show the word 'User'
	 *      Router::group('/admin', function() {	 
	 *
	 *          Router::get('/', function() {
	 *	            echo 'Hello world..!';
	 *          });
	 *
	 *      });
	 *   Example 2:
	 *      // Creates route: /admin show the word 'User'
	 *      Router::group(['prefix' => 'admin'], function() {	 
	 *
	 *          Router::get('/', function() {
	 *	            echo 'Hello world..!';
	 *          });
	 *
	 *      });
	 *
	 * @param  string           $params
	 * @param  \Closure|string  $callback
	 *
	 * @return $this
	 *
	 * @uses   \Closure    
	 *
 	 * @throws \InvalidArgumentException
	 */
	public function group($params, $callback) 
	{
		if ( ! isset($params) && ! is_null($params))
 		{
 			throw new InvalidArgumentException('Params must be set');
 		}

 		if ( ! (is_callable($callback) && ($callback instanceof Closure)) && ($callback === null || $callback === ''))
		{
			throw new InvalidArgumentException('Callback must be set');
		}

		$this->routeGroup->group($params, $callback);

		return $this;
	}

	/**
	 * Add new route to routes array.
	 *
	 * @param  array|string  $method
	 * @param  string        $route
	 * @param  mixed         $action
	 *
	 * @return void
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function map($method, $route, $action) 
	{
		if ( ! isset($method)) 
		{ 
			throw new InvalidArgumentException("Method must be set");
		}

		if ( ! isset($route))
		{
			throw new InvalidArgumentException("Route must be set");
		}

		$method = array_map('strtoupper', (array)$method);

		$route = $this->newRoute($method, $this->prefix($route), $action);

		$this->addRoute($route);		

		foreach ($route->getMethod() as $verbs)
		{
			$this->routesByMethod[$verbs][] = $route;
		}

		$this->addWhereToRoute($route);
		
		return $route;
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
	protected function newRoute($method, $uri, $action)
	{
		return take(new Route($method, $uri, $action))
		            ->setNamespace($this->namespace);
	}

	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 * 
	 * @param  \Syscode\Routing\Route
	 * 
	 * @return \Syscode\Routing\Route
	 */
	protected function addWhereToRoute($route)
	{
		$route->where(array_merge(
			$this->patterns, Arr::get($route->getAction(), 'where', [])
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
	public function prefix($uri)
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

	public function pattern($name, $regex)
	{
		return $this->patterns[$name] = $regex;
	}

	public function patterns($patterns)
	{
		foreach ($patterns as $key => $pattern)
		{
			$this->patterns[$key] = $pattern;
		}
	}

	/**
	 * Called the namespace and controller.
	 *
	 * @param  string           $route
	 * @param  \Closure|string  $classname
	 *
	 * @return string
	 */
	public function namespaces($route, $classname)
	{
		return $this;		
	}

	/**
	 * Parse url into a regex route.
	 *
	 * @param  string  $route
	 *
	 * @return string
	 */
	protected function parseRoute($route)
	{
		$pattern = array_keys($this->patterns);
		$replace = array_values($this->patterns);
		$uri 	 = preg_replace($pattern, $replace, $route);
		$uri 	 = trim($uri, '\/?');
		$uri 	 = trim($uri, '\/');
		
		return $uri;
	}

	/**
	 * Resolve the given url and call the method that belongs to the route.
	 *
	 * @param  string  $uri
	 * @param  string  $method
	 *
	 * @return array
	 */
	public function resolve($uri, $method)
	{
		return $this->resolver->resolve($this, $uri, $method);
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
	 * @param  string  $namespace
	 *
	 * @return string|bool 
	 */
	public function namespace($namespace)
	{
		$this->namespace = $namespace;

		return $this;
	} 
}