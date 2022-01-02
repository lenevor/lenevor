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

namespace Syscodes\Components\Routing;

use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Http\RedirectResponse;
use Syscodes\Components\Contracts\Routing\Routable;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Controller\MiddlewareResolver;

/**
 * The Router class allows the integration of an easy-to-use routing system.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Router implements Routable
{
	use Concerns\RouteMap,
	    Concerns\RouteResolver;

	/**
	 * The registered route value binders.
	 * 
	 * @var array $binders
	 */
	protected $binders = [];

	/**
	 * The container instance used by the router.
	 * 
	 * @var \Syscodes\Components\Contracts\Container\Container $container
	 */
	protected $container;

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
	 * @var array $middleware
	 */
	protected $middleware = [];
	
	/**
	 * All of the middleware groups.
	 * 
	 * @var array $middlewareGroups
	 */
	protected $middlewareGroups = [];
	
	/**
	 * The priority-sorted list of middleware.
	 * 
	 * @var array $middlewarePriority
	 */
	public $middlewarePriority = [];
	
	/**
	 * The globally available parameter patterns.
	 * 
	 * @var array $patterns
	 */
	protected $patterns = [];

	/** 
	 * The route collection instance. 
	 * 
	 * @var \Syscodes\Components\Routing\RouteCollection $routes
	 */
	protected $routes;

	/**
	 * The Resource instance.
	 * 
	 * @var \Syscodes\Components\Routing\ResourceRegister $resources
	 */
	protected $resources;

	/**
	 * Constructor. Create a new Router instance.
	 *
	 * @param  \Syscodes\Components\Contracts\Container\Container|null  $container
	 * 
	 * @return void
	 */
	public function __construct(Container $container = null)
	{
		$this->routes    = new RouteCollection();
		$this->container = $container ?: new Container;
	}

	/**
	 * Get the prefix from the group on the stack.
	 *
	 * @return string
	 */
	public function getGroupPrefix(): string
	{
		if ( ! empty($this->groupStack)) {
			$last = end($this->groupStack);

			return $last['prefix'] ?? '';
		}

		return '';
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
	 * @param  array  $attributes
	 * @param  \Closure|string  $callback
	 *
	 * @return self
	 */
	public function group(array $attributes, $callback): self
	{
		$this->updateGroupStack($attributes);

		$this->loadRoutes($callback);

		array_pop($this->groupStack);

		return $this;
	}

	/**
	 * Update the group stack with the given attributes.
	 * 
	 * @param  array  $attributes
	 * 
	 * @return void
	 */
	protected function updateGroupStack(array $attributes): void
	{
		if ( ! empty($this->groupStack)) {
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
	protected function mergeGroup($new): array
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
	protected function loadRoutes($callback): void
	{
		if ($callback instanceof Closure) {
			$callback($this);
		} else {
			(new RouteFileRegister($this))->register($callback);
		}
	}

	/**
	 * Add a route to the underlying route collection. 
	 *
	 * @param  array|string  $method
	 * @param  string  $route
	 * @param  mixed  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function addRoute($method, $route, $action)
	{
		return $this->routes->add($this->map($method, $route, $action));
	}

	/**
	 * Create a redirect from one URI to another.
	 * 
	 * @param  string  $uri
	 * @param  string  $destination
	 * @param  int  $status
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function redirect($uri, $destination, $status = 302): Route
	{
		return $this->any($uri, function () use ($destination, $status) {
			return new RedirectResponse($destination, $status);
		});
	}

	/**
	 * Register a new route that returns a view.
	 * 
	 * @param  string  $uri
	 * @param  string  $view
	 * @param  array  $data
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function view($uri, $view, $data = []): Route
	{
		return $this->match(['GET', 'HEAD'], $uri, function () use ($view, $data) {
			return $this->container->make('view')->make($view, $data);
		});
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
		if ($this->actionReferencesController($action)) {
			$action = $this->convertToControllerAction($action);
		}

		$route = $this->newRoute(
				array_map('strtoupper', (array) $method),
				$this->prefix($route),
				$action
		);

		if ($this->hasGroupStack()) {
			$this->mergeGroupAttributesIntoRoute($route);			
		}

		$this->addWhereClausesToRoute($route);
		
		return $route;
	}
	
	/**
	 * Determine if the action is routing to a controller.
	 * 
	 * @param  array  $action
	 * 
	 * @return bool
	 */
	protected function actionReferencesController($action): bool
	{
		if ($action instanceof Closure) {
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
	protected function convertToControllerAction($action): array
	{
		if (is_string($action)) {
			$action = ['uses' => $action];
		}
		
		if ( ! empty($this->groupStack)) {
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
	protected function prependGroupUses($uses): string
	{
		$group = end($this->groupStack);
		
		return isset($group['namespace']) ? $group['namespace'].'\\'.$uses : $uses;
	}

	/**
	 * Create a new Route object.
	 * 
	 * @param  array|string  $method
	 * @param  string  $uri
	 * @param  mixed  $action
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function newRoute($method, $uri, $action): route
	{
		return take(new Route($method, $uri, $action))
		              ->setContainer($this->container);
	}
	
	/**
	 * Determine if the router currently has a group stack.
	 * 
	 * @return bool
	 */
	public function hasGroupStack(): bool
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
	protected function mergeGroupAttributesIntoRoute($route): void
	{
		$action = static::mergeGroup($route->getAction(), end($this->groupStack));
		
		$route->setAction($action);
	}
	
	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 * 
	 * @param  \Syscodes\Components\Routing\Route  $route
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	protected function addWhereClausesToRoute($route): Route
	{
		return $route->where(array_merge(
			$this->patterns, Arr::get($route->getAction(), 'where', [])
		));
	}

	/**
	 * Add a prefix to the route URI.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 */
	protected function prefix($uri): string
	{
		$uri = is_null($uri) ? '' : trim($uri, '/').'/';

		$uri = filter_var($uri, FILTER_SANITIZE_STRING);

		// While we want to add a route within a group of '/',
		// it doens't work with matching, so remove them...
		if ($uri != '/') {
			$uri = ltrim($uri, '/');
		}

		return trim(trim($this->getGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
	}

	/**
	 * Set a global where pattern on all routes.
	 * 
	 * @param  string  $name
	 * @param  string  $pattern
	 * 
	 * @return void
	 */
	public function pattern($name, $pattern): void
	{
		$this->patterns[$name] = $pattern;
	}

	/**
	 * Set a group of global where patterns on all routes.
	 * 
	 * @param  array  $patterns
	 * 
	 * @return void
	 */
	public function patterns($patterns): void
	{
		foreach ($patterns as $key => $pattern) {
			$this->patterns[$key] = $pattern;
		}
	}

	/**
	 * Get a Resource instance.
	 * 
	 * @return \Syscodes\Components\Routing\ResourceRegister
	 */
	public function getResource()
	{
		if (isset($this->resources)) {
			return $this->resources;
		}

		return $this->resources = new ResourceRegister($this);
	}

	/**
	 * Dispatches the given url and call the method that belongs to the route.
	 *
	 * @param  \Syscodes\Components\Http\Request  $request
	 *
	 * @return mixed
	 */
	public function dispatch(Request $request)
	{
		return $this->resolve($request);
	}

	/**
	 * Gather the middleware for the given route.
	 * 
	 * @param  \Syscodes\Components\Routing\Route  $route
	 * 
	 * @return array
	 */
	public function gatherRouteMiddleware(Route $route): array
	{
		$middleware = array_map(function ($name) {
            return MiddlewareResolver::resolve($name, $this->middleware, $this->middlewareGroups);
        }, $route->gatherMiddleware());

        return Arr::flatten($middleware);
	}

	/**
	 * Get all of the defined middleware
	 * 
	 * @return array
	 */
	public function getMiddleware(): array
	{
		return $this->middleware;
	}

	/**
	 * Register a short-hand name for a middleware.
	 * 
	 * @param  string  $name
	 * @param  string  $class
	 * 
	 * @return self
	 */
	public function aliasMiddleware($name, $class): self
	{
		$this->middleware[$name] = $class;

		return $this;
	}

	/**
	 * Register a group of middleware.
	 * 
	 * @param  string  $name
	 * @param  array  $middleware
	 * 
	 * @return self
	 */
	public function middlewareGroup($name, array $middleware): self
	{
		$this->middlewareGroups[$name] = $middleware;

		return $this;
	}

	/**
	 * Check if a route with the given name exists.
	 * 
	 * @param  string  $name
	 * 
	 * @return bool
	 */
	public function has($name): bool
	{
		$names = is_array($name) ? $name : func_get_args();

		foreach ($names as $value) {
			if ( ! $this->routes->hasNamedRoute($value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the currently dispatched route instance.
	 * 
	 * @return \Syscodes\Components\Routing\Route|null
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Determine if the current route matches a pattern.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function is(...$patterns): bool
	{
		return $this->currentRouteNamed(...$patterns);
	}

	/**
	 * Determine if the current route matches a pattern.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function currentRouteNamed(...$patterns): bool
	{
		return $this->current() && $this->current()->named(...$patterns);
	}

	/**
	 * Register an array of resource controllers.
	 * 
	 * @param  array  $resources
	 * @param  array  $options
	 * 
	 * @return void
	 */
	public function resources(array $resources, array $options = []): void
	{
		foreach ($resources as $name => $controller) {
			$this->resource($name, $controller, $options);
		}
	}

	/**
	 * Route a resource to a controller.
	 * 
	 * @param  string  $name
	 * @param  string  $controller
	 * @param  array  $options
	 * 
	 * @return \Syscodes\Components\Routing\AwaitingResourceRegistration
	 */
	public function resource($name, $controller, array $options = []) 
	{
		if ($this->container) {
			$register = $this->container->make(ResourceRegister::class);
		} else {
			$register = new ResourceRegister($this);
		}

		return new AwaitingResourceRegistration(
			$register, $name, $controller, $options
		);
	}

	/**
	 * Get the route collection.
	 *
	 * @return array   
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Get or set the verbs used in the resource URIs.
	 * 
	 * @param  array  $verbs
	 * 
	 * @return array|null
	 */
	public function resourceVerbs(array $verbs = [])
	{
		ResourceRegister::verbs($verbs);
	}
	
	/**
	 * Register a custom macro.
	 * 
	 * @param  string  $name
	 * @param  callable  $callback
	 * 
	 * @return void
	 */
	public function macro($name, callable $callback): void
	{
		$this->macros[$name] = $callback;
	}
	
	/**
	 * Checks if macro is registered.
	 * 
	 * @param  string  $name
	 * 
	 * @return bool
	 */
	public function hasMacro($name): bool
	{
		return isset($this->macros[$name]);
	}
	
	/**
	 * Magic method.
	 * 
	 * Dynamically handle calls into the router instance.
	 * 
	 * @param  string  $method
	 * @param  array  $parameters
	 * 
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (isset($this->macros[$method])) {
			$callback = $this->macros[$method];

			return call_user_func_array($callback, $parameters);
		}
		
		return (new RouteRegister($this))->attribute($method, $parameters[0]);
	}
}