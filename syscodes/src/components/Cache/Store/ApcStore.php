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

use Syscodes\Contracts\Cache\Store;

/**
 * Apc cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ApcStore implements Store
{
    /**
     * The APC wrapper instance.
     * 
     * @var \Syscodes\Cache\Store\ApcWrapper $apc
     */
    protected $apc;

    /**
     * A string that should be prepended to keys.
     * 
     * @var string $prefix
     */
    protected $prefix;

    /**
     * Constructor. The new APC store instance.
     * 
     * @param  \Syscodes\Cache\Store\ApcWrapper  $apc
     * @param  string  $prefix
     * 
     * @return void
     */
    public function __construct(ApcWrapper $apc, $prefix = '')
    {
        $this->apc    = $apc;
        $this->prefix = $prefix;
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
        $value = $this->apc->get($this->prefix.$key);

        if (false !== $value)
        {
            return $value;
        }
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
        return $this->apc->put($this->prefix.$key, $value, $seconds);
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value  (1 by default)
     * 
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->apc->increment($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value  (1 by default)
     * 
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->apc->decrement($this->prefix.$key, $value);
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
        return $this->apc->delete($this->prefix.$key);
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
        return $this->put($key, $value, 0);
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush()
    {
        return $this->apc->flush();
    }

    /**
     * Gets the cache key prefix.
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}