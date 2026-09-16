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

namespace Syscodes\Components\Validation;

use Syscodes\Components\Contracts\Validation\DataAwareRule;
use Syscodes\Components\Contracts\Validation\ImplicitRule;
use Syscodes\Components\Contracts\Validation\InvokableRule;
use Syscodes\Components\Contracts\Validation\Rule;
use Syscodes\Components\Contracts\Validation\ValidationRule;
use Syscodes\Components\Contracts\Validation\ValidatorAwareRule;
use Syscodes\Components\Translation\Concerns\CreatesPotentiallyTranslatedStrings;

/**
 * Allows the invokable validation rule.
 */
class InvokableValidationRule implements Rule, ValidatorAwareRule
{
    use CreatesPotentiallyTranslatedStrings;
    
    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Indicates if the validation invokable failed.
     *
     * @var bool
     */
    protected $failed = false;

    /** 
     * The invokable that validates the attribute.
     *
     * @var \Syscodes\Components\Contracts\Validation\InvokableRule
     */
    protected $invokable;   

    /**
     * The validation error messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The current validator.
     *
     * @var \Syscodes\Components\Validation\Validator
     */
    protected $validator;    

    /**
     * Constructor. Create a new explicit Invokable validation rule.
     *
     * @param  \Syscodes\Components\Contracts\Validation\InvokableRule  $invokable
     * @return void
     */
    protected function __construct(InvokableRule $invokable)
    {
        $this->invokable = $invokable;
    }

    /**
     * Create a new implicit or explicit Invokable validation rule.
     *
     * @param  \Syscodes\Components\Contracts\Validation\InvokableRule  $invokable
     * @return \Syscodes\Components\Contracts\Validation\Rule
     */
    public static function make($invokable)
    {
        if ($invokable->implicit ?? false) {
            return new class($invokable) extends InvokableValidationRule implements ImplicitRule {};
        }

        return new InvokableValidationRule($invokable);
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
        $this->failed = false;

        if ($this->invokable instanceof DataAwareRule) {
            $this->invokable->setData($this->validator->getData());
        }

        if ($this->invokable instanceof ValidatorAwareRule) {
            $this->invokable->setValidator($this->validator);
        }

        $method = $this->invokable instanceof ValidationRule
            ? 'validate'
            : '__invoke';

        $this->invokable->{$method}($attribute, $value, function ($attribute, $message = null) {
            $this->failed = true;

            return $this->pendingPotentiallyTranslatedString($attribute, $message);
        });

        return ! $this->failed;
    }

    /**
     * Get the underlying invokable rule.
     *
     * @return \Syscodes\Components\Contracts\Validation\InvokableRule
     */
    public function invokable()
    {
        return $this->invokable;
    }

    /**
     * Get the validation error messages.
     *
     * @return array
     */
    public function message(): array
    {
        return $this->messages;
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
     * Set the current validator.
     *
     * @param  \Syscodes\Components\Validation\Validator  $validator
     * @return static
     */
    public function setValidator($validator): static
    {
        $this->validator = $validator;

        return $this;
    }
}