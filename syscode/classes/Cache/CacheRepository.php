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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscode\Cache;

use Closure;
use ArrayAccess;
use Syscode\Contracts\Cache\Store;
use Syscode\Support\InteractsWithTime;

/**
 * Begin executing operations of storage data if the store supports it.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class CacheRepository implements ArrayAccess
{
    use InteractsWithTime;

    /**
     * The default number of seconds to store items.
     * 
     * @var int $cacheTime
     */
    protected $cacheTime = 3600;

    /**
     * The cache store implementation.
     * 
     * @var \Syscode\Contracts\Cache\Store $store
     */
    protected $store;

    /**
     * Constructor. Create a new cache repository instance.
     * 
     * @param  \Syscode\Contracts\Cache\Store  $store
     * 
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Determine if an item exists in the cache.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function has($key)
    {
        return ! is_null($this->get($key));
    }

    /**
     * Attempts to retrieve an item from the cache by key.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $default  (null by default)
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->store->get($this->itemKey($key));

        if (is_null($value))
        {
            $value = value($default);
        }

        return $value;
    }

    /**
     * Retrieve an item from the cache and delete it.
     * 
     * @param  string  $key
     * @param  mixed  $default  (null by default)
     * 
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return take($this->get($key, $default), function () use ($key) {
            $this->delete($key);
        });
    }

    /**
     * Saves an item to the cache store.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $value  The data to save 
     * @param  int|null  $ttl  Time To Live, in seconds (null by default)
     */
    public function save($key, $value, $ttl = null)
    {
        if (null === $ttl)
        {
            return $this->forever($key, $value);
        }
        
        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0)
        {
            return $this->delete($key);
        }

        return $this->store->put($this->itemKey($key), $value, $seconds);
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
        return $this->store->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value  (1 by default)
     * 
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->store->decrement($key, $value);
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
        return $this->store->delete($this->itemKey($key));
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
        return $this->store->forever($this->itemKey($key), $value);
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function clear()
    {
        return $this->store->flush();
    }

    /**
     * Calculate the number of seconds with the given duration.
     * 
     * @param  \DateTime|\DateInterval|int  $ttl
     * 
     * @return int
     */
    protected function getSeconds($ttl)
    {
        $duration = $this->parseDateInterval($ttl);

        if ($duration instanceof DateTime)
        {
            $duration = $duration->diff($duration, false);
        }

        return (int) $duration > 0 ? $duration : 0;
    }

    /**
     * Format the key for a cache item.
     * 
     * @param  string  $key
     * 
     * @return string
     */
    protected function itemKey($key)
    {
        return $key;
    }

    /**
     * Get the cache store implementation.
     * 
     * @return \Syscode\Contracts\Cache\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Get the default cache time.
     * 
     * @return int
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * Set the default cache time in seconds
     * 
     * @param  int|null  $seconds
     * 
     * @return $this
     */
    public function setCacheTime($seconds)
    {
        $this->cacheTime = $seconds;

        return $this;
    }

    /*
    |-----------------------------------------------------------------
    | ArrayAccess Methods
    |-----------------------------------------------------------------
    */

    /**
     * Determine if a cached value exists.
     * 
     * @param  string  $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Retrieve an item from the cache by key.
     * 
     * @param  string  $offset
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Store an item in the cache for the default time.
     * 
     * @param  string  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return $this->put($key, $value, $this->cacheTime);
    }

    /**
     * Remove an item from the cache.
     * 
     * @param  string  $offset
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }

    /**
     * Handle dynamic calls into missing methods to the store.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->store->{$method}(...$parameters);
    }

    /**
     * Clone cache repository instance.
     * 
     * return void
     */
    public function __clone()
    {
        $this->store = clone $this->store;
    }
}