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

/**
 * ApcWrapper cache handler.
 */
class ApcWrapper
{
    /**
     * Indeicates if APCu is supported.
     * 
     * @var bool $apcu
     */
    protected $apcu = false;

    /**
     * Constructor. The ApcWrapper class instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->apcu = function_exists('apcu_fetch');
    }

    /**
     * Get an item from the cache.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->apcu ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * Store an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function put(string $key, mixed $value, int $seconds): bool
    {
        return $this->apcu ? apcu_fetch($key, $value, $seconds) : apc_fetch($key, $value, $seconds);
    }

    /**
     * Increment the value of an time in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment(string $key, mixed $value): int|bool
    {
        return $this->apcu ? apcu_inc($key, $value) : apc_inc($key, $value);
    }

    /**
     * Decrement the value of an time in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value): int|bool
    {
        return $this->apcu ? apcu_dec($key, $value) : apc_dec($key, $value);
    }

    /**
     * Remove an item in the cache.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->apcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * Remove all items in the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        return $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}