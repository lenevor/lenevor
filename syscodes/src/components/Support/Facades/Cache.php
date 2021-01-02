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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the Cache class facade.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 *
 * @method static \Syscodes\Cache\CacheRepository store(string $name = null)
 * @method static bool has(string $key)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed pull(string $key, mixed $default = null)
 * @method static int|bool increment(string $key, mixed $value = 1)
 * @method static int|bool decrement(string $key, mixed $value = 1)
 * @method static mixed delete(string $key)
 * @method static bool forever(string $key, mixed $value)
 * @method static void flush()
 * @method static string getPrefix()
 * 
 * @see  \Syscodes\Cache\CacheManager
 * @see  \Syscodes\Cache\CacheRepository
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}