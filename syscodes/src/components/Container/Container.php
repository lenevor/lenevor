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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Container;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionParameter;
use InvalidArgumentException;
use Syscodes\Contracts\Container\NotFoundException;
use Syscodes\Container\Exceptions\ContainerException;
use Syscodes\Contracts\Container\BindingResolutionException;
use Syscodes\Contracts\Container\ExpectedInvokableException;
use Syscodes\Container\Exceptions\UnknownIdentifierException;
use Syscodes\Contracts\Container\Container as ContainerContract;

/**
 * Class responsible of registering the bindings, instances and 
 * dependencies of classes when are contained for to be executes 
 * in the services providers.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Container implements ArrayAccess, ContainerContract
{
    /**
     * The current globally available container.
     * 
     * @var string $instance
     */
    protected static $instance;

    /**
     * The parameter override stack.
     *
     * @var array $across
     */
    protected $across = [];

    /**
     * Array of aliased.
     * 
     * @var array $aliases
     */
    protected $aliases = [];

    /**
     * Array registry of container bindings.
     * 
     * @var array $bindings
     */
    protected $bindings = [];
    
    /**
     * The stack of concretions currently being built.
     *
     * @var array $buildStack
     */
    protected $buildStack = [];

    /**
     * All of the registered callbacks.
     * 
     * @var array $hasCallbacks
     */
    protected $hasCallbacks = [];

    /**
     * The container's singleton instances.
     * 
     * @var array $instances
     */
    protected $instances = [];

    /**
     * An array of the types that have been resolved.
     * 
     * @var array $resolved
     */
    protected $resolved = [];

    /**
     * The extender closures for services.
     * 
     * @var array $services
     */
    protected $services = [];

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \Syscodes\Contracts\Container\Container|null  $container
     * 
     * @return \Syscodes\Contracts\Container\Container|static
     */
    public static function setInstance(ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Alias a type to a diferent name.
     * 
     * @param  string  $id
     * @param  string  $alias
     * 
     * @return void
     * 
     * @throws \Syscodes\Container\Exceptions\ContainerException
     */
    public function alias($id, $alias)
    {
        if ($alias === $id) {
            throw new ContainerException("[{$id}] is aliased to itself");
        }

        $this->aliases[$alias] = $id;
    }

    /**
     * Register a binding with container.
     * 
     * @param  string  $id
     * @param  \Closure|string|null  $value
     * @param  bool  $singleton
     * 
     * @return void
     */
    public function bind($id, $value = null, bool $singleton = false)
    {   
        $this->dropInstances($id);

        if (is_null($value)) {
            $value = $id;
        }

        if ( ! $value instanceof Closure) {
            $value = $this->getClosure($id, $value);
        }

        $this->bindings[$id] = compact('value', 'singleton');

        if ($this->resolved($id)) {
            $this->reBound($id);
        }
    }

    /**
     * Get the closure to be used when building a type.
     * 
     * @param  string  $id
     * @param  string  $value
     * 
     * @return \Closure
     */
    protected function getClosure($id, string $value)
    {
        return function ($container, $parameters = []) use ($id, $value) {
            if ($id == $value) {
                return $container->build($value);
            }
                       
            return $container->resolve($value, $parameters);
        };

    }

    /**
     * Determine if the given id type has been resolved.
     *
     * @param  string  $id
     * 
     * @return bool
     */
    public function resolved($id)
    {
        if ($this->isAlias($id)) {
            $id = $this->getAlias($id);
        }

        return isset($this->resolved[$id]) || isset($this->instances[$id]);
    }

    /**
     * Activate the  callbacks for the given id type.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function reBound($id)
    {
        $instance = $this->make($id);

        foreach ($this->getBound($id) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }

    /**
     * Register a singleton binding in the container.
     * 
     * @param  string  $id
     * @param  \Closure|string|null  $value
     * 
     * @return void
     */
    public function singleton($id, $value = null)
    {
        $this->bind($id, $value, true);
    }    

    /**
     * Instantiate a class instance of the given type.
     * 
     * @param  string  $class
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Contracts\Container\BindingResolutionException
     */
    public function build($class)
    {
        if ($class instanceof Closure) {
            return $class($this, $this->getLastParameterOverride());
        }

        $reflection = new ReflectionClass($class);

        if ( ! $reflection->isInstantiable()) {
            return $this->buildNotInstantiable($class);
        }

        $this->buildStack[] = $class;

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $class();
        }

        $dependencies = $constructor->getParameters();

        $instances = $this->getDependencies($dependencies);

        array_pop($this->buildStack);
        
        return $reflection->newInstanceArgs($instances);
    }

    /**
     * Throw an exception that the class is not instantiable.
     *
     * @param  string  $class
     * 
     * @return void
     *
     * @throws \Syscodes\Contracts\Container\BindingResolutionException
     */
    protected function buildNotInstantiable(string $class)
    {
        if ( ! empty($this->buildStack)) {
           $reset   = implode(', ', $this->buildStack);

           $message = "Target [{$class}] is not instantiable while building [{$reset}]."; 
        } else {
            $message = "Target [{$class}] is not instantiable.";
        }

        throw new BindingResolutionException($message);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     * 
     * @param  array  $dependencies
     * 
     * @return array
     */
    protected function getDependencies(array $dependencies) 
    {
        $param = [];

        foreach ($dependencies as $dependency) {
            if ($this->getHasParameters($dependency)) {
                $param[] = $this->getParameterOverride($dependency);

                continue;
            }

            $param[] = is_null($dependency->getClass()) 
                       ? $this->getResolveNonClass($dependency) 
                       : $this->getResolveClass($dependency);
        }

        return (array) $param;
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param  \ReflectionParameter  $dependency
     * 
     * @return bool
     */
    protected function getHasParameters($dependency)
    {
        return array_key_exists($dependency->name, $this->getLastParameterOverride());
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return count($this->across) ? end($this->across) : [];
    }

    /**
     * Get a parameter override for a dependency.
     *
     * @param  \ReflectionParameter  $dependency
     * 
     * @return mixed
     */
    protected function getParameterOverride($dependency)
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * 
     * @return mixed
     *
     * @throws \Syscodes\Container\Exceptions\BindingResolutionException
     */
    protected function getResolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        } catch (BindingResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * Resolve a non-class hinted dependency.
     *
     * @param  \ReflectionParameter  $parameter
     * 
     * @return mixed
     *
     * @throws \Syscodes\Container\Exceptions\BindingResolutionException
     */
    protected function getResolveNonClass(ReflectionParameter $parameter)
    {
        if ( ! is_null($class = $parameter->name)) {
            return $class instanceof Closure ? $class($this) : $class;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = "Unresolvable dependency resolving [{$parameter}] in class [{$parameter->getDeclaringClass()->getName()}]";

        throw new BindingResolutionException($message);
    }

    /**
     * Extender an id type in the container.
     *
     * @param  string  $id
     * @param  \Closure  $closure
     * 
     * @return void
     */
    public function extend($id, Closure $closure) 
    {
        if ( ! isset($this->bindings[$id])) {
            throw new InvalidArgumentException("Type {$id} is not bound.");
        }
        
        if (isset($this->instances[$id])) {
            $this->instances[$id] = $closure($this->instances[$id], $this);
            
            return $this->reBound($id);
        }
        
        $resolver = $this->bindings[$id]['value'];
        
        $this->bind($id, function ($container) use ($resolver, $closure) {
            return $closure($resolver($container), $container);
        }, $this->isSingleton($id));
    }

    /**
     * Remove all id traces of the specified binding.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function destroyBinding($id)
    {
        if ($this->has($id)) {
            unset($this->bindings[$id], $this->instances[$id], $this->resolved[$id]);
        }
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param  string  $id
     * 
     * @return void
     */
    protected function dropInstances($id)
    {
        unset($this->instances[$id], $this->aliases[$id]);
    }

    /**
     * Marks a callable as being a factory service.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function factory($id)
    {
        return function () use ($id) {
            return $this->make($id);
        };
    }

    /**
     * Get the alias for an id if available.
     * 
     * @param  string  $id
     * 
     * @return string
     */
    public function getAlias($id)
    {
        return isset($this->aliases[$id]) 
                ? $this->getAlias($this->aliases[$id])
                : $id;
    }

    /**
     * Return and array containing all bindings.
     * 
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the services callbacks for a given type.
     * 
     * @param  string  $id
     * 
     * @return array
     */
    protected function getServices($id)
    {
        return $this->services[$this->getAlias($id)] ?? [];
    }

    /**
     * Register an existing instance as singleton in the container.
     *
     * @param  string  $id
     * @param  mixed  $instance
     * 
     * @return mixed
     */
    public function instance($id, $instance) 
    {
        if (is_array($id)) {
            [$id, $alias] = $id;
            
            $this->alias($id, $alias);
        }
        
        unset($this->aliases[$id]);
        
        $bound = $this->bound($id);
        
        $this->instances[$id] = $instance;
        
        if ($bound) {
            $this->reBound($id);
        }
    }

    /**
     * Return all defined value binding.
     * 
     * @return array
     */
    public function keys()
    {
        return array_keys($this->bindings);
    }

    /**
     * Resolve the given type from the container.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * 
     * @return object
     */
    public function make($id, array $parameters = []) 
    {
        return $this->resolve($id, $parameters);
    }

    /**
     * Resolve the given type from the container.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * 
     * @return mixed
     */
    protected function resolve($id, array $parameters = []) 
    {
        $id = $this->getAlias($id);

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $this->across[] = $parameters;
        
        $value = $this->getValue($id);

        if ($this->isBuildable($value, $id)) {
            $object = $this->build($value);
        } else {
            $object = $this->make($value);
        }

        foreach ($this->getServices($id) as $services) {
            $object = $services($object, $this);
        }

        if ($this->isSingleton($id)) {
            $this->instances[$id] = $object;
        }

        $this->resolved[$id] = true;

        array_pop($this->across);
        
        return $object;
    }

    /**
     * Determine if a given string is an alias.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Determine if the given id is buildable.
     * 
     * @param  string  $class
     * @param  string  $id
     * 
     * @return string
     */
    protected function isBuildable($class, $id)
    {
        return $class === $id || $class instanceof Closure;
    }

    /**
     * Determine if a given type is singleton.
     * 
     * @param  string  $id
     * 
     * @return bool
     */
    protected function isSingleton($id)
    {
        return isset($this->instances[$id]) ||
               (isset($this->bindings[$id]['singleton']) &&
               $this->bindings[$id]['singleton'] === true);
    }

    /**
     * Get the class type for a given id.
     * 
     * @param  string  $id
     * 
     * @return mixed
     */
    protected function getValue($id)
    {
        if (isset($this->bindings[$id])) {
            return $this->bindings[$id]['value'];
        }

        return $id;
    }

     /**
     * Get the has callbacks for a given type.
     * 
     * @param  string  $id
     * 
     * @return array
     */
    protected function getBound($id)
    {
        if (isset($this->hasCallbacks[$id])) {
            return $this->hasCallbacks[$id];
        }

        return [];
    }

    /**
     * Call the given callable / class@method and inject its dependencies.
     * 
     * @param  \callable|string  $callback
     * @param  array  $parameters
     * @param  string|null  $defaultMethod
     * 
     * @return mixed
     */
    public function call($callback, array $parameters = [], string $defaultMethod = null)
    {
        return CallBoundCallback::call($this, $callback, $parameters, $defaultMethod);
    }
    
    /**
     * Remove all id traces of the specified binding.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function remove($id)
    {
        $this->destroyBinding($id);
    }

    /**
     * Set the binding with given key / value.
     * 
     * @param  string  $id
     * @param  string  $value
     * 
     * @return $this
     * 
     * @throws \Syscodes\Container\Exceptions\ContainerException
     */
    public function set($id, string $value)
    {
        if ( ! $this->bound($id)) {
            throw new ContainerException($id);
        }

        $this->bindings[$id] = $value;

        return $this;
    }

    /**
     * Flush the container of all bindings and resolved instances.
     * 
     * @return void
     */
    public function flush()
    {
        $this->aliases   = [];
        $this->resolved  = [];
        $this->bindings  = [];
        $this->instances = [];
    }

    /*
    |----------------------------------------------------------------
    | ContainerInterface Methods
    |---------------------------------------------------------------- 
    */

    /**
     * Gets a parameter or an object.
     * 
     * @param  string  $id
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Container\Exceptions\UnknownIdentifierException
     */
    public function get($id)
    {
        try {
            return $this->resolve($id);
        } catch (Exception $e) {
            if ( ! $this->has($id)) {
                throw new $e;
            }   

            throw new UnknownIdentifierException($id);
        }
    }

    /**
     * Check if binding with $id exists.
     * 
     * @param  string  $id
     * 
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->bound($id);
    }

    /**
     * Determine if the given id type has been bound.
     * 
     * @param  string  $id
     * 
     * @return bool
     */
    public function bound($id)
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /*
    |-----------------------------------------------------------------
    | ArrayAccess Methods
    |-----------------------------------------------------------------
    */

    /**
     * Determine if a given offset exists.
     * 
     * @param  string  $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->bound($offset);
    }

    /**
     * Get the value at a given offset.
     * 
     * @param  string  $offset
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * Set the value at a given offset.
     * 
     * @param  string  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value at a given offset.
     * 
     * @param  string  $offset
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Dynamically access container services.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Dynamically set container services.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this[$key] = $value;
    }
}