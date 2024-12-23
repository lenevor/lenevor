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

namespace Syscodes\Components\Contracts\Validation;

use Syscodes\Components\Validation\Rules;
use Syscodes\Components\Validation\Validation;

interface Validator
{
    /**
     * Get validator object from given key.
     * 
     * @param  mixed  $key
     * 
     * @return mixed
     */
    public function getValidator($key): mixed;

    /**
     * Register or override existing validator.
     * 
     * @param  mixed  $key
     * @param  \Syscodes\Components\Validation\Rules  $rule
     * 
     * @return void
     */
    public function setValidator(string $key, Rules $rule): void;

    /**
     * Validate the given data against the provided rules.
     * 
     * @param  array  $inputs
     * @param  array  $rules
     * @param  array  $messages
     * 
     * @return void
     */
    public function validate(array $inputs, array $rules, array $messages = []);

    /**
     * Given ruleName and rule to add new validator.
     * 
     * @param  string  $ruleName
     * @param  \Syscodes\Components\Validation\Rules  $rule
     * 
     * @return void
     */
    public function addValidator(string $ruleName, Rules $rule): void;

    /**
     * Set rule can allow to be overrided.
     * 
     * @param  boolean  $status
     * 
     * @return void
     */
    public function allowRuleOverride(bool $status = false): void;

    /**
     * Set this can use humanize keys.
     * 
     * @param  boolean  $useHumanizedKeys
     * 
     * @return void
     */
    public function setUseHumanizedKeys(bool $useHumanizedKeys = true): void;

    /**
     * Get can use humanized Keys value.
     * 
     * @return void
     */
    public function isUsingHumanizedKey(): bool;
}