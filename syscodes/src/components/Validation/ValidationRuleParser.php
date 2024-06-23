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
 * Allows the validation rule parser.
 */
class ValidationRuleParser
{
    /**
     * The data under validation.
     * 
     * @var array $data
     */
    public array $data;  
    
    /**
     * The implicit attributes.
     * 
     * @var array $implicitAttributes
     */
    public $implicitAttributes = [];

    /**
     * Constructor. Create a new ValidatorRuleParser class instance.
     * 
     * @param  array  $data
     * 
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Parse the human-friendly rules into a full rules array for the validator.
     * 
     * @param  array  $rules
     * 
     * @return \stdClass
     */
    public function explode($rules)
    {
        $this->implicitAttributes = [];
        
        $rules = $this->explodeRules($rules);
        
        return (object) [
            'rules' => $rules,
            'implicitAttributes' => $this->implicitAttributes,
        ];
    }
    
    /**
     * Explode the rules into an array of explicit rules.
     * 
     * @param  array  $rules
     * 
     * @return array
     */
    protected function explodeRules($rules): array
    {
        foreach ($rules as $key => $rule) {
            if (Str::contains($key, '*')) {
                $rules = $this->explodeWildcardRules($rules, $key, [$rule]);
                unset($rules[$key]);
            } else {
                $rules[$key] = $this->explodeExplicitRule($rule, $key);
            }
        }
        
        return $rules;
    }

    /**
     * Define a set of rules that apply to each element in an array attribute.
     * 
     * @param  array  $results
     * @param  string  $attributes
     * @param  string[]  $rules
     * 
     * @return array
     */
    protected function explodeWildcardRules($results, $attributes, $rules): array
    {
        $pattern = str_replace('\*', '[^\.]*', preg_quote($attributes, '/'));

        foreach ($this->data as $key => $value) {
            if (Str::startsWith($key, $attributes) || (bool) preg_match('/^'.$pattern.'\z/', $key)) {

            }
        }
    }
    
    /**
     * Explode the explicit rule into an array if necessary.
     * 
     * @param  mixed  $rule
     * @param  string  $attribute
     * 
     * @return array
     */
    protected function explodeExplicitRule($rule, $attribute): array
    {
        if (is_string($rule)) {
            return explode('|', $rule);
        }
    }
}