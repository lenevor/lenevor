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

use Closure;
use Syscodes\Collections\Arr;
use Syscodes\Collections\Collection;
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

if ( ! function_exists('array_divide'))
{
    /**
     * Divide an array into two arrays. 
     * One with keys and the other with values.
     *
     * @param  array  $array
     *
     * @return array
     *
     * @uses   Arr::divide
     */
    function array_divide($array)
    {
        return Arr::divide($array);
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

if ( ! function_exists('array_fetch'))
{
    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param  array  $array
     * @param  mixed  $key
     *
     * @return mixed
     *
     * @uses   Arr::fetch
     */
    function array_fetch($array, $key)
    {
        return Arr::fetch($array, $key);
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

if ( ! function_exists('array_flatten'))
{
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     *
     * @return array
     *
     * @uses   Arr::flatten
     */
    function array_flatten($array)
    {
        return Arr::flatten($array);
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

if ( ! function_exists('array_has'))
{
    /**
	 * Check if an item exists in an array using "dot" notation.
	 * 
	 * @param  array  $array
	 * @param  string  $key
	 * 
	 * @return bool
	 */
    function array_has($array, $key)
    {
        return Arr::has($array, $key);
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
}

if ( ! function_exists('array_pull'))
{
    /**
	 * Get a value from the array, and remove it.
	 * 
	 * @param  array  $array
	 * @param  string  $key
	 * @param  mixed  $default  (null by default)
	 * 
	 * @return mixed
     * 
     * @uses   Arr::pull
	 */
	function array_pull(&$array, $key, $default = null)
	{
		return Arr::pull($array, $key, $default);
	}
}

if ( ! function_exists('array_where'))
{
    /**
	 * Filter the array using the given Closure.
	 * 
	 * @param  array  $array
	 * @param  \Closure  $callback
	 * 
	 * @return array
     * 
     * @uses   Arr::where
	 */
	function array_where($array, Closure $callback)
	{
		return Arr::where($array, $callback);
	}
}

if ( ! function_exists('collect'))
{
    /**
     * Create a collection from the given value.
     * 
     * @param  mixed  $value  (null by default)
     * 
     * @return \Syscodes\Collections\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if ( ! function_exists('head'))
{
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array  $array
     *
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}

if ( ! function_exists('last'))
{
    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     *
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
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

if ( ! function_exists('value')) 
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * 
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
