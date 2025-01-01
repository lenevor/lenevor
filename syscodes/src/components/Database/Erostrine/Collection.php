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

namespace Syscodes\Components\Database\Erostrine;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Support\Collection as BaseCollection;

/**
 * Generate a collection for to exposes registers database.
 */
class Collection extends BaseCollection
{
    /**
     * Find a model in the collection by key.
     * 
     * @param  mixed  $key
     * @param  mixed  $default
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function find($key, $default = null)
    {
        if ($key instanceof Model) {
            $key = $key->getKey();
        }
        
        if ($key instanceof Arrayable) {
            $key = $key->toArray();
        }
        
        return Arr::first($this->items, fn ($model) => $model->getKey() == $key, $default);
    }
    
    /**
     * Load a set of relationships onto the collection.
     * 
     * @param  mixed  $relations
     * 
     * @return static
     */
    public function load($relations): static
    {
        if (count($this->items) > 0) {
            if (is_string($relations)) $relations = func_get_args();
            
            $query = $this->first()->newQuery()->with($relations);
            
            $this->items = $query->eagerLoadRelations($this->items);
        }
        
        return $this;
    }
    
    /**
     * Add an item to the collection.
     * 
     * @param  mixed  $item
     * 
     * @return static
     */
    public function add($item): static
    {
        $this->items[] = $item;
        
        return $this;
    }
    
    /**
     * Determine if a key exists in the collection.
     * 
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function contains($key, $operator = null, $value = null): bool
    {
        if (func_num_args() > 1 || $this->useAsCallable($key)) {
            return parent::contains(...func_get_args());
        }

        if ($key instanceof Model) {
            return parent::contains(fn ($model) => $model->is($key));
        }
        
        return parent::contains(fn ($model) => $model->getKey() == $key);
    }

    /**
     * Fetch a nested element of the collection.
     * 
     * @param  string  $key
     * 
     * @return static
     */
    public function fetch($key): static
    {
        return new static(Arr::fetch($this->toArray(), $key));
    }
    
    /**
     * Get the max value of a given key.
     * 
     * @param  string  $key
     *
     * @return mixed
     */
    public function max($key)
    {
        return $this->reduce(fn($result, $item) => (is_null($result) || $item->{$key} > $result) ? $item->{$key} : $result);
    }
    
    /**
     * Get the min value of a given key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function min($key)
    {
        return $this->reduce(fn ($result, $item) => (is_null($result) || $item->{$key} < $result) ? $item->{$key} : $result);
    }
    
    /**
     * Get the array of primary keys.
     * 
     * @return array
     */
    public function modelKeys(): array
    {
        return array_map(fn ($m) => $m->getKey(), $this->items);
    }
    
    /**
     * Merge the collection with the given items.
     * 
     * @param  \ArrayAccess|array  $items
     * 
     * @return static
     */
    public function merge($items): static
    {
        $dictionary = $this->getDictionary();
        
        foreach ($items as $item) {
            $dictionary[$item->getKey()] = $item;
        }
        
        return new static(array_values($dictionary));
    }
    
    /**
     * Diff the collection with the given items.
     * 
     * @param  \ArrayAccess|array  $items
     * 
     * @return static
     */
    public function diff($items): static
    {
        $diff = new static;
        
        $dictionary = $this->getDictionary($items);
        
        foreach ($this->items as $item) {
            if ( ! isset($dictionary[$item->getKey()])) {
                $diff->add($item);
            }
        }
        
        return $diff;
    }
    
    /**
     * Intersect the collection with the given items.
     * 
     * @param  \ArrayAccess|array  $items
     * 
     * @return static
     */
    public function intersect($items): static
    {
        $intersect = new static;
        
        $dictionary = $this->getDictionary($items);
        
        foreach ($this->items as $item) {
            if (isset($dictionary[$item->getKey()])) {
                $intersect->add($item);
            }
        }
        
        return $intersect;
    }
    
    /**
     * Return only unique items from the collection.
     * 
     * @return static
     */
    public function unique(): static
    {
        $dictionary = $this->getDictionary();
        
        return new static(array_values($dictionary));
    }
    
    /**
     * Returns only the models from the collection with the specified keys.
     * 
     * @param  mixed  $keys
     * 
     * @return static
     */
    public function only($keys): static
    {
        $dictionary = Arr::only($this->getDictionary(), $keys);
        
        return new static(array_values($dictionary));
    }
    
    /**
     * Returns all models in the collection except the models with specified keys.
     * 
     * @param  mixed  $keys
     * 
     * @return static
     */
    public function except($keys): static
    {
        $dictionary = Arr::except($this->getDictionary(), $keys);
        
        return new static(array_values($dictionary));
    }
    
    /**
     * Get a dictionary keyed by primary keys.
     * 
     * @param  \ArrayAccess|array  $items
     * 
     * @return array
     */
    public function getDictionary($items = null): array
    {
        $items = is_null($items) ? $this->items : $items;
        
        $dictionary = [];
        
        foreach ($items as $value) {
            $dictionary[$value->getKey()] = $value;
        }
        
        return $dictionary;
    }
    
    /**
     * Get an array with the values of a given key.
     * 
     * @param string|array|int|null  $value
     * @param  string|null  $key
     * 
     * @return \Syscodes\Components\Collections\Collection
     */
    public function pluck($value, ?string $key = null): static
    {
        return $this->toBase()->pluck($value, $key);
    }
    
    /**
     * Get a base Support collection instance from this collection.
     * 
     * @return \Syscodes\Components\Collections\Collection
     */
    public function toBase()
    {
        return new BaseCollection($this->items);
    }
}