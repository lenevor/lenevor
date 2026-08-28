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

namespace Syscodes\Components\Support;

use ArgumentCountError;
use ArrayAccess;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Collectable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Support\Traits\Macroable;
use Traversable;

/**
 * Gets all a given array for return dot-notated key from an array.
 */
class Arr
{
	use Macroable;
	
	/**
	 * Determine whether the value is accessible in a array.
	 *
	 * @param  mixed  $value The default value
	 * @return bool
	 */
	public static function accessible($value)
	{
		return is_array($value) || $value instanceof ArrayAccess;
	}

	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param  array  $array  The search array 
	 * @param  string  $key  The key exist
	 * @param  mixed  $value  The default value
	 * @return array 
	 */
	public static function add($array, $key, $value)
	{
		if (is_null(static::get($array, $key))) {
			static::set($array, $key, $value);
		}

		return $array;
	}

	/**
     * Determine whether the given value is arrayable.
     *
     * @param  mixed  $value
     * @return ($value is array
     *     ? true
     *     : ($value is \Syscodes\Components\Contracts\Support\Arrayable
     *         ? true
     *         : ($value is \Traversable
     *             ? true
     *             : ($value is \Syscodes\Components\Contracts\Support\Jsonable
     *                 ? true
     *                 : ($value is \JsonSerializable ? true : false)
     *             )
     *         )
     *     )
     * )
     */
    public static function arrayable($value)
    {
        return is_array($value)
            || $value instanceof Arrayable
            || $value instanceof Traversable
            || $value instanceof Jsonable
            || $value instanceof JsonSerializable;
    }

	/**
     * Get an array item from an array using "dot" notation.
     *
     * @throws \InvalidArgumentException
     */
    public static function array(ArrayAccess|array $array, string|int|null $key, ?array $default = null): array
    {
        $value = Arr::get($array, $key, $default);

        if ( ! is_array($value)) {
            throw new InvalidArgumentException(
                sprintf('Array value for key [%s] must be an array, %s found.', $key, gettype($value))
            );
        }

        return $value;
    }

	/**
     * Get a boolean item from an array using "dot" notation.
     *
     * @throws \InvalidArgumentException
     */
    public static function boolean(ArrayAccess|array $array, string|int|null $key, ?bool $default = null): bool
    {
        $value = Arr::get($array, $key, $default);

        if ( ! is_bool($value)) {
            throw new InvalidArgumentException(
                sprintf('Array value for key [%s] must be a boolean, %s found.', $key, gettype($value))
            );
        }

        return $value;
    }

	/**
     * Collapse the collection items into a single array.
	 * 
	 * @param  array  $array 
     * @return array
     */
    public static function collapse($array): array
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
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param  iterable  ...$arrays
     * @return array
     */
    public static function crossJoin(...$arrays)
    {
        $results = [[]];

        foreach ($arrays as $index => $array) {
            $append = [];

            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;

                    $append[] = $product;
                }
            }

            $results = $append;
        }

        return $results;
    }

	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	public static function divide($array): array
	{
		return [array_keys($array), array_values($array)];
	}
	
	/**
	 * Flatten a multi-dimensional associative array with dots.
	 * 
	 * @param  iterable  $array
	 * @param  string  $prepend
	 * @param  int  $depth
	 * @return array
	 */
	public static function dot($array, $prepend = '', $depth = INF): array
	{
		$results = [];
		
		$flatten = function ($data, $prefix, $current) use (&$results, &$flatten, $depth): void {
            foreach ($data as $key => $value) {
                $newKey = $prefix.$key;

                if (is_array($value) && ! empty($value) && $current < $depth) {
                    $flatten($value, $newKey.'.', $current + 1);
                } else {
                    $results[$newKey] = $value;
                }
            }
        };

        $flatten($array, $prepend, 0);

        $flatten = null;
		
		return $results;
	}

	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array  $array
	 * @param  array|string|int|float  $keys
	 * @return array
	 */
	public static function except($array, $keys)
	{
		static::erase($array, $keys);

		return $array;
	}

	/**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param  Closure|array|string  $value
     * @param  string|array|Closure|null  $key 
     * @return array
     */
    protected static function explodePluckParameters($value, $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) || $key instanceof Closure ? $key : explode('.', $key);

        return [$value, $key];
    }
	
	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param  ArrayAccess|array  $array  The search array
	 * @param  string|int|float  $key  The key exist
	 * @return bool
	 */
	public static function exists($array, $key)
	{
		if ($array instanceof Collectable) {
            return $array->has($key);
        }

		if ($array instanceof ArrayAccess) {
			return $array->offsetExists($key);
		}

		if (is_float($key) || is_null($key)) {
            $key = (string) $key;
        }
		
		return array_key_exists($key, $array);
	}

	/**
	 * Unsets dot-notated key from an array.
	 *
	 * @param  array  $array  The search array
	 * @param  array|string|int|float  $keys  The dot-notated key or array of keys
	 * @return void
	 */
	public static function erase(&$array, $keys)
	{
		$original = &$array;

		$keys = (array) $keys;

		if ($keys === []) {
			return;
		}

		foreach ($keys as $key) {
			// clean up before each pass
            $array = &$original;

			// if the exact key exists in the top-level, remove it
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
	
				if (isset($array[$part]) && static::accessible($array[$part])) {
					$array = &$array[$part];
				} else {
					continue 2;
				}
			}

			unset($array[array_shift($parts)]);
		}
	}

	/**
	 * Determine if all items pass the given truth test.
	 * 
	 * @param  iterable  $array
	 * @param  callable  $callback
	 * @return bool
	 */
	public static function every($array, callable $callback): bool
	{
		return array_all($array, $callback);
	}
	
	/**
	 * Fetch a flattened array of a nested array element.
	 * 
	 * @param  array  $array
	 * @param  string  $key
	 * @return array
	 */
	public static function fetch($array, $key): array
	{
		$segments = explode('.', $key);
		
		foreach ($segments as $segment) {
			$results = [];
			
			foreach ($array as $value) {
				if (static::exists($value = (array) $value, $segment)) {
					$results = $value[$segment];
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
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function first($array, ?callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			if (empty($array)) {
				return value($default);
			}

			if (is_array($array)) {
                return array_first($array);
            }
			
			foreach ($array as $item) {
				return $item;
			}

			return value($default);
		}

		$array = static::from($array);
		
		foreach ($array as $key => $value) { 
			if ($callback($value, $key)) {
				return $value;
			}
		}

		return value($default);
	}

	/**
	 * Flatten a multi-dimensional array into a single level.
	 * 
	 * @param  array  $array
	 * @param  int  $depth
	 * @return array
	 */
	public static function flatten($array, $depth = INF)
	{
		$result = [];

		foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if ( ! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

		return $result;
	}

	/**
     * Get a float item from an array using "dot" notation.
     *
     * @throws \InvalidArgumentException
     */
    public static function float(ArrayAccess|array $array, string|int|null $key, ?float $default = null): float
    {
        $value = Arr::get($array, $key, $default);

        if ( ! is_float($value)) {
            throw new InvalidArgumentException(
                sprintf('Array value for key [%s] must be a float, %s found.', $key, gettype($value))
            );
        }

        return $value;
    }
	
	/**
	 * Get the underlying array of items from the given argument.
	 * 
	 * @param  array|iterable|Collectable|Arrayable|Traversable|Jsonable|JsonSerializable|object  $items 
	 * @return list|array
	 * 
	 * @throws \InvalidArgumentException
	 */
	public static function from($items)
	{
		return match (true) {
			is_array($items) => $items,
			$items instanceof Collectable => $items->all(),
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
	 * @param  string|int|null  $key  The dot-notated key or array of keys or null
	 * @param  mixed  $default  The default value
	 * @return mixed
	 */
	public static function get($array, $key, $default = null)
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
			return value($default);
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
	 * Gets max width of an array.
	 * 
	 * @param  array  $data
	 * @param  bool  $exclude 
	 * @return int
	 */
	public static function getMaxWidth($data, $exclude = true): int
	{
		$maxWidth = 0;
		
		foreach ($data as $key => $value) {
			// key is not a integer
			if ( ! $exclude || ! is_numeric($key)) {
				$width = mb_strlen((string) $key, 'UTF-8');
				$maxWidth = $width > $maxWidth ? $width : $maxWidth;
			}
		}
		
		return $maxWidth;
	}

	/**
	 * Check if an item exists in an array using "dot" notation.
	 * 
	 * @param  array  $array
	 * @param  array|string  $keys 
	 * @return bool
	 */
	public static function has($array, $keys): bool
	{
		$keys = (array) $keys;

        if ( ! $array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

			$segments = explode('.', $key);

            foreach ($segments as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }
		
		return true;
	}
	
	/**
	 * Determine if all keys exist in an array using "dot" notation.
	 * 
	 * @param  \ArrayAccess|array  $array
	 * @param  string|array  $keys 
	 * @return bool
	 */
	public static function hasAll($array, $keys): bool
	{
		$keys = (array) $keys;
		
		if ( ! $array || $keys === []) {
			return false;
		}
		
		foreach ($keys as $key) {
			if ( ! static::has($array, $key)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Determine if any of the keys exist in an array using "dot" notation.
	 * 
	 * @param  \ArrayAccess|array  $array
	 * @param  string|array  $keys 
	 * @return bool
	 */
	public static function hasAny($array, $keys): bool
	{
		if (is_null($keys)) {
			return false;
		}
		
		$keys = (array) $keys;
		
		if ( ! $array) {
			return false;
		}
		
		if ($keys === []) {
			return false;
		}
		
		foreach ($keys as $key) {
			if (static::has($array, $key)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get an integer item from an array using "dot" notation.
	 * 
	 * @param  \ArrayAccess|array  $array
	 * @param  string|int|null  $key
	 * @param  int|null  $default 
	 * @return int
	 * 
	 * @throws InvalidArgumentException
	 */
	public static function integer(ArrayAccess|array $array, string|int|null $key, ?int $default = null): int
	{
		$value = Arr::get($array, $key, $default);
		
		if ( ! is_int($value)) {
			throw new InvalidArgumentException(
				sprintf('Array value for key [%s] must be an integer, %s found.', $key, gettype($value))
			);
		}
		
		return $value;
	}
	
	/**
	 * Determines if an array is associative.
	 * 
	 * @param  array  $array 
	 * @return bool
	 */
	public static function isAssoc(array $array): bool
	{
		return ! array_is_list($array);
	}
	
	/**
	 * Determines if an array is a list.
	 * 
	 * @param  array  $array 
	 * @return bool
	 */
	public static function isList($array): bool
	{
		return array_is_list($array);
	}

	/**
     * Key an associative array by a field or using a callback.
     *
     * @param  iterable  $array
     * @param  callable|array|string  $keyBy 
     * @return array
     */
    public static function keyBy($array, $keyBy)
    {
        return (new Collection($array))->keyBy($keyBy)->all();
    }

	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array  $array 
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function last($array, ?callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			return empty($array) ? value($default) : array_last($array);
		}
		
		return static::first(array_reverse($array, true), $callback, $default);
	}
	
	/**
	 * Run a map over each of the items in the array.
	 * 
	 * @param  array  $array
	 * @param  callable  $callback 
	 * @return array
	 */
	public static function map(array $array, callable $callback)
	{
		$keys = array_keys($array);
		
		try {
			$items = array_map($callback, $array, $keys);
		} catch (ArgumentCountError) {
			$items = array_map($callback, $array);
		}
		
		return array_combine($keys, $items);
	}

	/**
     * Run an associative map over each of the items.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    public static function mapKeys(array $array, callable $callback)
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

	/**
	 * Get a subset of the items from the given array.
	 * 
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	public static function only($array, $keys)
	{
		return array_intersect_key($array, array_flip((array) $keys));
	}
	
	/**
	 * Partition the array into two arrays using the given callback.
	 * 
	 * @param  array  $array
	 * @param  callable  $callback 
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
	 * @param  array  $array
	 * @param  mixed  $value
	 * @param  mixed  $key 
	 * @return array
	 */
	public static function prepend($array, $value, $key = null)
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
	 * @param  string|int  $key
	 * @param  mixed  $default 
	 * @return mixed
	 */
	public static function pull(&$array, $key, $default = null)
	{
		$value = static::get($array, $key, $default);

		static::erase($array, $key);

		return $value;
	}
	
	/**
	 * Pluck an array of values from an array.
	 * 
	 * @param  iterable  $array
	 * @param  string|array|int|null  $value
	 * @param  string|array|null  $key 
	 * @return array
	 */
	public static function pluck($array, $value, $key = null)
	{
		$results = [];
		
		[$value, $key] = static::explodePluckParameters($value, $key);

		foreach ($array as $item) {
			$itemValue = $value instanceof Closure
			    ? $value($item)
				: data_get($item, $value);
			
			// If the key is "null", we will just append the value to the array and keep
			// looping. 
			if (is_null($key)) {
				$results[] = $itemValue;
			} else {
				$itemKey = $key instanceof Closure
				    ? $key($item)
					: data_get($item, $key);
				
				if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
					$itemKey = (string) $itemKey;
				}
				
				$results[$itemKey] = $itemValue;
			}
		}
		
		return $results;
	}

	/**
     * Push an item into an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int|null  $key
     * @param  mixed  $values
     * @return array
     */
    public static function push(ArrayAccess|array &$array, string|int|null $key, mixed ...$values): array
    {
        $target = static::array($array, $key, []);

        array_push($target, ...$values);

        return static::set($array, $key, $target);
    }

	/**
	 * Convert the array into a query string.
	 * 
	 * @param  array  $array 
	 * @return string
	 */
	public static function query($array): string
	{
		return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Sets a value in an array using "dot" notation.
	 *
	 * @param  array  $array  The search array
	 * @param  string|int|null  $key  The dot-notated key or array of keys
	 * @param  mixed  $value  The default value
	 * @return array
	 */
	public static function set(&$array, $key, $value)
	{
		if (is_null($key)) {
			return $array = $value;
		}

		$keys = explode('.', $key);

		foreach ($keys as $i => $k) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$k]) || ! is_array($array[$k])) {
                $array[$k] = [];
            }

            $array = &$array[$k];
        }

		$array[array_shift($keys)] = $value;

		return $array;
	}

	/**
	 * Determine if some items pass the given truth test.
	 * 
	 * @param  iterable  $array
	 * @param  callable  $callback 
	 * @return bool
	 */
	public static function some($array, callable $callback): bool
	{
		return array_any($array, $callback);
	}
	
	/**
	 * Sort the array using the given callback or "dot" notation.
	 * 
	 * @param  array  $array
	 * @param  callable|array|string|null  $callback 
	 * @return array
	 */
	public static function sort($array, $callback = null): array
	{
		return Collection::make($array)->sortBy($callback)->all();
	}

	/**
     * Take the first or last {$limit} items from an array.
     *
     * @param  array  $array
     * @param  int  $limit
     * @return array
     */
    public static function take($array, $limit)
    {
        if ($limit < 0) {
            return array_slice($array, $limit, abs($limit));
        }

        return array_slice($array, 0, $limit);
    }
	
	/**
	 * Convert a flatten "dot" notation array into an expanded array.
	 * 
	 * @param  iterable  $array 
	 * @return array
	 */
	public static function undot($array)
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
	 * @param  callable  $callback 
	 * @return array
	 */
	public static function where($array, callable $callback)
	{
		return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
	}

	/**
     * Filter items where the value is not null.
     *
     * @param  array  $array
     * @return array
     */
    public static function whereNotNull($array)
    {
        return static::where($array, fn ($value) => ! is_null($value));
    }

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 * 
	 * @param  array|null  $value 
	 * @return array
	 */
	public static function wrap($value)
	{
		if (is_null($value)) {
			return [];
		}

		return is_array($value) ? $value : [$value];
	}
}