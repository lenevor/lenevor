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
     * @var MessageBag $errors
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
     * Get attribute by key.
     * 
     * @param  string  $attributeKey
     * 
     * @return string|null
     */
    public function getAttribute(string $attributeKey): string|null
    {
        return isset($this->attributes[$attributeKey])? $this->attributes[$attributeKey] : null;
    }
    
    /**
     * Add error to the errors.
     * 
     * @param  Attribute  $attribute
     * @param  mixed  $value
     * @param  Rules  $ruleValidator
     * 
     * @return void
     */
    protected function addError(Attribute $attribute, $value, Rules $ruleValidator): void
    {
        $ruleName = $ruleValidator->getKey();
        $message  = $this->resolveMessage($attribute, $value, $ruleValidator);
        
        $this->errors->add($attribute->getKey(), $ruleName, $message);
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
     * Resolve message.
     * 
     * @param  Attribute  $attribute
     * @param  mixed  $value
     * @param  Rules  $validator
     * 
     * @return string
     */
    protected function resolveMessage(Attribute $attribute, $value, Rules $validator): string
    {
        $primaryAttribute = $attribute->getPrimaryAttribute();
        $params           = array_merge($validator->getParameters(), $validator->getParametersTexts());
        $attributeKey     = $attribute->getKey();
        $ruleKey          = $validator->getKey();
        $alias            = $attribute->getAlias() ?: $this->resolveAttributeName($attribute);
        $message          = $validator->getMessage(); // default rule message
        $messageKeys      = [
            $attributeKey.$this->msgSeparator.$ruleKey,
            $attributeKey,
            $ruleKey,
        ];
        
        if ($primaryAttribute) {
            $primaryAttributeKey = $primaryAttribute->getKey();
            array_splice($messageKeys, 1, 0, $primaryAttributeKey.$this->msgSeparator.$ruleKey);
            array_splice($messageKeys, 3, 0, $primaryAttributeKey);
        }
        
        foreach ($messageKeys as $key) {
            if (isset($this->messages[$key])) {
                $message = $this->messages[$key];
                break;
            }
        }
        
        // Replace message params
        $vars = array_merge($params, [
            'attribute' => $alias,
            'value' => $value,
        ]);
        
        foreach ($vars as $key => $value) {
            $value   = $this->stringify($value);
            $message = str_replace(':'.$key, $value, $message);
        }
        
        // Replace key indexes
        $keyIndexes = $attribute->getKeyIndexes();
        
        foreach ($keyIndexes as $pathIndex => $index) {
            $replacers = [
                "[{$pathIndex}]" => $index,
            ];
            
            if (is_numeric($index)) {
                $replacers["{{$pathIndex}}"] = $index + 1;
            }
            
            $message = str_replace(array_keys($replacers), array_values($replacers), $message);
        }
        
        return $message;
    }
    
    /**
     * Resolve attribute name.
     * 
     * @param  Attribute  $attribute
     * 
     * @return string
     */
    protected function resolveAttributeName(Attribute $attribute): string
    {
        $primaryAttribute = $attribute->getPrimaryAttribute();
        
        if (isset($this->aliases[$attribute->getKey()])) {
            return $this->aliases[$attribute->getKey()];
        } elseif ($primaryAttribute and isset($this->aliases[$primaryAttribute->getKey()])) {
            return $this->aliases[$primaryAttribute->getKey()];
        } elseif ($this->validator->isUsingHumanizedKey()) {
            return $attribute->getHumanizedKey();
        } else {
            return $attribute->getKey();
        }
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
     * Check validations are passed.
     * 
     * @return bool
     */
    public function passes(): bool
    {
        return $this->errors->count() == 0;
    }
    
    /**
     * Check validations are failed.
     * 
     * @return bool
     */
    public function fails(): bool
    {
        return ! $this->passes();
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
        return Arr::get($this->inputs, $key);
    }

    /**
     * Set value given the key and check value is existed.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function setValue(string $key, mixed $value): void
    {
        Arr::set($this->inputs, $key, $value);
    }

    /**
     * Given key and check value is existed.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function hasValue(string $key): bool
    {
        return Arr::has($this->inputs, $key);
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
    
    /**
     * Stringify value.
     * 
     * @param  mixed  $value
     * 
     * @return string
     */
    protected function stringify($value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return $value;
        } elseif (is_array($value) || is_object($value)) {
            return json_encode($value);
        } else {
            return '';
        }
    }

    /**
     * Get validated data.
     *
     * @return array
     */
    public function getValidatedData(): array
    {
        return array_merge($this->validData, $this->invalidData);
    }

    /**
     * Get valid data.
     * 
     * @return array
     */
    public function getValidData(): array
    {
        return $this->validData;
    }
    
    /**
     * Set valid data.
     * 
     * @param  Attribute  $attribute
     * @param  mixed  $value
     * 
     * @return void
     */
    protected function setValidData(Attribute $attribute, $value): void
    {
        $key = $attribute->getKey();
        
        if ($attribute->isArrayAttribute() || $attribute->isUsingDotNotation()) {
            Arr::set($this->validData, $key, $value);
            Arr::erase($this->invalidData, $key);
        } else {
            $this->validData[$key] = $value;
        }
    }

    /**
     * Get invalid data.
     * 
     * @return void
     */
    public function getInvalidData(): array
    {
        return $this->invalidData;
    }
    
    /**
     * Set invalid data.
     * 
     * @param  Attribute  $attribute
     * @param  mixed  $value
     * 
     * @return void
     */
    protected function setInvalidData(Attribute $attribute, $value): void
    {
        $key = $attribute->getKey();
        
        if ($attribute->isArrayAttribute() || $attribute->isUsingDotNotation()) {
            Arr::set($this->invalidData, $key, $value);
            Arr::erase($this->validData, $key);
        } else {
            $this->invalidData[$key] = $value;
        }
    }
    
    /**
     * The MessageBag instance.
     * 
     * @return MessageBag
     */
    public function errors(): MessageBag
    {
        return $this->errors;
    }
}