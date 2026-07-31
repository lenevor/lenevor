<?php

namespace Syscodes\Components\Validation;

use Syscodes\Components\Contracts\Validation\DataAwareRule;
use Syscodes\Components\Contracts\Validation\ImplicitRule;
use Syscodes\Components\Contracts\Validation\InvokableRule;
use Syscodes\Components\Contracts\Validation\Rule;
use Syscodes\Components\Contracts\Validation\ValidatorAwareRule;
use Syscodes\Components\Translation\PotentiallyTranslatedString;

/**
 * 
 */
class InvokableValidationRule implements Rule, ValidatorAwareRule
{
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

        $this->invokable->__invoke($attribute, $value, function ($attribute, $message = null) {
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

    /**
     * Create a pending potentially translated string.
     *
     * @param  string  $attribute
     * @param  string|null  $message
     * @return \Syscodes\Components\Translation\PotentiallyTranslatedString
     */
    protected function pendingPotentiallyTranslatedString($attribute, $message)
    {
        $destructor = $message === null
            ? fn ($message) => $this->messages[] = $message
            : fn ($message) => $this->messages[$attribute] = $message;

        return new class($message ?? $attribute, $this->validator->getTranslator(), $destructor) extends PotentiallyTranslatedString
        {
            /**
             * The callback to call when the object destructs.
             *
             * @var \Closure
             */
            protected $destructor;

            /**
             * Constructor. Create a new pending potentially translated string.
             *
             * @param  string  $message
             * @param  \Syscodes\Components\Contracts\Translation\Translator  $translator
             * @param  \Closure  $destructor
             * @return void
             */
            public function __construct($message, $translator, $destructor)
            {
                parent::__construct($message, $translator);

                $this->destructor = $destructor;
            }

            /**
             * Magic method.
             * 
             * Handle the object's destruction.
             *
             * @return void
             */
            public function __destruct()
            {
                ($this->destructor)($this->toString());
            }
        };
    }
}