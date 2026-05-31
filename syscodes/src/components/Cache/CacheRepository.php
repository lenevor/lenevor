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

namespace Syscodes\Components\Cache;

use ArrayAccess;
use Closure;
use DateTime;
use Syscodes\Components\Contracts\Cache\Repository;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Support\Traits\Macroable;

use function Syscodes\Components\Support\enum_value;

/**
 * Begin executing operations of storage data if the store supports it.
 */
class CacheRepository implements ArrayAccess, Repository
{
    use InteractsWithTime, Macroable {
        __call as macroCall;
    }

    /**
     * The default number of seconds to store items.
     * 
     * @var int
     */
    protected $cacheTime = 3600;

    /**
     * The cache store configuration options.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The event dispatcher implementation.
     *
     * @var \Syscodes\Components\Contracts\Events\Dispatcher|null
     */
    protected $events;

    /**
     * The cache store implementation.
     * 
     * @var \Syscodes\Components\Contracts\Cache\Store
     */
    protected $store;

    /**
     * Constructor. Create a new cache repository instance.
     * 
     * @param  \Syscodes\Components\Contracts\Cache\Store  $store
     * @param  array  $config
     * 
     * @return void
     */
    public function __construct(Store $store, array $config = [])
    {
        $this->store = $store;
        $this->config = $config;
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
     * Determine if an item doesn't exist in the cache.
     *
     * @param  \UnitEnum|string  $key
     * 
     * @return bool
     */
    public function missing($key): bool
    {
        return ! $this->has($key);
    }

    /**
     * Attempts to retrieve an item from the cache by key.
     * 
     * @param  string  $key  Cache item name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $key = enum_value($key);
        
        // $this->event(new RetrievingKey($this->getName(), $key));

        $value = $this->store->get($this->itemKey($key));

        if (is_null($value)) {
            // $this->event(new CacheMissed($this->getName(), $key));

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
        $values = $this->store->many((new Collection($keys))
            ->map(fn ($value, $key)  => is_string($key) ? $key : $value)
            ->values()
            ->all()
        );
        
        return (new Collection($values))
            ->map(fn ($value, $key) => $this->handleMany($keys, $key, $value))
            ->all();
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
    protected function handleMany($keys, $key, $value)
    {
        if (is_null($value)) {
            return isset($keys[$key]) ? value($keys[$key]) : null;
        }
        
        return $value;
    }

    /**
     * Store an item in the cache if the key does not exist.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
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
     * @param  \UnitEnum|array|string  $key
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

        return $this->store->put($this->itemKey($key), $value, $seconds);
    }

    /**
     * Store an item in the cache.
     *
     * @param  \UnitEnum|array|string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * 
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->put($key, $value, $ttl);
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
        
        return $this->store->putMany($values, $seconds);
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
     * @param  \UnitEnum|array|string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return take($this->get($key, $default), fn () => $this->delete($key));
    }

    /**
     * Saves an item to the cache store.
     * 
     * @param  \UnitEnum|string  $key  Cache item name
     * @param  mixed  $value  The data to save 
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl  Time To Live, in second
     * 
     * @return bool
     */
    public function save($key, $value, $ttl = null): bool
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
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment($key, $value = 1): int|bool
    {
        return $this->store->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement($key, $value = 1): int|bool
    {
        return $this->store->decrement($key, $value);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param  \UnitEnum|string  $key
     * @param  \Closure|\DateTimeInterface|\DateInterval|int|null  $ttl
     * @param  \Closure $callback
     * 
     * @return mixed
     */
    public function remember($key, $ttl, Closure $callback)
    {
        $value = $this->get($key);

        if ( ! is_null($value)) {
            return $value;
        }

        $value = $callback();

        $this->put($key, $value, value($ttl, $value));

        return $value;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param  \UnitEnum|string  $key
     * @param  \Closure  $callback
     * 
     * @return mixed
     */
    public function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);

        if ( ! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }


    /**
     * Set the expiration of a cached item.
     *
     * @param  \UnitEnum|string  $key
     * @param  \DateTimeInterface|\DateInterval|int  $ttl
     * 
     * @return bool
     */
    public function touch($key, $ttl): bool
    {
        $key = enum_value($key);

        return $this->store->touch($this->itemKey($key), $this->getSeconds($ttl));
    }

    /**
     * Remove a specific item from the cache store.
     * 
     * @param  \UnitEnum|string  $key
     * 
     * @return mixed
     */
    public function delete($key)
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
            if ( ! $this->delete($key)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function forever($key, $value): bool
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
     * Get the name of the cache store.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->config['store'] ?? null;
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
     * @return \Syscodes\Components\Contracts\Cache\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Set the cache store implementation.
     *
     * @param  \Syscodes\Components\Contracts\Cache\Store  $store
     * 
     * @return static
     */
    public function setStore($store): static
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Fire an event for this cache instance.
     *
     * @param  object|string  $event
     * 
     * @return void
     */
    protected function event($event)
    {
        $this->events?->dispatch($event);
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Syscodes\Components\Contracts\Events\Dispatcher|null
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @return void
     */
    public function setEventDispatcher(Dispatcher $events): void
    {
        $this->events = $events;
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