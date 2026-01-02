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
use Syscodes\Components\Support\InteractsWithTime;

/**
 * Array cache handler.
 */
class ArrayStore implements Store
{
    use CacheMultipleKeys,
        InteractsWithTime;

    /**
     * The array storaged value.
     * 
     * @var array $storage
     */
    protected $storage = [];

    /**
     * Indicates if values are serialized within the store.
     * 
     * @var bool $serialized
     */
    protected $serialized;

    /**
     * Constructor. Create a new ArrayStore class instance.
     * 
     * @param  bool  $serialized
     * 
     * @return void
     */
    public function __construct($serialized = false)
    {
        $this->serialized = $serialized;
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
     * @param  int     $seconds
     * 
     * @return bool
     */
    public function put(string $key, mixed $value, int $seconds): bool
    {
        $this->storage[$key] = [
            'value' => $this->serialized ? serialize($value) : $value,
            'expiration' => $this->calcExpiration($seconds)
        ];

        return true;
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
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value = 1): int|bool
    {
        return $this->increment($key, $value * -1);
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
     * @param  mixed   $value
     * 
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
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