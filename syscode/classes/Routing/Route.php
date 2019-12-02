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
use InvalidArgumentException;

/**
 * A Route describes a route and its parameters.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Route 
{
	/**
	 * Action that the route will use when called.
	 *
	 * @var \Closure|string $action
	 */
	protected $action;

	/**
	 * Variable of HTTP method.
	 *  
	 * @var array|string $method
	 */
	protected $method;

	/**
	 * The name of the route.
	 *
	 * @var string $name
	 */
	protected $name;

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
	 * @param  array|string     $method
	 * @param  string           $uri
	 * @param  \Closure|string  $action
	 * @param  array            $arguments
	 *
	 * @return void
	 */
	public function __construct($method = null, $uri = null, $action = null, array $arguments = [])
	{
		// Set the method
		$this->parseMethod($method);
		// Set the route
		$this->parseRoute($uri);
		// Set the action
		$this->parseAction($action);

		$this->wheres = $arguments;
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
		return $this->name;
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
	 * Display all routes.
	 * 
	 * @return array|null
	 */
	public function getList()
	{
		echo '<pre style="border:1px solid #eee;padding:0 10px;width:960px;max-height:780;margin:20px auto;font-size:17px;overflow:auto;">';
		var_dump($this->getRoute());
		echo '</pre>';
		die();
	}

	// Setters

	/**
	 * Set the name.
	 *
	 * @param  string  $name
	 *
	 * @return string
	 */
	public function name($name)
	{
		if ($name !== null)
		{
			$this->name = (string) $name;
		}
		else
		{
			$this->name = $name;
		}

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
			throw new InvalidArgumentException('Action should be a Closure or a path to a function');
		}

		$this->action = $action;

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
			throw new InvalidArgumentException('No method provided');
			
		}

		foreach ($method as $httpMethod) 
		{
			if ( ! in_array($httpMethod, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'ANY']))	
			{
				throw new InvalidArgumentException('Method not allowed. allowed methods: GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, ANY');
				
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
			throw new InvalidArgumentException('No route provided, for root use /');
		}

		$this->uri = trim($uri, '\/?');

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
	 * @param  string|null   $regex
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
}