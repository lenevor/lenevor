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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing;

use Closure;
use LogicException;
use ReflectionFunction;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Container\Container;
use Syscodes\Components\Routing\ControllerDispatcher;
use Syscodes\Components\Routing\Matching\UriValidator;
use Syscodes\Components\Routing\Matching\HostValidator;
use Syscodes\Components\Routing\Matching\MethodValidator;
use Syscodes\Components\Routing\Matching\SchemeValidator;
use Syscodes\Components\Http\Exceptions\HttpResponseException;

/**
 * A Route describes a route and its parameters.
 */
class Route 
{
	use Concerns\RouteCondition,
	    Concerns\RouteDependencyResolver;

	/**
	 * The validators used by the routes.
	 * 
	 * @var array $validators
	 */
	public static $validators;
	
	/**
	 * Action that the route will use when called.
	 *
	 * @var \Closure|string|array $action
	 */
	public $action;

	/**
	 * The compiled version of the route.
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Routing\CompiledRoute|string $compiled
	 */
	public $compiled;

	/**
	 * The computed gathered middleware.
	 * 
	 * @var array|null $computedMiddleware
	 */
	public $computedMiddleware;

	/**
	 * The container instance used by the route.
	 * 
	 * @var \Syscodes\Components\Container\Container $container
	 */
	protected $container;

	/**
	 * The controller instance.
	 * 
	 * @var string $controller
	 */
	public $controller;

	/**
	 * The default values for the route.
	 * 
	 * @var array $defaults
	 */
	public $defaults = [];

	/**
	 * Indicates whether the route is a fallback route.
	 * 
	 * @var bool $fallback
	 */
	protected $fallback = false;

	/**
	 * Variable of HTTP method.
	 *  
	 * @var array|string $method
	 */
	public $method;

	/**
	 * The array of matched parameters.
	 * 
	 * @var array $parameters
	 */
	public $parameters = [];

	/**
	 * The parameter names for the route.
	 * 
	 * @var array|null $parameterNames
	 */
	public $parameterNames;

	/**
	 * The URI pattern the route responds to.
	 *
	 * @var string $uri
	 */
	public $uri;

	/**
	 * Contains the arguments of the current route.
	 *
	 * @var array $where
	 */
	public $wheres = [];

	/**
	 * Constructor. Initialize route.
	 *
	 * @param  array|string  $method  
	 * @param  string  $uri  
	 * @param  \Closure|array  $action  
	 *
	 * @return void
	 */
	public function __construct($method, $uri, $action)
	{
		$this->uri = $uri;

		// Set the method
		$this->method = $this->parseMethod($method);

		// Set the action
		$this->action = Arr::except($this->parseAction($action), ['prefix']);
		
		if (in_array('GET', $this->method) && ! in_array('HEAD', $this->method)) {
			$this->method[] = 'HEAD';
		}

		$this->prefix(is_array($action) ? Arr::get($action, 'prefix') : '');
	}

	/**
	 * Get the controller instance for the route.
	 * 
	 * @return mixed
	 */
	public function getController()
	{
		if ( ! $this->controller) {
			$class = $this->parseControllerCallback()[0];
 
			$this->controller = $this->container->make(ltrim($class, '\\'));
		}

		return $this->controller;
	}

	/**
	 * Get the controller method used for the route.
	 * 
	 * @return string
	 */
	public function getControllerMethod(): string
	{
		return $this->parseControllerCallback()[1];
	}

	/**
	 * Get or set the domain for the route.
	 * 
	 * @param  string|null  $domain  
	 * 
	 * @return mixed
	 */
	public function domain($domain = null)
	{
		if (is_null($domain)) {
			return $this->getDomain();
		}
		
		$parsed = RouteUri::parse($domain);

		$this->action['domain'] = $parsed->uri;

		return $this;
	}

	/**
	 * Get the domain defined for the route.
	 * 
	 * @return string|null
	 */
	public function getDomain(): ?string
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
	public function parseControllerCallback(): array
	{
		return Str::parseCallback($this->action['uses']);
	}
	
	/**
	 * Checks whether the route's action is a controller.
	 * 
	 * @return bool
	 */
	public function isControllerAction(): bool
	{
		return is_string($this->action['uses']);
	}

	/**
	 * Get the dispatcher for the route's controller.
	 * 
	 * @return \Syscodes\Components\Routing\ControllerDispatcher
	 */
	private function controllerDispatcher(): ControllerDispatcher
	{
		return new ControllerDispatcher($this->container);
	}
	
	/**
	 * Run the route action and return the response.
	 * 
	 * @return mixed
	 */
	public function runResolver()
	{
		$this->container = $this->container ?: new Container;

		try {
			if ($this->isControllerAction()) {
				return $this->runResolverController();
			}

			return $this->runResolverCallable();
		} catch (HttpResponseException $e) {
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
			$this->parametersWithouNulls(), new ReflectionFunction($callable)
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
	 * Determine if the route matches a given request.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * @param  bool  $method
	 * 
	 * @return bool
	 */
	public function matches(Request $request, bool $method = true): bool
	{
		$this->compileRoute();

		foreach (self::getValidators() as $validator) {
			if ($method && $validator instanceof MethodValidator) {
				continue;
			}

			if ( ! $validator->matches($this, $request)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the route validators for the instance.
	 * 
	 * @return array
	 */
	public function getValidators(): array
	{
		if (isset(static::$validators)) {
			return static::$validators;
		}

		return static::$validators = [
			new HostValidator, new MethodValidator,
			new SchemeValidator, new UriValidator
		];
	}

	/**
	 * Parse the route action.
	 *
	 * @param  \callable|array|null  $action
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function parseAction($action): array
	{
		if ( ! (is_object($action) && ($action instanceof Closure)) && ($action === null || $action === '')) {
			throw new InvalidArgumentException(__('route.actionClosureOrFunction'));
		}

		return RouteAction::parse($this->uri, $action);
	}

	/**
	 * Set the method of the current route.
	 *
	 * @param  array  $method
	 *
	 * @return array
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function parseMethod($method): array
	{
		if ($method === null || empty($method)) {
			throw new InvalidArgumentException(__('route.methodNotProvided'));			
		}

		foreach ((array) $method as $httpMethod) {
			if ( ! in_array($httpMethod, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'ANY'])) {
				throw new InvalidArgumentException(__('route.methodNotAllowed'));				
			}
		}

	    return array_map('strtoupper', (array) $method);
	}

	/**
	 * Set the URI that the route responds to.
	 *
	 * @param  string|null  $uri
	 *
	 * @return static
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function setUri($uri): static
	{
		if ($uri === null) {
			throw new InvalidArgumentException(__('route.uriNotProvided'));
		}	

		$this->uri = $this->parseUri($uri);

		return $this;
	}
	
	/**
	 * Parse the route URI and normalize.
	 * 
	 * @param  string  $uri
	 * 
	 * @return string
	 */
	protected function parseUri($uri): string
	{
		return take(RouteUri::parse($uri), fn ($uri) => $uri)->uri;
	}

	/**
	 * Add a prefix to the route URI.
	 * 
	 * @param  string  $prefix
	 * 
	 * @return static
	 */
	public function prefix($prefix): static
	{
		$prefix = $prefix ?? '';

		if ( ! empty($newPrefix = trim(rtrim($prefix, '/').'/'.ltrim($this->action['prefix'] ?? '', '/'), '/'))) {
			$this->action['prefix'] = $newPrefix;
		}
		
		$uri = rtrim($prefix, '/').'/'.ltrim($this->uri, '/');
		
		return $this->setUri($uri !== '/' ? trim($uri, '/') : $uri);
	}

	/**
	 * Set the action array for the route.
	 * 
	 * @param  array  $action
	 * 
	 * @return mixed
	 */
	public function setAction(array $action)
	{
		$this->action = $action;

		if (isset($this->action['domain'])) {
			$this->domain($this->action['domain']);
		}
		
		return $this;
	}

	/**
	 * Set the name.
	 *
	 * @param  string  $name
	 *
	 * @return static
	 */
	public function name($name): static
	{
		$this->action['as'] = isset($this->action['as']) ? $this->action['as'].$name : $name;

		return $this;
	}

	/**
	 * Determine whether the route's name matches the given patterns.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function named(...$patterns): bool
	{
		if (is_null($routeName = $this->getName())) {
			return false;
		}

		foreach ($patterns as $pattern) {
			if (Str::is($pattern, $routeName)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set a default value for the route.
	 * 
	 * @param  string  $key
	 * @param  mixed   $value
	 * 
	 * @return static
	 */
	public function defaults($key, $value): static
	{
		$this->defaults[$key] = $value;

		return $this;
	}

	/**
	 * Set a default values for the route.
	 * 
	 * @param  array  $defaults
	 * 
	 * @return static
	 */
	public function setDefaults(array $defaults): static
	{
		$this->defaults = $defaults;

		return $this;
	}

	/**
	 * Set the flag of fallback mode on the route.
	 * 
	 * @return static
	 */
	public function fallback(): static
	{
		$this->fallback = true;

		return $this;
	}

	/**
	 * Set the facllback value.
	 * 
	 * @param  bool  $fallback
	 * 
	 * @return static
	 */
	public function setFallback(bool $fallback): static
	{
		$this->fallback = $fallback;

		return $this;
	}

	/**
	 * Set the where.
	 *
	 * @param  array|string  $name
	 * @param  string|null  $expression  
	 *
	 * @return static
	 */
	public function where($name, ?string $expression = null): static
	{
		$wheres = is_array($name) ? $name : [$name => $expression];
		
		foreach ($wheres as $name => $expression) {
			$this->wheres[$name] = $expression;
		}

		return $this;
	}

	/**
	 * Set the where when have a variable assign.
	 * 
	 * @param  string  $key
	 * 
	 * @return string|null
	 */
	public function setPattern(string $key): ?string
	{
		return $this->wheres[$key] ?? null;
	}

	/**
	 * Bind the route to a given request for execution.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return static
	 */
	public function bind(Request $request): static
	{
		$this->compileRoute();
		
		$this->parameters = (new RouteParameter($this))->parameters($request);

		return $this;
	}

	/**
	 * Compile into a Route Compile instance.
	 * 
	 * @return \Symfony\Component\Routing\CompiledRoute
	 */
	protected function compileRoute()
	{
		if ( ! $this->compiled) {
			$this->compiled = (new RouteCompiler($this))->compile();
		}

		return $this->compiled;
	}

	/**
	 * Get all of the parameter names for the route.
	 * 
	 * @return array
	 */
	public function parameterNames(): array
	{
		if (isset($this->parameterNames)) {
			return $this->parameterNames;
		}

		return $this->parameterNames = $this->compileParameterNames();
	}

	/**
	 * Get the parameter names for the route.
	 * 
	 * @return array
	 */
	protected function compileParameterNames(): array
	{
		preg_match_all('~\{(.*?)\}~', $this->getDomain().$this->getUri(), $matches);

		return array_map(fn ($match) => trim($match, '?'), $matches[1]);
	}

	/**
	 * Get a given parameter from the route.
	 * 
	 * @param  string  $name
	 * @param  mixed  $default  
	 * 
	 * @return mixed
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
	 * @return void
	 */
	public function setParameter($name, $value): void
	{
		$this->parameters();

		$this->parameters[$name] = $value;
	}

	/**
	 * Get the key / value list of parameters without null values.
	 * 
	 * @return array
	 */
	public function parametersWithouNulls(): array
	{
		return array_filter($this->parameters(), fn ($parameter) => ! is_null($parameter));
	}

	/**
	 * Get the key / value list of parameters for the route.
	 * 
	 * @return array
	 */
	public function parameters(): array
	{
		if (isset($this->parameters)) {
			return $this->parameters;
		}

		throw new LogicException('The route is not bound');
	}

	/**
	 * Get all middleware, including the ones from the controller.
	 * 
	 * @return array
	 */
	public function gatherMiddleware(): array
	{
		if ( ! is_null($this->computedMiddleware)) {
			return $this->computedMiddleware;
		}

		$this->computedMiddleware = [];

		return $this->computedMiddleware = Router::uniqueMiddleware(array_merge(
			$this->middleware(),
			$this->controllerMiddleware()
		));
	}

	/**
	 * Get or set the middlewares attached to the route.
	 * 
	 * @param  array|string|null  $middleware
	 * 
	 * @return array|static
	 */
	public function middleware($middleware = null)
	{
		if (is_null($middleware)) {
			return $this->getMiddleware();
		}

		if (is_string($middleware)) {
			$middleware = func_get_args();
		}

		foreach ($middleware as $index => $value) {
			$middleware[$index] = (string) $value;
		}

		$this->action['middleware'] = array_merge(
			$this->getMiddleware(),
			$middleware
		);

		return $this;
	}

	/**
	 * Get the middlewares attached to the route.
	 * 
	 * @return array
	 */
	protected function getMiddleware(): array
	{
		return (array) ($this->action['middleware'] ?? []);
	}

	/**
	 * Get the middleware for the route's controller.
	 * 
	 * @return array
	 */
	public function controllerMiddleware(): array
	{
		if ( ! $this->isControllerAction()) {
			return [];
		}

		return $this->controllerDispatcher()->getMiddleware(
			$this->getController(),
			$this->getControllerMethod()
		);
	}

	/**
	 * Determine if the route only responds to HTTP requests.
	 * 
	 * @return bool
	 */
	public function httpOnly(): bool
	{
		return in_array('http', $this->action, true);
	}

	/**
	 * Determine if the route only responds to HTTPS requests.
	 * 
	 * @return bool
	 */
	public function httpsOnly(): bool
	{
		return $this->secure();
	}

	/**
	 * Determine if the route only responds to HTTPS requests.
	 * 
	 * @return bool
	 */
	public function secure(): bool
	{
		return in_array('https', $this->action, true);
	}

	/**
	 * Get the action of the current route.
	 *
	 * @return \Closure|string|array
	 */
	public function getAction()
	{
		return $this->action;
	}
	
	/**
	 * Get the compiled version of the route.
	 * 
	 * @return \Syscodes\Bundles\ApplicationBundle\Routing\CompiledRoute
	 */
	public function getCompiled()
	{
		return $this->compiled;
	}

	/**
	 * Get the URI associated with the route.
	 *
	 * @return string
	 */
	public function getUri(): string
	{
		return $this->uri;
	}

	/**
	 * Get the patterns of the current route.
	 *
	 * @return array
	 */
	public function getPatterns(): array
	{
		return $this->wheres;
	}

	/**
	 * Get the request method of the current route.
	 *
	 * @return array|string
	 */
	public function getMethod(): array|string
	{
		return $this->method;
	}

	/**
	 * Get the url of the current route.
	 *
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->action['as'] ?? null;
	}

	/**
	 * Set the container instance on the route.
	 * 
	 * @param  \Syscodes\Components\Container\Container  $container
	 * 
	 * @return static
	 */
	public function setContainer(Container $container): static
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * Magic method.
	 * 
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