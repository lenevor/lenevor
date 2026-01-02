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

use Closure;
use Exception;
use Syscodes\Components\Cache\concerns\CacheMultipleKeys;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Database\Connections\ConnectionInterface;
use Syscodes\Components\Database\Connections\PostgresConnection;
use Syscodes\Components\Database\Exceptions\QueryException;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Support\Str;

/**
 * Database cache handler.
 */
class DatabaseStore implements Store
{
    use CacheMultipleKeys,
        InteractsWithTime;

    /**
     * The database connection instance.
     * 
     * @var \Syscodes\Components\Database\Connections\ConnectionInterface $connection
     */
    protected $connection;

    /**
     * A string that should be prepended to keys.
     * 
     * @var string $prefix
     */
    protected $prefix;

    /**
     * The name of the cache table.
     * 
     * @var string $table
     */
    protected $table;

    /**
     * Constructor. Create a new DatabaseStore class instance.
     * 
     * @param  \Syscodes\Components\Database\Connections\ConnectionInterface  $connection
     * @param  string  $table
     * @param  string  $prefix
     * 
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table, $prefix = '')
    {
        $this->table      = $table;
        $this->prefix     = $prefix;
        $this->connection = $connection;
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
        $prefixed = $this->prefix.$key;

        $cache = $this->table()->where('key', '=', $prefixed)->first();
        
        if (is_null($cache)) return;
        
        $cache = is_array($cache) ? (object) $cache : $cache;
        
        if ($this->currentTime() >= $cache->expiration) {
            $this->delete($key);
            
            return;
        }
        
        return $this->unserialize($cache->value);
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
        $key        = $this->prefix.$key;
        $value      = $this->serialize($value);
        $expiration = $this->getTime() + $seconds;
        
        try {
            return $this->table()->insert(compact('key', 'value', 'expiration'));
        } catch (Exception $e) {
            $result = $this->table()->where('key', $key)->update(compact('value', 'expiration'));
            
            return $result > 0;
        }
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
        $key        = $this->prefix.$key;
        $value      = $this->serialize($value);
        $expiration = $this->getTime() + $seconds;
        
        try {
            return $this->table()->insert(compact('key', 'value', 'expiration'));
        } catch (QueryException $e) {
            return $this->table()
                ->where('key', $key)
                ->where('expiration', '<=', $this->getTime())
                ->update([
                    'value' => $value,
                    'expiration' => $expiration,
                ]) >= 1;
        }
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
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current + $value;
        });
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
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current - $value;
        });
    }
    
    /**
     * Increment or decrement an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  \Closure  $callback
     * 
     * @return int|bool
     */
    protected function incrementOrDecrement(string $key, mixed $value, Closure $callback): int|bool
    {
        return $this->connection->transaction(function () use ($key, $value, $callback) {
            $prefixed = $this->prefix.$key;
            $cache    = $this->table()->where('key', $prefixed)->first();
            
            if (is_null($cache)) return false;
            
            $cache = is_array($cache) ? (object) $cache : $cache;
            
            $current = $this->unserialize($cache->value);
            
            $result = $callback((int) $current, $value);

            if ( ! is_numeric($current)) return false;
            
            $this->table()->where('key', $prefixed)->update([
                'value' => $this->serialize($result),
            ]);
            
            return $result;
        });
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
        $this->table()->where('key', '=', $this->prefix.$key)->delete();

        return true;
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
        return $this->put($key, $value, 315360000);
    }
    
    /**
     * Get the current system time.
     * 
     * @return int
     */
    protected function getTime(): int
    {
        return $this->currentTime();
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        $this->table()->delete();
        
        return true;
    }
    
    /**
     * Get a query builder for the cache table.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function table()
    {
        return $this->connection->table($this->table);
    }
    
    /**
     * Get the underlying database connection.
     * 
     * @return \Syscodes\Components\Database\Connections\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
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
     * Serialize the given value.
     * 
     * @param  mixed  $value
     * 
     * @return string
     */
    protected function serialize($value): string
    {
        $result = serialize($value);
        
        if ($this->connection instanceof PostgresConnection && str_contains($result, "\0")) {
            $result = base64_encode($result);
        }
        
        return $result;
    }
    
    /**
     * Unserialize the given value.
     * 
     * @param  string  $value
     * 
     * @return mixed
     */
    protected function unserialize($value): mixed
    {
        if ($this->connection instanceof PostgresConnection && ! Str::contains($value, [':', ';'])) {
            $value = base64_decode($value);
        }
        
        return unserialize($value);
    }
}