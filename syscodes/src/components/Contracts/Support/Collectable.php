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

namespace Syscodes\Components\Contracts\Support;

use Countable;
use JsonSerializable;
use IteratorAggregate;

/**
 * Allows the collection of items from an array.
 */
interface Collectable extends Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    /**
     * Create a new collection instance if the value isn't one already.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public static function make($items = []): static;

    /**
     * Add an item in the collection.
     * 
     * @param  mixed  $item
     * 
     * @return static
     */
    public function add(mixed $item): static;

    /**
     * Determine if the collection is not empty.
     * 
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Get all of the items in the collection.
     * 
     * @return array
     */
    public function all(): array;

    /**
     * Collapse the collection items into a single array.
     * 
     * @return static
     */
    public function collapse(): static;

    /**
     * Creates a collection by using this collection for 
     * keys and other its values.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function combine(mixed $items): static;

    /**
     * Determine if an item exists in the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function contains($key, mixed $operator = null, mixed $value = null): bool;

    /**
     * Chunk the underlying collection array.
     * 
     * @param  int  $size
     * 
     * @return static
     */
    public function chunk(int $size): static;

    /**
     * Diff the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diff(mixed $items): static;

    /**
     * Returns the items in a collection that are not present 
     * in the given items, using the callback.
     * 
     * @param  mixed  $items
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function diffUsing(mixed $items, callable $callback): static;

     /**
     * Returns the items in the collection when the keys and values 
     * are not present in the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diffAssoc(mixed $items): static;

    /**
     * Returns the items in the collection when the keys and values 
     * are not present in the given items, using the callback.
     * 
     * @param  mixed  $items
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function diffAssocUsing(mixed $items, callable $callback): static;

    /**
     * Returns the items in the collection when the keys 
     * are not present in the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function diffKeys(mixed $items): static;

    /**
     * Returns the items in the collection when the keys are 
     * not present in the given items, using the callback.
     * 
     * @param  mixed  $items
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function diffKeyUsing(mixed $items, callable $callback): static;

    /**
     * Execute a callback over each item.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function each(callable $callback): static;

    /**
     * Remove an item from the collection by key.
     * 
     * @param  string|array  $keys
     * 
     * @return static
     */
    public function erase($keys): static;

    /**
     * Get all items exceptions with the specified keys.
     * 
     * @param  mixed  $keys
     *
     * @return static
     */
    public function except(mixed $keys): static;

    /**
     * Run a filter over each of the items.
     * 
     * @param  \callable|null  $callback
     * 
     * @return static
     */
    public function filter(?callable $callback = null): static;
    
    /**
     * Get the first item from the collection.
     * 
     * @param  \callable|null  $callback
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function first(?callable $callback = null, mixed $default = null);

    /**
     * Flip the items in the collection.
     * 
     * @return static
     */
    public function flip(): static;

    /**
     * Get a flattened array of the items in the collection.
     * 
     * @return static
     */
    public function flatten(): static;

    /**
     * Get an item from the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($key, mixed $default = null);

    /**
     * Determine if an item exists in the collection by key.
     * 
     * @param  mixed  $key
     * 
     * @return bool
     */
    public function has(mixed $key): bool;
    
    /**
     * Concatenate values of a given key as a string.
     * 
     * @param  string  $value
     * @param  string|null  $string
     * 
     * @return string
     */
    public function implode(string $value, ?string $string = null): string;

    /**
     * Intersect the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function intersect(mixed $items): static;

    /**
     * Intersect the collection with the given items by key.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function intersectKey(mixed $items): static;

    /**
     * Determine if the collection is empty or not.
     * 
     * @return bool
     */
    public function isEmpty(): bool;
    
    /**
     * Reset the keys of the collection.
     * 
     * @return static
     */
    public function keys(): static;

    /**
     * Get the last item from the collection.
     * 
     * @param  \callable|null  $callback
     * @param  mixed|null  $default
     * 
     * @return mixed
     */
    public function last(?callable $callback = null, mixed $default = null);

    /**
     * Run a map over each of the items.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function map(callable $callback): static;

    /**
     * Run an associative map over each of the items.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function mapKeys(callable $callback): static;

    /**
     * Merge the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function merge(array $items): static;

    /**
     * Recursively Merge the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function mergeRecursive($items): static;

    /**
     * Get the items with the specified keys.
     * 
     * @param  mixed  $keys
     * 
     * @return static
     */
    public function only(mixed $keys): static;

    /**
     * Pad collection to the specified length with a value.
     * 
     * @param  int  $size
     * @param  mixed  $value
     * 
     * @return static
     */
    public function pad(int $size, mixed $value): static;

    /**
     * Get the values of a given key.
     * 
     * @param  string|array|int|null  $value
     * @param  string|null  $key
     * 
     * @return static
     */
    public function pluck($value, ?string $key = null): static;

    /**
     * Get and remove the last item from the collection.
     * 
     * @return mixed
     */
    public function pop(): mixed;

    /**
     * Push an item onto the beginning of the collection.
     * 
     * @param  mixed  $value
     * @param  mixed|null  $key
     * 
     * @return array
     */
    public function prepend(mixed $value, mixed $key = null): array;

    /**
     * Get and remove an item from the collection.
     * 
     * @param  mixed  $key
     * @param  mixed|null  $default
     * 
     * @return mixed
     */
    public function pull(mixed $key, mixed $default = null): mixed;

    /**
     * Push an item onto the end of the collection.
     * 
     * @param  mixed  $values  [optional]
     * 
     * @return static
     */
    public function push(...$values): static;

    /**
     * Put an item in the collection by key.
     * 
     * @param  mixed  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function put(mixed $key, mixed $value): static;

    /**
     * Create a collection with the given range.
     * 
     * @param  int  $from
     * @param  int  $to
     * 
     * @return static
     */
    public function range(int $from, int $to): static;

    /**
     * Reduce the collection to a single value.
     * 
     * @param  \callable  $callback
     * @param  mixed|null  $initial
     * 
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial = null): mixed;

    /**
     * Replace the collection items with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function replace(mixed $items): static;

    /**
     * Recursively replace the collection items with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function replaceRecursive(mixed $items): static;

    /**
     * Reverse items order.
     * 
     * @return static
     */
    public function reverse(): static;

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     * 
     * @param  mixed  $value
     * @param  bool  $strict
     * 
     * @return mixed
     */
    public function search(mixed $value, bool $strict = false): mixed;

    /**
     * Get one or more items randomly from the collection.
     * 
     * @param  int  $amount
     * 
     * @return mixed
     */
    public function random(int $amount = 1): mixed;

    /**
     * Get and remove the first item from the collection.
     * 
     * @return array
     */
    public function shift(): array;
    
    /**
     * Skip the first {$count} items.
     * 
     * @param  int  $count
     * 
     * @return static
     */
    public function skip(int $count): static;

    /**
     * Slice the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length
     * 
     * @return static
     */
    public function slice(int $offset, ?int $length = null): static;

    /**
     * Sort through each item.
     * 
     * @param  \callable|int|null  $callback
     * 
     * @return static
     */
    public function sort($callback = null): static;

    /**
     * Sort items in descending order.
     * 
     * @param  int  $options
     * 
     * @return static
     */
    public function sortDesc(int $options = SORT_REGULAR): static;

    /**
     * Sort the collection keys.
     * 
     * @param  int  $options
     * @param  bool  $descending
     * 
     * @return static
     */
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): static;

    /**
     * Sort the collection keys in descending order.
     * 
     * @param  int  $options
     * 
     * @return static
     */
    public function sortKeysDesc(int $options =  SORT_REGULAR): static;

    /**
     * Sort the collection keys using a callback.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function sortKeysUsing(callable $callback): static;

    /**
     * Splice portion of the underlying collection array.
     * 
     * @param  int  $offset
     * @param  int|null  $length
     * @param  mixed  $replacement
     * 
     * @return static
     */
    public function splice(int $offset, ?int $length = null, mixed $replacement = []): static;

    /**
     * Take the first or last {$limit} items.
     * 
     * @param  int  $limit
     * 
     * @return static
     */
    public function take(int $limit): static;

    /**
     * Transform each item in the collection.
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function transform(callable $callback): static;

    /**
     * Union the collection with the given items.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public function union(mixed $items): static;

    /**
     * Return only unique items from the collection array.
     * 
     * @return static
     */
    public function unique(): static;

    /**
     * Reset the keys on the underlying array.
     * 
     * @return static
     */
    public function values(): static;
    
    /**
     * Get the collection of items as a plain array.
     * 
     * @return array
     */
    public function toArray(): array;
    
    /**
     * Indicate that the model's string representation should be escaped when __toString is invoked.
     * 
     * @param  bool  $escape
     * 
     * @return static
     */
    public function escapeWhenLoadingToString($escape = true): static;

    /**
     * Add a method to the list of proxied methods.
     * 
     * @param  string  $method
     * 
     * @return void
     */
    public static function proxy($method): void;

    /**
     * Magic method.
     * 
     * Dynamically access collection proxies.
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key);

    /**
     * Convert the collection to its string representation.
     * 
     * @return string
     */
    public function __toString(): string;
}