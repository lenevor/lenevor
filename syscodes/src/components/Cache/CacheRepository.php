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
     * Determine if an item exists in the cache.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function has(string $key): bool
    {
        return ! is_null($this->get($key));
    }

    /**
     * Attempts to retrieve an item from the cache by key.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $value = $this->store->get($this->itemKey($key));

        if (is_null($value)) {
            $value = value($default);
        }

        return $value;
    }
    
    /**
     * Gets multiple items from the cache by key.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function many(array $keys): array
    {
        $values = $this->store->many(collect($keys)->map(
            fn ($value, $key)  => is_string($key) ? $key : $value)->values()->all()
        );
        
        return collect($values)->map(
            fn ($value, $key) => $this->handleMany($keys, $key, $value)
        )->all();
    }
    
    /**
     * Handle a result for the "many" method.
     * 
     * @param  array  $keys
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return mixed
     */
    protected function handleMany(array $keys, string $key, mixed $value): mixed
    {
        if (is_null($value)) {
            return isset($keys[$key]) ? value($keys[$key]) : null;
        }
        
        return $value;
    }

    /**
     * Store an item in the cache if the key does not exist.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function add(string $key, mixed $value, $ttl = null): bool
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
    public function put(string $key, mixed $value, $ttl = null): bool
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
     * Store multiple items in the cache for a given number of seconds.
     * 
     * @param  array  $values
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function putMany(array $values, $ttl = null): bool
    {
        if ($ttl === null) {
            return $this->putManyForever($values);
        }
        
        $seconds = $this->getSeconds($ttl);
        
        if ($seconds <= 0) {
            return $this->deleteMultiple(array_keys($values));
        }
        
        $result = $this->store->putMany($values, $seconds);
        
        return $result;
    }
    
    /**
     * Store multiple items in the cache indefinitely.
     * 
     * @param  array  $values
     * 
     * @return bool
     */
    protected function putManyForever(array $values): bool
    {
        foreach ($values as $key => $value) {
            if ( ! $this->forever($key, $value)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Retrieve an item from the cache and delete it.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        return take($this->get($key, $default), fn () => $this->delete($key));
    }

    /**
     * Saves an item to the cache store.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $value  The data to save 
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl  Time To Live, in second
     * 
     * @return bool
     */
    public function save(string $key, mixed $value, $ttl = null): bool
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
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment(string $key, mixed $value = 1): int|bool
    {
        return $this->store->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value = 1): int|bool
    {
        return $this->store->decrement($key, $value);
    }

    /**
     * Remove a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function delete(string $key): mixed
    {
        return $this->store->delete($this->itemKey($key));
    }

    /**
     * Removes multiple items from the cache store.
     * 
     * @param  array  $keys
     * 
     * @return bool
     */
    public function deleteMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            if ( ! $this->forget($key)) {
                return false;
            }
        }
        
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
    public function forever(string $key, mixed $value): bool
    {
        return $this->store->forever($this->itemKey($key), $value);
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
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
    protected function getMinutes($duration): ?int
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
    protected function itemKey(string $key): string
    {
        return $key;
    }

    /**
     * Get the cache store implementation.
     * 
     * @return \Syscodes\Contracts\Cache\Store
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
    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }

    /**
     * Set the default cache time in seconds
     * 
     * @param  int|null  $seconds
     * 
     * @return static
     */
    public function setCacheTime(?int $seconds): static
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
     * @param  mixed  $offset
     * 
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieve an item from the cache by key.
     * 
     * @param  mixed  $offset
     * 
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Store an item in the cache for the default time.
     * 
     * @param  mixed  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
       $this->put($offset, $value, $this->cacheTime);
    }

    /**
     * Remove an item from the cache.
     * 
     * @param  mixed  $offset
     * 
     * @return void
     */
    public function offsetUnset(mixed $offset): void
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
     * @return void
     */
    public function __clone()
    {
        $this->store = clone $this->store;
    }
}