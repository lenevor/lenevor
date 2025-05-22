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

namespace Syscodes\Components\Routing\Resources;

use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Routing\Router;

/**
 * Allows the calls to route register.
 * 
 * @method \Syscodes\Components\Routing\Route get(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Route post(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Route put(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Route delete(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Route patch(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Route options(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Route any(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister as(string $value)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister controller(string $controller)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister domain(string $value)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister middleware(array|string|null $middleware)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister name(string $value)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister namespace(string $value)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister prefix(string  $prefix)
 * @method \Syscodes\Components\Routing\Resources\RouteRegister where(array  $where)
 */
class RouteRegister
{
    /**
     * The router instance.
     * 
     * @var \Syscodes\Components\Routing\Router $router
     */
    protected $router;
    
    /**
     * The attributes to pass on to the router.
     * 
     * @var array $attributes
     */
    protected $attributes = [];
    
    /**
     * The methods to dynamically pass through to the router.
     * 
     * @var array $verbs
     */
    protected $verbs = [
        'get', 'post', 'put', 'patch', 'delete', 'options', 'any',
    ];
    
    /**
     * The attributes that can be set through this class.
     * 
     * @var array $allowedAttributes
     */
    protected $allowedAttributes = [
        'as',
        'controller',
        'domain', 
        'middleware', 
        'name', 
        'namespace', 
        'prefix', 
        'where',
    ];
    
    /**
     * The attributes that are aliased.
     * 
     * @var array $aliases
     */
    protected $aliases = [
        'name' => 'as',
    ];
    
    /**
     * Constructor. Create a new route registrar instance.
     * 
     * @param  \Syscodes\Components\Routing\Router  $router
     * 
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    /**
     * Set the value for a given attribute.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function attribute($key, $value): static
    {
        if ( ! in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        $attributeKey = Arr::get($this->aliases, $key, $key);
        
        $this->attributes[$attributeKey] = $value;
        
        return $this;
    }
    
    /**
     * Route a resource to a controller.
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Routing\Resources\ResourceRegister
     */
    public function resource($name, $controller, array $options = [])
    {
        return $this->router->resource($name, $controller, $this->attributes + $options);
    }

    /**
     * Route an API resource to a controller.
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Routing\Resources\ResourceRegister
     */
    public function apiResource($name, $controller, array $options = [])
    {
        return $this->router->apiResource($name, $controller, $this->attributes + $options);
    }
    
    /**
     * Create a route group with shared attributes.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public function group($callback): void
    {
        $this->router->group($this->attributes, $callback);
    }
    
    /**
     * Register a new route with the given verbs.
     * 
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  \Closure|array|string|null  $action
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    public function match($methods, $uri, $action = null)
    {
        return $this->router->match($methods, $uri, $this->compileAction($action));
    }
    
    /**
     * Register a new route with the router.
     * 
     * @param  string  $method
     * @param  string  $uri
     * @param  \Closure|array|string|null  $action
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function registerRoute($method, $uri, $action = null): Route
    {
        if ( ! is_array($action)) {
            $action = array_merge($this->attributes, $action ? ['uses' => $action] : []);
        }
        
        return $this->router->{$method}($uri, $this->compileAction($action));
    }
    
    /**
     * Compile the action into an array including the attributes.
     * 
     * @param  \Closure|array|string|null  $action
     * 
     * @return array
     */
    protected function compileAction($action): array
    {
        if (is_null($action)) {
            return $this->attributes;
        }
        
        if (is_string($action) || $action instanceof Closure) {
            $action = ['uses' => $action];
        }
        
        if (is_array($action) && is_callable($action)) {
            if (strncmp($action[0], '\\', 1)) {
                $action[0] = '\\'.$action[0];
            }
            
            $action = [
                'uses' => $action[0].'@'.$action[1],
                'controller' => $action[0].'@'.$action[1],
            ];
        }
        
        return array_merge($this->attributes, $action);
    }
    
    /**
     * Magic method.
     * 
     * Dynamically handle calls into the route register.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return \Syscodes\Components\Routing\Route|$this
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->verbs)) {
            return $this->registerRoute($method, ...$parameters);
        }
        
        if (in_array($method, $this->allowedAttributes)) {
            if ($method === 'middleware') {
                return $this->attribute($method, is_array($parameters[0] ? $parameters[0] : $parameters));
            }

            $parameters = array_key_exists(0, $parameters) ? $parameters[0] : true;

            return $this->attribute($method, $parameters);
        }
        
        throw new BadMethodCallException(
            sprintf('Method %s::%s does not exist.', static::class, $method)
        );
    }
}