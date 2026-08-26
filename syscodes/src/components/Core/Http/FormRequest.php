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

namespace Syscodes\Components\Core\Http;

use Syscodes\Components\Auth\Access\Exceptions\AuthorizationException;
use Syscodes\Components\Auth\Access\Response;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Validation\ValidatesResolved;
use Syscodes\Components\Contracts\Validation\Factory as ValidationFactory;
use Syscodes\Components\Contracts\Validation\Validator;
use Syscodes\Components\Core\Http\Attributes\ErrorBag;
use Syscodes\Components\Core\Http\Attributes\FailOnUnknownFields;
use Syscodes\Components\Core\Http\Attributes\StopOnFirstFailure;
use Syscodes\Components\Core\Http\Attributes\RedirectTo;
use Syscodes\Components\Core\Http\Attributes\RedirectToRoute;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Generators\Redirector;
use Syscodes\Components\Validation\Concerns\ValidationWhenResolved;
use ReflectionClass;
use Syscodes\Components\Support\Arr;

/**
 * Gets the form request.
 */
class FormRequest extends Request implements ValidatesResolved
{
    use ValidationWhenResolved;
    
    /**
     * The container instance.
     * 
     * @var \Syscodes\Components\Contracts\Container\Container
     */
    protected $container;
    
    /**
     * The key to be used for the view error bag.
     * 
     * @var string
     */
    protected $errorBag = 'default';
    
    /**
     * The input keys that should not be flashed on redirect.
     * 
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];
    
    /**
     * The redirector instance.
     * 
     * @var \Syscodes\Components\Routing\Generators\Redirector
     */
    protected $redirector;
    
    /**
     * The URI to redirect to if validation fails.
     * 
     * @var string
     */
    protected $redirect;
    
    /**
     * The controller action to redirect to if validation fails.
     * 
     * @var string
     */
    protected $redirectAction;
    
    /**
     * The route to redirect to if validation fails.
     * 
     * @var string
     */
    protected $redirectRoute;

    /**
     * Indicates whether validation should stop after the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = false;

    /**
     * The validator instance.
     *
     * @var \Syscodes\Components\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * Indicates if unknown fields should be rejected for all form requests.
     *
     * @var bool
     */
    protected static bool $globalFailOnUnknownFields = false;
    
    /**
     * Get the validator instance for the request.
     * 
     * @return \Syscodes\Components\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        if ($this->validator) {
            return $this->validator;
        }

        $this->configureFromAttributes();

        $factory = $this->container->make(ValidationFactory::class);
        
        if (method_exists($this, 'validator')) {
            $validator = $this->container->call($this->validator(...), ['factory' => $factory]);
        } else {
            $validator = $this->createDefaultValidator($factory);
        }

        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }

        if (method_exists($this, 'after')) {
            $validator->after($this->container->call(
                $this->after(...),
                ['validator' => $validator]
            ));
        }
        
        $this->setValidator($validator);
        
        return $this->validator;
    }

    /**
     * Configure the form request from class attributes.
     *
     * @return void
     */
    protected function configureFromAttributes(): void
    {
        $reflection = new ReflectionClass($this);

        if ($reflection->getAttributes(StopOnFirstFailure::class) !== []) {
            $this->stopOnFirstFailure = true;
        }

        $redirectTo = $reflection->getAttributes(RedirectTo::class);

        if ($redirectTo !== []) {
            $this->redirect = $redirectTo[0]->newInstance()->url;
        }

        $redirectToRoute = $reflection->getAttributes(RedirectToRoute::class);

        if ($redirectToRoute !== []) {
            $this->redirectRoute = $redirectToRoute[0]->newInstance()->route;
        }

        $errorBag = $reflection->getAttributes(ErrorBag::class);

        if ($errorBag !== []) {
            $this->errorBag = $errorBag[0]->newInstance()->name;
        }
    }

    /**
     * Create the default validator instance.
     *
     * @param  \Syscodes\Components\Contracts\Validation\Factory  $factory
     * @return \Syscodes\Components\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        $rules = $this->validationRules();

        $validator = $factory->make(
            $this->validationData(),
            $rules,
            $this->messages(),
            $this->attributes(),
        )->stopOnFirstFailure($this->stopOnFirstFailure);

        if ($this->isPrecognitive()) {
            $validator->setRules(
                $this->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
            );
        }

        return $validator;
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData(): array
    {
        return $this->all();
    }

    /**
     * Get the validation rules for this form request.
     *
     * @return array
     */
    protected function validationRules()
    {
        return method_exists($this, 'rules') ? $this->container->call([$this, 'rules']) : [];
    }

    /**
     * Determine if fields not present in rules should fail validation.
     *
     * @return bool
     */
    protected function shouldFailOnUnknownFields(): bool
    {
        $failOnUnknownFields = (new ReflectionClass($this))->getAttributes(FailOnUnknownFields::class);

        return $failOnUnknownFields !== []
            ? $failOnUnknownFields[0]->newInstance()->value
            : static::$globalFailOnUnknownFields;
    }

    /**
     * Validate that no unknown fields were sent as input.
     *
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function validateNoUnknownFields(Validator $validator)
    {
        $allowedKeys = array_keys($this->validationRules());

        $input = $this->isJson() ? $this->json()->all() : $this->request->all();

        foreach (array_keys(Arr::dot($input)) as $inputKey) {
            if ( ! $this->isKnownField($inputKey, $allowedKeys)) {
                $validator->errors()->add($inputKey, trans('validation.prohibited', [
                    'attribute' => str_replace('_', ' ', $inputKey),
                ]));
            }
        }
    }

    /**
     * Determine if the given input key is an allowed key based on the validation rules.
     *
     * @param  string  $inputKey
     * @param  array  $allowedKeys
     * @return bool
     */
    protected function isKnownField(string $inputKey, array $allowedKeys): bool
    {
        foreach ($allowedKeys as $ruleKey) {
            if ($ruleKey === $inputKey) {
                return true;
            }

            if (str_ends_with($inputKey, '_confirmation') &&
                $ruleKey === substr($inputKey, 0, -13)) {
                return true;
            }

            if (str_contains($ruleKey, '*')) {
                $pattern = '/^'.str_replace('\*', '[^.]+', preg_quote($ruleKey, '/')).'$/';

                if (preg_match($pattern, $inputKey)) {
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * Handle a failed validation attempt.
     * 
     * @param  \Syscodes\Components\Validation\Validator  $validator 
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        $exception = $validator->getException();

        throw (new $exception($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
    
    /**
     * Get the response for a forbidden operation.
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function forbiddenResponse()
    {
        return new Response('Forbidden', 403);
    }
    
    /**
     * Get the URL to redirect to on a validation error.
     * 
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        $url = $this->redirector->getUrlGenerator();

        return match (true) {
            ! empty($this->redirect) => $url->to($this->redirect),
            ! empty($this->redirectRoute) => $url->route($this->redirectRoute),
            ! empty($this->redirectAction) => $url->action($this->redirectAction),
            default => $url->previous(),
        };
    }

     /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     *
     * @throws \Syscodes\Components\Auth\Access\Exceptions\AuthorizationException
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            $result = $this->container->call([$this, 'authorize']);

            return $result instanceof Response ? $result->authorize() : $result;
        }

        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Syscodes\Components\Auth\Access\Exceptions\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException;
    }

    /**
     * Get a validated input container for the validated input.
     *
     * @param  array<int, string>|null  $keys
     * @return ($keys is array ? array<string, mixed> : \Syscodes\Components\Support\ValidatedInput)
     *
     * @throws \Syscodes\Components\Validation\Exceptions\ValidationException
     */
    public function safe(?array $keys = null)
    {
        return is_array($keys)
            ? $this->validator->safe()->only($keys)
            : $this->validator->safe();
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        return data_get($this->validator->validated(), $key, $default);
    }
    
    /**
     * Set custom messages for validator errors.
     * 
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
    
    /**
     * Set custom attributes for validator errors.
     * 
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

     /**
     * Set the Validator instance.
     *
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * @return static
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }
    
    /**
     * Set the Redirector instance.
     * 
     * @param  \Syscodes\Components\Routing\Generators\Redirector  $redirector 
     * @return static
     */
    public function setRedirector(Redirector $redirector): static
    {
        $this->redirector = $redirector;
        
        return $this;
    }
    
    /**
     * Set the container implementation.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container 
     * @return static
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;
        
        return $this;
    }
}