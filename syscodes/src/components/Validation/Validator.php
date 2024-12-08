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

use Syscodes\Components\Validation\Exceptions\RuleQuashException;
use Syscodes\Components\Validation\Exceptions\RuleNotFoundException;

/**
 * Entry point for the Validation component.
 */
class Validator
{
    use Traits\Messages,
        Traits\RegisterValidators;
    
    /**
     * Allows the rules override.
     * 
     * @var bool $allowRuleOverride
     */
    protected $allowRuleOverride = false;
    
    /**
     * Allows use humanize keys.
     * 
     * @var bool $useHumanizeKeys
     */
    protected $useHumanizedKeys = true;
    
    /**
     * Gets the validators.
     * 
     * @var array $validators
     */
    protected $validators = [];
        
    /**
     * Constructor. Create new Validator class instance.
     * 
     * @param  array  $messages
     * 
     * @return void
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
        
        $this->registerBaseValidators();
    }
    
    /**
     * Get validator object from given key.
     * 
     * @param  mixed  $key
     * 
     * @return mixed
     */
    public function getValidator($key): mixed
    {
        return isset($this->validators[$key]) ? $this->validators[$key] : null;
    }
    
    /**
     * Register or override existing validator.
     * 
     * @param  mixed  $key
     * @param  \Syscodes\Components\Validation\Rules  $rule
     * 
     * @return void
     */
    public function setValidator(string $key, Rules $rule): void
    {
        $this->validators[$key] = $rule;
        
        $rule->setKey($key);
    }
    
    /**
     * Validate inputs.
     * 
     * @param  array  $inputs
     * @param  array  $rules
     * @param  array  $messages
     * 
     * @return Validation
     */
    public function validate(array $inputs, array $rules, array $messages = []): Validation
    {
        $validation = $this->make($inputs, $rules, $messages);
        $validation->validate();
        
        return $validation;
    }
    
    /**
     * Given inputs, rules and messages to make the Validation class instance.
     * 
     * @param  array  $inputs
     * @param  array  $rules
     * @param  array  $messages
     * 
     * @return Validation
     */
    public function make(array $inputs, array $rules, array $messages = []): Validation
    {
        $messages = array_merge($this->messages, $messages);
        
        return new Validation($this, $inputs, $rules, $messages);
    }
    
    /**
     * Given ruleName and rule to add new validator.
     * 
     * @param  string  $ruleName
     * @param  \Syscodes\Components\Validation\Rules  $rule
     * 
     * @return void
     */
    public function addValidator(string $ruleName, Rules $rule): void
    {
        if ( ! $this->allowRuleOverride && array_key_exists($ruleName, $this->validators)) {
            throw new RuleQuashException(
                "You cannot override a built in rule. You have to rename your rule"
            );
        }
        
        $this->setValidator($ruleName, $rule);
    }
    
    /**
     * Set rule can allow to be overrided.
     * 
     * @param  boolean  $status
     * 
     * @return void
     */
    public function allowRuleOverride(bool $status = false): void
    {
        $this->allowRuleOverride = $status;
    }
    
    /**
     * Set this can use humanize keys.
     * 
     * @param  boolean  $useHumanizedKeys
     * 
     * @return void
     */
    public function setUseHumanizedKeys(bool $useHumanizedKeys = true): void
    {
        $this->useHumanizedKeys = $useHumanizedKeys;
    }
    
    /**
     * Get can use humanized Keys value.
     * 
     * @return void
     */
    public function isUsingHumanizedKey(): bool
    {
        return $this->useHumanizedKeys;
    }
    
    /**
     * Magic method.
     * 
     * Invokes method to make Rule instance.
     * 
     * @param  string  $rule
     * 
     * @return Rules
     * 
     * @throws RuleNotFoundException
     */
    public function __invoke(string $rule): Rules
    {
        $args      = func_get_args();
        $rule      = array_shift($args);
        $params    = $args;
        $validator = $this->getValidator($rule);
        
        if ( ! $validator) {
            throw new RuleNotFoundException("Validator '{$rule}' is not registered", 1);
        }
        
        $clonedValidator = clone $validator;
        $clonedValidator->fillParameters($params);
        
        return $clonedValidator;
    }
}