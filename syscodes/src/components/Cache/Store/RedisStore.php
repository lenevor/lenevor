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

namespace Syscodes\Components\Cache\Store;

use Syscodes\Components\Redis\RedisManager;
use Syscodes\Components\Contracts\Cache\Store;

/**
 * Redis cache handler.
 */
class RedisStore implements Store
{
    /**
     * The Redis connection that should be used.
     * 
     * @var string $connection
     */
    protected $connection;

    /**
     * A string that should be prepended to keys.
     * 
     * @var string $prefix
     */
    protected $prefix;

    /**
     * The Redis database connection.
     * 
     * @var \Syscodes\Components\Redis\RedisManager $redis 
     */
    protected $redis;

    /**
     * Constructor. Create a new Redis store.
     * 
     * @param  \Syscodes\Components\Redis\RedisManager  $redis 
     * @param  string  $prefix
     * @param  string  $connection  
     * 
     * @return void
     */
    public function __construct(RedisManager $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;

        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }

    /**
     * Gets an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->connection()->get($this->prefix.$key);

        return ! is_null($value) ? $this->unserialize($value) : null;
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
        $results = [];
        
        $values = $this->connection()->mget(array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys));
        
        foreach ($values as $index => $value) {
            $results[$keys[$index]] = ! is_null($value) ? $this->unserialize($value) : null;
        }
        
        return $results;
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
        return (bool) $this->connection()->setex(
                $this->prefix.$key,
                (int) max(1, $seconds),
                $this->serialize($value)
        );
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
        $this->connection()->multi();
        
        $manyResult = null;
        
        foreach ($values as $key => $value) {
            $result = $this->put($key, $value, $seconds);
            
            $manyResult = is_null($manyResult) ? $result : $result && $manyResult;
        }
        
        $this->connection()->exec();
        
        return $manyResult ?: false;
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
        return $this->connection()->incrby($this->prefix.$key, $value);
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
        return $this->connection()->decrby($this->prefix.$key, $value);
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
        return (bool) $this->connection()->del($this->prefix.$key);
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
        return (bool) $this->connection()->set($this->prefix.$key, $this->serialize($value));
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        $this->connection()->flushdb();

        return true; 
    }

    /**
     * Get the Redis database instance.
     * 
     * @return \Syscodes\Components\Redis\RedisManager
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * Get the Redis connection instance.
     * 
     * @return \Predis\ClientInterface
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Set the connection name to be used.
     * 
     * @param  string  $connection
     * 
     * @return void
     */
    public function setConnection($connection): void
    {
        $this->connection = $connection;
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
        $this->prefix = (strlen($prefix) > 0) ? $prefix.':' : '';
    }

    /**
     * Serialize the value.
     * 
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function serialize(mixed $value): mixed
    {
        return is_numeric($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     * 
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function unserialize(mixed $value): mixed
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}