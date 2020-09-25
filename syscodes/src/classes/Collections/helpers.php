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
 * @since       0.7.2
 */

use Syscodes\Collections\Arr;
use Syscodes\Collections\HigherOrderTakeProxy;

if ( ! function_exists('array_add'))
{
    /**
     * Unsets dot-notated key from an array.
     *
     * @param  array  $array  
     * @param  string  $key  
     * @param  mixed  $value 
     *
     * @return array
     *
     * @uses   Arr::add
     */
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if ( ! function_exists('array_except'))
{
    /**
     * Get all of the given array except for a specified array of items.
     * 
     * @param  array  $array
     * @param  string|array  $key
     * 
     * @return array
     * 
     * @uses   Arr::except
     */
    function array_except($array, $key)
    {
        return Arr::except($array, $key);
    }
}

if ( ! function_exists('array_erase'))
{
    /**
     * Unsets dot-notated key from an array.
     *
     * @param  array  $array
     * @param  mixed  $key
     *
     * @return mixed
     *
     * @uses   Arr::erase
     */
    function array_erase(&$array, $key)
    {
        return Arr::erase($array, $key);
    }
}

if ( ! function_exists('array_first'))
{
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array  $array 
     * @param  \Closure  $callback
     * @param  mixed  $default
     *
     * @return mixed
     *
     * @uses   Arr::first
     */
    function array_first($array, $callback, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if ( ! function_exists('array_get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return mixed
     * 
     * @uses   Arr::get
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if ( ! function_exists('array_last'))
{
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array  $array 
     * @param  \Closure  $callback
     * @param  mixed  $default 
     *
     * @return mixed
     *
     * @uses   Arr::last
     */
    function array_last($array, $callback, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if ( ! function_exists('array_set'))
{
    /**
     * Sets a value in an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return mixed
     *
     * @uses   Arr::set
     */
    function array_set(& $array, $key, $default = null)
    {
        return Arr::set($array, $key, $default);
    }

    if ( ! function_exists('take'))
    {
        /**
         * Call the given Closure if this activated then return the value.
         * 
         * @param  string  $value
         * @param  \Closure|null  $callback
         * 
         * @return mixed
         * 
         * @uses   \Syscodes\Collections\HigherOrderTakeProxy
         */
        function take($value, $callback = null)
        {
            if (is_null($callback))
            {
                return new HigherOrderTakeProxy($value);
            }

            $callback($value);

            return $value;
        }
    }
}
