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

namespace Syscodes\Components\Auth\Concerns;

/**
 * Gets the token for identification in session of user.
 */
trait Authenticatable
{
    /**
     * The column name of the "remember me" token.
     * 
     * @var string $rememberTokenName
     */
    protected $rememberTokenName = 'remember_token';
    
    /**
     * Get the name of the unique identifier for the user.
     * 
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }
    
    /**
     * Get the unique identifier for the user.
     * 
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }
    
    /**
     * Get the password for the user.
     * 
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }
    
    /**
     * Get the token value for the "remember me" session.
     * 
     * @return string
     */
    public function getRememberToken(): string
    {
        if ( ! empty($this->getRememberTokenName())) {
            return (string) $this->{$this->getRememberTokenName()};
        }
    }
    
    /**
     * Set the token value for the "remember me" session.
     * 
     * @param  string  $value
     * 
     * @return void
     */
    public function setRememberToken($value): void
    {
        if ( ! empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }
    
    /**
     * Get the column name for the "remember me" token.
     * 
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return $this->rememberTokenName;
    }
}