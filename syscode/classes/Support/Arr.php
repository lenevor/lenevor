<?php 

namespace Syscode\Support;

use ArrayAccess;
use InvalidArgumentException;
use Syscode\Core\Http\Lenevor;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */
class Arr
{
	/**
	 * Determine whether the value is accessible in a array.
	 *
	 * @param  mixed  $value The default value
	 *
	 * @return bool
	 *
	 * @uses   instanceof ArrayAccess
	 */
	public static function access($value) 
	{
		return is_array($value) || $value instanceof ArrayAccess;
	}

	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param  array   $array  The search array 
	 * @param  string  $key    The key exist
	 * @param  mixed   $value  The default value
	 *
	 * @return array 
	 */
	public static function add($array, $key, $value)
	{
		if (is_null(static::get($array, $key)))
		{
			static::set($array, $key, $value);
		}

		return $array;
	}

	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 *
	 * @return array
	 */
	public static function divide($array)
	{
		return [array_keys($array), array_values($array)];
	}

	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array         $array
	 * @param  string|array  $key
	 *
	 * @return array
	 */
	public static function except($array, $key)
	{
		return array_diff_key($array, array_flip((array) $key));
	}
	
	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param  ArrayAccess|array  $array  The search array
	 * @param  string|int         $key    The key exist
	 *
	 * @return bool
	 *
	 * @uses   instaceof ArrayAccess
	 */
	public static function exists($array, $key) 
	{
		if ($array instanceof ArrayAccess) 
		{
			return $array->offsetExists($key);
		}
		
		return array_key_exists($key, $array);
	}

	/**
	 * Unsets dot-notated key from an array.
	 *
	 * @param  array  $array  The search array
	 * @param  mixed  $key    The dot-notated key or array of keys
	 *
	 * @return mixed
	 */
	public static function erase(&$array, $key)
	{
		if (is_null($key)) 
		{
			return false;
		}

		$keys = explode('.', $key);

		// traverse the array into the second last key
		while (count($keys) > 1) 
		{
			$key = array_shift($keys);

			if (array_key_exists($key, $array)) {
				$array =& $array[$key];
			}
		}

		// if the last key exists unset it
		if (array_key_exists($key = array_shift($keys), $array)) {
			unset($array[$key]);
		}

		return true;
	}

	/**
     * Fetch a flattened array of a nested array element.
     *
	 * @param  array   $array
     * @param  string  $key
     *
     * @return array
     */
    public static function fetch($array, $key)
    {
        foreach (explode('.', $key) as $segment)
        {
            $results = array();

            foreach ($array as $value)
            {
                if (array_key_exists($segment, $value = (array) $value))
                {
                    $results[] = $value[$segment];
                }
            }

            $array = array_values($results);
        }

        return array_values($results);
    }

	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * @param  array     $array 
	 * @param  \Closure  $callback
	 * @param  mixed     $default
	 *
	 * @return mixed
	 */
	public static function first($array, $callback, $default = null)
	{
		foreach ($array as $key => $value)
		{ 
			if (call_user_func($callback, $key, $value)) return $value;
		}

		return value($default);
	}	

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  ArrayAccess|array  $array    The search array
	 * @param  string             $key      The dot-notated key or array of keys
	 * @param  mixed              $default  The default value
	 *
	 * @return mixed
	 */
	public static function get($array, $key, $default = null)
	{
		if ( ! static::access($array))
		{
			return value($default);
		}

		if (static::exists($array, $key)) 
		{
			return $array[$key];
		}

		foreach (explode('.', $key) as $segm)
		{
			if ( static::access($array) && static::exists($array, $segm))
			{
				$array = $array[$segm];
			}
			else
			{
				return value($default);
			}
		}

		return $array;		
	}

	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array     $array 
	 * @param  \Closure  $callback
	 * @param  mixed     $default 
	 *
	 * @return mixed
	 *
	 * @uses   \Syscode\Support\Arr::first
	 */
	public static function last($array, $callback, $default = null)
	{
		return static::first(array_reverse($array), $callback, $default);
	}

	/**
	 * Sets a value in an array using "dot" notation.
	 *
	 * @param  array   $array  The search array
	 * @param  string  $key    The dot-notated key or array of keys
	 * @param  mixed   $value  The default value
	 *
	 * @return mixed
	 */
	public static function set(& $array, $key, $value = null)
	{
		$keys = explode('.', $key);

		while (count($keys) > 1)
		{
			$key = array_shift($keys);

			if ( ! static::exists($array, $key))
			{
				$array[$key] = [];
			}

			$array =& $array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}
}