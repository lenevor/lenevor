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

namespace Syscodes\Components\Support\Traits;

use Closure;
use Exception;
use JsonSerializable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Collectable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\HigherOrderCollectionProxy;
use UnitEnum;

use function Syscodes\Components\Support\enum_value;

/**
 * Trait Enumerates.
 */
trait Enumerates
{
    /**
     * Indicates that the object's string representation should be escaped when __toString is invoked.
     * 
     * @var bool
     */
    protected $escapeWhenLoadingToString = false;

    /**
     * The methods that can be proxied.
     * 
     * @var array
     */
    protected static $proxies = [
        'contains',
        'filter',
        'first',
        'flatMap',
        'flip',
        'first',
        'intersect',
        'keyBy',
        'keys',
        'map',
        'merge',
        'pad',
        'partition',
        'pop',
        'reduce',
        'reject',
        'replace',
        'reverse',
        'shift',
        'unique',
        'values',
    ];

    /**
     * Create a new collection instance if the value isn't one already.
     * 
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable|null  $items
     * @param  mixed  $args
     * 
     * @return static
     */
    public static function make($items = [], ...$args): static
    {
        return new static($items, ...$args);
    }

    /**
     * Wrap the given value in a collection if applicable.
     *
     * @param  iterable  $value
     * @param  mixed  $args
     * 
     * @return array
     */
    public static function wrap($value, ...$args)
    {
        return $value instanceof Collectable
            ? new static($value, ...$args)
            : new static(Arr::wrap($value), ...$args);
    }

    /**
     * Get the underlying items from the given collection if applicable.
     *
     * @param  array  $value
     * 
     * @return array
     */
    public static function unwrap($value)
    {
        return $value instanceof Collectable ? $value->all() : $value;
    }

    /**
     * Create a new instance with no items.
     * 
     * @param  mixed  $args
     * 
     * @return static
     */
    public static function empty(...$args): static
    {
        return new static([], ...$args);
    }

    /**
     * Collect the values into a collection.
     *
     * @return \Syscodes\Components\Support\Collection
     */
    public function collect(): Collection
    {
        return new Collection($this->all());
    }
    
    /**
     * Dump the given arguments and terminate execution.
     * 
     * @param  mixed  ...$args
     * 
     * @return never
     */
    public function dd(...$args)
    {
        dd($this->all(), ...$args);
    }
    
    /**
     * Dump the items.
     * 
     * @param  mixed  ...$args
     * 
     * @return static
     */
    public function dump(...$args): static
    {
        dump($this->all(), ...$args);
        
        return $this;
    }
    
    /**
     * Execute a callback over each item.
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function each(callable $callback): static
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        
        return $this;
    }
    
    /**
     * Map a collection and flatten the result by a single level.
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function flatMap(callable $callback): static
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Get the first item by the given key value pair.
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function firstWhere($key, $operator = null, $value = null)
    {
        return $this->first($this->operatorCallback(...func_get_args()));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return static
     */
    public function where($key, $operator = null, $value = null): static
    {
        return $this->filter($this->operatorCallback(...func_get_args()));
    }

    /**
     * Filter items where the value for the given key is null.
     *
     * @param  string|null  $key
     * 
     * @return static
     */
    public function whereNull($key = null): static
    {
        return $this->whereStrict($key, null);
    }

    /**
     * Filter items where the value for the given key is not null.
     *
     * @param  string|null  $key
     * 
     * @return static
     */
    public function whereNotNull($key = null): static
    {
        return $this->where($key, '!==', null);
    }

    /**
     * Filter items by the given key value pair using strict comparison.
     *
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function whereStrict($key, $value): static
    {
        return $this->where($key, '===', $value);
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable  $values
     * @param  bool  $strict
     * 
     * @return static
     */
    public function whereIn($key, $values, $strict = false): static
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(fn ($item) => in_array(data_get($item, $key), $values, $strict));
    }

    /**
     * Filter items by the given key value pair using strict comparison.
     *
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable  $values
     * 
     * @return static
     */
    public function whereInStrict($key, $values): static
    {
        return $this->whereIn($key, $values, true);
    }

    /**
     * Filter items such that the value of the given key is between the given values.
     *
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable  $values
     * 
     * @return static
     */
    public function whereBetween($key, $values): static
    {
        return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
    }

    /**
     * Filter items such that the value of the given key is not between the given values.
     *
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable  $values
     * 
     * @return static
     */
    public function whereNotBetween($key, $values): static
    {
        return $this->filter(
            fn ($item) => data_get($item, $key) < reset($values) || data_get($item, $key) > end($values)
        );
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable  $values
     * @param  bool  $strict
     * 
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false): static
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(fn ($item) => in_array(data_get($item, $key), $values, $strict));
    }

    /**
     * Filter items by the given key value pair using strict comparison.
     *
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|iterable  $values
     * 
     * @return static
     */
    public function whereNotInStrict($key, $values): static
    {
        return $this->whereNotIn($key, $values, true);
    }

    /**
     * Get an operator checker callback.
     * 
     * @param  \callable|string  $key
     * @param  string|null  $operator
     * @param  mixed  $value
     * 
     * @return \Closure
     */
    protected function operatorCallback($key, $operator = null, $value = null)
    {
        if ($this->useAsCallable($key)) return $key;
        
        if (func_num_args() === 1) {
            $value = true;
            
            $operator = '=';
        }
        
        if (func_num_args() === 2) {
            $value = $operator;
            
            $operator = '=';
        }
        
        return function ($item) use ($key, $operator, $value) {
            $retrieved = enum_value(data_get($item, $key));
            $value = enum_value($value);

            $strings = array_filter([$retrieved, $value], function ($value) {
                return match (true) {
                    is_string($value) => true,
                    $value instanceof \Stringable => true,
                    default => false,
                };
            });

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
            }
        };
    }

    /**
     * Pass the collection to the given callback and return the result.
     *
     * @param  callable  $callback
     * 
     * @return callable
     */
    public function pipe(callable $callback): callable
    {
        return $callback($this);
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     * 
     * @param \callable|mixed  $callable
     * 
     * @return static
     */
    public function reject($callback = true): static
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(
            fn($value, $key) => $useAsCallable
                ? ! $callback($value, $key)
                : $value != $callback
        );
    }

    /**
     * Determine if the given value is callable, but not a string.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    protected function useAsCallable(mixed $value): bool
    {
        return ! is_string($value) && is_callable($value);
    }
    
    /**
     * Get the collection of items as a plain array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->map(fn ($value) => $value instanceof Arrayable ? $value->toArray() : $value)->all();
    }
    
    /**
     * Determine if the collection is not empty.
     * 
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }
    
    /**
     * "Paginate" the collection by slicing it into a smaller collection.
     * 
     * @param  int  $page
     * @param  int  $perPage
     * 
     * @return static
     */
    public function forPage(int $page, int $perPage): static
    {
        $offset = max(0, ($page - 1) * $perPage);
        
        return $this->slice($offset, $perPage);
    }

    /**
     * Partition the collection into two arrays using the given callback or key.
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return static[]
     */
    public function partition($key, $operator = null, $value = null): static
    {
        $callback = func_num_args() === 1
            ? $this->valueRetriever($key)
            : $this->operatorCallback(...func_get_args());

        [$passed, $failed] = Arr::partition($this->getIterator(), $callback);

        return new static([new static($passed), new static($failed)]);
    }
    
    /**
     * Get a value retrieving callback.
     * 
     * @param  callable|string|null  $value
     * 
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) return $value;
        
        return fn ($item) => data_get($item, $value);
    }
    
    /**
     * Get the collection of items as JSON.
     * 
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    
    /**
     * Get the collection of items as pretty print formatted JSON.
     * 
     * @param  int  $options
     * 
     * @return string
     */
    public function toPrettyJson(int $options = 0): string
    {
        return $this->toJson(JSON_PRETTY_PRINT | $options);
    }
    
    /**
     * Convert the object into something JSON serializable.
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            return match (true) {
                $value instanceof JsonSerializable => $value->jsonSerialize(),
                $value instanceof Jsonable => json_decode($value->toJson(), true),
                $value instanceof Arrayable => $value->toArray(),
                default => $value,
            };
        }, $this->all());
    }
    
    /**
     * Indicate that the model's string representation should be escaped when __toString is invoked.
     * 
     * @param  bool  $escape
     * 
     * @return stati
     */
    public function escapeWhenLoadingToString($escape = true): static
    {
        $this->escapeWhenLoadingToString = $escape;
        
        return $this;
    }
    
    /**
     * Add a method to the list of proxied methods.
     * 
     * @param  string  $method
     * 
     * @return void
     */
    public static function proxy($method): void
    {
        static::$proxies[] = $method;
    }

    /**
     * Results array of items from Collection.
     * 
     * @param  mixed  $items
     * 
     * @return array
     */
    protected function getArrayableItems($items)
    {
        return is_null($items) || is_scalar($items) || $items instanceof UnitEnum
            ? Arr::wrap($items)
            : Arr::from($items);
    }

    /**
     * Magic method.
     * 
     * Dynamically access collection proxies.
     * 
     * @param  string  $key
     * 
     * @return mixed
     * 
     * @throws \Exception
     */
    public function __get($key): mixed
    {
        if ( ! in_array($key, static::$proxies)) {
            throw new Exception("Property [{$key}] does not exist on this collection instance.");
        }
        
        return new HigherOrderCollectionProxy($this, $key);
    }
    
    /**
     * Magic method.
     * 
     * Convert the collection to its string representation.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->escapeWhenLoadingToString
                    ? e($this->toJson())
                    : $this->toJson();
    }    
}