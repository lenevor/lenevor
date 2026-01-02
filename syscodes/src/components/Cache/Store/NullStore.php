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

namespace Syscodes\Components\Cache\Store;

use Syscodes\Components\Cache\concerns\CacheMultipleKeys;
use Syscodes\Components\Contracts\Cache\Store;

/**
 * Null cache handler.
 */
class NullStore implements Store
{
    use CacheMultipleKeys;

    /**
     * The array storaged value.
     * 
     * @var array $storage
     */
    protected $storage = [];

    /**
     * Gets an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key)
    {
        //
    }

    /**
     * Store an item in the cache for a given number of seconds.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * 
     * @return bool
     */
    public function put(string $key, mixed $value, int $seconds): bool
    {
        return false;
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function increment(string $key, mixed $value = 1): int|bool
    {
        return false;
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value = 1): int|bool
    {
        return false;
    }

    /**
     * Deletes a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function delete(string $key): mixed
    {
        return true;
    }

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        return false;
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        return true;
    }

    /**
     * Gets the cache key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return '';
    }
}