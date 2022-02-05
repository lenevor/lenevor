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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Collections;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Collections\Traits\Enumerates;

/**
 * Allows provide a way for working with arrays of data.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    use Enumerates;

    /**
     * The items contained in the collection.
     * 
     * @var array $items
     */
    protected $items = [];

    /**
     * Constructor. Create a new Collection instance.
     * 
     * @param  mixed  $items
     * 
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayItems($items);
    }

    /**
     * Add an item in the collection.
     * 
     * @param  mixed  $item
     * 
     * @return $this
     */
    public function add($item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Get all of the items in the collection.
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Collapse the collection items into a single array.
     * 
     * @return static
     */
    public function collapse()
    {
        return new static(Arr::collapse($this->items));
    }

    /**
     * Creates a collection by using this collection for 
     * keys and other its values.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function combine($items)
    {
        return new static(array_combine($this->all(), $this->getArrayItems($items)));
    }

    /**
     * Chunk the underlying collection array.
     * 
     * @param  int  $size
     * 
     * @return static
     */
    public function chunk($size)
    {
        if ($size <= 0) {
            return new static;
        }

        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = $chunk;
        }

        return new static($chunks);
    }

    /**
     * Diff the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diff($items)
    {
        return new static(array_diff($this->items, $this->getArrayItems($items)));
    }

    /**
     * Returns the items in a collection that are not present 
     * in the given items, using the callback.
     * 
     * @param  mixed  $items
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function diffUsing($items, callable $callback)
    {
        return new static(array_udiff($this->items, $this->getArrayItems($items), $callback));
    }

    /**
     * Returns the items in the collection when the keys and values 
     * are not present in the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diffAssoc($items)
    {
        return new static(array_diff_assoc($this->items, $this->getArrayItems($items)));
    }

    /**
     * Returns the items in the collection when the keys and values 
     * are not present in the given items, using the callback.
     * 
     * @param  mixed  $items
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function diffAssocUsing($items, callable $callback)
    {
        return new static(array_diff_uassoc($this->items, $this->getArrayItems($items), $callback));
    }

    /**
     * Returns the items in the collection when the keys 
     * are not present in the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diffKeys($items)
    {
        return new static(array_diff_key($this->items, $this->getArrayItems($items)));
    }

    /**
     * Returns the items in the collection when the keys are 
     * not present in the given items, using the callback.
     * 
     * @param  mixed  $items
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function diffKeyUsing($items, callable $callback)
    {
        return new static(array_diff_ukey($this->items, $this->getArrayItems($items), $callback));
    }

    /**
     * Execute a callback over each item.
     * 
     * @param  \callable  $callback
     * 
     * @return self
     */
    public function each(callable $callback): self
    {
        array_map($callback, $this->items);

        return $this;
    }

    /**
     * Remove an item from the collection by key.
     * 
     * @param  string|array  $keys
     * 
     * @return self
     */
    public function erase($keys): self
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Get all items exceptions with the specified keys.
     * 
     * @param  mixed  $keys
     *
     * @return static
     */
    public function except($keys)
    {
        if ($keys instanceof static) {
            $keys = $keys->all();
        } else {
            $keys = func_get_args();
        }

        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Run a filter over each of the items.
     * 
     * @param  \callable|null  $callback
     * 
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the first item from the collection.
     * 
     * @param  \callable|null  $callback
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Flip the items in the collection.
     * 
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * Get a flattened array of the items in the collection.
     * 
     * @return static
     */
    public function flatten()
    {
        return new static(Arr::flatten($this->items));
    }

    /**
     * Get an item from the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return value($default);
    }

    /**
     * Determine if an item exists in the collection by key.
     * 
     * @param  mixed  $keys
     * 
     * @return bool
     */
    public function has($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ( ! Arr::exists($this->items, $value)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * Concatenate values of a given key as a string.
     * 
     * @param  string  $value
     * @param  string|null  $string
     * 
     * @return string
     */
    public function implode($value, $string = null)
    {
        $first = $this->first();
        
        if (is_array($first) || (is_object($first))) {
            return implode($string ?? '', $this->pluck($value)->all());
        }
        
        return implode($value ?? '', $this->items);
    }

    /**
     * Intersect the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function intersect($items)
    {
        return new static(array_intersect($this->items, $this->getArrayItems($items)));
    }

    /**
     * Intersect the collection with the given items by key.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function intersectKey($items)
    {
        return new static(array_intersect_key($this->items, $this->getArrayItems($items)));
    }

    /**
     * Determine if the collection is empty or not.
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
    
    /**
     * Reset the keys of the collection.
     * 
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * Get the last item from the collection.
     * 
     * @param  \callable|null  $callback
     * @param  mixed|null  $default
     * 
     * @return mixed
     */
    public function last(callable $callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }

    /**
     * Run a map over each of the items.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Merge the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function merge(array $items)
    {
        return new static(array_merge($this->items, $this->getArrayItems($items)));
    }

    /**
     * Recursively Merge the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function mergeRecursive($items)
    {
        return new static(array_merge_recursive($this->items, $this->getArrayItems($items)));
    }

    /**
     * Get the items with the specified keys.
     * 
     * @param  mixed  $keys
     * 
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        if ($keys instanceof static) {
            $keys = $keys->all() ;
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::only($this->items, $keys));
    }

    /**
     * Pad collection to the specified length with a value.
     * 
     * @param  int  $size
     * @param  mixed  $value
     * 
     * @return static
     */
    public function pad($size, $value)
    {
        return new static(array_pad($this->items, $size, $value));
    }

    /**
     * Get and remove the last item from the collection.
     * 
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Push an item onto the beginning of the collection.
     * 
     * @param  mixed  $value
     * @param  mixed  $key
     * 
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        return Arr::prepend($this->items, $value, $key);
    }

    /**
     * Get and remove an item from the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Put an item in the collection by key.
     * 
     * @param  mixed  $key
     * @param  mixed  $value
     * 
     * @return $this
     */
    public function put($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Push an item onto the end of the collection.
     * 
     * @param  mixed  $values  [optional]
     * 
     * @return self
     */
    public function push(...$values): self
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }

        return $this;
    }
    
    /**
     * Get the values of a given key.
     * 
     * @param  string|array|int|null  $value
     * @param  string|null  $key
     * 
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Create a collection with the given range.
     * 
     * @param  int  $from
     * @param  int  $to
     * 
     * @return static
     */
    public function range($from, $to)
    {
        return new static(range($from, $to));
    }

    /**
     * Reduce the collection to a single value.
     * 
     * @param  \callable  $callback
     * @param  mixed  $initial
     * 
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Replace the collection items with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function replace($items)
    {
        return new static(array_replace($this->items, $this->getArrayItems($items)));
    }

    /**
     * Recursively replace the collection items with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function replaceRecursive($items)
    {
        return new static(array_replace_recursive($this->items, $this->getArrayItems($items)));
    }

    /**
     * Reverse items order.
     * 
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     * 
     * @param  mixed  $value
     * @param  bool  $strict  (false by default)
     * 
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        if ( ! $this->usesAscallable($value)) {
            return array_search($value, $this->items, $strict);
        }

        foreach($this->items as $key => $item) {
            if ($value($item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get one or more items randomly from the collection.
     * 
     * @param  int  $amount  (1 by default)
     * 
     * @return mixed
     */
    public function random($amount = 1)
    {
        $keys = array_rand($this->items, $amount);

        return is_array($keys) ? array_intersect_key($this->items, array_flip($keys)) : $this->items[$keys];
    }

    /**
     * Get and remove the first item from the collection.
     * 
     * @return void
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * Slice the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length
     * 
     * @return static
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Sort through each item.
     * 
     * @param  Callable|int|null  $callback
     * 
     * @return static
     */
    public function sort($callback = null)
    {
        $items =  $this->items;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items, $callback ?? SORT_REGULAR);

        return new static($items);
    }

    /**
     * Sort items in descending order.
     * 
     * @param  int  $options
     * 
     * @return static
     */
    public function sortDesc($options = SORT_REGULAR)
    {
        $items = $this->items;

        arsort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection keys.
     * 
     * @param  int  $options
     * @param  bool  $descending
     * 
     * @return static
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = $this->items;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection keys in descending order.
     * 
     * @param  int  $options
     * 
     * @return static
     */
    public function sortKeysDesc($options =  SORT_REGULAR)
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Sort the collection keys using a callback.
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function sortKeysUsing(callable $callback)
    {
        $items = $this->items;

        uksort($items, $callback);

        return new static($items);
    }

    /**
     * Splice portion of the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length
     * @param  mixed  $replacement
     * 
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() == 1) {
            return new static(array_splice($this->items, $offset));
        }

        return new static(array_splice($this->items, $offset, $length, $replacement));
    }

    /**
     * Take the first or last {$limit} items.
     * 
     * @param  int  $limit
     * 
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Transform each item in the collection.
     * 
     * @param  callable  $callback
     * 
     * @return self
     */
    public function transform(callable $callback): self
    {
        $this->items = $this->map($callback)->all();

        return $this;
    }

    /**
     * Union the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function union($items)
    {
        return new static($this->items + $this->getArrayItems($items));
    }

    /**
     * Return only unique items from the collection array.
     * 
     * @return static
     */
    public function unique()
    {
        return new static(array_unique($this->items, SORT_REGULAR));
    }

    /**
     * Reset the keys on the underlying array.
     * 
     * @return static
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * Results array of items from Collection.
     * 
     * @param  \Syscodes\Collections\Collection|array  $items
     * 
     * @return array
     */
    private function getArrayItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Collection) {
            return $items->all();
        } elseif($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /*
    |-----------------------------------------------------------------
    | ArrayIterator Methods
    |-----------------------------------------------------------------
    */

    /**
     * Get an iterator for the items.
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /*
    |-----------------------------------------------------------------
    | Countable Methods
    |-----------------------------------------------------------------
    */

    /**
     * Count the number of items in the collection.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /*
    |-----------------------------------------------------------------
    | ArrayAccess Methods
    |-----------------------------------------------------------------
    */

    /**
     * Determine if a given offset exists.
     * 
     * @param  string  $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get the value at a given offset.
     * 
     * @param  string  $offset
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Set the value at a given offset.
     * 
     * @param  string  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the value at a given offset.
     * 
     * @param  string  $offset
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
    
    /**
     * Magic method.
     * 
     * Convert the collection to its string representation.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}