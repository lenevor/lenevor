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

namespace Syscodes\Components\Contracts\Auth\Access;

/**
 * Determine if a given ability this authorized on the users.
 */
interface Gate
{
    /**
     * Determine if a given ability has been defined.
     * 
     * @param  string[]  $ability
     * 
     * @return bool
     */
    public function has($ability): bool;
    
    /**
     * Define a new ability.
     * 
     * @param  string  $ability
     * @param  \callable|string  $callback
     * 
     * @return static
     */
    public function define(string $ability, callable|string $callback): static;
    
    /**
     * Define abilities for a resource.
     * 
     * @param  string  $name
     * @param  string  $class
     * @param  array|null  $abilities
     * 
     * @return static
     */
    public function resource(string $name, string $class, ?array $abilities = null): static;
    
    /**
     * Define a policy class for a given class type.
     * 
     * @param  string  $class
     * @param  string  $policy
     * 
     * @return static
     */
    public function policy(string $class, string $policy): static;
    
    /**
     * Register a callback to run before all Gate checks.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function before(callable $callback): static;
    
    /**
     * Register a callback to run after all Gate checks.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function after(callable $callback): static;
    
    /**
     * Determine if the given ability should be granted for the current user.
     * 
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return bool
     */
    public function allows(string $ability, array $arguments = []): bool;
    
    /**
     * Determine if the given ability should be denied for the current user.
     * 
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return bool
     */
    public function denies(string $ability, array $arguments = []): bool;
    
    /**
     * Determine if the given ability should be granted.
     * 
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return bool
     */
    public function check(string $ability, array $arguments = []): bool;
    
    /**
     * Determine if any one of the given abilities should be granted for the current user.
     * 
     * @param  \iterable|string  $abilities
     * @param  array  $arguments
     * 
     * @return bool
     */
    public function any($abilities, array $arguments = []): bool;
    
    /**
     * Determine if the given ability should be granted for the current user.
     * 
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     * 
     * @throws \Syscodes\Components\Auth\Access\AuthorizationException
     */
    public function authorize(string $ability, array $arguments = []);
    
    /**
     * Inspect the user for the given ability.
     * 
     * @param  string  $ability
     * @param  array  $arguments
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public function inspect(string $ability, array $arguments = []);
    
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
    public function raw(string $ability, array $arguments): mixed;
    
    /**
     * Get a policy instance for a given class.
     * 
     * @param  object|string  $class
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getPolicyFor($class);
    
    /**
     * Get a guard instance for the given user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|mixed  $user
     * 
     * @return static
     */
    public function forUser($user): static;
    
    /**
     * Get all of the defined abilities.
     * 
     * @return array
     */
    public function abilities(): array;
}