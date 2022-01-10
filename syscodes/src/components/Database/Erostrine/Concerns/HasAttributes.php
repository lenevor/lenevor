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

namespace Syscodes\Components\Database\Erostrine\Concerns;

/**
 * Trait HasAttributes.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
    public function getAttribute($key): array
    {
        return $this->attributes[$key];
    }
    
    /**
     * Set a given attribute on the model.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function setAttribute($key, $value): void
    {
        $this->attributes[$key] = $value;
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
     * @return self
     */
    public function syncOriginal(): self
    {
        $this->original = $this->getAttributes();
        
        return $this;
    }


}