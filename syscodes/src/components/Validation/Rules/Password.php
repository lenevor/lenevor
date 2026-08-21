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

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Syscodes\Components\Container\Container;
use Syscodes\Components\Contracts\Validation\Rule;
use Syscodes\Components\Contracts\Validation\DataAwareRule;
use Syscodes\Components\Contracts\Validation\ImplicitRule;
use Syscodes\Components\Contracts\Validation\ValidatorAwareRule;
use Syscodes\Components\Contracts\Validation\UncompromisedCheck;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Traits\Conditionable;
use Syscodes\Components\Support\Facades\Validator;

class Password implements DataAwareRule, ImplicitRule, IteratorAggregate, Rule, ValidatorAwareRule
{
    use Conditionable;

    /**
     * The number of times a password can appear in data leaks before being considered compromised.
     *
     * @var int
     */
    protected $compromisedThreshold = 0;

    /**
     * Additional validation rules that should be merged into the default rules during validation.
     *
     * @var array
     */
    protected $customRules = [];

    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data;

    /**
     * The callback that will generate the "default" version of the password rule.
     *
     * @var string|array|callable|null
     */
    public static $defaultCallback;   

    /**
     * If the password requires at least one letter.
     *
     * @var bool
     */
    protected $letters = false;

    /**
     * The maximum size of the password.
     *
     * @var int
     */
    protected $max; 

    /**
     * The minimum size of the password.
     *
     * @var int
     */
    protected $min = 8;   

    /**
     * If the password requires at least one uppercase and one lowercase letter.
     *
     * @var bool
     */
    protected $mixedCase = false;

    /**
     * The failure messages, if any.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * If the password requires at least one number.
     *
     * @var bool
     */
    protected $numbers = false;

    /**
     * If the password is required.
     *
     * @var bool
     */
    protected $required = false;

    /**
     * If the password should only be validated when present.
     *
     * @var bool
     */
    protected $sometimes = false;

    /**
     * If the password requires at least one symbol.
     *
     * @var bool
     */
    protected $symbols = false;

    /**
     * If the password should not have been compromised in data leaks.
     *
     * @var bool
     */
    protected $uncompromised = false;

    /**
     * The validator performing the validation.
     * 
     * @var \Syscodes\Components\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * Constructor. Create a new rule instance.
     *
     * @param  int  $min
     * @return void
     */
    public function __construct($min)
    {
        $this->min = max((int) $min, 1);
    }

    /**
     * Set the default callback to be used for determining a password's default rules.
     *
     * If no arguments are passed, the default password rule configuration will be returned.
     *
     * @param  static|callable|null  $callback
     * @return static|null
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
     * Get the default configuration of the password rule.
     *
     * @return static
     */
    public static function default()
    {
        $password = is_callable(static::$defaultCallback)
            ? call_user_func(static::$defaultCallback)
            : static::$defaultCallback;

        return $password instanceof Rule ? $password : static::min(8);
    }

    /**
     * Get the default configuration of the password rule and mark the field as required.
     *
     * @return array
     */
    public static function required(): array
    {
        return ['required', static::default()];
    }

    /**
     * Get the default configuration of the password rule and mark the field as sometimes being required.
     *
     * @return array
     */
    public static function sometimes()
    {
        return ['sometimes', static::default()];
    }

    /**
     * Set the performing validator.
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
     * Set the data under validation.
     *
     * @param  array  $data
     * @return static
     */
    public function setData($data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the minimum size of the password.
     *
     * @param  int  $size
     * @return static
     */
    public static function min($size): static
    {
        return new static($size);
    }

    /**
     * Ensures the password has not been compromised in data leaks.
     *
     * @param  int  $threshold
     * @return static
     */
    public function uncompromised($threshold = 0): static
    {
        $this->uncompromised = true;

        $this->compromisedThreshold = $threshold;

        return $this;
    }

    /**
     * Makes the password require at least one uppercase and one lowercase letter.
     *
     * @return static
     */
    public function mixedCase(): static
    {
        $this->mixedCase = true;

        return $this;
    }

    /**
     * Makes the password require at least one letter.
     *
     * @return static
     */
    public function letters(): static
    {
        $this->letters = true;

        return $this;
    }

    /**
     * Makes the password require at least one number.
     *
     * @return static
     */
    public function numbers(): static
    {
        $this->numbers = true;

        return $this;
    }

    /**
     * Makes the password require at least one symbol.
     *
     * @return static
     */
    public function symbols(): static
    {
        $this->symbols = true;

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
        $this->customRules = Arr::wrap($rules);

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

        $validator = Validator::make(
            $this->data,
            [$attribute => array_merge(['string', 'min:'.$this->min], $this->customRules)],
            $this->validator->customMessages,
            $this->validator->customAttributes
        )->after(function ($validator) use ($attribute, $value) {
            if ( ! is_string($value)) {
                return;
            }

            $value = (string) $value;

            if ($this->mixedCase && ! preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one uppercase and one lowercase letter.');
            }

            if ($this->letters && ! preg_match('/\pL/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one letter.');
            }

            if ($this->symbols && ! preg_match('/\p{Z}|\p{S}|\p{P}/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one symbol.');
            }

            if ($this->numbers && ! preg_match('/\pN/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one number.');
            }
        });

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        if ($this->uncompromised && ! Container::getInstance()->make(UncompromisedCheck::class)->check([
            'value' => $value,
            'threshold' => $this->compromisedThreshold,
        ])) {
            return $this->fail(
                'The given :attribute has appeared in a data leak. Please choose a different :attribute.'
            );
        }

        return true;
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
     * Adds the given failures, and return false.
     *
     * @param  array|string  $messages
     * @return bool
     */
    protected function fail($messages): bool
    {
        $this->messages = array_merge($this->messages, Arr::wrap($messages));

        return false;
    }

    /**
     * Get an iterator for the password validation rules.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator([
            ...($this->required ? ['required'] : []),
            ...($this->sometimes ? ['sometimes'] : []),
            'string',
            'min:'.$this->min,
            ...($this->max ? ['max:'.$this->max] : []),
            ...$this->customRules,
        ]);
    }
}