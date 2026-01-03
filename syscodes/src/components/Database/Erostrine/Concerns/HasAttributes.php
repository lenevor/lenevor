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

namespace Syscodes\Components\Database\Erostrine\Concerns;

use LogicException;
use Syscodes\Components\Database\Erostrine\Relations\Relation;
use Syscodes\Components\Support\Str;

/**
 * Trait HasAttributes.
 */
trait HasAttributes
{
    /**
     * The model's attributes.
     * 
     * @var array $attributes
     */
    protected $attributes = [];

    /**
	 * The model attribute's original state.
	 * 
	 * @var array $original
	 */
	protected $original = [];

    /**
     * Indicates whether attributes are snake cased on arrays.
     * 
     * @var bool $snakeAttributes
     */
    protected static $snakeAttributes = true;

    /**
     * The cache of the mutated attributes for each class.
     * 
     * @var array $mutatorCache
     */
    protected static $mutatorCache = [];

    /**
     * Get all given attribute on the model.
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get a given attribute on the model.
     * 
     * @param  string  $key
     * 
     * @return array
     */
    public function getAttribute($key)
    {
        if ( ! $key) {
            return;
        }
        
        // If the key references an attribute, return its plain value from the model.
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }
        
        if (method_exists(self::class, $key)) {
            return;
        }
        
        return $this->getRelationValue($key);
    }
    
    /**
     * Set a given attribute on the model.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function setAttribute($key, $value): static
    {
        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studlycaps($key).'Attribute';
            
            return call_user_func([$this, $method], $value);
        }
        
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function hasGetMutator($key): bool
    {
        $method = 'get'.Str::studlycaps($key).'Attribute';

        return method_exists($this, $method);
    }
    
    /**
     * Determine if a set mutator exists for an attribute.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function hasSetMutator($key)
    {
        $method = 'set'.Str::studlycaps($key).'Attribute';
        
        return method_exists($this, $method);
    }

    
    /**
     * Get a plain attribute (not a relationship).
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    protected function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);
        
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }
        
        return $value;
    }
    
    /**
     * Get an attribute from the $attributes array.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }
    
    /**
     * Get the value of an attribute using its mutator.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        $method = 'get'.Str::studlycaps($key).'Attribute';
        
        return call_user_func([$this, $method], $value);
    }
    
    /**
     * Get a relationship.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function getRelationValue($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }
        
        if (method_exists($this, $key)) {
            return $this->getRelationFromMethod($key);
        }
    }

    /**
     * Get a relationship value from a method.
     * 
     * @param  string  $method
     * 
     * @return mixed
     */
    protected function getRelationFromMethod($method)
    {
        $relation = $this->$method();
        
        if ( ! $relation instanceof Relation) {
            if (is_null($relation)) {
                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', 
                    static::class,
                    $method
                ));
            }
            
            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance', static::class, $method
            ));
        }
        
        return take($relation->getResults(), fn ($results) => $this->setRelation($method, $results));
    }
    
    /**
     * Set the array of model attributes. No checking is done.
     * 
     * @param  array  $attributes
     * @param  bool   $sync
     * 
     * @return static
     */
    public function setRawAttributes(array $attributes, $sync = false): static
    {
        $this->attributes = $attributes;
        
        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }
    
    /**
     * Get an attribute array of all arrayable relations.
     * 
     * @return array
     */
    protected function getArrayRelations(): array
    {
        return $this->getArrayableItems($this->relations);
    }
    
    /**
     * Get an attribute array of all arrayable values.
     * 
     * @param  array  $values
     * 
     * @return array
     */
    protected function getArrayableItems(array $values): array
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }
        
        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }
        
        return $values;
    }
    
    /**
     * Determine if the model or given attribute(s) have been modified.
     * 
     * @param  array|string|null  $attributes
     * 
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        $dirty = $this->getDirty();
        
        if (is_null($attributes)) {
            return count($dirty) > 0;
        } else if ( ! is_array($attributes)) {
            $attributes = func_get_args();
        }
        
        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the attributes that have been changed since last sync.
     * 
     * @return array
     */
    public function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if ( ! array_key_exists($key, $this->original)) {
                $dirty[$key] = $value;
            } else if (($value !== $this->original[$key]) && ! $this->originalIsNumericallyEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * Determine if the new and old values for a given key are numerically equivalent.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    protected function originalIsNumericallyEquivalent($key): bool
    {
        $current  = $this->attributes[$key];
        $original = $this->original[$key];
        
        return is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) === 0;
    }
    
    /**
     * Sync the original attributes with the current.
     * 
     * @return static
     */
    public function syncOriginal(): static
    {
        $this->original = $this->getAttributes();
        
        return $this;
    }
}