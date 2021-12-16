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

namespace Syscodes\Components\Cache\Store;

use Syscodes\Components\Redis\RedisManager;
use Syscodes\Components\Contracts\Cache\Store;

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
     * {@inheritdoc}
     */
    public function get($key)
    {
        $value = $this->connection()->get($this->prefix.$key);

        return ! is_null($value) ? $this->unserialize($value) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds): bool
    {
        return (bool) $this->connection()->setex(
                $this->prefix.$key,
                (int) max(1, $seconds),
                $this->serialize($value)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrby($this->prefix.$key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrby($this->prefix.$key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return (bool) $this->connection()->del($this->prefix.$key);
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value): bool
    {
        return (bool) $this->connection()->set($this->prefix.$key, $this->serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
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
     * {@inheritdoc}
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