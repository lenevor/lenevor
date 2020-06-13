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

namespace Syscodes\Routing;

use Closure;
use LogicException;
use ReflectionFunction;
use Syscodes\Support\Arr;
use Syscodes\Support\Str;
use InvalidArgumentException;
use Syscodes\Container\Container;
use Syscodes\Controller\ControllerDispatcher;
use Syscodes\Http\Exceptions\HttpResponseException;

/**
 * A Route describes a route and its parameters.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Route 
{
	use RouteConditionTrait, RouteDependencyResolverTrait;
	
	/**
	 * Action that the route will use when called.
	 *
	 * @var \Closure|string|array $action
	 */
	protected $action;

	/**
	 * The container instance used by the route.
	 * 
	 * @var \Syscodes\Container\Container $container
	 */
	protected $container;

	/**
	 * The controller instance.
	 * 
	 * @var string $controller
	 */
	protected $controller;

	/**
	 * The default values for the route.
	 * 
	 * @var array $defaults
	 */
	protected $defaults = [];

	/**
	 * Variable of HTTP method.
	 *  
	 * @var array|string $method
	 */
	protected $method;

	/**
	 * Namespace for the route.
	 *
	 * @var string $namespace
	 */
	protected $namespace;

	/**
	 * The array of matched parameters.
	 * 
	 * @var array $parameters
	 */
	protected $parameters = [];

	/**
	* Patterns that should be replaced.
	*
	* @var array $patterns 
	*/
	protected $patterns = [
		'~/~'                    =>  '\/',               // Slash
		'~{an:[^\/{}]+}~'        => '([0-9a-zA-Z]++)',   // Placeholder accepts alphabetic and numeric chars
		'~{n:[^\/{}]+}~'         => '([0-9]++)',         // Placeholder accepts only numeric
		'~{a:[^\/{}]+}~'         => '([a-zA-Z]++)',      // Placeholder accepts only alphabetic chars
		'~{w:[^\/{}]+}~'         => '([0-9a-zA-Z-_]++)', // Placeholder accepts alphanumeric and underscore
		'~{\*:[^\/{}]+}~'        => '(.++)',             // Placeholder match rest of url
		'~(\\\/)?{\?:[^\/{}]+}~' => '\/?([^\/]*)',		 // Optional placeholder
		'~{[^\/{}]+}~'           => '([^\/]++)'			 // Normal placeholder
	];

	/**
	 * The URI pattern the route responds to.
	 *
	 * @var array $uri
	 */
	protected $uri = [];

	/**
	 * Contains the arguments of the current route.
	 *
	 * @var array $where
	 */
	public $wheres = [];

	/**
	 * Constructor. Initialize route.
	 *
	 * @param  array|string|null  $method  (null by default)
	 * @param  string|null  $uri  (null by default)
	 * @param  \Closure|string|null  $action  (null by default)
	 *
	 * @return void
	 */
	public function __construct($method = null, $uri = null, $action = null)
	{
		// Set the method
		$this->parseMethod($method);
		// Set the route
		$this->parseRoute($uri);
		// Set the action
		$this->parseAction($action);
	}

	// Getters

	/**
	 * Get the action of the current route.
	 *
	 * @return \Closure|string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Get the arguments of the current route.
	 *
	 * @return string
	 */
	public function getArguments()
	{
		return $this->wheres;
	}

	/**
	 * Get the controller instance for the route.
	 * 
	 * @return mixed
	 */
	public function getController()
	{
		if ( ! $this->controller)
		{
			$class = $this->getNamespace().'\\'.$this->parseControllerCallback()[0];
 
			$this->controller = $this->container->make(ltrim($class, '\\'));
		}

		return $this->controller;
	}

	/**
	 * Get the controller method used for the route.
	 * 
	 * @return string
	 */
	public function getControllerMethod()
	{
		return $this->parseControllerCallback()[1];
	}

	/**
	 * Get the request method of the current route.
	 *
	 * @return array
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Get the url of the current route.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->action['as'] ?? null;
	}

	/**
	 * Get namespace.
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Get the url of the current route.
	 *
	 * @return string
	 */
	public function getRoute()
	{
		return $this->uri;
	}

	/**
	 * Get the domain defined for the route.
	 * 
	 * @return string|null
	 */
	public function getDomain()
	{
		return isset($this->action['domain']) 
				? str_replace(['http://', 'https://'], '', $this->action['domain']) 
				: null;
	}

	/**
	 * Parse the controller.
	 * 
	 * @return array
	 */
	public function parseControllerCallback()
	{
		return Str::parseCallback($this->action['uses']);
	}
	
	/**
	 * Checks whether the route's action is a controller.
	 * 
	 * @return bool
	 */
	public function isControllerAction()
	{
		return is_string($this->action['uses']);
	}

	/**
	 * Get the dispatcher for the route's controller.
	 * 
	 * @return \Syscodes\Controller\ControllerDispacther
	 */
	private function controllerDispatcher()
	{
		return new ControllerDispatcher($this->container);
	}

	// Setters
	
	/**
	 * Run the route action and return the response.
	 * 
	 * @return mixed
	 */
	public function runResolver()
	{
		$this->container = $this->container ?: new Container;

		try
		{
			if ($this->isControllerAction())
			{
				return $this->runResolverController();
			}

			return $this->runResolverCallable();
		}
		catch (HttpResponseException $e)
		{
			return $e->getResponse();
		}
	}

	/**
	 * Run the route action and return the response.
	 *  
	 * @return mixed
	 */
	protected function runResolverCallable()
	{
		$callable = $this->action['uses'];

		return $callable(...array_values($this->resolveMethodDependencies(
			$this->parametersWithouNulls(), new ReflectionFunction($this->action['uses'])
		)));
	}

	/**
	 * Run the route action and return the response.
	 * 
	 * @return mixed
	 */
	protected function runResolverController()
	{
		return $this->controllerDispatcher()->dispatch($this, $this->getController(), $this->getControllerMethod());
	}

	/**
	 * Set the name.
	 *
	 * @param  string  $name
	 *
	 * @return string
	 */
	public function name($name)
	{
		$this->action['as'] = isset($this->action['as']) ? $this->action['as'].$name : $name;

		return $this;
	}

	/**
	 * Set the action.
	 *
	 * @param  \Closure|string  $action
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function parseAction($action)
	{
		if ( ! (is_object($action) && ($action instanceof Closure)) && ($action === null || $action === ''))
		{
			throw new InvalidArgumentException(__('route.actionClosureOrFunction'));
		}

		$this->action = RouteAction::parse($this->uri, $action);

		return $this;
	}

	/**
	 * Set the method of the current route.
	 *
	 * @param  array  $method
	 *
	 * @return string $this
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function parseMethod($method)
	{
		if ($method === null || ! is_array($method) || empty($method))
		{
			throw new InvalidArgumentException(__('route.methodNotProvided'));
			
		}

		foreach ($method as $httpMethod) 
		{
			if ( ! in_array($httpMethod, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'ANY']))	
			{
				throw new InvalidArgumentException(__('route.methodNotAllowed'));
				
			}
		}

	    $this->method = $method;

	    return $this;
	}

	/**
	 * Set the route.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function parseRoute($uri)
	{
		if ($uri === null) 
		{
			throw new InvalidArgumentException(__('route.uriNotProvided'));
		}	

		$this->uri = $this->parseRoutePath($uri);

		return $this;
	}

	/**
	 * Replace word patterns with regex in route path.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	protected function parseRoutePath(string $path)
	{
		$pattern = array_keys($this->patterns);
		$replace = array_values($this->patterns);
		$uri 	 = trim($path, '\/');
		$uri     = trim($uri, '\/?');

		return preg_replace($pattern, $replace, $uri);
	}

	/**
	 * Parse arguments into a regex route.
	 *
	 * @return array
	 */
	public function parseArgs($route)
	{ 
		preg_match_all('~{(n:|a:|an:|w:|\*:|\?:)?([a-zA-Z0-9_]+)}~', $route, $matches);
	
		if (isset($matches[2]) && ! empty($matches[2])) 
		{
			$this->wheres = $matches[2];
		}

		return $this;
	}
	
	/**
	 * Set the action array for the route.
	 * 
	 * @param  array  $action
	 * 
	 * @return $this
	 */
	public function setAction(array $action)
	{
		$this->action = $action;
		
		return $this;
	}

	/**
	 * Set a default value for the route.
	 * 
	 * @param  string  $key
	 * @param  mixed   $value
	 * 
	 * @return $this
	 */
	public function defaults($key, $value)
	{
		$this->defaults[$key] = $value;

		return $this;
	}

	/**
	 * Set the namespace.
	 *
	 * @param  string  $namespace
	 *
	 * @return $this
	 */
	public function setNamespace($namespace)
	{   
		$this->namespace = $namespace;
	
		return $this;
	}

	/**
	 * Set the where.
	 *
	 * @param  array|string  $name
	 * @param  string|null  $regex  (null by default)
	 *
	 * @return $this
	 */
	public function where($name, $regex = null)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$this->wheres[$key] = $value;
			}
		}
		else
		{
			$this->wheres[$name] = $regex;
		}

		return $this;
	}

	/**
	 * Get a given parameter from the route.
	 * 
	 * @param  string  $name
	 * @param  mixed  $default  (null by default)
	 * 
	 * @return array
	 */
	public function parameter($name, $default = null)
	{
		return Arr::get($this->parameters(), $name, $default);
	}

	/**
	 * Set a parameter to the given value.
	 * 
	 * @param  string  $name
	 * @param  mixed  $value
	 * 
	 * @return array
	 */
	public function setParameter($name, $value)
	{
		$this->parameters();

		$this->parameters[$name] = $value;
	}

	/**
	 * Get the key / value list of parameters without null values.
	 * 
	 * @return array
	 */
	public function parametersWithouNulls()
	{
		return array_filter($this->parameters(), function ($parameter) {
			return ! is_null($parameter);
		});
	}

	/**
	 * Get the key / value list of parameters for the route.
	 * 
	 * @return array
	 */
	public function parameters()
	{
		if (isset($this->parameters))
		{
			return $this->parameters;
		}

		throw new LogicException('The route is not bound.');
	}

	/**
	 * Dynamically access route parameters.
	 * 
	 * @param  string  $key
	 * 
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->parameter($key);
	}
}