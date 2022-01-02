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

namespace Syscodes\Components\Contracts\Cache;

/**
 * Sets functions by the item from the cache repository store.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
    public function has($key): bool;

    /**
     * Attempts to retrieve an item from the cache by key.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Retrieve an item from the cache and delete it.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Saves an item to the cache store.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $value  The data to save 
     * @param  int|null  $ttl  Time To Live, in second
     */
    public function save($key, $value, $ttl = null);

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement($key, $value = 1);

    /**
     * Remove a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function delete($key): bool;

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function forever($key, $value): bool;

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function clear(): bool;

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
     * @return self
     */
    public function setCacheTime($seconds): self;
}