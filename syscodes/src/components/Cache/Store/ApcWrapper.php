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

/**
 * ApcWrapper cache handler.
 */
class ApcWrapper
{
    /**
     * Get an item from the cache.
     * 
     * @param  string  $key 
     * @return mixed
     */
    public function get($key)
    {
        $fetchedValue = apcu_fetch($key, $success);

        return $success ? $fetchedValue : null;
    }

    /**
     * Store an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds 
     * @return bool
     */
    public function put($key, $value, $seconds): bool
    {
        return apcu_store($key, $value, $seconds);
    }

    /**
     * Increment the value of an time in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value 
     * @return int|bool
     */
    public function increment($key, $value): int|bool
    {
        return apcu_inc($key, $value);
    }

    /**
     * Decrement the value of an time in the cache.
     * 
     * @param  string  $key
     * @param  mixed  $value 
     * @return int|bool
     */
    public function decrement($key, $value): int|bool
    {
        return apcu_dec($key, $value);
    }

    /**
     * Remove an item in the cache.
     * 
     * @param  string  $key 
     * @return bool
     */
    public function delete(string $key): bool
    {
        return apcu_delete($key);
    }

    /**
     * Remove all items in the cache.
     * 
     * @return bool
     */
    public function flush(): bool
    {
        return apcu_clear_cache();
    }
}