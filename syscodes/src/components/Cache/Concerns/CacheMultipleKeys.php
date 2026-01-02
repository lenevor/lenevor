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

namespace Syscodes\Components\Cache\concerns;

/**
 * Stores multiple items in the cache using an array.
 */
trait CacheMultipleKeys
{
    /**
     * Gets multiple items from the cache by key.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function many(array $keys): array
    {
        $return = [];
        
        $keys = collect($keys)->mapKeys(function ($value, $key) {
            return [is_string($key) ? $key : $value => is_string($key) ? $value : null];
        })->all();
        
        foreach ($keys as $key => $default) {
            $return[$key] = $this->get($key, $default);
        }
        
        return $return;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     * 
     * @param  array $values
     * @param  int  $seconds
     * 
     * @return bool
     */
    public function putMany(array $values, $seconds): bool
    {
        $result = null;

        foreach ($values as $key => $value) {
            $result = $this->put($key, $value, $seconds);
        }

        return $result;
    }
}