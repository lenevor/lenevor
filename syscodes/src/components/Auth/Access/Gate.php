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

namespace Syscodes\Components\Auth\Access;

use Exception;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Auth\Concerns\HandlesAuthorization;
use Syscodes\Components\Contracts\Auth\Access\Gate as GateContract;

/**
 * Allows the registered of authorizations into given abilities.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Gate implements GateContract
{
    use HandlesAuthorization;

    /**
     * All of the defined abilities.
     * 
     * @var array $abilities
     */
    protected $abilities = [];
    
    /**
     * All of the registered after callbacks.
     * 
     * @var array $afterCallbacks
     */
    protected $afterCallbacks = [];
    
    /**
     * All of the registered before callbacks.
     * 
     * @var array $beforeCallbacks
     */
    protected $beforeCallbacks = [];
    
    /**
     * The container instance.
     * 
     * @var \Syscodes\Components\Container\Container $container
     */
    protected $container;
    
    /**
     * All of the defined policies.
     * 
     * @var array $policies
     */
    protected $policies = [];
    
    /**
     * The user resolver callable.
     * 
     * @var \callable $userResolver
     */
    protected $userResolver;

    /**
     * Constructor. Create a new Gate class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\container  $container
     * @param  \callable  $userResolver
     * @param  array  $abilities
     * @param  array  $policies
     * @param  array  $beforeCallbacks
     * @param  array  $afterCallbacks
     * 
     * @return void
     */
    public function __construct(
        Container $container,
        callable $userResolver,
        array $abilities = [],
        array $policies = [],
        array $beforeCallbacks = [],
        array $afterCallbacks = []
    ) {
        $this->policies = $policies;
        $this->container = $container;
        $this->abilities = $abilities;
        $this->userResolver = $userResolver;
        $this->afterCallbacks = $afterCallbacks;
        $this->beforeCallbacks = $beforeCallbacks; 
    }    

    /**
     * Determine if a given ability has been defined.
     * 
     * @param  string  $ability
     * 
     * @return bool
     */
    public function has($ability): bool
    {
        $abilities = is_array($ability) ? $ability : func_get_args();
        
        foreach ($abilities as $ability) {
            if (! isset($this->abilities[$ability])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Define a new ability.
     * 
     * @param  string  $ability
     * @param  callable|string  $callback
     * 
     * @return self
     */
    public function define($ability, $callback): self
    {
        if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
            $callback = $callback[0].'@'.$callback[1];
        }
        
        if (is_callable($callback)) {
            $this->abilities[$ability] = $callback;
        } elseif (is_string($callback) && Str::contains($callback, '@')) {            
            $this->abilities[$ability] = $this->buildAbilityCallback($ability, $callback);
        } else {
            throw new InvalidArgumentException("Callback must be a callable, callback array, or a 'Class@method' string.");
        }
        
        return $this;
    }
    
    /**
     * Define abilities for a resource.
     * 
     * @param  string  $name
     * @param  string  $class
     * @param  array|null  $abilities
     * 
     * @return self
     */
    public function resource($name, $class, array $abilities = null): self
    {
        $abilities = $abilities ?: [
            'view' => 'view',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
        ];
        
        foreach ($abilities as $ability => $method) {
            $this->define($name.'.'.$ability, $class.'@'.$method);
        }
        
        return $this;
    }
    
    /**
     * Create the ability callback for a callback string.
     * 
     * @param  string  $ability
     * @param  string  $callback
     * 
     * @return \Closure
     */
    protected function buildAbilityCallback($ability, $callback)
    {
        return function () use ($ability, $callback) {
            if (Str::contains($callback, '@')) {
                [$class, $method] = Str::parseCallback($callback);
            } else {
                $class = $callback;
            }
            
            $policy = $this->resolvePolicy($class);

            $arguments = func_get_args();
            
            return isset($method)
                    ? $policy->{$method}(...$arguments)
                    : $policy(...$arguments);
        };
    }
    
    /**
     * Define a policy class for a given class type.
     * 
     * @param  string  $class
     * @param  string  $policy
     * 
     * @return self
     */
    public function policy($class, $policy): self
    {
        $this->policies[$class] = $policy;
        
        return $this;
    }
    
    /**
     * Register a callback to run before all Gate checks.
     * 
     * @param  \callable  $callback
     * 
     * @return self
     */
    public function before(callable $callback): self
    {
        $this->beforeCallbacks[] = $callback;
        
        return $this;
    }
    
    /**
     * Register a callback to run after all Gate checks.
     * 
     * @param  \callable  $callback
     * 
     * @return self
     */
    public function after(callable $callback): self
    {
        $this->afterCallbacks[] = $callback;
        
        return $this;
    }
    
    /**
     * Determine if the given ability should be granted for the current user.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return bool
     */
    public function allows($ability, $arguments = []): bool
    {
        return $this->check($ability, $arguments);
    }
    
    /**
     * Determine if the given ability should be denied for the current user.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return bool
     */
    public function denies($ability, $arguments = []): bool
    {
        return ! $this->check($ability, $arguments);
    }
    
    /**
     * Determine if the given ability should be granted.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return bool
     */
    public function check($ability, $arguments = []): bool
    {
        try {
            $result = $this->inspect($ability, $arguments);
        } catch (Exception $e) {
            return false;
        }
        
        return (bool) $result;
    }
    
    /**
     * Determine if any one of the given abilities should be granted for the current user.
     * 
     * @param  \iterable|string  $abilities
     * @param  array|mixed  $arguments
     * 
     * @return bool
     */
    public function any($abilities, $arguments = []): bool
    {
        return collect($abilities)->contains(function ($ability) use ($arguments) {
            return $this->check($ability, $arguments);
        });
    }
    
    /**
     * Determine if the given ability should be granted for the current user.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return \Syscodes\Components\Auth\Access\Response
     * 
     * @throws \Syscodes\Components\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        return $this->inspect($ability, $arguments)->authorize();
    }
    
    /**
     * Inspect the user for the given ability.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public function inspect($ability, $arguments = [])
    {
        try {
            $result = $this->raw($ability, $arguments);
            
            if ($result instanceof Response) {
                return $result;
            }
            
            return $result ? Response::allow() : Response::deny();
        } catch (InvalidArgumentException $e) {
            throw $e;
        }
    }
    
    /**
     * Get the raw result from the authorization callback.
     * 
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Auth\Access\AuthorizationException
     */
    public function raw($ability, array $arguments)
    {
        $arguments = Arr::wrap($arguments);
        
        if (is_null($user = $this->resolveUser())) {
            return false;
        }
        
        $result = $this->callBeforeCallbacks($user, $ability, $arguments);
        
        if (is_null($result)) {
            $result = $this->callAuthCallback($user, $ability, $arguments);
        }
        
        $this->callAfterCallbacks($user, $ability, $arguments, $result);
        
        return $result;
    }
    
    /**
     * Resolve and call the appropriate authorization callback.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return bool
     */
    protected function callAuthCallback($user, $ability, array $arguments): bool
    {
        $callback = $this->resolveAuthCallback($user, $ability, $arguments);
        
        return $callback($user, ...$arguments);
    }
    
    /**
     * Call all of the before callbacks and return if a result is given.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return bool|null
     */
    protected function callBeforeCallbacks($user, $ability, array $arguments)
    {
        $arguments = array_merge([$user, $ability], $arguments);
        
        foreach ($this->beforeCallbacks as $callback) {
            if ( ! is_null($result = call_user_func_array($callback, $arguments))) {
                return $result;
            }
        }
    }
    
    /**
     * Call all of the after callbacks with check result.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @param  bool  $result
     * 
     * @return void
     */
    protected function callAfterCallbacks($user, $ability, array $arguments, $result): void
    {
        $arguments = array_merge([$user, $ability, $result], $arguments);
        
        foreach ($this->afterCallbacks as $callback) {
            call_user_func_array($callback, $arguments);
        }
    }
    
    /**
     * Resolve the callable for the given ability and arguments.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return \callable
     */
    protected function resolveAuthCallback($user, $ability, array $arguments)
    {
        if ($this->firstArgumentToPolicy($arguments)) {
            return $this->resolvePolicyCallback($user, $ability, $arguments);
        }
        elseif (isset($this->abilities[$ability])) {
            return $this->abilities[$ability];
        }

        return function ()
        {
            return false;
        };
    }
    
    /**
     * Determine if the first argument in the array corresponds to a policy.
     * 
     * @param  array  $arguments
     * 
     * @return bool
     */
    protected function firstArgumentToPolicy(array $arguments): bool
    {
        if ( ! isset($arguments[0])) {
            return false;
        }
        
        $argument = $arguments[0];
        
        if (is_object($argument)) {
            $class = getClass($argument, true);
            
            return isset($this->policies[$class]);
        }
        
        return is_string($argument) && isset($this->policies[$argument]);
    }
    
    /**
     * Resolve the callback for a policy check.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return \callable
     */
    protected function resolvePolicyCallback($user, $ability, array $arguments): callable
    {
        return function () use ($user, $ability, $arguments) {
            $class = headItem($arguments);
            
            if (method_exists($instance = $this->getPolicyFor($class), 'before')) {
                $parameters = array_merge(array($user, $ability), $arguments);
                
                if ( ! is_null($result = call_user_func_array(array($instance, 'before'), $parameters))) {
                    return $result;
                }
            }
            
            if ( ! method_exists($instance, $method = Str::camelcase($ability))) {
                return false;
            }
            
            return call_user_func_array(array($instance, $method), array_merge([$user], $arguments));
        };
    }
    
    /**
     * Get a policy instance for a given class.
     * 
     * @param  object|string  $class
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getPolicyFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if ( ! is_string($class)) {
            return;
        }
        
        if (isset($this->policies[$class])) {
            return $this->resolvePolicy($this->policies[$class]);
        }
        
        foreach ($this->policies as $expected => $policy) {
            if (is_subclass_of($class, $expected)) {
                return $this->resolvePolicy($policy);
            }
        }
        
        throw new InvalidArgumentException("Policy not defined for [{$class}].");
    }
    
    /**
     * Build a policy class instance of the given type.
     * 
     * @param  object|string  $class
     * 
     * @return mixed
     */
    public function resolvePolicy($class)
    {
        return $this->container->make($class);
    }
    
    /**
     * Get a guard instance for the given user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|mixed  $user
     * 
     * @return static
     */
    public function forUser($user)
    {
        $callback = function () use ($user) {
            return $user;
        };
        
        return new static(
            $this->container, $callback, $this->abilities,
            $this->policies, $this->beforeCallbacks, $this->afterCallbacks
        );

    }
    
    /**
     * Resolve the user from the user resolver.
     * 
     * @return mixed
     */
    protected function resolveUser()
    {
        return call_user_func($this->userResolver);
    }
    
    /**
     * Get all of the defined abilities.
     * 
     * @return array
     */
    public function abilities(): array
    {
        return $this->abilities;
    }
    
    /**
     * Get all of the defined policies.
     * 
     * @return array
     */
    public function policies(): array
    {
        return $this->policies;
    }

    /**
     * Set the container instance used by the gate.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
     * 
     * @return self
     */
    public function setContainer(Container $container): self
    {
        $this->container = $container;
        
        return $this;
    }
}