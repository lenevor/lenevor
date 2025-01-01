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
 * Sets functions by the item from the cache repository store.
 */
interface Repository
{
    /**
     * Determine if an item exists in the cache.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Attempts to retrieve an item from the cache by key.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Gets multiple items from the cache by key.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function many(array $keys): array;

    /**
     * Store an item in the cache if the key does not exist.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function add(string $key, mixed $value, $ttl = null): bool;

    /**
     * Store an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function put(string $key, mixed $value, $ttl = null): bool;

    /**
     * Store multiple items in the cache for a given number of seconds.
     * 
     * @param  array  $values
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function putMany(array $values, $ttl = null): bool;

    /**
     * Retrieve an item from the cache and delete it.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed;

    /**
     * Saves an item to the cache store.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $value  The data to save 
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl  Time To Live, in second
     * 
     * @return bool
     */
    public function save(string $key, mixed $value, $ttl = null): bool;

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment(string $key, mixed $value = 1): int|bool;

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value = 1): int|bool;

    /**
     * Remove a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function delete(string $key): mixed;

    /**
     * Removes multiple items from the cache store.
     * 
     * @param  array  $keys
     * 
     * @return bool
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed  $value
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
     * Get the cache store implementation.
     * 
     * @return \Syscodes\Contracts\Cache\Store
     */
    public function getStore();

    /**
     * Get the default cache time.
     * 
     * @return int
     */
    public function getCacheTime(): int;

    /**
     * Set the default cache time in seconds
     * 
     * @param  int|null  $seconds
     * 
     * @return static
     */
    public function setCacheTime(?int $seconds): static;
}