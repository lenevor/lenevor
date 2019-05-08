<?php 

namespace Syscode\Contracts\Cache;

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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */
interface Store
{
    /**
     * Will delete all items in the entire cache.
     * 
     * @return mixed
     */
    public function clean();

    /**
     * Create the file cache directory if necessary.
     * 
     * @param string  $path
     * 
     * @return void
     */
    public function createCacheDirectory($path);

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  int     $value The default value (1)
     * 
     * @return mixed
     */
    public function decrement($key, $value = 1);

    /**
     * Deletes a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function erase($key);

    /**
     * Remove all items from the cache.
     * 
     * @return void
     */
    public function flush();

    /**
     * Attempts to fetch an item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key);
    
    /**
     * Returns information on the entire cache.
     * 
     * @return mixed
     */
    public function getCacheInfo();

    /**
     * Returns detailed information about the specific item in the cache.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function getMetaData($key);

    /**
     * Get the cache key prefix
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  int     $value The default value (1)
     * 
     * @return mixed
     */
    public function increment($key, $value = 1);

    /**
     * Determines if the driver is supported on this system.
     * 
     * @return boolean
     */
    public function isSupported();

    /**
     * Saves an item to the cache store.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $ttl    The default ttl (60
     * 
     * @return mixed
     */
    public function save($key, $value, $ttl = 60);
}