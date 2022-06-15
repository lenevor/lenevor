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

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the Config class facade.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static array getQueuedCookies()
 * @method static unqueue($name)
 * @method static void queue(...$parameters)
 * 
 * @see \Syscodes\Components\Cookie\CookieManager
 */
class Cookie extends Facade
{
    /**
     * Determine if a cookie exists on the request.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public static function has($key): bool
    {
        return ! is_null(static::$applications['request']->cookie($key, null));
    }
    
    /**
     * Retrieve a cookie from the request.
     * 
     * @param  string|null  $key
     * @param  mixed  $default
     * 
     * @return string|array|null
     */
    public static function get($key = null, $default = null)
    {
        return static::$applications['request']->cookie($key, $default);
    }
    
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cookie';
    }
}