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

use stdClass;
use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Components\Contracts\Support\Collectable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Traits\Enumerates;

/**
 * Allows provide a way for working with arrays of data.
 */
class Collection implements ArrayAccess, Arrayable, IteratorAggregate, Countable, Collectable
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
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Add an item in the collection.
     * 
     * @param  mixed  $item
     * 
     * @return static
     */
    public function add(mixed $item): static
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
    public function collapse(): static
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
    public function combine(mixed $items): static
    {
        return new static(array_combine($this->all(), $this->getArrayableItems($items)));
    }
    
    /**
     * Determine if an item exists in the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function contains($key, mixed $operator = null, mixed $value = null): bool
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;
                
                return $this->first($key, $placeholder) !== $placeholder;
            }
            
            return in_array($key, $this->items);
        }

        return $this->contains($this->operatorCallback(...func_get_args()));
    }


    /**
     * Chunk the underlying collection array.
     * 
     * @param  int  $size
     * 
     * @return static
     */
    public function chunk(int $size): static
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
    public function diff(mixed $items): static
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
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
    public function diffUsing(mixed $items, callable $callback): static
    {
        return new static(array_udiff($this->items, $this->getArrayableItems($items), $callback));
    }

    /**
     * Returns the items in the collection when the keys and values 
     * are not present in the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diffAssoc(mixed $items): static
    {
        return new static(array_diff_assoc($this->items, $this->getArrayableItems($items)));
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
    public function diffAssocUsing(mixed $items, callable $callback): static
    {
        return new static(array_diff_uassoc($this->items, $this->getArrayableItems($items), $callback));
    }

    /**
     * Returns the items in the collection when the keys 
     * are not present in the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diffKeys(mixed $items): static
    {
        return new static(array_diff_key($this->items, $this->getArrayableItems($items)));
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
    public function diffKeyUsing(mixed $items, callable $callback): static
    {
        return new static(array_diff_ukey($this->items, $this->getArrayableItems($items), $callback));
    }
    
    /**
     * Retrieve duplicate items from the collection.
     * 
     * @param  callable|string|null  $callback
     * @param  bool  $strict
     * 
     * @return static
     */
    public function duplicates($callback = null, $strict = false): static
    {
        $items = $this->map($this->valueRetriever($callback));
        
        $uniqueItems = $items->unique(null, $strict);
        
        $compare = $this->duplicateComparator($strict);
        
        $duplicates = new static;
        
        foreach ($items as $key => $value) {
            if ($uniqueItems->isNotEmpty() && $compare($value, $uniqueItems->first())) {
                $uniqueItems->shift();
            } else {
                $duplicates[$key] = $value;
            }
        }
        
        return $duplicates;
    }
    
    /**
     * Retrieve duplicate items from the collection using strict comparison.
     * 
     * @param  callable|string|null  $callback
     * 
     * @return static
     */
    public function duplicatesStrict($callback = null): static
    {
        return $this->duplicates($callback, true);
    }
    
    /**
     * Get the comparison function to detect duplicates.
     * 
     * @param  bool  $strict
     * 
     * @return callable|bool
     */
    protected function duplicateComparator($strict)
    {
        if ($strict) {
            return fn ($a, $b) => $a === $b;
        }
        
        return fn ($a, $b) => $a == $b;
    }

    /**
     * Execute a callback over each item.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function each(callable $callback): static
    {
        array_map($callback, $this->items);

        return $this;
    }

    /**
     * Remove an item from the collection by key.
     * 
     * @param  string|array  $keys
     * 
     * @return static
     */
    public function erase($keys): static
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
    public function except(mixed $keys): static
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
    public function filter(?callable $callback = null): static
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
    public function first(?callable $callback = null, mixed $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Flip the items in the collection.
     * 
     * @return static
     */
    public function flip(): static
    {
        return new static(array_flip($this->items));
    }

    /**
     * Get a flattened array of the items in the collection.
     * 
     * @return static
     */
    public function flatten(): static
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
    public function get($key, mixed $default = null)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return value($default);
    }

    /**
     * Determine if an item exists in the collection by key.
     * 
     * @param  mixed  $key
     * 
     * @return bool
     */
    public function has(mixed $key): bool
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
    public function implode(string $value, ?string $string = null): string
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
    public function intersect(mixed $items): static
    {
        return new static(array_intersect($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Intersect the collection with the given items by key.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function intersectKey(mixed $items): static
    {
        return new static(array_intersect_key($this->items, $this->getArrayableItems($items)));
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
     * Join all items from the collection using a string. The final items can use a separate glue string.
     * 
     * @param  string  $glue
     * @param  string  $finalGlue
     * 
     * @return mixed
     */
    public function join($glue, $finalGlue = ''): string
    {
        if ($finalGlue === '') return $this->implode($glue);
        
        $count = $this->count();
        
        if ($count === 0) return '';
        
        if ($count === 1) return $this->last();
        
        $collection = new static($this->items);
        
        $finalItem = $collection->pop();
        
        return $collection->implode($glue).$finalGlue.$finalItem;
    }
    
    /**
     * Reset the keys of the collection.
     * 
     * @return static
     */
    public function keys(): static
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
    public function last(?callable $callback = null, mixed $default = null)
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
    public function map(callable $callback): static
    {
        return new static(Arr::map($this->items, $callback));
    }

    /**
     * Run an associative map over each of the items.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function mapKeys(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return new static($result);
    }

    /**
     * Merge the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function merge(array $items): static
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Recursively Merge the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function mergeRecursive($items): static
    {
        return new static(array_merge_recursive($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the items with the specified keys.
     * 
     * @param  mixed  $keys
     * 
     * @return static
     */
    public function only(mixed $keys): static
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        if ($keys instanceof static) {
            $keys = $keys->all();
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
    public function pad(int $size, mixed $value): static
    {
        return new static(array_pad($this->items, $size, $value));
    }

    /**
     * Get the values of a given key.
     * 
     * @param  string|array|int|null  $value
     * @param  string|null  $key
     * 
     * @return static
     */
    public function pluck($value, ?string $key = null): static
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Get and remove the last item from the collection.
     * 
     * @return mixed
     */
    public function pop(): mixed
    {
        return array_pop($this->items);
    }

    /**
     * Push an item onto the beginning of the collection.
     * 
     * @param  mixed  $value
     * @param  mixed|null  $key
     * 
     * @return array
     */
    public function prepend(mixed $value, mixed $key = null): array
    {
        return Arr::prepend($this->items, $value, $key);
    }

    /**
     * Get and remove an item from the collection.
     * 
     * @param  mixed  $key
     * @param  mixed|null  $default
     * 
     * @return mixed
     */
    public function pull(mixed $key, mixed $default = null): mixed
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Push an item onto the end of the collection.
     * 
     * @param  mixed  $values  [optional]
     * 
     * @return static
     */
    public function push(...$values): static
    {
        foreach ($values as $value) {
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
     * @return static
     */
    public function put(mixed $key, mixed $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Create a collection with the given range.
     * 
     * @param  int  $from
     * @param  int  $to
     * 
     * @return static
     */
    public function range(int $from, int $to): static
    {
        return new static(range($from, $to));
    }

    /**
     * Reduce the collection to a single value.
     * 
     * @param  \callable  $callback
     * @param  mixed|null  $initial
     * 
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
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
    public function replace(mixed $items): static
    {
        return new static(array_replace($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Recursively replace the collection items with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function replaceRecursive(mixed $items): static
    {
        return new static(array_replace_recursive($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Reverse items order.
     * 
     * @return static
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     * 
     * @param  mixed  $value
     * @param  bool  $strict
     * 
     * @return mixed
     */
    public function search(mixed $value, bool $strict = false): mixed
    {
        if ( ! $this->useAsCallable($value)) {
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
     * @param  int  $amount
     * 
     * @return mixed
     */
    public function random(int $amount = 1): mixed
    {
        $keys = array_rand($this->items, $amount);

        return is_array($keys) ? array_intersect_key($this->items, array_flip($keys)) : $this->items[$keys];
    }

    /**
     * Get and remove the first item from the collection.
     * 
     * @return array
     */
    public function shift(): array
    {
        return array_shift($this->items);
    }
    
    /**
     * Skip the first {$count} items.
     * 
     * @param  int  $count
     * 
     * @return static
     */
    public function skip(int $count): static
    {
        return $this->slice($count);
    }

    /**
     * Slice the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length
     * 
     * @return static
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Sort through each item.
     * 
     * @param  \callable|int|null  $callback
     * 
     * @return static
     */
    public function sort($callback = null): static
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
    public function sortDesc(int $options = SORT_REGULAR): static
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
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): static
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
    public function sortKeysDesc(int $options =  SORT_REGULAR): static
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Sort the collection keys using a callback.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function sortKeysUsing(callable $callback): static
    {
        $items = $this->items;

        uksort($items, $callback);

        return new static($items);
    }
    
    /**
     * Sort the collection using the given callback.
     * 
     * @param  \Closure|string  $callback
     * @param  int  $options
     * @param  bool  $descending
     * 
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false): static
    {
        if (is_array($callback) && ! is_callable($callback)) {
            return $this->sortByMany($callback, $options);
        }
        
        $results = [];
        
        $callback = $this->valueRetriever($callback);
        
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }
        
        $descending ? arsort($results, $options) : asort($results, $options);
        
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }
        
        return new static($results);
    }
    
    /**
     * Sort the collection using multiple comparisons.
     * 
     * @param  array  $comparisons
     * @param  int  $options
     * 
     * @return static
     */
    protected function sortByMany(array $comparisons = [], int $options = SORT_REGULAR): static
    {
        $items = $this->items;
        
        uasort($items, function ($a, $b) use ($comparisons, $options) {
            foreach ($comparisons as $comparison) {
                $comparison = Arr::wrap($comparison);
                
                $prop = $comparison[0];
                
                $ascending = Arr::get($comparison, 1, true) === true || Arr::get($comparison, 1, true) === 'asc';
                
                if ( ! is_string($prop) && is_callable($prop)) {
                    $result = $prop($a, $b);
                } else {
                    $values = [data_get($a, $prop), data_get($b, $prop)];
                    
                    if ( ! $ascending) {
                        $values = array_reverse($values);
                    }
                    
                    if (($options & SORT_FLAG_CASE) === SORT_FLAG_CASE) {
                        if (($options & SORT_NATURAL) === SORT_NATURAL) {
                            $result = strnatcasecmp($values[0], $values[1]);
                        } else {
                            $result = strcasecmp($values[0], $values[1]);
                        }
                    } else {
                        $result = match ($options) {
                            SORT_NUMERIC => (int) $values[0] <=> (int) $values[1],
                            SORT_STRING => strcmp($values[0], $values[1]),
                            SORT_NATURAL => strnatcmp((string) $values[0], (string) $values[1]),
                            SORT_LOCALE_STRING => strcoll($values[0], $values[1]),
                            default => $values[0] <=> $values[1],
                        };
                    }
                }
                
                if ($result === 0) {
                    continue;
                }
                
                return $result;
            }
        });
        
        return new static($items);
    }
    
    /**
     * Sort the collection in descending order using the given callback.
     * 
     * @param  \Closure|string  $callback
     * @param  int  $options
     * 
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR): static
    {
        if (is_array($callback) && ! is_callable($callback)) {
            foreach ($callback as $index => $key) {
                $comparison = Arr::wrap($key);
                $comparison[1] = 'desc';
                $callback[$index] = $comparison;
            }
        }
        
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Splice portion of the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length
     * @param  array  $replacement
     * 
     * @return static
     */
    public function splice($offset, $length = null, $replacement = []): static
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->items, $offset));
        }

        return new static(array_splice($this->items, $offset, $length, $this->getArrayableItems($replacement)));
    }

    /**
     * Take the first or last {$limit} items.
     * 
     * @param  int  $limit
     * 
     * @return static
     */
    public function take(int $limit): static
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Transform each item in the collection.
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function transform(callable $callback): static
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
    public function union(mixed $items): static
    {
        return new static($this->items + $this->getArrayableItems($items));
    }

    /**
     * Return only unique items from the collection array.
     * 
     * @return static
     */
    public function unique(): static
    {
        return new static(array_unique($this->items, SORT_REGULAR));
    }

    /**
     * Reset the keys on the underlying array.
     * 
     * @return static
     */
    public function values(): static
    {
        return new static(array_values($this->items));
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
    public function getIterator(): Traversable
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
    public function count(): int
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
     * @param  mixed  $offset
     * 
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get the value at a given offset.
     * 
     * @param  mixed  $offset
     * 
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Set the value at a given offset.
     * 
     * @param  mixed  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
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
     * @param  mixed  $offset
     * 
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }
}