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

namespace Syscodes\Components\Contracts\Cache;

/**
 * Sets functions by the item from the cache store.
 */
interface Store
{
    /**
     * Gets an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get(string $key);
    
    /**
     * Gets multiple items from the cache by key.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function many(array $keys): array;

    /**
     * Store an item in the cache for a given number of seconds.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * 
     * @return bool
     */
    public function put(string $key, mixed $value, int $seconds): bool;
    
    /**
     * Store multiple items in the cache for a given number of seconds.
     * 
     * @param  array  $values
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function putMany(array $values, int $seconds): bool;

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function increment(string $key, mixed $value = 1): int|bool;

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value = 1): int|bool;

    /**
     * Deletes a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function delete(string $key): mixed;

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return bool
     */
    public function forever(string $key, mixed $value): bool;

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool;
    
    /**
     * Gets the cache key prefix.
     *
     * @return string
     */
    public function getPrefix(): string;
}