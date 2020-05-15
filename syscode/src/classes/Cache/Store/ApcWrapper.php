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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscode\Cache\Store;

/**
 * ApcWrapper cache handler.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class ApcWrapper
{
    /**
     * Indeicates if APCu is supported.
     * 
     * @var bool $apcu
     */
    protected $acpu = false;

    /**
     * Constructor. The ApcWrapper class instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->acpu = function_exists('apcu_fetch');
    }

    /**
     * Get an item from the cache.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key)
    {
        return $this->acpu ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * Store an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     * 
     * @return array|bool
     */
    public function put($key, $value, $seconds)
    {
        return $this->apcu ? apcu_fetch($key, $value, $seconds) : apc_fetch($key, $value, $seconds);
    }

    /**
     * Increment the value of an time in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function increment($key, $value)
    {
        return $this->apcu ? apcu_inc($key, $value) : apc_inc($key, $value);
    }

    /**
     * Decrement the value of an time in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return int|bool
     */
    public function decrement($key, $value)
    {
        return $this->apcu ? apcu_dec($key, $value) : apc_dec($key, $value);
    }

    /**
     * Remove an item in the cache.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function delete($key)
    {
        return $this->acpu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * Remove all items in the cache.
     * 
     * @return bool
     */
    public function flush()
    {
        return $this->acpu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}