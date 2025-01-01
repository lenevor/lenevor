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
use Traversable;
use JsonSerializable;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Collectable;
use Syscodes\Components\Support\HigherOrderCollectionProxy;

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
        'flip',
        'intersect',
        'keys',
        'map',
        'merge',
        'pad',
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
     * Get an operator checker callback.
     * 
     * @param  \callable|string  $key
     * @param  string|null  $operator
     * @param  mixed  $value
     * 
     * @return \Closure
     */
    public function operatorCallback($key, $operator = null, $value = null)
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
            $retrieved = data_get($item, $key);

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
     * Convert the object into something JSON serializable.
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }
            
            return $value;
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
        if (is_array($items)) {
            return $items;
        }
        
        return match (true) {
            $items instanceof Collectable => $items->all(),
            $items instanceof Arrayable => $items->toArray(),
            $items instanceof Traversable => iterator_to_array($items),
            $items instanceof Jsonable => json_decode($items->toJson(), true),
            $items instanceof JsonSerializable => (array) $items->jsonSerialize(),
            default => (array) $items,
        };
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
    public function __get($key)
    {
        if ( ! in_array($key, static::$proxies)) {
            throw new Exception("Property [{$key}] does not exist on this collection instance");
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