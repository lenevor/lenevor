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

namespace Syscodes\Components\Cache;

use DateTime;
use ArrayAccess;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Contracts\Cache\Repository;

/**
 * Begin executing operations of storage data if the store supports it.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class CacheRepository implements ArrayAccess, Repository
{
    use InteractsWithTime,
        Macroable {
            __call as macroCall;
    }

    /**
     * The default number of seconds to store items.
     * 
     * @var int $cacheTime
     */
    protected $cacheTime = 3600;

    /**
     * The cache store implementation.
     * 
     * @var \Syscodes\Components\Contracts\Cache\Store $store
     */
    protected $store;

    /**
     * Constructor. Create a new cache repository instance.
     * 
     * @param  \Syscodes\Components\Contracts\Cache\Store  $store
     * 
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return ! is_null($this->get($key));
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $value = $this->store->get($this->itemKey($key));

        if (is_null($value)) {
            $value = value($default);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $value, $ttl = null): bool
    {
        $seconds = null;

        if ($ttl !== null) {
            $seconds = $this->getSeconds($ttl);

            if ($seconds <= 0) {
                return false;
            }
        }

        if (is_null($this->get($key))) {
            return $this->put($key, $value, $seconds);
        }

        return false;
    }
    
    /**
     * Store an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function put($key, $value, $ttl = null): bool
    {
        if (null === $ttl) {
            return $this->forever($key, $value);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->delete($key);
        }

        $result = $this->store->put($this->itemKey($key), $value, $seconds);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function pull($key, $default = null)
    {
        return take($this->get($key, $default), function () use ($key) {
            $this->delete($key);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            return $this->forever($key, $value);
        }
        
        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->delete($key);
        }

        return $this->store->put($this->itemKey($key), $value, $seconds);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        return $this->store->increment($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->store->decrement($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key): bool
    {
        return $this->store->delete($this->itemKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value): bool
    {
        return $this->store->forever($this->itemKey($key), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        return $this->store->flush();
    }
    
    /**
     * Calculate the number of minutes with the given duration.
     * 
     * @param  \DateTime|int  $duration
     * 
     * @return int|null
     */
    protected function getMinutes($duration)
    {
        if ($duration instanceof DateTime) {
            $fromNow = Chronos::instance($duration)->getMinutes();
            
            return $fromNow > 0 ? $fromNow : null;
        }
        
        return is_string($duration) ? (int) $duration : $duration;
    }

    /**
     * Calculate the number of seconds with the given duration.
     * 
     * @param  \DateTime|\DateInterval|int  $ttl
     * 
     * @return int
     */
    protected function getSeconds($ttl): int
    {
        $duration = $this->parseDateInterval($ttl);

        if ($duration instanceof DateTime) {
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
    protected function itemKey($key): string
    {
        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheTime($seconds): self
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
    public function offsetExists($offset): bool
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
    public function offsetSet($offset, $value): void
    {
       $this->put($offset, $value, $this->cacheTime);
    }

    /**
     * Remove an item from the cache.
     * 
     * @param  string  $offset
     * 
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }

    /**
     * Magic method.
     * 
     * Handle dynamic calls into missing methods to the store.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        
        return $this->store->{$method}(...$parameters);
    }

    /**
     * Magic Method.
     * 
     * Clone cache repository instance.
     * 
     * return void
     */
    public function __clone()
    {
        $this->store = clone $this->store;
    }
}