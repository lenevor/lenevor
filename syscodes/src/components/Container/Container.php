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

namespace Syscodes\Components\Container;

use ArrayAccess;
use Closure;
use Exception;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use TypeError;
use Syscodes\Components\Container\Attributes\Bind;
use Syscodes\Components\Container\Attributes\Scoped;
use Syscodes\Components\Container\Attributes\Singleton;
use Syscodes\Components\Container\Exceptions\EntryIdentifierException;
use Syscodes\Components\Contracts\Container\BindingResolutionException;
use Syscodes\Components\Contracts\Container\Container as ContainerContract;
use Syscodes\Components\Contracts\Container\ContextualAttribute;

/**
 * Class responsible of registering the bindings, instances and 
 * dependencies of classes when are contained for to be executes 
 * in the services providers.
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
     * All of the after resolving callbacks by class type.
     * 
     * @var array[] $afterResolvingCallbacks
     */
    protected $afterResolvingCallbacks = [];

    /**
     * Array of aliased.
     * 
     * @var array $aliases
     */
    protected $aliases = [];
    
    /**
     * The registered aliases keyed by the abstract name.
     * 
     * @var array[]
     */
    protected $abstractAliases = [];
    
    /**
     * All of the before resolving callbacks by class type.
     * 
     * @var array[] $beforeResolvingCallbacks
     */
    protected $beforeResolvingCallbacks = [];

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
     * Whether an abstract class has already had its attributes checked for bindings.
     * 
     * @var array
     */
    protected $checkedAttributeBindings = [];
    
    /**
     * Whether a class has already been checked for Singleton or Scoped attributes.
     * 
     * @var array
     */
    protected $checkedSingletonOrScopedAttributes = [];
    
    /**
     * The contextual binding map.
     * 
     * @var array
     */
    public $contextual = [];
    
    /**
     * The callback used to determine the container's environment.
     * 
     * @var callable|array|string|bool|null
     */
    protected $environmentResolver = null;

    /**
     * The extension closures for services.
     * 
     * @var array $extenders
     */
    protected $extenders = [];
    
    /**
     * All of the global after resolving callbacks.
     * 
     * @var \Closure[] $globalAfterResolvingCallbacks
     */
    protected $globalAfterResolvingCallbacks = [];
    
    /**
     * All of the global before resolving callbacks.
     * 
     * @var \Closure[] $globalBeforeResolvingCallbacks
     */
    protected $globalBeforeResolvingCallbacks = [];
    
    /**
     * All of the global resolving callbacks.
     * 
     * @var \Closure[] $globalResolvingCallbacks
     */
    protected $globalResolvingCallbacks = [];    

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
     * All of the resolving callbacks by class type.
     * 
     * @var array[] $resolvingCallbacks
     */
    protected $resolvingCallbacks = [];
    
    /**
     * The container's scoped instances.
     * 
     * @var array
     */
    protected $scopedInstances = [];

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance ??= new static;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \Syscodes\Components\Contracts\Container\Container|null  $container
     * 
     * @return \Syscodes\Components\Contracts\Container\Container|static
     */
    public static function setInstance(?ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Determine if the given id type has been bound.
     * 
     * @param  string  $id
     * 
     * @return bool
     */
    public function bound($id): bool
    {
        return isset($this->bindings[$id]) ||
               isset($this->instances[$id]) ||
               $this->isAlias($id);
    }

    /**
     * Determine if a given string is an alias.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function isAlias($name): bool
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
     * Alias a type to a diferent name.
     * 
     * @param  string  $id
     * @param  string  $alias
     * 
     * @return void
     */
    public function alias($id, string $alias): void
    {
        if ($alias === $id) {
            throw new LogicException("[{$id}] is aliased to itself");
        }
        
        $this->removeAbstractAlias($alias);

        $this->aliases[$alias] = $id;
        
        $this->abstractAliases[$id][] = $alias;
    }
    
    /**
     * Refresh an instance on the given target and method.
     * 
     * @param  string  $id
     * @param  mixed  $target
     * @param  string  $method
     * 
     * @return mixed
     */
    public function refresh($id, $target, $method): mixed
    {
        return $this->rebinding($id, fn($app, $instance) => $target->{$method}($instance));
    }

    /**
     * Bind a new callback to an id rebind event.
     * 
     * @param  string  $id
     * @param  \Closure  $callback
     * 
     * @return mixed
     */
    public function rebinding($id, Closure $callback)
    {
        $this->hasCallbacks[$id = $this->getAlias($id)][] = $callback;
        
        if ($this->bound($id)) return $this->make($id);
    }

    /**
     * Register a binding with container.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|string|null  $value
     * @param  bool  $shared
     * 
     * @return void
     */
    public function bind($id, $value = null, bool $shared = false): void
    {   
        $this->dropInstances($id);

        // If no value type was given, we will simply set the value type to the
        // id type. After that, the value type to be registered as shared
        // without being forced to state their classes in both of the parameters.
        if (is_null($value)) {
            $value = $id;
        }

        if ( ! $value instanceof Closure) {
            if ( ! is_string($value)) {
                throw new TypeError(self::class.'::bind: Argument #2 ($value) must be of type Closure|string|null');
            }

            $value = $this->getClosure($id, $value);
        }

        $this->bindings[$id] = ['value' => $value, 'shared' => $shared];

        if ($this->resolved($id)) {
            $this->reBound($id);
        }
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param  string  $id
     * 
     * @return void
     */
    protected function dropInstances($id): void
    {
        unset($this->instances[$id], $this->aliases[$id]);
    }

    /**
     * Get the closure to be used when building a type.
     * 
     * @param  string  $id
     * @param  string  $value
     * 
     * @return mixed
     */
    protected function getClosure($id, string $value): mixed
    {
        return function ($container, $parameters = []) use ($id, $value) {
            if ($id == $value) {
                return $container->build($value);
            }
                       
            return $container->resolve(
                $value, $parameters, raiseEvents: false
            );
        };

    }

    /**
     * Determine if the given id type has been resolved.
     *
     * @param  string  $id
     * 
     * @return bool
     */
    public function resolved($id): bool
    {
        if ($this->isAlias($id)) {
            $id = $this->getAlias($id);
        }

        return isset($this->resolved[$id]) || isset($this->instances[$id]);
    }

    /**
     * Extender an id type in the container.
     *
     * @param  string  $id
     * @param  \Closure  $closure
     * 
     * @return mixed
     */
    public function extend($id, Closure $closure)
    {
        $id = $this->getAlias($id);
        
        if (isset($this->instances[$id])) {
            $this->instances[$id] = $closure($this->instances[$id], $this);
            
            return $this->reBound($id);
        } else {
            $this->extenders[$id][] = $closure;

            if ($this->resolved($id)) {
                $this->rebound($id);
            }
        }
    }

    /**
     * Activate the  callbacks for the given id type.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function reBound($id): void
    {
        $instance = $this->make($id);

        foreach ($this->getReBound($id) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }

    /**
     * Get the has callbacks for a given type.
     * 
     * @param  string  $id
     * 
     * @return array
     */
    protected function getReBound($id): array
    {
        return $this->hasCallbacks[$id] ?? [];
    }

    /**
     * Register a binding if it hasn't already been registered.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|string|null  $value
     * @param  bool  $shared
     * 
     * @return void
     */
    public function bindIf($id, $value = null, $shared = false): void
    {
        if ( ! $this->bound($id)) {
            $this->bind($id, $value, $shared);
        }
    }

    /**
     * Register a singleton binding in the container.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|string|null  $value
     * 
     * @return void
     */
    public function singleton($id, $value = null): void
    {
        $this->bind($id, $value, true);
    }
    
    /**
     * Register a singleton if it hasn't already been registered.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|string|null  $value
     * 
     * @return void
     */
    public function singletonIf($id, $value = null): void
    {
        if ( ! $this->bound($id)) {
            $this->singleton($id, $value);
        }
    }

    /**
     * Register a scoped binding in the container.
     *
     * @param  \Closure|string  $id
     * @param  \Closure|string|null  $value
     * 
     * @return void
     */
    public function scoped($id, $value = null): void
    {
        $this->scopedInstances[] = $id;

        $this->singleton($id, $value);
    }
    
    /**
     * Register a scoped binding if it hasn't already been registered.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|string|null  $value
     * 
     * @return void
     */
    public function scopedIf($id, $value = null): void
    {
        if ( ! $this->bound($id)) {
            $this->scoped($id, $value);
        }
    }

    /**
     * Remove all id traces of the specified binding.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function destroyBinding($id): void
    {
        if ($this->has($id)) {
            unset($this->bindings[$id], $this->instances[$id], $this->resolved[$id]);
        }
    }

    /**
     * Marks a callable as being a factory service.
     * 
     * @param  string  $id
     * 
     * @return \Closure
     */
    public function factory($id): Closure
    {
        return fn () => $this->make($id);
    }

    /**
     * Return and array containing all bindings.
     * 
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
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
        $this->removeAbstractAlias($id);

        $isBound = $this->bound($id);
        
        unset($this->aliases[$id]);
        
        $this->instances[$id] = $instance;
        
        if ($isBound) {
            $this->reBound($id);
        }

        return $instance;
    }
    
    /**
     * Remove an alias from the contextual binding alias cache.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function removeAbstractAlias($id)
    {
        if ( ! isset($this->aliases[$id])) {
            return;
        }
        
        foreach ($this->abstractAliases as $abstract => $aliases) {
            foreach ($aliases as $index => $alias) {
                if ($alias == $id) {
                    unset($this->abstractAliases[$abstract][$index]);
                }
            }
        }
    }

    /**
     * Return all defined value binding.
     * 
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->bindings);
    }

    /**
     * An alias function name for make().
     * 
     * @param  string  $id
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function makeAssign($id, array $parameters = []): mixed
    {
        return $this->make($id, $parameters);
    }

    /**
     * Resolve the given type from the container.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * 
     * @return object
     */
    public function make($id, array $parameters = []): mixed 
    {
        return $this->resolve($id, $parameters);
    }

    /**
     * Resolve the given type from the container.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * @param  bool $raiseEvents
     * 
     * @return mixed
     */
    protected function resolve($id, array $parameters = [], bool $raiseEvents = true)
    {
        $id = $this->getAlias($id);
        
        // First we'll fire any event handlers which handle the "before" 
        // resolving of specific types.
        if ($raiseEvents) {
            $this->fireBeforeResolvingCallbacks($id, $parameters);
        }

        $value = $this->getContextualValue($id);
        
        $needsContextualBuild = ! empty($parameters) || ! is_null($value);

        if (isset($this->instances[$id]) && ! $needsContextualBuild) {
            return $this->instances[$id];
        }

        $this->across[] = $parameters;
        
        if (is_null($value)) {
            $value = $this->getValue($id);
        }

        $object = ($this->isBuildable($value, $id))
            ? $this->build($value)
            : $this->make($value);
            
        // If we defined any extenders for this type, we'll need to spin 
        // through them and apply them to the object being built.
        foreach ($this->getExtenders($id) as $extenders) {
            $object = $extenders($object, $this);
        }

        if ($this->isShared($id)) {
            $this->instances[$id] = $object;
        }
        
        if ($raiseEvents) {
            $this->fireResolvingCallbacks($id, $object);
        }
        
        // Before returning, we will also set the resolved flag to "true" 
        // and pop off the parameter overrides for this build.
        if ( ! $needsContextualBuild) {
            $this->resolved[$id] = true;
        }

        array_pop($this->across);
        
        return $object;
    }

    /**
     * Get the alias for an id if available.
     * 
     * @param  string  $id
     * 
     * @return string
     */
    public function getAlias($id): string
    {
        return isset($this->aliases[$id]) 
                ? $this->getAlias($this->aliases[$id])
                : $id;
    }

    /**
     * Get the class type for a given id.
     *
     * @param  string|callable  $id
     * 
     * @return mixed
     */
    protected function getvalue($id)
    {
        // If we don't have a registered resolver or value for the type, we'll just
        // assume each type is a value name and will attempt to resolve it as is
        // since the container should be able to resolve values automatically.
        if (isset($this->bindings[$id])) {
            return $this->bindings[$id]['value'];
        }

        if ($this->environmentResolver === null ||
            ($this->checkedAttributeBindings[$id] ?? false) || ! is_string($id)) {
            return $id;
        }

        return $this->getValueBindingFromAttributes($id);
    }

    /**
     * Get the class binding for an id from the Bind attribute.
     *
     * @param  string  $id
     * 
     * @return mixed
     */
    protected function getValueBindingFromAttributes($id)
    {
        $this->checkedAttributeBindings[$id] = true;

        try {
            $reflected = new ReflectionClass($id);
        } catch (ReflectionException) {
            return $id;
        }

        $bindAttributes = $reflected->getAttributes(Bind::class);

        if ($bindAttributes === []) {
            return $id;
        }

        $value = $maybeValue = null;

        foreach ($bindAttributes as $reflectedAttribute) {
            $instance = $reflectedAttribute->newInstance();

            if ($instance->environments === ['*']) {
                $maybeValue = $instance->value;

                continue;
            }

            if ($this->currentEnvironmentIs($instance->environments)) {
                $class = $instance->value;

                break;
            }
        }

        if ($maybeValue !== null && $value === null) {
            $value = $maybeValue;
        }

        if ($value === null) {
            return $id;
        }

        match ($this->getScopedTyped($reflected)) {
            'scoped' => $this->scoped($id, $value),
            'singleton' => $this->singleton($id, $value),
            null => $this->bind($id, $value),
        };

        return $this->bindings[$id]['value'];
    }
    
    /**
     * Get the contextual value binding for the given id.
     * 
     * @param  string|callable  $id
     * 
     * @return \Closure|string|array|null
     */
    protected function getContextualValue($id)
    {
        if ( ! is_null($binding = $this->findInContextualBindings($id))) {
            return $binding;
        }
        
        if (empty($this->abstractAliases[$id])) {
            return;
        }
        
        foreach ($this->abstractAliases[$id] as $alias) {
            if ( ! is_null($binding = $this->findInContextualBindings($alias))) {
                return $binding;
            }
        }
    }
    
    /**
     * Find the value binding for the given id in the contextual binding array.
     * 
     * @param  string|callable  $id
     * 
     * @return \Closure|string|null
     */
    protected function findInContextualBindings($id)
    {
        return $this->contextual[end($this->buildStack)][$id] ?? null;
    }

    /**
     * Instantiate a value instance of the given type.
     * 
     * @param  \Closure|string  $value
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Contracts\Container\BindingResolutionException
     */
    public function build($value): mixed
    {
        if ($value instanceof Closure) {
            $this->buildStack[] = spl_object_hash($value);

            try {
                return $value($this, $this->getLastParameterOverride());
            } finally {
                array_pop($this->buildStack);
            }
        }
        
        try {
            $reflection = new ReflectionClass($value);
        } catch (ReflectionException $e) {
            throw new BindingResolutionException("Target class [$value] does not exist", 0, $e);
        }

        if ( ! $reflection->isInstantiable()) {
            return $this->buildNotInstantiable($value);
        }

        $this->buildStack[] = $value;

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildStack);
            
            $this->fireAfterResolvingAttributeCallbacks(
                $reflection->getAttributes(), $instance = new $value
            );
            
            return $instance;
        }

        $dependencies = $constructor->getParameters();
        
        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (BindingResolutionException $e) {
            array_pop($this->buildStack);
            
            throw $e;
        }

        array_pop($this->buildStack);
        
        $this->fireAfterResolvingAttributeCallbacks(
            $reflection->getAttributes(), $instance = $reflection->newInstanceArgs($instances)
        );
        
        return $instance;
    }

    /**
     * Throw an exception that the value is not instantiable.
     *
     * @param  string  $value
     * 
     * @return void
     *
     * @throws \Syscodes\Components\Contracts\Container\BindingResolutionException
     */
    protected function buildNotInstantiable(string $value): void
    {
        if ( ! empty($this->buildStack)) {
           $reset   = implode(', ', $this->buildStack);

           $message = "Target [{$value}] is not instantiable while building [{$reset}]"; 
        } else {
            $message = "Target [{$value}] is not instantiable";
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
    protected function resolveDependencies(array $dependencies): array
    {
        $params = [];

        foreach ($dependencies as $dependency) {
            if ($this->getHasParameters($dependency)) {
                $params[] = $this->getParameterOverride($dependency);

                continue;
            }
            
            $param = is_null(Util::getParameterClassName($dependency)) 
                       ? $this->getResolveNonClass($dependency) 
                       : $this->getResolveClass($dependency);

            $this->fireAfterResolvingAttributeCallbacks($dependency->getAttributes(), $param);
                       
            if ($dependency->isVariadic()) {
                $params = array_merge($params, $param);
            } else {
                $params[] = $param;
            }
        }

        return $params;
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param  \ReflectionParameter  $dependency
     * 
     * @return bool
     */
    protected function getHasParameters($dependency): bool
    {
        return array_key_exists($dependency->name, $this->getLastParameterOverride());
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride(): array
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
    protected function getParameterOverride($dependency): mixed
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Resolve a non-class hinted dependency.
     *
     * @param  \ReflectionParameter  $parameter
     * 
     * @return mixed
     *
     * @throws \Syscodes\Components\Container\Exceptions\BindingResolutionException
     */
    protected function getResolveNonClass(ReflectionParameter $parameter)
    {
        if ( ! is_null($class = $this->getContextualValue('$'.$parameter->getName()))) {
            return Util::unwrapExistOfClosure($class, $this);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isVariadic()) {
            return [];
        }
        
        if ($parameter->hasType() && $parameter->allowsNull()) {
            return null;
        }

        return $this->unresolvableNonClass($parameter);
    }
    
    /**
     * Throw an exception for an unresolvable class.
     * 
     * @param  \ReflectionParameter  $parameter
     * 
     * @return void
     * 
     * @throws \Syscodes\Components\Contracts\Container\BindingResolutionException
     */
    protected function unresolvableNonClass(ReflectionParameter $parameter): void
    {
        $message = "Unresolvable dependency resolving [{$parameter}] in class [{$parameter->getDeclaringClass()->getName()}]";
        
        throw new BindingResolutionException($message);
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * 
     * @return mixed
     *
     * @throws \Syscodes\Components\Container\Exceptions\BindingResolutionException
     */
    protected function getResolveClass(ReflectionParameter $parameter): mixed
    {
        $className = Util::getParameterClassName($parameter);

        if ($parameter->isDefaultValueAvailable() && ! $this->bound($className)) {
            array_pop($this->across);
            
            return $parameter->getDefaultValue();
        }

        try {
            return $parameter->isVariadic()
                ? $this->resolveVariadicClass($parameter)
                : $this->make($className);
        } catch (BindingResolutionException $e) {            
            if ($parameter->isVariadic()) {
                array_pop($this->across);
                
                return [];
            }

            throw $e;
        }
    }
    
    /**
     * Resolve a class based variadic dependency from the container.
     * 
     * @param  \ReflectionParameter  $parameter
     * 
     * @return mixed
     */
    protected function resolveVariadicClass(ReflectionParameter $parameter): mixed
    {
        $className = Util::getParameterClassName($parameter);
        
        $id = $this->getAlias($className);
        
        if ( ! is_array($value = $this->getContextualValue($id))) {
            return $this->make($className);
        }
        
        return array_map(fn ($id) => $this->resolve($id), $value);
    }
    
    /**
     * Register a new before resolving callback for all types.
     * 
     * @param  \Closure|string  $id 
     * @param  \Closure|null  $callback
     * 
     * @return void
     */
    public function beforeResolving($id, ?Closure $callback = null)
    {
        if (is_string($id)) {
            $id = $this->getAlias($id);
        }
        
        if ($id instanceof Closure && is_null($callback)) {
            $this->globalBeforeResolvingCallbacks[] = $id;
        } else {
            $this->beforeResolvingCallbacks[$id][] = $callback;
        }
    }
    
    /**
     * Register a new resolving callback.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|null  $callback
     * 
     * @return void
     */
    public function resolving($id, ?Closure $callback = null)
    {
        if (is_string($id)) {
            $id = $this->getAlias($id);
        }
        
        if (is_null($callback) && $id instanceof Closure) {
            $this->globalResolvingCallbacks[] = $id;
        } else {
            $this->resolvingCallbacks[$id][] = $callback;
        }
    }
    
    /**
     * Register a new after resolving callback for all types.
     * 
     * @param  \Closure|string  $id
     * @param  \Closure|null  $callback
     * 
     * @return void
     */
    public function afterResolving($id, ?Closure $callback = null)
    {
        if (is_string($id)) {
            $id = $this->getAlias($id);
        }
        
        if ($id instanceof Closure && is_null($callback)) {
            $this->globalAfterResolvingCallbacks[] = $id;
        } else {
            $this->afterResolvingCallbacks[$id][] = $callback;
        }
    }
    
    /**
     * Fire all of the before resolving callbacks.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * 
     * @return void
     */
    protected function fireBeforeResolvingCallbacks($id, $parameters = [])
    {
        $this->fireBeforeCallbackArray($id, $parameters, $this->globalBeforeResolvingCallbacks);
        
        foreach ($this->beforeResolvingCallbacks as $type => $callbacks) {
            if ($type === $id || is_subclass_of($id, $type)) {
                $this->fireBeforeCallbackArray($id, $parameters, $callbacks);
            }
        }
    }
    
    /**
     * Fire an array of callbacks with an object.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * @param  array  $callbacks
     * 
     * @return void
     */
    protected function fireBeforeCallbackArray($id, $parameters, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($id, $parameters, $this);
        }
    }
    
    /**
     * Fire all of the resolving callbacks.
     * 
     * @param  string  $id
     * @param  mixed  $object
     * 
     * @return void
     */
    protected function fireResolvingCallbacks($id, $object)
    {
        $this->fireCallbackArray($object, $this->globalResolvingCallbacks);
        
        $this->fireCallbackArray(
            $object, $this->getCallbacksForType($id, $object, $this->resolvingCallbacks)
        );
        
        $this->fireAfterResolvingCallbacks($id, $object);
    }
    
    /**
     * Fire all of the after resolving callbacks.
     * 
     * @param  string  $id
     * @param  mixed  $object
     * 
     * @return void
     */
    protected function fireAfterResolvingCallbacks($id, $object)
    {
        $this->fireCallbackArray($object, $this->globalAfterResolvingCallbacks);
        
        $this->fireCallbackArray(
            $object, $this->getCallbacksForType($id, $object, $this->afterResolvingCallbacks)
        );
    }
    
    /**
     * Fire all of the after resolving attribute callbacks.
     * 
     * @param  \ReflectionAttribute[]  $attributes
     * @param  mixed  $object
     * 
     * @return void
     */
    public function fireAfterResolvingAttributeCallbacks(array $attributes, $object)
    {
        foreach ($attributes as $attribute) {
            if (is_a($attribute->getName(), ContextualAttribute::class, true)) {
                $instance = $attribute->newInstance();
                
                if (method_exists($instance, 'after')) {
                    $instance->after($instance, $object, $this);
                }
            }
            
            $callbacks = $this->getCallbacksForType(
                $attribute->getName(), $object, $this->afterResolvingAttributeCallbacks
            );
            
            foreach ($callbacks as $callback) {
                $callback($attribute->newInstance(), $object, $this);
            }
        }
    }
    
    /**
     * Get all callbacks for a given type.
     * 
     * @param  string  $id
     * @param  object  $object
     * @param  array  $callbacksPerType
     * 
     * @return array
     */
    protected function getCallbacksForType($id, $object, array $callbacksPerType)
    {
        $results = [];
        
        foreach ($callbacksPerType as $type => $callbacks) {
            if ($type === $id || $object instanceof $type) {
                $results = array_merge($results, $callbacks);
            }
        }
        
        return $results;
    }
    
    /**
     * Fire an array of callbacks with an object.
     * 
     * @param  mixed  $object
     * @param  array  $callbacks
     * 
     * @return void
     */
    protected function fireCallbackArray($object, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($object, $this);
        }
    }

    /**
     * Determine if a given type is shared.
     * 
     * @param  string  $id
     * 
     * @return bool
     */
    protected function isShared($id): bool
    {
        if (isset($this->instances[$id])) {
            return true;
        }
        
        if (isset($this->bindings[$id]['shared']) && $this->bindings[$id]['shared'] === true) {
            return true;
        }
        
        if ( ! class_exists($id)) {
            return false;
        }
        
        if (($scopedType = $this->getScopedTyped($id)) === null) {
            return false;
        }
        
        if ($scopedType === 'scoped') {
            if ( ! in_array($id, $this->scopedInstances, true)) {
                $this->scopedInstances[] = $id;
            }
        }
        
        return true;
    }/**
     * Determine if a ReflectionClass has scoping attributes applied.
     *
     * @param  ReflectionClass|string  $reflection
     * 
     * @return "singleton"|"scoped"|null
     */
    protected function getScopedTyped(ReflectionClass|string $reflection): ?string
    {
        $className = $reflection instanceof ReflectionClass
            ? $reflection->getName()
            : $reflection;

        if (array_key_exists($className, $this->checkedSingletonOrScopedAttributes)) {
            return $this->checkedSingletonOrScopedAttributes[$className];
        }

        try {
            $reflection = $reflection instanceof ReflectionClass
                ? $reflection
                : new ReflectionClass($reflection);
        } catch (ReflectionException) {
            return $this->checkedSingletonOrScopedAttributes[$className] = null;
        }

        $type = null;

        if ( ! empty($reflection->getAttributes(Singleton::class))) {
            $type = 'singleton';
        } elseif ( ! empty($reflection->getAttributes(Scoped::class))) {
            $type = 'scoped';
        }

        return $this->checkedSingletonOrScopedAttributes[$className] = $type;
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
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        return CallBoundMethod::call($this, $callback, $parameters, $defaultMethod);
    }
    
    /**
     * Remove all id traces of the specified binding.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function remove($id): void
    {
        $this->destroyBinding($id);
    }

    /**
     * Set the binding with given key / value.
     * 
     * @param  string  $id
     * @param  mixed  $value
     * 
     * @return static
     */
    public function set($id, $value): static
    {
        $this->bind($id, $value instanceof Closure ? $value : fn () => $value);

        return $this;
    }

    /**
     * Get the extender callbacks for a given type.
     * 
     * @param  string  $id
     * 
     * @return array
     */
    protected function getExtenders(string $id): array
    {
        return $this->extenders[$this->getAlias($id)] ?? [];
    }

    /**
     * Remove all of the extender callbacks.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function eraseExtenders(string $id): void
    {
        unset($this->extenders[$this->getAlias($id)]);
    }

    /**
     * Determine the environment for the container.
     *
     * @param  array<int, string>|string  $environments
     * 
     * @return bool
     */
    public function currentEnvironmentIs($environments)
    {
        return $this->environmentResolver === null
            ? false
            : call_user_func($this->environmentResolver, $environments);
    }

    /**
     * Flush the container of all bindings and resolved instances.
     * 
     * @return void
     */
    public function flush(): void
    {
        $this->aliases   = [];
        $this->resolved  = [];
        $this->bindings  = [];
        $this->instances = [];
        $this->abstractAliases = [];
        $this->scopedInstances = [];
        $this->checkedSingletonOrScopedAttributes = [];
    }

    /*
    |----------------------------------------------------------------
    | ContainerInterface Methods
    |---------------------------------------------------------------- 
    */

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param  string  $id  Identifier of the entry to look for.
     * 
     * @return mixed Entry.
     *
     * @throws EntryIdentifierException
     */
    public function get(string $id)
    {
        try {
            return $this->resolve($id);
        } catch (Exception $e) {
            if ( ! $this->has($id)) {
                throw new $e;
            }   

            throw new EntryIdentifierException($id, is_int($e->getCode()) ? $e->getCode() : 0, $e);
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * 
     * @param  string  $id  Identifier of the entry to look for.
     * 
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->bound($id);
    }

    /*
    |-----------------------------------------------------------------
    | ArrayAccess Methods
    |-----------------------------------------------------------------
    */

    /**
     * Determine if a given offset exists.
     * 
     * @param  mixed  $offset
     * 
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->bound($offset);
    }

    /**
     * Get the value at a given offset.
     * 
     * @param  mixed  $offset
     * 
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Set the value at a given offset.
     * 
     * @param  mixed  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value at a given offset.
     * 
     * @param  mixed  $offset
     * 
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Magic method.
     * 
     * Dynamically access container services.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this[$key];
    }

    /**
     * Magic method.
     * 
     * Dynamically set container services.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function __set(string $key, mixed $value)
    {
        return $this[$key] = $value;
    }
}