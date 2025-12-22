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

namespace Syscodes\Components\Support\Traits;

use Exception;
use JsonSerializable;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Collection\Enumerable;
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
     * @var bool $escapeWhenLoadingToString
     */
    protected $escapeWhenLoadingToString = false;

    /**
     * The methods that can be proxied.
     * 
     * @var array<int, string> $proxies
     */
    protected static $proxies = [
        'contains',
        'filter',
        'first',
        'flatMap',
        'flip',
        'intersect',
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
     * @param  mixed  $items
     * 
     * @return static
     */
    public static function make($items = []): static
    {
        return new static($items);
    }

    /**
     * Collect the values into a collection.
     *
     * @return \Syscodes\Components\Support\Collection<TKey, TValue>
     */
    public function collect()
    {
        return new Collection($this->all());
    }
    
    /**
     * Create a new instance with no items.
     * 
     * @return static
     */
    public static function empty(): static
    {
        return new static([]);
    }

    /**
     * Wrap the given value in a collection if applicable.
     *
     * @template TWrapValue
     *
     * @param  iterable  $value
     * 
     * @return array
     */
    public static function wrap($value)
    {
        return $value instanceof Enumerable
            ? new static($value)
            : new static(Arr::wrap($value));
    }

    /**
     * Get the underlying items from the given collection if applicable.
     *
     * @template TUnwrapKey of array-key
     * @template TUnwrapValue
     *
     * @param  array  $value
     * 
     * @return array
     */
    public static function unwrap($value)
    {
        return $value instanceof Enumerable ? $value->all() : $value;
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