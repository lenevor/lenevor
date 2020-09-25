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
 * @since       0.7.2
 */

namespace Syscodes\Routing;

use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use Syscodes\Collections\Arr;

/**
 * @method \Syscodes\Routing\Route get(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\Route post(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\Route put(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\Route delete(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\Route patch(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\Route options(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\Route any(string $uri, \Closure|array|string|null $action = null)
 * @method \Syscodes\Routing\RouteRegister as(string $value)
 * @method \Syscodes\Routing\RouteRegister domain(string $value)
 * @method \Syscodes\Routing\RouteRegister name(string $value)
 * @method \Syscodes\Routing\RouteRegister namespace(string $value)
 * @method \Syscodes\Routing\RouteRegister prefix(string  $prefix)
 * @method \Syscodes\Routing\RouteRegister where(array  $where)
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteRegister
{
    /**
     * The router instance.
     * 
     * @var \Syscodes\Routing\Router $router
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
        'as', 'domain', 'name', 'namespace', 'prefix', 'where',
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
     * @param  \Syscodes\Routing\Router  $router
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
     * @return $this
     * 
     * @throws \InvalidArgumentException
     */
    public function attribute($key, $value)
    {
        if ( ! in_array($key, $this->allowedAttributes))
        {
            throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
        }
        
        $this->attributes[Arr::get($this->aliases, $key, $key)] = $value;
        
        return $this;
    }
    
    /**
     * Route a resource to a controller.
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return \Syscodes\Routing\RouteResourceRegistration
     */
    public function resource($name, $controller, array $options = [])
    {
        return $this->router->resource($name, $controller, $this->attributes + $options);
    }
    
    /**
     * Create a route group with shared attributes.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public function group($callback)
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
     * @return \Syscodes\Routing\Route
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
     * @return \Syscodes\Routing\Route
     */
    protected function registerRoute($method, $uri, $action = null)
    {
        if ( ! is_array($action))
        {
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
    protected function compileAction($action)
    {
        if (is_null($action))
        {
            return $this->attributes;
        }
        
        if (is_string($action) || $action instanceof Closure)
        {
            $action = ['uses' => $action];
        }
        
        return array_merge($this->attributes, $action);
    }
    
    /**
     * Dynamically handle calls into the route register.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return \Syscodes\Routing\Route|$this
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->verbs))
        {
            return $this->registerRoute($method, ...$parameters);
        }
        
        if (in_array($method, $this->allowedAttributes))
        {
            return $this->attribute($method, $parameters[0]);
        }
        
        throw new BadMethodCallException(
            sprintf('Method %s::%s does not exist.', static::class, $method)
        );
    }
}