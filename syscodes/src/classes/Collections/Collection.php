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

namespace Syscodes\Collections;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Allows provide a way for working with arrays of data.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
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
     * @param  \Callable  $callback
     * @param  mixed|null  $default  (null by default)
     * 
     * @return mixed
     */
    public function first(Callable $callback, $default = null)
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