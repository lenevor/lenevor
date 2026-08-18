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

use Syscodes\Components\Auth\Access\Response;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Validation\ValidatesResolved;
use Syscodes\Components\Contracts\Validation\Factory as ValidationFactory;
use Syscodes\Components\Contracts\Validation\Validator;
use Syscodes\Components\Core\Http\Attributes\ErrorBag;
use Syscodes\Components\Core\Http\Attributes\StopOnFirstFailure;
use Syscodes\Components\Core\Http\Attributes\RedirectTo;
use Syscodes\Components\Core\Http\Attributes\RedirectToRoute;
use Syscodes\Components\Http\Exceptions\HttpResponseException;
use Syscodes\Components\Http\JsonResponse;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Generators\Redirector;
use Syscodes\Components\Validation\Concerns\ValidationWhenResolved;
use ReflectionClass;

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
        )
        ->stopOnFirstFailure($this->stopOnFirstFailure);

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
     * Determine if the request passes the authorization check.
     * 
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            $result = $this->container->call([$this, 'authorize']);

            return $result instanceof Response ? $result->authorize() : $result;
        }
        
        return false;
    }
    
    /**
     * Handle a failed authorization attempt.
     * 
     * @return mixed
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException($this->forbiddenResponse());
    }
    
    /**
     * Get the proper failed validation response for the request.
     * 
     * @param  array  $errors 
     * @return \Syscodes\Components\Http\Response
     */
    public function response(array $errors)
    {
        if ($this->ajax() || $this->wantsJson()) {
            return new JsonResponse($errors, 422);
        }
        
        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
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