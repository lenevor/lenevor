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

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the App class facade.
 *
 * @method static \Syscodes\Components\Auth\AuthManager extend(string $driver, \Closure $callback)
 * @method static \Syscodes\Components\Auth\AuthManager provider(string $name, \Closure $callback)
 * @method static \Syscodes\Components\Contracts\Auth\Authenticatable loginUsingId(mixed $id, bool $remember = false)
 * @method static \Syscodes\Components\Contracts\Auth\Authenticatable|null user()
 * @method static \Syscodes\Components\Contracts\Auth\Guard|\Syscodes\Components\Contracts\Auth\StateGuard guard(string|null $name = null)
 * @method static \Syscodes\Components\Contracts\Auth\UserProvider|null createUserProvider(string $provider = null)
 * @method static bool attempt(array $credentials = [], bool $remember = false)
 * @method static bool hasUser()
 * @method static bool check()
 * @method static bool guest()
 * @method static bool once(array $credentials = [])
 * @method static bool onceUsingId(mixed $id)
 * @method static bool validate(array $credentials = [])
 * @method static bool viaRemember()
 * @method static int|string|null id()
 * @method static void login(\Syscodes\Components\Contracts\Auth\Authenticatable $user, bool $remember = false)
 * @method static void logout()
 * @method static void setUser(\Syscodes\Components\Contracts\Auth\Authenticatable $user)
 * @method static void shouldUse(string $name);
 *
 * @see \Syscodes\Components\Auth\AuthManager
 * @see \Syscodes\Components\Contracts\Auth\Factory
 * @see \Syscodes\Components\Contracts\Auth\Guard
 * @see \Syscodes\Components\Contracts\Auth\StateGuard
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'auth';
    }
}