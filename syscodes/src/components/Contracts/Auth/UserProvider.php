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

namespace Syscodes\Components\Contracts\Auth;

/**
 * Gets the token and credentials for the given user.
 */
interface UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     * 
     * @param  mixed  $identifier
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById(mixed $identifier);
    
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * 
     * @param  mixed  $identifier
     * @param  string  $token
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken(mixed $identifier, string $token);
    
    /**
     * Update the "remember me" token for the given user in storage.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * 
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, string $token): void;
    
    /**
     * Retrieve a user by the given credentials.
     * 
     * @param  array  $credentials
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials);
    
    /**
     * Validate a user against the given credentials.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * 
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool;
}