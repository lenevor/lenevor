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

namespace Syscodes\Components\Validation;

use Syscodes\Components\Validation\Exceptions\MissingRequiredParameterException;

/**
 * Get the types of rules.
 */
abstract class Rules
{
    /**
     * Get the attribute.
     * 
     * @var Attribute|null $attribute
     */
    protected $attribute;
    
    /**
     * The fillable params.
     * 
     * @var array $fillableParams
     */
    protected $fillableParams = [];
    
    /** 
     * The implicit implementation.
     * 
     * @var bool $implicit
     */
    protected $implicit = false;
    
    /**
     * The key as rule selected.
     * 
     * @var string $key
     */
    protected $key;
    
    /**
     * The message depends of attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute is invalid";
    
    /**
     * The parameters according to the type of rule.
     * 
     * @var array $params
     */
    protected $params = [];

    /**
     * The parameters texts according to the type of rule
     * 
     * @var array $paramsTexts
     */
    protected $paramsTexts = [];
    
    /**
     * The Validation implementation.
     * 
     * @var Validation|null $validation
     */
    protected $validation;

    /**
     * Checks the value to given a rule.
     * 
     * @param  string  $value
     * 
     * @return bool
     */
    abstract public function check($value): bool;
    
    /**
     * Set Validation class instance
     * 
     * @param  Validation  $validation
     * 
     * @return void
     */
    public function setValidation(Validation $validation): void
    {
        $this->validation = $validation;
    }
    
    /**
     * Set key.
     * 
     * @param  string  $key
     * 
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }
    
    /**
     * Get key.
     * 
     * @return string
     */
    public function getKey(): string
    {
        return $this->key ?: get_class($this);
    }
    
    /**
     * Set attribute.
     * 
     * @param  Attribute  $attribute
     * 
     * @return void
     */
    public function setAttribute(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }
    
    /**
     * Get attribute.
     * 
     * @return Attribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
    
    /**
     * Get parameters.
     * 
     * @return array
     */
    public function getParameters(): array
    {
        return $this->params;
    }
    
    /**
     * Set params.
     * 
     * @param array $params
     * 
     * @return static
     */
    public function setParameters(array $params): static
    {
        $this->params = array_merge($this->params, $params);
        
        return $this;
    }
    
    /**
     * Set parameters.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function setParameter(string $key, $value): static
    {
        $this->params[$key] = $value;
        
        return $this;
    }
    
    /**
     * Fill $params to $this->params.
     * 
     * @param  array  $params
     * 
     * @return static
     */
    public function fillParameters(array $params): static
    {
        foreach ($this->fillableParams as $key) {
            if (empty($params)) {
                break;
            }
            
            $this->params[$key] = array_shift($params);
        }
        
        return $this;
    }
    
    /**
     * Get parameter from given $key, return null if it not exists.
     * 
     * @param  string  $key
     * 
     * @return string|null
     */
    public function parameter(string $key): string|null
    {
        return isset($this->params[$key])? $this->params[$key] : null;
    }
    
    /**
     * Set parameter text that can be displayed in error message using ':param_key'.
     * 
     * @param  string  $key
     * @param  string  $text
     * 
     * @return void
     */
    public function setParameterText(string $key, string $text): void
    {
        $this->paramsTexts[$key] = $text;
    }
    
    /**
     * Get $paramsTexts.
     * 
     * @return array
     */
    public function getParametersTexts(): array
    {
        return $this->paramsTexts;
    }
    
    /**
     * Check whether this rule is implicit.
     * 
     * @return bool
     */
    public function isImplicit(): bool
    {
        return $this->implicit;
    }
    
    /**
     * Just alias of setMessage.
     * 
     * @param  string  $message
     * 
     * @return static
     */
    public function message(string $message): static
    {
        return $this->setMessage($message);
    }
    
    /**
     * Set message.
     * 
     * @param  string  $message
     * 
     * @return static
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;
        
        return $this;
    }
    
    /**
     * Get message.
     * 
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
    
    /**
     * Check given $params must be exists.
     * 
     * @param  array  $params
     * 
     * @return void
     * 
     * @throws MissingRequiredParameterException
     */
    protected function requireParameters(array $params): void
    {
        foreach ($params as $param) {
            if ( ! isset($this->params[$param])) {
                $rule = $this->getKey();
                
                throw new MissingRequiredParameterException("Missing required parameter '{$param}' on rule '{$rule}'");
            }
        }
    }
}