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

namespace Syscodes\Components\Contracts\Auth;

/**
 * Allows user authentication and log into given sessions or cookies.
 */
interface StateGuard extends Guard
{
    /**
     * Attempt to authenticate a user using the given credentials.
     * 
     * @param  array  $credentials
     * @param  bool  $remember
     * 
     * @return bool
     */
    public function attempt(array $credentials = [], bool $remember = false): bool;
    
    /**
     * Log a user into the application without sessions or cookies.
     * 
     * @param  array  $credentials
     * 
     * @return bool
     */
    public function once(array $credentials = []): bool;
    
    /**
     * Log a user into the application.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * 
     * @return void
     */
    public function login(Authenticatable $user, bool $remember = false): void;
    
    /**
     * Log the given user ID into the application.
     * 
     * @param  mixed  $id
     * @param  bool  $remember
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|bool
     */
    public function loginUsingId(mixed $id, bool $remember = false);
    
    /**
     * Log the given user ID into the application without sessions or cookies.
     * 
     * @param  mixed  $id
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|bool
     */
    public function onceUsingId(mixed $id);
    
    /**
     * Determine if the user was authenticated via "remember me" cookie.
     * 
     * @return bool
     */
    public function viaRemember(): bool;
    
    /**
     * Log the user out of the application.
     * 
     * @return void
     */
    public function logout(): void;
}