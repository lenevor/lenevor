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

use Closure;
use Exception;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\MessageBag;
use Syscodes\Components\Validation\Messages;

/**
 * Get validation based on message.
 */
final class Validation
{
    use Messages;
    
    /**
     * Gets array of the aliases.
     * 
     * @var array $aliases
     */
    protected $aliases = [];

    /**
     * Gets the attributes.
     * 
     * @var array $attributes
     */
    protected $attributes = [];

    /**
     * Get the errors.
     * 
     * @var ErroBag $errors
     */
    public $errors;

    /**
     * Gets the input.
     * 
     * @var array $inputs
     */
    protected $inputs = [];

    /**
     * Gets the invalid data.
     * 
     * @var array invalidData
     */
    protected $invalidData = [];

    /**
     * Get the message separator.
     * 
     * @var string msgSeparator
     */
    protected $msgSeparator = ':';

    /**
     * The validator implementation.
     * 
     * @var mixed $validator
     */
    protected $validator;

    /**
     * Gets the valid data.
     * 
     * @var array $validData
     */
    protected $validData = [];

    /**
     * Constructor. Create new a Validation class instance.
     * 
     * @param  Validator $validator
     * @param  array $inputs
     * @param  array $rules
     * @param  array $messages
     * 
     * @return void
     */
    public function __construct(
        Validator $validator,
        array $inputs,
        array $rules,
        array $messages = []
    ) {
        $this->validator = $validator;
        $this->inputs    = $this->resolveInputAttributes($inputs);
        $this->messages  = $messages;
        $this->errors    = new MessageBag;
        
        foreach ($rules as $attributeKey => $rules) {
            $this->addAttribute($attributeKey, $rules);
        }
    }
    
    /**
     * Add attribute rules.
     * 
     * @param  string  $key
     * @param  string|array  $rules
     * 
     * @return void
     */
    public function addAttribute(string $attributeKey, $rules): void
    {
        $resolvedRules = $this->resolveRules($rules);
        $attribute     = new Attribute($this, $attributeKey, $this->getAlias($attributeKey), $resolvedRules);
        
        $this->attributes[$attributeKey] = $attribute;
    }
    
    /**
     * Get value given the key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function getValue($key): mixed
    {
        return Arr::get([], $key);
    }
    
    /**
     * Resolve rules.
     * 
     * @param  mixed  $rules
     * 
     * @return array
     */
    protected function resolveRules($rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        $resolvedRules    = [];
        $validatorFactory = $this->getValidator();
        
        foreach ($rules as $i => $rule) {
            if (empty($rule)) {
                continue;
            }
            
            $params = [];
            
            if (is_string($rule)) {
                list($rulename, $params) = $this->parseRule($rule);
                
                $validator = call_user_func_array((array) $validatorFactory, array_merge([$rulename], $params));
            } elseif ($rule instanceof Rules) {
                $validator = $rule;
            } elseif ($rule instanceof Closure) {
                $validator = call_user_func_array((array) $validatorFactory, ['callback', $rule]);
            } else {
                $ruleName = is_object($rule) ? get_class($rule) : gettype($rule);
                $message  = "Rule must be a string, Closure or '".Rules::class."' instance. ".$ruleName." given";
                
                throw new Exception($message);
            }
            
            $resolvedRules[] = $validator;
        }
        
        return $resolvedRules;
    }
    
    /**
     * Parse rules.
     * 
     * @param  string  $rule
     * 
     * @return array
     */
    protected function parseRule(string $rule): array
    {
        $exp      = explode(':', $rule, 2);
        $rulename = $exp[0];
        
        if ($rulename !== 'regex') {
            $params = isset($exp[1])? explode(',', $exp[1]) : [];
        } else {
            $params = [$exp[1]];
        }
        
        return [$rulename, $params];
    }
    
    /**
     * Get Validator class instance.
     * 
     * @return static
     */
    public function getValidator(): static
    {
        return $this->validator;
    }
    
    /**
     * Given $inputs and resolve input attributes.
     * 
     * @param  array  $inputs
     * 
     * @return array
     */
    protected function resolveInputAttributes(array $inputs): array
    {
        $resolvedInputs = [];
        
        foreach ($inputs as $key => $rules) {
            $exp = explode(':', $key);
            
            if (count($exp) > 1) {
                $this->aliases[$exp[0]] = $exp[1];
            }
            
            $resolvedInputs[$exp[0]] = $rules;
        }
        
        return $resolvedInputs;
    }
    
    /**
     * Given $attributeKey and $alias then assign alias.
     * 
     * @param  mixed  $attributeKey
     * @param  mixed  $alias
     * 
     * @return void
     */
    public function setAlias(string $attributeKey, string $alias): void
    {
        $this->aliases[$attributeKey] = $alias;
    }
    
    /**
     * Get attribute alias from given key.
     * 
     * @param  mixed  $attributeKey
     * 
     * @return string|null
     */
    public function getAlias(string $attributeKey): string|null
    {
        return isset($this->aliases[$attributeKey]) ? $this->aliases[$attributeKey] : null;
    }
    
    /**
     * Set attributes aliases.
     * 
     * @param  array  $aliases
     * 
     * @return void
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = array_merge($this->aliases, $aliases);
    }
}