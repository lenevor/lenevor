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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Collections;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Contracts\Support\Jsonable;
use Syscodes\Contracts\Support\Arrayable;
use Syscodes\Collections\Traits\Enumerates;

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
     * Get all of the items in the collection.
     * 
     * @return array
     */
    public function all()
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
     * @param  \Callable  $callback
     * 
     * @return static
     */
    public function diffUsing($items, Callable $callback)
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
     * @param  \Callable  $callback
     * 
     * @return static
     */
    public function diffAssocUsing($items, Callable $callback)
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
     * @param  \Callable  $callback
     * 
     * @return static
     */
    public function diffKeyUsing($items, Callable $callback)
    {
        return new static(array_diff_ukey($this->items, $this->getArrayItems($items), $callback));
    }

    /**
     * Execute a callback over each item.
     * 
     * @param  \Callable  $callback
     * 
     * @return $this
     */
    public function each(Callable $callback)
    {
        array_map($callback, $this->items);

        return $this;
    }

    /**
     * Run a filter over each of the items.
     * 
     * @param  \Callable|null  (null by default) 
     * 
     * @return static
     */
    public function filter(Callable $callback = null)
    {
        if ($callback)
        {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the first item from the collection.
     * 
     * @param  \Callable|null  $callback  (null by default)
     * @param  mixed  $default  (null by default)
     * 
     * @return mixed
     */
    public function first(Callable $callback = null, $default = null)
    {
        if (is_null($callback))
        {
            return count($this->items) > 0 ? head($this->items) : null;
        }
        else
        {
            return Arr::first($this->items, $callback, $default);
        }
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
     * Determine if an item exists in the collection by key.
     * 
     * @param  mixed  $keys
     * 
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Remove an item from the collection by key.
     * 
     * @param  string|array  $keys
     * 
     * @return $this
     */
    public function erase($keys)
    {
        foreach ((array) $keys as $key)
        {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Run a map over each of the items.
     * 
     * @param  \Callable  $callback
     * 
     * @return static
     */
    public function map(Callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
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
        return new static(array_recursive_merge($this->items, $this->getArrayItems($items)));
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
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get the last item from the collection.
     * 
     * @param  \Callable|null  $callback  (null by default)
     * @param  mixed|null  $default  (null by default)
     * 
     * @return mixed
     */
    public function last(Callable $callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
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
     * @param  mixed  $key  (null by default)
     * 
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        return Arr::prepend($this->items, $value, $key);
    }

    /**
     * Push an item onto the end of the collection.
     * 
     * @param  mixed  $values  [optional]
     * 
     * @return $this
     */
    public function push(...$values)
    {
        foreach ($values as $value)
        {
            $this->items[] = $value;
        }

        return $this;
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
     * Get and remove an item from the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $default  (null by default)
     * 
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Reduce the collection to a single value.
     * 
     * @param  \Callable  $callback
     * @param  mixed  $initial  (null by default)
     * 
     * @return mixed
     */
    public function reduce(Callable $callback, $initial = null)
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
        if ( ! $this->usesAsCallable($value))
        {
            return array_search($value, $this->items, $strict);
        }

        foreach($this->items as $key => $item)
        {
            if ($value($item, $key))
            {
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
     * Slice the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length  (null by default)
     * 
     * @return static
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
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
        if ($size <= 0)
        {
            return new static;
        }

        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk)
        {
            $chunks[] = $chunk;
        }

        return new static($chunks);
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
     * Splice portion of the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length  (null by default)
     * @param  mixed  $replacement
     * 
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() == 1)
        {
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
        if ($limit < 0)
        {
            $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
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
     * Return only unique items from the collection array.
     * 
     * @return static
     */
    public function unique()
    {
        return new static(array_unique($this->items));
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
        if (is_array($items))
        {
            return $items;
        }
        elseif($items instanceof Arrayable)
        {
            return $items->toArray();
        }
        elseif ($items instanceof Jsonable)
        {
            return json_decode($items->toJson(), true);
        }
        elseif ($items instanceof Collection)
        {
            return $items->all();
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
        if (is_null($offset))
        {
            $this->items[] = $value;
        }
        else
        {
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
}