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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Support\MessageBag;
use Syscodes\Components\Support\ViewErrorBag;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Http\Concerns\HttpResponse;
use Syscodes\Components\Support\Traits\ForwardsCalls;
use Syscodes\Components\Session\Store as SessionStore;
use Syscodes\Components\Contracts\Support\MessageProvider;
use Syscodes\Components\Http\Response\RedirectResponseHeader as BaseRedirectResponse;

/**
 * Redirects to another URL. Sets the redirect header, sends the headers and exits.
 * Can redirect via a Location header or using a Refresh header.
 */
class RedirectResponse extends BaseRedirectResponse
{
    use ForwardsCalls,
        HttpResponse,
        Macroable {
            __call as macroCall;
        }

    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request $request
     */
    protected $request;

    /**
     * The session store implementation.
     * 
     * @var \Syscodes\Components\Session\Store $session
     */
    protected $session;
    
    /**
     * Flash a piece of data to the session.
     * 
     * @param  string|array  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function with($key, $value = null): static
    {
        $key = is_array($key) ? $key : [$key => $value];
        
        foreach ($key as $k => $v) {
            $this->session->flash($k, $v);
        }
        
        return $this;
    }
    
    /**
     * Add multiple cookies to the response.
     * 
     * @param  array  $cookies
     * 
     * @return static
     */
    public function withCookies(array $cookies): static
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }
        
        return $this;
    }
    
    /**
     * Flash a container of errors to the session.
     * 
     * @param  \Syscodes\Components\Contracts\Support\MessageProvider|array|string  $provider
     * @param  string  $key
     * 
     * @return static
     */
    public function withErrors($provider, $key = 'default'): static
    {
        $value  = $this->parseErrors($provider);
        $errors = $this->session->get('errors', new ViewErrorBag);
        
        if ( ! $errors instanceof ViewErrorBag) {
            $errors = new ViewErrorBag;
        }
        
        $this->session->flash(
            'errors', $errors->put($key, $value)
        );
        
        return $this;
    }
    
    /**
     * Parse the given errors into an appropriate value.
     * 
     * @param  \Syscodes\Components\Contracts\Support\MessageProvider|array|string  $provider
     * 
     * @return \Syscodes\Components\Support\MessageBag
     */
    protected function parseErrors($provider)
    {
        return $provider instanceof MessageProvider 
                   ? $provider->getMessageBag() 
                   : new MessageBag((array) $provider);
    }
    
    /**
     * Gets the Request instance.
     * 
     * @return \Syscodes\Components\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets the current Request instance.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
    
    /**
     * Get the session store instance.
     * 
     * @return \Syscodes\Components\Session\Store|null
     */
    public function getSession()
    {
        return $this->session;
    }
    
    /**
     * Set the session store instance.
     * 
     * @param  \Syscodes\Components\Session\Store  $session
     * 
     * @return void
     */
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }
    
    /**
     * Magic method.
     * 
     * Dynamically bind flash data in the session.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }
        
        static::throwBadMethodCallException($method);
    }
}