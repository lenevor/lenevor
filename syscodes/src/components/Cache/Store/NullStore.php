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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 * @since       0.3.1
 */

namespace Syscodes\Cache\Store;

use Syscodes\Contracts\Cache\Store;

/**
 * Null cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class NullStore implements Store
{
    /**
     * The array storaged value.
     * 
     * @var array $storage
     */
    protected $storage = [];

    /**
     * Gets an item from the cache by key.
     * 
     * @param  string|array  $key
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
     * @param  mixed  $value
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function put($key, $value, $seconds)
    {
        return false;
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value  (1 by default)
     * 
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return false;
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value  (1 by default)
     * 
     * @return int
     */
    protected function decrement($key, $value = 1)
    {
        return false;
    }

    /**
     * Remove a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function forever($key, $value)
    {
        return false;
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush()
    {
        return true;
    }

    /**
     * Gets the cache key prefix.
     * 
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }
}