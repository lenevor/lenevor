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

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Translation\Translator;

/**
 * Entry point for the Validation component.
 */
class Validator
{
    /**
     * The container instance.
     * 
     * @var Container $container
     */
    protected Container $container;
    
    /**
     * The array of custom attribute names.
     * 
     * @var array $customAttributes
     */
    protected array $customAttributes = [];
    
    /**
     * The array of custom error messages.
     * 
     * @var array $customMessages
     */
    public array $customMessages = [];
    
    /**
     * The data under validation.
     * 
     * @var array $data
     */
    protected array $data;
    
    /**
     * The default numeric related validation rules.
     * 
     * @var string[] $defaultNumericRules
     */
    protected string|array $defaultNumericRules = ['Numeric', 'Integer', 'Decimal'];
    
    /**
     * The current placeholder for dots in rule keys.
     * 
     * @var string $dotPlaceholder
     */
    protected string $dotPlaceholder;
    
    /**
     * The validation rules that can exclude an attribute.
     * 
     * @var string[] $excludeRules
     */
    protected array $excludeRules = ['Exclude', 'ExcludeIf', 'ExcludeUnless', 'ExcludeWith', 'ExcludeWithout'];
    
    /**
     * The initial rules provided.
     * 
     * @var array $initialRules
     */
    protected array $initialRules;
    
    /**
     * The numeric related validation rules.
     * 
     * @var string[] $numericRules
     */
    protected array $numericRules = ['Numeric', 'Integer', 'Decimal'];
    
    /**
     * The rules to be applied to the data.
     * 
     * @var array $rules
     */
    protected array $rules;
    
    /**
     * The size related validation rules.
     * 
     * @var string[] $sizeRules
     */
    protected array $sizeRules = ['Size', 'Between', 'Min', 'Max', 'Gt', 'Lt', 'Gte', 'Lte'];

    /**
     * The translator instance.
     * 
     * @var Translator $translator
     */
    protected Translator $translator;

    /**
     * Constructor. Create a new Validator class instance.
     * 
     * @param  Translator  $translator
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $attributes
     * 
     * @return void
     */
    public function __construct(
        Translator $translator,
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ) {
        $this->dotPlaceholder = Str::random();

        $this->initialRules = $rules;
        $this->translator = $translator;
        $this->customMessages = $messages;
        $this->data = $this->parseData($data);
        $this->customAttributes = $attributes;
    }
    
    /**
     * Parse the data array, converting dots and asterisks.
     * 
     * @param  array  $data
     * 
     * @return array
     */
    public function parseData(array $data): array
    {
        $newData = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->parseData($value);
            }
            
            $key = str_replace(
                ['.', '*'],
                [$this->dotPlaceholder, '__asterisk__'],
                $key
            );
            
            $newData[$key] = $value;
        }
        
        return $newData;
    }
    
    /**
     * Get the data under validation.
     * 
     * @return array
     */
    public function attributes(): array
    {
        return $this->getData();
    }
    
    /**
     * Get the data under validation.
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Get the value of a given attribute.
     * 
     * @param  string  $attribute
     * 
     * @return mixed
     */
    public function getValue($attribute)
    {
        return Arr::get($this->data, $attribute);
    }
    
    /**
     * Set the value of a given attribute.
     * 
     * @param  string  $attribute
     * @param  mixed  $value
     * 
     * @return void
     */
    public function setValue($attribute, $value)
    {
        Arr::set($this->data, $attribute, $value);
    }
    
    /**
     * Get the validation rules.
     * 
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}