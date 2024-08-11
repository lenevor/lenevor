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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation;

use Syscodes\Components\Support\Str;

/**
 * Get attributes based on message. 
 */
final class Attribute
{
    /**
     * Get alias of a string.
     * 
     * @var string|null $alias
     */
    protected $alias;
    
    /**
     * Get key.
     * 
     * @var string $key 
     */
    protected $key;
    
    /**
     * Get key from the indexes.
     * 
     * @var array $keyIndexes
     */
    protected $keyIndexes = [];
    
    /**
     * Gets other attributes.
     * 
     * @var array $otherAttributes
     */
    protected $otherAttributes = [];
    
    /**
     * Get the primary attribute of validation.
     * 
     * @var Validation|null $primaryAttribute
     */
    protected $primaryAttribute = null;
    
    /**
     * Get required of a rule.
     * 
     * @var bool $required
     */
    protected $required = false;
    
    /**
     * Get a list of rules.
     * 
     * @var array $rules
     */
    protected $rules = [];
    
    /**
     * Get the validation implementation.
     * 
     * @var Validation $validation
     */
    protected $validation;
    
    /**
     * Constructor. Create a new Attribute class instance.
     * 
     * @param  Validation  $validation
     * @param  string  $key
     * @param  string|null  $alias
     * @param  array  $rules
     * 
     * @return void
     */
    public function __construct(
        Validation $validation,
        string $key,
        $alias = null,
        array $rules = []
    ) {
        $this->validation = $validation;
        $this->key        = $key;
        $this->alias      = $alias;
        
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }
    
    /**
     * Set the primary attribute.
     * 
     * @param Attribute $primaryAttribute
     * 
     * @return void
     */
    public function setPrimaryAttribute(Attribute $primaryAttribute): void
    {
        $this->primaryAttribute = $primaryAttribute;
    }
    
    /**
     * Set key indexes.
     * 
     * @param array $keyIndexes
     * 
     * @return void
     */
    public function setKeyIndexes(array $keyIndexes): void
    {
        $this->keyIndexes = $keyIndexes;
    }
    
    /**
     * Get primary attributes.
     * 
     * @return Attribute|null
     */
    public function getPrimaryAttribute()
    {
        return $this->primaryAttribute;
    }
    
    /**
     * Set other attributes.
     * 
     * @param array $otherAttributes
     * 
     * @return void
     */
    public function setOtherAttributes(array $otherAttributes): void
    {
        $this->otherAttributes = [];
        
        foreach ($otherAttributes as $otherAttribute) {
            $this->addOtherAttribute($otherAttribute);
        }
    }
    
    /**
     * Add other attributes.
     * 
     * @param Attribute $otherAttribute
     * 
     * @return void
     */
    public function addOtherAttribute(Attribute $otherAttribute): void
    {
        $this->otherAttributes[] = $otherAttribute;
    }
    
    /**
     * Get other attributes.
     * 
     * @return array
     */
    public function getOtherAttributes(): array
    {
        return $this->otherAttributes;
    }
    
    /**
     * Add rule.
     * 
     * @param Rules $rule
     * 
     * @return void
     */
    public function addRule(Rules $rule): void
    {
        $rule->setAttribute($this);
        $rule->setValidation($this->validation);
        $this->rules[$rule->getKey()] = $rule;
    }
    
    /**
     * Get rule.
     * 
     * @param string $ruleKey
     * 
     * @return bool
     */
    public function getRule(string $ruleKey): bool
    {
        return $this->hasRule($ruleKey) ? $this->rules[$ruleKey] : null;
    }
    
    /**
     * Get rules.
     * 
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
    
    /**
     * Check the $ruleKey has in the rule.
     * 
     * @param string $ruleKey
     * 
     * @return bool
     */
    public function hasRule(string $ruleKey): bool
    {
        return isset($this->rules[$ruleKey]);
    }
    
    /**
     * Set required.
     * 
     * @param boolean $required
     * 
     * @return void
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }
    
    /**
     * Set rule is required.
     * 
     * @return boolean
     */
    public function isRequired(): bool
    {
        return $this->required;
    }
    
    /**
     * Get key.
     * 
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
    
    /**
     * Get key indexes.
     * 
     * @return array
     */
    public function getKeyIndexes(): array
    {
        return $this->keyIndexes;
    }
    
    /**
     * Get value.
     * 
     * @param string|null $key
     * 
     * @return mixed
     */
    public function getValue(string $key = null): mixed
    {
        if ($key && $this->isArrayAttribute()) {
            $key = $this->resolveSiblingKey($key);
        }
        
        if ( ! $key) {
            $key = $this->getKey();
        }
        
        return $this->validation->getValue($key);
    }
    
    /**
     * Get that is array attribute.
     * 
     * @return boolean
     */
    public function isArrayAttribute(): bool
    {
        return count($this->getKeyIndexes()) > 0;
    }
    
    /**
     * Check this attribute is using dot notation.
     * 
     * @return boolean
     */
    public function isUsingDotNotation(): bool
    {
        return strpos($this->getKey(), '.') !== false;
    }
    
    /**
     * Resolve sibling key.
     * 
     * @param string $key
     * 
     * @return string
     */
    public function resolveSiblingKey(string $key): string
    {
        $indexes        = $this->getKeyIndexes();
        $keys           = explode("*", $key);
        $countAsterisks = count($keys) - 1;
        
        if (count($indexes) < $countAsterisks) {
            $indexes = array_merge($indexes, array_fill(0, $countAsterisks - count($indexes), "*"));
        }
        
        $args = array_merge([str_replace("*", "%s", $key)], $indexes);
        
        return call_user_func_array('sprintf', $args);
    }
    
    /**
     * Get humanize key.
     * 
     * @return string
     */
    public function getHumanizedKey(): string
    {
        $primaryAttribute = $this->getPrimaryAttribute();
        
        $key = str_replace('_', ' ', $this->key);
        
        // Resolve key from array validation
        if ($primaryAttribute) {
            $split = explode('.', $key);
            $key   = implode(' ', array_map(function ($word) {
                if (is_numeric($word)) {
                    $word = $word + 1;
                }
                
                return Str::snake($word, ' ');
            }, $split));
        }
        
        return ucfirst($key);
    }
    
    /**
     * Set alias.
     * 
     * @param string $alias
     * 
     * @return void
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }
    
    /**
     * Get alias.
     * 
     * @return string|null
     */
    public function getAlias(): string|null
    {
        return $this->alias;
    }
}