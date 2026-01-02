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
 * Allows identifier the user data by authenticate.
 */
interface Authenticatable
{
    /**
     * Get the unique identifier for the user.
     * 
     * @return mixed
     */
    public function getAuthIdentifier(): mixed;
    
    /**
     * Get the name of the unique identifier for the user.
     * 
     * @return string
     */
    public function getAuthIdentifierName(): string;
    
    /**
     * Get the password for the user.
     * 
     * @return string
     */
    public function getAuthPassword(): string;
    
    /**
     * Get the token value for the "remember me" session.
     * 
     * @return string
     */
    public function getRememberToken();
    
    /**
     * Set the token value for the "remember me" session.
     * 
     * @param  string  $value
     * 
     * @return void
     */
    public function setRememberToken(string $value): void;
    
    /**
     * Get the column name for the "remember me" token.
     * 
     * @return string
     */
    public function getRememberTokenName(): string;
}