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

use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Validation\ValidatesResolved;
use Syscodes\Components\Contracts\Validation\Validator;
use Syscodes\Components\Http\Exceptions\HttpResponseException;
use Syscodes\Components\Http\JsonResponse;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Routing\Generators\Redirector;
use Syscodes\Components\Validation\Traits\ValidationWhenResolved;

/**
 * Gets the form request.
 */
class FormRequest extends Request implements ValidatesResolved
{
    use ValidationWhenResolved;
    
    /**
     * The container instance.
     * 
     * @var \Syscodes\Compopnents\Contracts\Container\Container $container
     */
    protected $container;
    
    /**
     * The key to be used for the view error bag.
     * 
     * @var string $errorBag
     */
    protected $errorBag = 'default';
    
    /**
     * The input keys that should not be flashed on redirect.
     * 
     * @var array $dontFlash
     */
    protected $dontFlash = ['password', 'password_confirmation'];
    
    /**
     * The redirector instance.
     * 
     * @var \Syscodes\Components\Routing\Generators\Redirector $redirector
     */
    protected $redirector;
    
    /**
     * The URI to redirect to if validation fails.
     * 
     * @var string $redirect
     */
    protected $redirect;
    
    /**
     * The controller action to redirect to if validation fails.
     * 
     * @var string $redirectAction
     */
    protected $redirectAction;
    
    /**
     * The route to redirect to if validation fails.
     * 
     * @var string $redirectRoute
     */
    protected $redirectRoute;
    
    /**
     * Get the validator instance for the request.
     * 
     * @return \Syscodes\Components\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $validation = $this->container->make(Validator::class);
        
        if (method_exists($this, 'validator')) {
            return $this->container->call([$this, 'validator'], compact('factory'));
        }
        
        $rules = $this->container->call([$this, 'rules']);
        
        return $validation->make($this->all(), $rules, $this->messages(), $this->attributes());
    }
    
    /**
     * Handle a failed validation attempt.
     * 
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * 
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->response(
            $this->formatErrors($validator)
        ));
    }
    
    /**
     * Determine if the request passes the authorization check.
     * 
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->container->call([$this, 'authorize']);
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
     * 
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
     * Format the errors from the given Validator instance.
     * 
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * 
     * @return array
     */
    protected function formatErrors(Validator $validator): array
    {
        return $validator->getMessageBag()->toArray();
    }
    
    /**
     * Get the URL to redirect to on a validation error.
     * 
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        $url = $this->redirector->getUrlGenerator();
        
        if ($this->redirect) {
            return $url->to($this->redirect);
        } else if ($this->redirectRoute) {
            return $url->route($this->redirectRoute);
        } else if ($this->redirectAction) {
            return $url->action($this->redirectAction);
        }
        
        return $url->previous();
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
     * Set the Redirector instance.
     * 
     * @param  \Syscodes\Components\Routing\Generators\Redirector  $redirector
     * 
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
     * 
     * @return static
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;
        
        return $this;
    }
}