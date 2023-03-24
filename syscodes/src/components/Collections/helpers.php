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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

use Closure;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\HigherOrderTakeProxy;

if ( ! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     * 
     * @param  mixed  $value
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    function collect(mixed $value = null)
    {
        return new Collection($value);
    }
}

if ( ! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     * 
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed   $default
     * 
     * @return mixed
     */
    function data_get(mixed $target, string|array $key, mixed $default = null)
    {
        if (is_null($key)) return $target;
        
        $key = is_array($key) ? $key : explode('.', $key);
        
        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return value($default);
                }
                
                $result = Arr::pluck($target, $key);
                
                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }
            
            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        
        return $target;
    }
}

if( ! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     * 
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * 
     * @return mixed
     */
    function data_set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);
        
        if (($segment = array_shift($segments)) === '*') {
            if ( ! Arr::accessible($target)) {
                $target = [];
            }
            
            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if ( ! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }
                
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if ( ! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }
                
                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];
            
            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }
        
        return $target;
    }
}

if ( ! function_exists('headItem')) {
    /**
     * Get the actual element of an array. Useful for method chaining.
     *
     * @param  array  $array
     * @param  bool  $bool
     *
     * @return mixed
     */
    function headItem(array $array, bool $bool = false)
    {
        return $bool ? reset($array) : current($array);
    }
}

if ( ! function_exists('lastItem')) {
    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     *
     * @return mixed
     */
    function lastItem(array $array)
    {
        return end($array);
    }
}

if ( ! function_exists('take')) {
    /**
     * Call the given Closure if this activated then return the value.
     * 
     * @param  string  $value
     * @param  \Closure|null  $callback
     * 
     * @return mixed
     * 
     * @uses   \Syscodes\Components\Support\HigherOrderTakeProxy
     */
    function take(string $value, Closure $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTakeProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if ( ! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * 
     * @return mixed
     */
    function value(mixed $value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}