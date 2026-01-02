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

use Memcached;
use ReflectionMethod;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Support\InteractsWithTime;

/**
 * Memcached cache handler.
 */
class MemcachedStore implements Store
{
    use InteractsWithTime;

    /**
     * The Memcached instance.
     * 
     * @var \Memcached $memcached
     */
    protected $memcached;

    /**
     * Indicates whether we are using Memcached version >= 3.0.0.
     * 
     * @var bool $onVersion
     */
    protected $onVersion;

    /**
     * A string that should be prepended to keys.
     * 
     * @var string $prefix
     */
    protected $prefix;

    /**
     * Constructor. The new Memcached store instance.
     * 
     * @param  \Memcached  $memcached
     * @param  string  $prefix
     * 
     * @return void
     */
    public function __construct($memcached, $prefix = '')
    {
        $this->setPrefix($prefix);

        $this->memcached = $memcached;
        $this->onVersion = (new ReflectionMethod('Memcached', 'getMulti'))
                           ->getNumberOfParameters() == 2;
    }

    /**
     * Destructor. Closes the connection to Memcache(d) if present.
     * 
     * @return void
     */
    public function __destruct()
    {
        if ($this->memcached instanceof Memcached) {
            $this->memcached->quit();
        }
    }

    /**
     * Gets an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get(string $key)
    {
        $value = $this->memcached->get($this->prefix.$key);

        if ($this->memcached->getResultCode() == 0) {
            return $value;
        }
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
        $prefixed = array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys);
        
        if ($this->onVersion) {
            $values = $this->memcached->getMulti($prefixed, Memcached::GET_PRESERVE_ORDER);
        } else {
            $values = $this->memcached->getMulti($prefixed, null, Memcached::GET_PRESERVE_ORDER);
        }
        
        if ($this->memcached->getResultCode() != 0) {
            return array_fill_keys($keys, null);
        }
        
        return array_combine($keys, $values);
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function add(string $key, mixed $value, int $seconds): bool
    {
        return $this->memcached->add($this->prefix.$key, $value, $this->calcExpiration($seconds));
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
        return $this->memcached->set($this->prefix.$key, $value, $this->calcExpiration($seconds));
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     * 
     * @param  array  $values
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function putMany(array $values, int $seconds): bool
    {
        $prefixed = [];
        
        foreach ($values as $key => $value) {
            $prefixed[$this->prefix.$key] = $value;
        }
        
        return $this->memcached->setMulti(
            $prefixed, $this->calcExpiration($seconds)
        );
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
        return $this->memcached->increment($this->prefix.$key, $value);
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
        return $this->memcached->decrement($this->prefix.$key, $value = 1);
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
        return $this->memcached->delete($this->prefix.$key);
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
        return $this->put($this->prefix.$key, $value, 0);
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * Gets the Memcached connection.
     * 
     * @return \Memcached
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * Gets the cache key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    
    /**
     * Set the cache key prefix.
     * 
     * @param  string  $prefix
     * 
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }

    /**
     * Gets the expiration time of the key.
     * 
     * @param  int  $seconds
     * 
     * @return int
     */
    protected function calcExpiration(int $seconds): int
    {
        return $this->toTimestamp($seconds);
    }

    /**
     * Gets the UNIX timestamp for the given number of seconds.
     * 
     * @param  int  $seconds
     * 
     * @return int
     */
    protected function toTimestamp(int $seconds): int
    {
        return $seconds > 0 ? $this->availableAt($seconds) : 0;
    }
}