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
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Database\Connections\ConnectionInterface;
use Syscodes\Components\Database\Connections\PostgresConnection;
use Syscodes\Components\Database\Connections\SQLiteConnection;
use Syscodes\Components\Database\Connections\SqlServerConnection;
use Syscodes\Components\Database\Exceptions\QueryException;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Arr;

/**
 * Database cache handler.
 */
class DatabaseStore implements Store
{
    use InteractsWithTime;

    /**
     * The database connection instance.
     * 
     * @var \Syscodes\Components\Database\Connections\ConnectionInterface
     */
    protected $connection;

    /**
     * A string that should be prepended to keys.
     * 
     * @var string
     */
    protected $prefix;

    /**
     * The name of the cache table.
     * 
     * @var string
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
        $this->table = $table;
        $this->prefix = $prefix;
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
        return $this->many([$key])[$key];
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * @param  array  $keys
     *
     * @return array
     */
    public function many(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $results = array_fill_keys($keys, null);

        // First we will retrieve all of the items from the cache using their keys and
        // the prefix value.
        $values = $this->table()
            ->whereIn('key', array_map(function ($key) {
                return $this->prefix.$key;
            }, $keys))
            ->get()
            ->map(function ($value) {
                return is_array($value) ? (object) $value : $value;
            });

        $currentTime = $this->currentTime();

        // If this cache expiration date is past the current time, we will remove this
        // item from the cache.
        [$values, $expired] = $values->partition(function ($cache) use ($currentTime) {
            return $cache->expiration > $currentTime;
        });

        return Arr::map($results, function ($value, $key) use ($values) {
            if ($cache = $values->firstWhere('key', $this->prefix.$key)) {
                return $this->unserialize($cache->value);
            }

            return $value;
        });
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
    public function put($key, $value, $seconds): bool
    {
        return $this->putMany([$key => $value], $seconds);
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param  array  $values
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function putMany(array $values, $seconds): bool
    {
        $serializedValues = [];

        $expiration = $this->getTime() + $seconds;

        foreach ($values as $key => $value) {
            $serializedValues[] = [
                'key' => $this->prefix.$key,
                'value' => $this->serialize($value),
                'expiration' => $expiration,
            ];
        }

        return $this->table()->upsert($serializedValues, 'key') > 0;
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
    public function add($key, $value, $seconds): bool
    {
        if ( ! is_null($this->get($key))) return false;

        $key = $this->prefix.$key;
        $value = $this->serialize($value);
        $expiration = $this->getTime() + $seconds;

        if ( ! $this->getConnection() instanceof SqlServerConnection) {
            return $this->table()->insertOrIgnore(['key' => $key, 'value' => $value, 'expiration' => $expiration]) > 0;
        }
        
        try {
            return $this->table()->insert(['key' => $key, 'value' => $value, 'expiration' => $expiration]);
        } catch (QueryException $e) {
            // ...
        }
        return false;
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment($key, $value = 1): int|bool
    {
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current + $value;
        });
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement($key, $value = 1): int|bool
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

            $cache = $this->table()->where('key', $prefixed)->first();
            
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
    public function delete($key): bool
    {
        $this->table()->where('key', '=', $this->prefix.$key)->delete();

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
    public function forever($key, $value): bool
    {
        return $this->put($key, $value, 315360000);
    }

    /**
     * Adjust the expiration time of a cached item.
     *
     * @param  string  $key
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function touch($key, $seconds): bool
    {
        return (bool) $this->table()
            ->where('key', '=', $this->getPrefix().$key)
            ->where('expiration', '>', $now = $this->getTime())
            ->update(['expiration' => $now + $seconds]);
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
     * Set the underlying database connection.
     *
     * @param  \Syscodes\Components\Database\Connections\ConnectionInterface  $connection
     * 
     * @return static
     */
    public function setConnection($connection): static
    {
        $this->connection = $connection;

        return $this;
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
    public function setPrefix($prefix): void
    {
        $this->prefix = $prefix;
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
        
        if (($this->connection instanceof PostgresConnection ||
            $this->connection instanceof SQLiteConnection) &&
            str_contains($result, "\0")) {
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
    protected function unserialize($value)
    {
        if (($this->connection instanceof PostgresConnection ||
            $this->connection instanceof SQLiteConnection) &&
            ! Str::contains($value, [':', ';'])) {
            $value = base64_decode($value);
        }
        
        return unserialize($value);
    }
}