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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

use ArrayAccess;
use Traversable;
use JsonSerializable;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Collection\Enumerable;

/**
 * Gets all a given array for return dot-notated key from an array.
 */
class Arr
{
	/**
	 * Determine whether the value is accessible in a array.
	 *
	 * @param  mixed  $value The default value
	 *
	 * @return mixed
	 *
	 * @uses   instanceof ArrayAccess
	 */
	public static function accessible(mixed $value): mixed
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
	public static function add(array $array, string $key, mixed $value): array
	{
		if (is_null(static::get($array, $key))) {
			static::set($array, $key, $value);
		}

		return $array;
	}

	/**
     * Collapse the collection items into a single array.
	 * 
	 * @param  array  $array
     * 
     * @return array
     */
    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
			if ($values instanceof Collection) {
				$values = $values->all();
			} elseif ( ! is_array($values)) {
				continue;
			}

			$results[] = $values;
        }

        return array_merge([], ...$results);
    }

	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 *
	 * @return array
	 */
	public static function divide(array $array): array
	{
		return [array_keys($array), array_values($array)];
	}
	
	/**
	 * Flatten a multi-dimensional associative array with dots.
	 * 
	 * @param  iterable  $array
	 * @param  string  $prepend
	 * 
	 * @return array
	 */
	public static function dot($array, $prepend = ''): array
	{
		$results = [];
		
		foreach ($array as $key => $value) {
			if (is_array($value) && ! empty($value)) {
				$results = array_merge($results, static::dot($value, $prepend.$key.'.'));
			} else {
				$results[$prepend.$key] = $value;
			}
		}
		
		return $results;
	}

	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array  $array
	 * @param  string|array  $keys
	 *
	 * @return array
	 */
	public static function except(array $array, string|array $keys): array
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
	 */
	public static function exists($array, $key): bool
	{
		if ($array instanceof ArrayAccess) {
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
	public static function erase(array &$array, mixed $keys)
	{
		$original = &$array;

		$keys = (array) $keys;

		if (count($keys) === 0) {
			return;
		}

		foreach ($keys as $key) {
			if (static::exists($array, $key)) {
				unset($array[$key]);

				continue;
			}
			
			$parts = explode('.', $key);

			// Clean up after each pass
			$array = &$original;
	
			// traverse the array into the second last key
			while (count($parts) > 1) {
				$part = array_shift($parts);
	
				if (isset($array[$part]) && is_array($array[$part])) {
					$array = &$array[$key];
				} else {
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
	public static function flatten(array $array): array
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
	public static function fetch(array $array, string $key): array
	{
		$segments = explode('.', $key);
		
		foreach ($segments as $segment) {
			$results = array();
			
			foreach ($array as $value) {
				if (array_key_exists($segment, $value = (array) $value)) {
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
	 * @param  \callable|null  $callback
	 * @param  mixed  $default
	 *
	 * @return mixed
	 */
	public static function first(array $array, ?callable $callback = null, mixed $default = null)
	{
		if (is_null($callback)) {
			if (empty($array)) {
				return value($default);
			}
			
			foreach ($array as $item) {
				return $item;
			}
		}
		
		foreach ($array as $key => $value) { 
			if ($callback($value, $key)) return $value;
		}

		return value($default);
	}
	
	/**
	 * Get the underlying array of items from the given argument.
	 * 
	 * @param  array|iterable  $items
	 * 
	 * @return mixed
	 * 
	 * @throws \InvalidArgumentException
	 */
	public static function from($items)
	{
		return match (true) {
			is_array($items) => $items,
			$items instanceof Enumerable => $items->all(),
			$items instanceof Arrayable => $items->toArray(),
			$items instanceof Traversable => iterator_to_array($items),
			$items instanceof Jsonable => json_decode($items->toJson(), true),
			$items instanceof JsonSerializable => (array) $items->jsonSerialize(),
			is_object($items) => (array) $items,
			default => throw new InvalidArgumentException('Items cannot be represented by a scalar value.'),
		};
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  \ArrayAccess|array  $array  The search array
	 * @param  string|array|null  $key  The dot-notated key or array of keys or null
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public static function get($array, $key = null, mixed $default = null)
	{
		if ( ! static::accessible($array)) {
			return value($default);
		}

		if (is_null($key)) {
			return $array;
		}

		if (static::exists($array, $key)) {
			return $array[$key];
		}
		
		if ( ! str_contains($key, '.')) {
			return $array[$key] ?? value($default);
		}

		$segments = explode('.', $key);

		foreach ($segments as $segment) {
			if (static::accessible($array) && static::exists($array, $segment)) {
				$array = $array[$segment];
			} else {
				return value($default);
			}
		}

		return $array;		
	}

	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array  $array 
	 * @param  \callable|null  $callback
	 * @param  mixed  $default 
	 *
	 * @return mixed
	 */
	public static function last(array $array, ?callable $callback = null, mixed $default = null)
	{
		if (is_null($callback)) {
			return empty($array) ? value($default) : last($array);
		}
		
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
	public static function has(array $array, string $key): bool
	{
		if (empty($array) || is_null($key)) return false;
		
		if (static::exists($array, $key)) return true;

		$segments = explode('.', $key);
		
		foreach ($segments as $segment) {
			if ( ! is_array($array) || ! static::exists($array, $segment)) {
				return false;
			}
			
			$array = $array[$segment];
		}
		
		return true;
	}

	/**
	 * Gets max width of an array.
	 * 
	 * @param  array  $data
	 * @param  bool  $exclude
	 * 
	 * @return int
	 */
	public static function getMaxWidth(array $data, bool $exclude = true): int
	{
		$maxWidth = 0;
		
		foreach ($data as $key => $value) {
			// key is not a integer
			if ( ! $exclude || ! is_numeric($key)) {
				$width    = mb_strlen((string) $key, 'UTF-8');
				$maxWidth = $width > $maxWidth ? $width : $maxWidth;
			}
		}
		
		return $maxWidth;
	}
	
	/**
	 * Run a map over each of the items in the array.
	 * 
	 * @param  array  $array
	 * @param  callable  $callback
	 * 
	 * @return array
	 */
	public static function map(array $array, callable $callback): array
	{
		$keys = array_keys($array);
		
		try {
			$items = array_map($callback, $array, $keys);
		} catch (InvalidArgumentException) {
			$items = array_map($callback, $array);
		}
		
		return array_combine($keys, $items);
	}

	/**
	 * Get a subset of the items from the given array.
	 * 
	 * @param  array  $array
	 * @param  array|string  $keys
	 * 
	 * @return array
	 */
	public static function only(array $array, array|string $keys): array
	{
		return array_intersect_key($array, array_flip((array) $keys));
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
	public static function set(array &$array, string $key, mixed $value = null): mixed
	{
		if (is_null($key)) {
			return $array = $value;
		}

		$keys = explode('.', $key);

		while (count($keys) > 1) {
			$key = array_shift($keys);

			if ( ! static::exists($array, $key)) {
				$array[$key] = [];
			}

			$array = &$array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}
	
	/**
	 * Partition the array into two arrays using the given callback.
	 * 
	 * @template TKey of array-key
	 * @template TValue of mixed
	 * 
	 * @param  array  $array
	 * @param  callable  $callback
	 * 
	 * @return mixed
	 */
	public static function partition($array, callable $callback)
	{
		$passed = [];
		$failed = [];
		
		foreach ($array as $key => $item) {
			if ($callback($item, $key)) {
				$passed[$key] = $item;
			} else {
				$failed[$key] = $item;
			}
		}
		
		return [$passed, $failed];
	}

	/**
	 * Push an item onto the beginning of an array.
	 * 
	 * @param  mixed  $array
	 * @param  mixed  $value
	 * @param  mixed  key
	 * 
	 * @return array
	 */
	public static function prepend(mixed $array, mixed $value, mixed $key = null): array
	{
		if (func_num_args() == 2) {
			array_unshift($array, $value);
		} else {
			$array = [$key => $value] + $array;
		}

		return $array;
	}

	/**
	 * Get a value from the array, and remove it.
	 * 
	 * @param  array  $array
	 * @param  string  $key
	 * @param  mixed  $default
	 * 
	 * @return mixed
	 */
	public static function pull(array &$array, string $key, mixed $default = null): mixed
	{
		$value = static::get($array, $key, $default);

		static::erase($array, $key);

		return $value;
	}
	
	/**
	 * Pluck an array of values from an array.
	 * 
	 * @param  \iterable  $array
	 * @param  string|array|int|null  $value
	 * @param  string|array|null  $key
	 * 
	 * @return array
	 */
	public static function pluck($array, $value, $key = null): array
	{
		$results = [];

		foreach ($array as $item) {
			$itemValue = is_object($item) ? $item->{$value} : $item[$value];
			
			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if (is_null($key)) {
				$results[] = $itemValue;
			} else {
				$itemKey = is_object($item) ? $item->{$key} : $item[$key];
				
				$results[$itemKey] = $itemValue;
			}
		}
		
		return $results;
	}

	/**
	 * Convert the array into a query string.
	 * 
	 * @param  array  $array
	 * 
	 * @return string
	 */
	public static function query(array $array): string
	{
		return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
	}
	
	/**
	 * Sort the array using the given callback or "dot" notation.
	 * 
	 * @param  array  $array
	 * @param  callable|array|string|null  $callback
	 * 
	 * @return array
	 */
	public static function sort($array, $callback = null): array
	{
		return Collection::make($array)->sortBy($callback)->all();
	}
	
	/**
	 * Convert a flatten "dot" notation array into an expanded array.
	 * 
	 * @param  iterable  $array
	 * 
	 * @return mixed
	 */
	public static function undot($array): mixed
	{
		$results = [];
		
		foreach ($array as $key => $value) {
			static::set($results, $key, $value);
		}
		
		return $results;
	}

	/**
	 * Filter the array using the given callback.
	 * 
	 * @param  array  $array
	 * @param  \Callable  $callback
	 * 
	 * @return array
	 */
	public static function where(array $array, Callable $callback): array
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
	public static function wrap(mixed $value): array
	{
		if (is_null($value)) {
			return [];
		}

		return is_array($value) ? $value : [$value];
	}
}