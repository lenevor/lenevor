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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth;

use Syscodes\Components\Contracts\Auth\Authenticatable as UserContract;

/**
 * Gets the generic user's attributes for authentication.
 */
class GenericUser implements UserContract
{
    /**
     * All of the user's attributes.
     * 
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Constructor. Create a new GenericUser class instance.
     * 
     * @param  array  $attributes
     * 
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;        
    }

    /**
     * Get the unique identifier for the user.
     * 
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->attributes[$this->getAuthIdentifierName()];
    }
    
    /**
     * Get the name of the unique identifier for the user.
     * 
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }
    
    /**
     * Get the password for the user.
     * 
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->attributes['password'];
    }
    
    /**
     * Get the token value for the "remember me" session.
     * 
     * @return string
     */
    public function getRememberToken(): string
    {
        return $this->attributes[$this->getRememberTokenName()];
    }
    
    /**
     * Set the token value for the "remember me" session.
     * 
     * @param  string  $value
     * 
     * @return void
     */
    public function setRememberToken(string $value): void
    {
        $this->attributes[$this->getRememberTokenName()] = $value;
    }
    
    /**
     * Get the column name for the "remember me" token.
     * 
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    /**
     * Magic method.
     * 
     * Dynamically access the user's attributes.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Magic method.
     * 
     * Dynamically set an attribute on the user.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Magic method.
     * 
     * Dynamically check if a value is set on the user.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset a value on the user.
     * 
     * @param  string  $key
     * 
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}