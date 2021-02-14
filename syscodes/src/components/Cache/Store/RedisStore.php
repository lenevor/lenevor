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
 */

namespace Syscodes\Cache\Store;

use Syscodes\Redis\RedisManager;
use Syscodes\Contracts\Cache\Store;

/**
 * Redis cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * @var \Syscodes\Redis\RedisManager $redis 
     */
    protected $redis;

    /**
     * Constructor. Create a new Redis store.
     * 
     * @param  \Syscodes\Redis\RedisManager  $redis 
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
     * @param  string|array  $key
     * 
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->connection()->get($this->prefix.$key);

        return ! is_null($value) ? $this->unserialize($value) : null;
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
        return (bool) $this->connection()->setex(
                $this->prefix.$key,
                (int) max(1, $seconds),
                $this->serialize($value)
        );
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrby($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrby($this->prefix.$key, $value);
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
        return (bool) $this->connection()->del($this->prefix.$key);
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
        return (bool) $this->connection()->set($this->prefix.$key, $this->serialize($value));
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush()
    {
        $this->connection()->flushdb();

        return true; 
    }

    /**
     * Get the Redis database instance.
     * 
     * @return \Syscodes\Redis\RedisManager
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
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the cache key prefix.
     * 
     * @return string
     */
    public function getPrefix()
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
    public function setPrefix($prefix)
    {
        return $this->prefix = (strlen($prefix) > 0) ? $prefix.':' : '';
    }

    /**
     * Serialize the value.
     * 
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function serialize($value)
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
    public function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}