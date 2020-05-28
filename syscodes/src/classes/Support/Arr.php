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
 * @since       0.1.0
 */

namespace Syscodes\Support;

use ArrayAccess;
use InvalidArgumentException;
use Syscodes\Core\Http\Lenevor;

/**
 * Gets all a given array for return dot-notated key from an array.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
	 * @param  array  $array  The search array 
	 * @param  string  $key  The key exist
	 * @param  mixed  $value  The default value
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
	 * @param  array  $array
	 * @param  string|array  $keys
	 *
	 * @return array
	 */
	public static function except($array, $keys)
	{
		static::erase($array, $keys);

		return $array;
	}
	
	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param  ArrayAccess|array  $array  The search array
	 * @param  string|int  $key  The key exist
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
	 * @param  mixed  $keys  The dot-notated key or array of keys
	 *
	 * @return mixed
	 */
	public static function erase(&$array, $keys)
	{
		$original = &$array;

		$keys = (array) $keys;

		if (count($keys) === 0) 
		{
			return;
		}

		foreach ($keys as $key)
		{
			if (static::exists($array, $key))
			{
				unset($array[$key]);

				continue;
			}
			
			$parts = explode('.', $key);

			// Clean up after each pass
			$array = &$original;
	
			// traverse the array into the second last key
			while (count($parts) > 1) 
			{
				$part = array_shift($parts);
	
				if (isset($array[$part]) && is_array($array[$part])) 
				{
					$array = &$array[$key];
				}
				else
				{
					continue 2;
				}
			}

			unset($array[array_shift($parts)]);
		}
	}

	/**
	 * Flatten a multi-dimensional array into a single level.
	 * 
	 * @param  array  $array
	 * 
	 * @return array
	 */
	public static function flatten($array)
	{
		$result = [];

		array_walk_recursive($array, function ($value) use (&$result) {
			$result[] = $value;
		});

		return $result;
	}
	
	/**
	 * Fetch a flattened array of a nested array element.
	 * 
	 * @param  array  $array
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
	 * @param  array  $array 
	 * @param  \Closure  $callback
	 * @param  mixed  $default  (null by default)
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
	 * @param  \ArrayAccess|array  $array  The search array
	 * @param  string  $key  The dot-notated key or array of keys
	 * @param  mixed  $default  The default value
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
			if (static::access($array) && static::exists($array, $segm))
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
	 * @param  array  $array 
	 * @param  \Closure  $callback
	 * @param  mixed  $default 
	 *
	 * @return mixed
	 *
	 * @uses   \Syscodes\Support\Arr::first
	 */
	public static function last($array, $callback, $default = null)
	{
		return static::first(array_reverse($array), $callback, $default);
	}

	/**
	 * Check if an item exists in an array using "dot" notation.
	 * 
	 * @param  array  $array
	 * @param  string  $key
	 * 
	 * @return bool
	 */
	public static function has($array, $key)
	{
		if (empty($array) || is_null($key)) return false;
		
		if (array_key_exists($key, $array)) return true;
		
		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! static::exists($array, $segment))
			{
				return false;
			}
			
			$array = $array[$segment];
		}
		
		return true;
	}

	/**
	 * Sets a value in an array using "dot" notation.
	 *
	 * @param  array  $array  The search array
	 * @param  string  $key  The dot-notated key or array of keys
	 * @param  mixed  $value  The default value
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

	/**
	 * Get a value from the array, and remove it.
	 * 
	 * @param  array  $array
	 * @param  string  $key
	 * @param  mixed  $default  (null by default)
	 * 
	 * @return mixed
	 */
	public static function pull(&$array, $key, $default = null)
	{
		$value = static::get($array, $key, $default);

		static::erase($array, $key);

		return $value;
	}

	/**
	 * Convert the array into a query string.
	 * 
	 * @param  array  $array
	 * 
	 * @return array
	 */
	public static function query($array)
	{
		return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Filter the array using the given callback.
	 * 
	 * @param  array  $array
	 * @param  \Callable  $callback
	 * 
	 * @return array
	 */
	public static function where($array, Callable $callback)
	{
		return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 * 
	 * @param  mixed  $value
	 * 
	 * @return array
	 */
	public static function wrap($value)
	{
		if (is_null($value))
		{
			return [];
		}

		return is_array($value) ? $value : [$value];
	}
}