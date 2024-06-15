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

/**
 * Allows the nested rules compilating the callback into an array of rules.
 */
class NestedRules
{
    /**
     * The callback to execute.
     * 
     * @var callable $callback
     */
    protected $callback;
    
    /**
     * Constructor. Create a new nested rule instance.
     * 
     * @param  callable  $callback
     * 
     * @return void
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }
    
    /**
     * Compile the callback into an array of rules.
     * 
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $data
     * @param  mixed  $context
     * 
     * @return mixed
     */
    public function compile($attribute, $value, $data = null, $context = null)
    {
        $rules = call_user_func($this->callback, $value, $attribute, $data, $context);
        
        $parser = new ValidationRuleParser(
            Arr::undot(Arr::wrap($data))
        );
        
        if (is_array($rules) && ! array_is_list($rules)) {
            $nested = [];
            
            foreach ($rules as $key => $rule) {
                $nested[$attribute.'.'.$key] = $rule;
            }
            
            $rules = $nested;
        } else {
            $rules = [$attribute => $rules];
        }
        
        return $rules;
    }
}