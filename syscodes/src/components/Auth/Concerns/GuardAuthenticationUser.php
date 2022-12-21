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

namespace Syscodes\Components\Auth\Concerns;

use Syscodes\Components\Contracts\Auth\UserProvider;
use Syscodes\Components\Auth\Exceptions\AuthenticationException;
use Syscodes\Components\Contracts\Auth\Authenticatable as AuthenticatableInterface;

/**
 * These methods are typically the same across all guards.
 */
trait GuardAuthenticationUser
{
    /**
     * The currently authenticated user.
     * 
     * @var Authenticatable $user
     */
    protected $user;
    
    /**
     * The user provider implementation.
     * 
     * @var \Syscodes\Components\Contracts\Auth\UserProvider $provider
     */
    protected $provider;
    
    /**
     * Determine if the current user is authenticated. If not, throw an exception.
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable
     * 
     * @throws \Syscodes\Components\Auth\AuthenticationException
     */
    public function authenticate()
    {
        if ( ! is_null($user = $this->user())) {
            return $user;
        }
        
        throw new AuthenticationException;
    }
    
    /**
     * Determine if the guard has a user instance.
     * 
     * @return bool
     */
    public function hasUser(): bool
    {
        return ! is_null($this->user);
    }
    
    /**
     * Determine if the current user is authenticated.
     * 
     * @return bool
     */
    public function check(): bool
    {
        return ! is_null($this->user());
    }
    
    /**
     * Determine if the current user is a guest.
     * 
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }
    
    /**
     * Get the ID for the currently authenticated user.
     * 
     * @return int|string|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }
    
    /**
     * Set the current user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return self
     */
    public function setUser(AuthenticatableInterface $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    /**
     * Get the user provider used by the guard.
     * 
     * @return \Syscodes\Components\Contracts\Auth\UserProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    /**
     * Set the user provider used by the guard.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\UserProvider  $provider
     * 
     * @return void
     */
    public function setProvider(UserProvider $provider): void
    {
        $this->provider = $provider;
    }
}