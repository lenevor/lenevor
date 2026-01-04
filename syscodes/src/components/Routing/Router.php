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

namespace Syscodes\Components\Routing;

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Routing\Routable;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Collections\RouteCollection;
use Syscodes\Components\Routing\Resources\AwaitingResourceRegistration;
use Syscodes\Components\Routing\Resources\ResourceRegister;
use Syscodes\Components\Routing\RouteFileRegister;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * The Router class allows the integration of an easy-to-use routing system.
 */
class Router implements Routable
{
	use Concerns\Mapper,
	    Macroable {
		    __call as macroCall;
	    }

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
	 * Resolve the given route.
	 * 
	 * @var \Syscodes\Components\Routing\RouteResolver $resolves
	 */
	protected $resolves;

	/**
	 * The Resource instance.
	 * 
	 * @var \Syscodes\Components\Routing\Resources\ResourceRegister $resources
	 */
	protected $resources;

	/** 
	 * The route collection instance. 
	 * 
	 * @var \Syscodes\Components\Routing\Collections\RouteCollection $routes
	 */
	protected $routes;

	/**
	 * Constructor. Create a new Router instance.
	 *
	 * @param  \Syscodes\Components\Contracts\Container\Container|null  $container
	 * 
	 * @return void
	 */
	public function __construct(?Container $container = null)
	{
		$this->routes    = new RouteCollection;
		$this->container = $container ?: new Container;
		$this->resolves  = new RouteResolver($this, $this->routes, $this->container);
	}

	/**
	 * Get the prefix from the group on the stack.
	 *
	 * @return string
	 */
	public function getGroupPrefix(): string
	{
		if ($this->hasGroupStack()) {
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
	 * @param  \Closure|array|string  $callback
	 *
	 * @return void
	 */
	public function group(array $attributes, $routes): void
	{
		foreach (Arr::wrap($routes) as $groupRoutes) {
			$this->updateGroupStack($attributes);
	
			$this->loadRoutes($groupRoutes);
	
			array_pop($this->groupStack);			
		}
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
		if ($this->hasGroupStack()) {
			$attributes = $this->mergeLastGroup($attributes);
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given group attributes.
	 * 
	 * @param  array  $new
	 * @param  bool  $existsPrefix
	 * 
	 * @return array
	 */
	public function mergeLastGroup($new, bool $existsPrefix = true): array
	{
		return RouteGroup::mergeGroup($new, end($this->groupStack), $existsPrefix);
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
	 * Register a new fallback route with the router.
	 * 
	 * @param  array|string|callable|null  $action
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function fallback($action)
	{
		$placeholder = 'fallbackPlaceholder';
		
		return $this->addRoute('GET', "{{$placeholder}}", $action)
		            ->where($placeholder, '.*')
		            ->fallback();
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
	public function redirect($uri, $destination, $status = 302)
	{
		return $this->any($uri, '\Syscodes\Components\Routing\Controllers\RedirectController')
		            ->defaults('destination', $destination)
		            ->defaults('status', $status);
	}
	
	/**
	 * Create a permanent redirect from one URI to another.
	 * 
	 * @param  string  $uri
	 * @param  string  $destination
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function permanentRedirect($uri, $destination)
	{
		return $this->redirect($uri, $destination, 301);
	}

	/**
	 * Register a new route that returns a view.
	 * 
	 * @param  string  $uri
	 * @param  string  $view
	 * @param  array  $data
	 * @param  int|array  $status
	 * @param  array  $headers
	 * 
	 * @return \Syscodes\Components\Routing\Route
	 */
	public function view($uri, $view, $data = [], $status = 200, array $headers = [])
	{
		return $this->match(['GET', 'HEAD'], $uri, '\Syscodes\Components\Routing\Controllers\ViewController')
		            ->setDefaults([
		                'view' => $view,
		                'data' => $data,
		                'status' => is_array($status) ? 200 : $status,
		                'headers' => is_array($status) ? $status : $headers,
		            ]);
	}

	/**
	 * Add new route to routes array.
	 *
	 * @param  array|string  $method
	 * @param  string  $route
	 * @param  mixed  $action
	 *
	 * @return \Syscodes\Components\Routing\Route
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function map($method, $route, $action): Route
	{
		if ($this->actionReferencesController($action)) {
			$action = $this->convertToControllerAction($action);
		}

		$route = $this->newRoute(
		              $method,
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
		if ( ! $action instanceof Closure) {
			return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
		}
		
		return false;
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
		
		if ($this->hasGroupStack()) {
			$action['uses'] = $this->prependGroupController($action['uses']);
			$action['uses'] = $this->prependGroupNamespace($action['uses']);
		}
		
		$action['controller'] = $action['uses'];
		
		return $action;
	}
	
	/**
	 * Prepend the last group namespaces onto the use clause.
	 * 
	 * @param  string  $class
	 * 
	 * @return string
	 */
	protected function prependGroupNamespace($class): string
	{
		$group = end($this->groupStack);
		
		return isset($group['namespace']) && ! Str::startsWith($class, '\\') && ! Str::startsWith($class, $group['namespace']) 
		            ? $group['namespace'].'\\'.$class 
					: $class;
	}
	
	/**
	 * Prepend the last group controller onto the use clause.
	 * 
	 * @param  string  $class
	 * 
	 * @return string
	 */
	protected function prependGroupController($class): string
	{
		$group = end($this->groupStack);
		
		if ( ! isset($group['controller'])) {
			return $class;
		}
		
		if (class_exists($class)) {
			return $class;
		}
		
		if (Str::contains($class, '@')) {
			return $class;
		}
		
		return $group['controller'].'@'.$class;
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
		$action = $this->mergeLastGroup(
			$route->getAction(),
			existsPrefix: false
		);
		
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
	protected function prefix($uri): string
	{
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
		return $this->resolves->resolve($request);
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
		$middleware = array_map(
		                    fn ($name) => RouteMiddlewareResolver::resolve($name, $this->middleware, $this->middlewareGroups),
		                    $route->gatherMiddleware()
		              );
		
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
	 * @return static
	 */
	public function aliasMiddleware($name, $class): static
	{
		$this->middleware[$name] = $class;

		return $this;
	}
	
	/**
	 * Get all of the defined middleware groups.
	 * 
	 * @return array
	 */
	public function getMiddlewareGroups(): array
	{
		return $this->middlewareGroups;
	}

	/**
	 * Register a group of middleware.
	 * 
	 * @param  string  $name
	 * @param  array  $middleware
	 * 
	 * @return static
	 */
	public function middlewareGroup($name, array $middleware): static
	{
		$this->middlewareGroups[$name] = $middleware;

		return $this;
	}
	
	/**
	 * Flush the router's middleware groups.
	 * 
	 * @return static
	 */
	public function flushMiddlewareGroups(): static
	{
		$this->middlewareGroups = [];
		
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
		return $this->resolves->current() && $this->resolves->current()->named(...$patterns);
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
	 * @return \Syscodes\Components\Routing\Resources\AwaitingResourceRegistration
	 */
	public function resource($name, $controller, array $options = []) 
	{
		if ($this->container && $this->container->bound(ResourceRegister::class)) {
			$register = $this->container->make(ResourceRegister::class);
		} else {
			$register = new ResourceRegister($this);
		}

		return new AwaitingResourceRegistration(
			$register, $name, $controller, $options
		);
	}
	
	/**
	 * Register an array of API resource controllers.
	 * 
	 * @param  array  $resources
	 * @param  array  $options
	 * 
	 * @return void
	 */
	public function apiResources(array $resources, array $options = [])
	{
		foreach ($resources as $name => $controller) {
			$this->apiResource($name, $controller, $options);
		}
	}
	
	/**
	 * Route an API resource to a controller.
	 * 
	 * @param  string  $name
	 * @param  string  $controller
	 * @param  array  $options
	 * 
	 * @return \Syscodes\Components\Routing\AwaitingResourceRegistration
	 */
	public function apiResource($name, $controller, array $options = [])
	{
		$only = ['index', 'show', 'store', 'update', 'erase'];
		
		if (isset($options['except'])) {
			$only = array_diff($only, (array) $options['except']);
		}
		
		return $this->resource($name, $controller, array_merge([
			'only' => $only,
		], $options));
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
	 * Remove any duplicate middleware from the given array.
	 * 
	 * @param  array  $middleware
	 * 
	 * @return array
	 */
	public static function uniqueMiddleware(array $middleware): array
	{
		return array_unique($middleware, SORT_REGULAR);
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
		if (static::hasMacro($method)) {
			return $this->macroCall($method, $parameters);
		}
		
		if ($method === 'middleware') {
			return (new RouteRegister($this))->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
		}
		
		if ($method === 'can') {
			return (new RouteRegister($this))->attribute($method, [$parameters]);
		}
		
		if ($method !== 'where' && Str::startsWith($method, 'where')) {
			return (new RouteRegister($this))->{$method}(...$parameters);
		}
		
		return (new RouteRegister($this))->attribute($method, array_key_exists(0, $parameters) ? $parameters[0] : true);
	}
}