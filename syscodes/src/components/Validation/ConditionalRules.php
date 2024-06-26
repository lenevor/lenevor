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

use Syscodes\Components\Support\Flowing;

/**
 * Allows generate conditionals in the rules.
 */
class ConditionalRules
{
    /**
     * The boolean condition indicating if the rules should be added to the attribute.
     * 
     * @var callable|bool $condition
     */
    protected $condition;
    
    /**
     * The rules to be added to the attribute.
     * 
     * @var \Closure|array|string
     */
    protected $rules;
    
    /**
     * The rules to be added to the attribute if the condition fails.
     * 
     * @var \Closure|array|string $defaultRules
     */
    protected $defaultRules;
    
    /**
     * Create a new conditional rules instance.
     * 
     * @param  callable|bool  $condition
     * @param  \Closure|array|string  $rules
     * @param  \Closure|array|string  $defaultRules
     * 
     * @return void
     */
    public function __construct($condition, $rules, $defaultRules = [])
    {
        $this->condition    = $condition;
        $this->rules        = $rules;
        $this->defaultRules = $defaultRules;
    }
    
    /**
     * Determine if the conditional rules should be added.
     * 
     * @param  array  $data
     * 
     * @return bool
     */
    public function passes(array $data = [])
    {
        return is_callable($this->condition)
                    ? call_user_func($this->condition, new Flowing($data))
                    : $this->condition;
    }
    
    /**
     * Get the rules.
     * 
     * @param  array  $data
     * 
     * @return array
     */
    public function rules(array $data = []): array
    {
        return is_string($this->rules)
                    ? explode('|', $this->rules)
                    : value($this->rules, new Flowing($data));
    }
    
    /**
     * Get the default rules.
     * 
     * @param  array  $data
     * 
     * @return array
     */
    public function defaultRules(array $data = []): array
    {
        return is_string($this->defaultRules)
                    ? explode('|', $this->defaultRules)
                    : value($this->defaultRules, new Flowing($data));
    }
}