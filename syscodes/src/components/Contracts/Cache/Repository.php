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

namespace Syscodes\Components\Contracts\Cache;

use Closure;
use UnitEnum;

/**
 * Sets functions by the item from the cache repository store.
 */
interface Repository
{
    /**
     * Store an item in the cache if the key does not exist.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl 
     * @return bool
     */
    public function add($key, $value, $ttl = null): bool;

    /**
     * Store an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl 
     * @return bool
     */
    public function put($key, $value, $ttl = null): bool;

    /**
     * Retrieve an item from the cache and delete it.
     * 
     * @param  \UnitEnum|array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value 
     * @return int|bool
     */
    public function increment($key, $value = 1): int|bool;

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value 
     * @return int|bool
     */
    public function decrement($key, $value = 1): int|bool;

    /**
     * Remove a specific item from the cache store.
     * 
     * @param  \UnitEnum|string  $key 
     * @return mixed
     */
    public function delete($key);

     /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param  \UnitEnum|string  $key
     * @param  \DateTimeInterface|\DateInterval|\Closure|int|null  $ttl
     * @param  \Closure  $callback 
     * @return mixed
     */
    public function remember($key, $ttl, Closure $callback);

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param  \UnitEnum|string  $key
     * @param  \Closure  $callback 
     * @return mixed
     */
    public function rememberForever($key, Closure $callback);

    /**
     * Set the expiration of a cached item.
     *
     * @param  \UnitEnum|string  $key
     * @param  \DateTimeInterface|\DateInterval|int  $ttl 
     * @return bool
     */
    public function touch($key, $ttl): bool;

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * @return bool
     */
    public function forever($key, $value): bool;

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool;

    /**
     * Get the cache store implementation.
     * 
     * @return \Syscodes\Components\Contracts\Cache\Store
     */
    public function getStore();
}