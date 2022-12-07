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
        return $this;
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

    }
    
    /**
     * Get the raw result from the authorization callback.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Auth\Access\AuthorizationException
     */
    public function raw($ability, $arguments = [])
    {

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

    }
    
    /**
     * Get all of the defined abilities.
     * 
     * @return array
     */
    public function abilities(): array
    {

    }
}
