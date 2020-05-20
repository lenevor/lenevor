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

namespace Syscodes\Contracts\Cache;

/**
 * Sets functions by the item from the cache store.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface Store
{
    /**
     * Gets an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key);

    /**
     * Store an item in the cache for a given number of seconds.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * 
     * @return bool
     */
    public function put($key, $value, $seconds);

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value  (1 by default)
     * 
     * @return int|bool
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value  (1 by default)
     * 
     * @return int|bool
     */
    public function decrement($key, $value = 1);

    /**
     * Deletes a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function delete($key);

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return bool
     */
    public function forever($key, $value);

    /**
     * Remove all items from the cache.
     * 
     * @return void
     */
    public function flush();
    
    /**
     * Gets the cache key prefix
     *
     * @return string
     */
    public function getPrefix();
}