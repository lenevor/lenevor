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

use Syscodes\Components\Cache\concerns\CacheMultipleKeys;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Support\InteractsWithTime;

/**
 * Session cache handler.
 */
class SessionStore implements Store
{
    use InteractsWithTime,
        CacheMultipleKeys;

    /**
     * The key for cache items.
     *
     * @var string
     */
    public $key;

    /**
     * The session instance.
     *
     * @var \Syscodes\Components\Contracts\Session\Session
     */
    public $session;

    /**
     * Constructor. Create a new session cache store.
     *
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * @param  string  $key
     * 
     * @return void
     */
    public function __construct($session, $key = '_cache')
    {
        $this->key = $key;
        $this->session = $session;
    }

    /**
     * Get all of the cached values and their expiration times.
     *
     * @return array<string, array{value: mixed, expiresAt: float}>
     */
    public function all()
    {
        return $this->session->get($this->key, []);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key)
    {
        if ( ! $this->session->exists($this->itemKey($key))) {
            return;
        }

        $item = $this->session->get($this->itemKey($key));

        $expiresAt = $item['expiresAt'] ?? 0;

        if ($this->isExpired($expiresAt)) {
            $this->delete($key);

            return;
        }

        return $item['value'];
    }

    /**
     * Determine if the given expiration time is expired.
     *
     * @param  int|float  $expiresAt
     * @return bool
     */
    protected function isExpired($expiresAt)
    {
        return $expiresAt !== 0 && (Chronos::now()->getPreciseTimestamp(3) / 1000) >= $expiresAt;
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
        $this->session->put($this->itemKey($key), [
            'value' => $value,
            'expiresAt' => $this->toTimestamp($seconds),
        ]);

        return true;
    }

    /**
     * Get the UNIX timestamp, with milliseconds, for the given number of seconds in the future.
     *
     * @param  int  $seconds
     * @return float
     */
    protected function toTimestamp($seconds)
    {
        return $seconds > 0 ? (Chronos::now()->getPreciseTimestamp(3) / 1000) + $seconds : 0;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int
     */
    public function increment($key, $value = 1): int
    {
        if ( ! is_null($existing = $this->get($key))) {
            return take(((int) $existing) + $value, function ($incremented) use ($key) {
                $this->session->put($this->itemKey("{$key}.value"), $incremented);
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
     * 
     * @return int
     */
    public function decrement($key, $value = 1): int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function forever($key, $value): bool
    {
        return $this->put($key, $value, 0);
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
        if ($this->session->exists($this->itemKey($key))) {
            $this->session->erase($this->itemKey($key));

            return true;
        }

        return false;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush(): bool
    {
        $this->session->put($this->key, []);

        return true;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function itemKey($key): string
    {
        return "{$this->key}.{$key}";
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return '';
    }
}