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

use Syscodes\Components\Cache\concerns\CacheMultipleKeys;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\InteractsWithTime;

/**
 * Array cache handler.
 */
class ArrayStore implements Store
{
    use CacheMultipleKeys, InteractsWithTime;

    /**
     * The array storaged value.
     * 
     * @var array
     */
    protected $storage = [];

    /**
     * Indicates if values are serialized within the store.
     * 
     * @var bool
     */
    protected $serialized;

    /**
     * Constructor. Create a new ArrayStore class instance.
     * 
     * @param  bool  $serialized 
     * @return void
     */
    public function __construct($serialized = false)
    {
        $this->serialized = $serialized;
    }

    /**
     * Get all of the cached values and their expiration times.
     *
     * @param  bool  $unserialize 
     * @return array
     */
    public function all($unserialize = true): array
    {
        if ($unserialize === false) {
            return $this->storage;
        }

        $storage = [];

        foreach ($this->storage as $key => $data) {
            $storage[$key] = [
                'value' => unserialize($data['value']),
                'expiresAt' => $data['expiresAt'],
            ];
        }

        return $storage;
    }

    /**
     * Gets an item from the cache by key.
     * 
     * @param  string  $key 
     * @return mixed
     */
    public function get($key)
    {
        if ( ! isset($this->storage[$key])) return;

        $item = $this->storage[$key];

        $expiration = $item['expiration'] ?? 0;

        if ($expiration !== 0 && $this->currentTime() > $expiration) {
            $this->delete($key);

            return;
        }

        return $this->serialized ? unserialize($item['value']) : $item['value'];
    }

    /**
     * Store an item in the cache for a given number of seconds.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  int   $seconds 
     * @return bool
     */
    public function put($key, $value, $seconds): bool
    {
        $this->storage[$key] = [
            'value' => $this->serialized ? serialize($value) : $value,
            'expiration' => $this->calculateExpiration($seconds)
        ];

        return true;
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value 
     * @return int|bool
     */
    public function increment($key, $value = 1): int|bool
    {
        if ( ! is_null($existing = $this->get($key))) {
            return take(((int) $existing) + $value, function ($incremented) use ($key) {
                $value = $this->serialized ? serialize($incremented) : $incremented;
                
                $this->storage[$key]['value'] = $value;
            });
        }
        
        $this->forever($key, $value);
        
        return $value;
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value 
     * @return int|bool
     */
    public function decrement($key, $value = 1): int|bool
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Deletes a specific item from the cache store.
     * 
     * @param  string  $key 
     * @return bool
     */
    public function delete($key): bool
    {
        if (array_key_exists($key, $this->storage)) {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed  $value 
     * @return bool
     */
    public function forever($key, $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Adjust the expiration time of a cached item.
     *
     * @param  string  $key
     * @param  int  $seconds 
     * @return bool
     */
    public function touch($key, $seconds): bool
    {
        $item = Arr::get($this->storage, $key = $this->getPrefix().$key, null);

        if (is_null($item)) {
            return false;
        }

        $item['expiration'] = $this->calculateExpiration($seconds);

        $this->storage = array_merge($this->storage, [$key => $item]);

        return true;
    }


    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        $this->storage = [];

        return true;
    }

    /**
     * Gets the cache key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return '';
    }

    /**
     * Gets the expiration time of the key.
     * 
     * @param  int  $seconds
     * @return int
     */
    protected function calculateExpiration(int $seconds): int
    {
        return $this->toTimestamp($seconds);
    }

    /**
     * Gets the UNIX timestamp for the given number of seconds.
     * 
     * @param  int  $seconds 
     * @return int
     */
    protected function toTimestamp(int $seconds): int
    {
        return $seconds > 0 ? $this->availableAt($seconds) : 0;
    }
}