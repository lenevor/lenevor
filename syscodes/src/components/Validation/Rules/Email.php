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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation\Rules;

use InvalidArgumentException;
use Syscodes\Components\Contracts\Validation\DataAwareRule;
use Syscodes\Components\Contracts\Validation\Rule;
use Syscodes\Components\Contracts\Validation\ValidatorAwareRule;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Facades\Validator;
use Syscodes\Components\Support\Traits\Conditionable;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Gets the attribute must be email.
 */
class Email implements Rule, DataAwareRule, ValidatorAwareRule
{
    use Conditionable, Macroable;

    public bool $validateMxRecord = false;
    public bool $preventSpoofing = false;
    public bool $nativeValidation = false;
    public bool $nativeValidationWithUnicodeAllowed = false;
    public bool $rfcCompliant = false;
    public bool $strictRfcCompliant = false;

    /**
     * The validator performing the validation.
     *
     * @var \Syscodes\Components\Validation\Validator
     */
    protected $validator;

    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data;

    /**
     * An array of custom rules that will be merged into the validation rules.
     *
     * @var array
     */
    protected $customRules = [];

    /**
     * The error message after validation, if any.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The callback that will generate the "default" version of the file rule.
     *
     * @var string|array|callable|null
     */
    public static $defaultCallback;

    /**
     * Set the default callback to be used for determining the email default rules.
     *
     * If no arguments are passed, the default email rule configuration will be returned.
     *
     * @param  static|callable|null  $callback
     * @return static|void
     */
    public static function defaults($callback = null)
    {
        if (is_null($callback)) {
            return static::default();
        }

        if ( ! is_callable($callback) && ! $callback instanceof static) {
            throw new InvalidArgumentException('The given callback should be callable or an instance of '.static::class);
        }

        static::$defaultCallback = $callback;
    }

    /**
     * Get the default configuration of the file rule.
     *
     * @return static
     */
    public static function default(): static
    {
        $email = is_callable(static::$defaultCallback)
            ? call_user_func(static::$defaultCallback)
            : static::$defaultCallback;

        return $email instanceof static ? $email : new static;
    }

    /**
     * Ensure that the email is an RFC compliant email address.
     *
     * @param  bool  $strict
     * @return static
     */
    public function rfcCompliant(bool $strict = false): static
    {
        if ($strict) {
            $this->strictRfcCompliant = true;
        } else {
            $this->rfcCompliant = true;
        }

        return $this;
    }

    /**
     * Ensure that the email is a strictly enforced RFC compliant email address.
     *
     * @return static
     */
    public function strict(): static
    {
        return $this->rfcCompliant(true);
    }

    /**
     * Ensure that the email address has a valid MX record.
     *
     * Requires the PHP intl extension.
     *
     * @return static
     */
    public function validateMxRecord(): static
    {
        $this->validateMxRecord = true;

        return $this;
    }

    /**
     * Ensure that the email address is not attempting to spoof another email address using invalid unicode characters.
     *
     * @return static
     */
    public function preventSpoofing(): static
    {
        $this->preventSpoofing = true;

        return $this;
    }

    /**
     * Ensure the email address is valid using PHP's native email validation functions.
     *
     * @param  bool  $allowUnicode
     * @return static
     */
    public function withNativeValidation(bool $allowUnicode = false): static
    {
        if ($allowUnicode) {
            $this->nativeValidationWithUnicodeAllowed = true;
        } else {
            $this->nativeValidation = true;
        }

        return $this;
    }

    /**
     * Specify additional validation rules that should be merged with the default rules during validation.
     *
     * @param  string|array  $rules
     * @return static
     */
    public function rules($rules): static
    {
        $this->customRules = array_merge($this->customRules, Arr::wrap($rules));

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->messages = [];

        if ( ! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return false;
        }

        $validator = Validator::make(
            $this->data,
            [$attribute => $this->buildValidationRules()],
            $this->validator->customMessages,
            $this->validator->customAttributes
        );

        if ($validator->fails()) {
            $this->messages = array_merge($this->messages, $validator->messages()->all());

            return false;
        }

        return true;
    }

    /**
     * Build the array of underlying validation rules based on the current state.
     *
     * @return array
     */
    protected function buildValidationRules(): array
    {
        $rules = [];

        if ($this->rfcCompliant) {
            $rules[] = 'rfc';
        }

        if ($this->strictRfcCompliant) {
            $rules[] = 'strict';
        }

        if ($this->validateMxRecord) {
            $rules[] = 'dns';
        }

        if ($this->preventSpoofing) {
            $rules[] = 'spoof';
        }

        if ($this->nativeValidation) {
            $rules[] = 'filter';
        }

        if ($this->nativeValidationWithUnicodeAllowed) {
            $rules[] = 'filter_unicode';
        }

        if ($rules) {
            $rules = ['email:'.implode(',', $rules)];
        } else {
            $rules = ['email'];
        }

        return array_merge(array_filter($rules), $this->customRules);
    }

    /**
     * Get the validation error message.
     *
     * @return array
     */
    public function message(): array
    {
        return $this->messages;
    }

    /**
     * Set the current validator.
     *
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * @return static
     */
    public function setValidator($validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the current data under validation.
     *
     * @param  array  $data
     * @return static
     */
    public function setData($data): static
    {
        $this->data = $data;

        return $this;
    }
}