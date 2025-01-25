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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Generators;

use Syscodes\Components\Http\RedirectResponse;
use Syscodes\Components\Session\Store as SessionStore;

/**
 * Returns redirect of the routes defined by the user.
 */
class Redirector
{
    /**
     * The URL generator instance.
     * 
     * @var object $generator
     */
    protected $generator;
    
    /**
     * The session store instance.
     * 
     * @var \Syscodes\Components\Session\Store
     */
    protected $session;

    /**
     * Constructor. The Redirector class instance.
     * 
     * @param  \Syscodes\Components\Routing\Generators\UrlGenerator  $generator
     * 
     * @return void
     */
    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Create a new redirect response to the "home" route.
     * 
     * @param  int  $status  
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function home($status = 302)
    {
        return $this->to($this->generator->route('home'), $status);
    }

    /**
     * Create a new redirect response to the previous location.
     * 
     * @param  int  $status  
     * @param  array  $headers
     * @param  mixed  $fallback
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function back($status = 302, $headers = [], $fallback = false)
    {
        return $this->createRedirect($this->generator->previous($fallback), $status, $headers);
    }

    /**
     * Create a new redirect response to the current URI.
     * 
     * @param  int  $status  
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function refresh($status = 302, $headers = [])
    {
        return $this->to($this->generator->getRequest()->path(), $status, $headers);
    }
    
    /**
     * Create a new redirect response, while putting the current URL in the session.
     * 
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function guest(
        $path, 
        $status = 302, 
        $headers = [], 
        $secure = null
    ) {
        $request = $this->generator->getRequest();
        
        $intended = $request->isMethod('GET') && $request->route()
                        ? $this->generator->full()
                        : $this->generator->previous();
                        
        if ($intended) {
            $this->setIntendedUrl($intended);
        }
        
        return $this->to($path, $status, $headers, $secure);
    }
    
    /**
     * Create a new redirect response to the previously intended location.
     * 
     * @param  mixed  $default
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function intended(
        $default = '/', 
        $status = 302, 
        $headers = [], 
        $secure = null
    ) {
        $path = $this->session->pull('url.intended', $default);
        
        return $this->to($path, $status, $headers, $secure);
    }

    /**
     * Create a new redirect response to the given path.
     * 
     * @param  string  $path
     * @param  int  $status  
     * @param  array  $headers
     * @param  bool|null  $secure  
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function to(
        $path, 
        $status = 302, 
        $headers = [], 
        $secure = null
    ) {
        return $this->createRedirect($this->generator->to($path, [], $secure), $status, $headers);
    }

    /**
     * Create a new redirect response to an external URL (no validation).
     * 
     * @param  string  $path
     * @param  int  $status  
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function away($path, $status = 302, $headers = [])
    {
        return $this->createRedirect($path, $status, $headers);
    }

    /**
     * Create a new redirect response to the given HTTPS path.
     * 
     * @param  string  $path
     * @param  int  $status  
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function secure($path, $status = 302, $headers = [])
    {
        return $this->to($path, $status, $headers, true);
    }

    /**
     * Create a new redirect response to a named route.
     * 
     * @param  string  $route
     * @param  array  $parameters
     * @param  int  $status  
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function route(
        $route, 
        $parameters = [], 
        $status = 302, 
        $headers = []
    ) {
        $path = $this->generator->route($route, $parameters);

        return $this->to($path, $status, $headers);
    }

    /**
     * Create a new redirect response to a controller action.
     * 
     * @param  string|array  $route
     * @param  array  $parameters
     * @param  int  $status  
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function action(
        $route, 
        $parameters = [], 
        $status = 302, 
        $headers = []
    ) {
        $path = $this->generator->action($route, $parameters);

        return $this->to($path, $status, $headers);
    }

    /**
     * Creates a new redirect response.
     * 
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    protected function createRedirect($path, $status, $headers)
    {
        return take(new RedirectResponse($path, $status, $headers), function($redirect) {
            if (isset($this->session)) {
                $redirect->setSession($this->session);
            }
            
            $redirect->setRequest($this->generator->getRequest());
        });
    }
    
    /**
     * Get the URL generator instance.
     * 
     * @return \Syscodes\Components\Routing\Generators\UrlGenerator
     */
    public function getUrlGenerator()
    {
        return $this->generator;
    }
    
    /**
     * Set the active session store.
     * 
     * @param  \Syscodes\Components\Session\Store  $session
     * 
     * @return void
     */
    public function setSession(SessionStore $session): void
    {
        $this->session = $session;
    }
    
    /**
     * Get the "intended" URL from the session.
     * 
     * @return string|null
     */
    public function getIntendedUrl()
    {
        return $this->session->get('url.intended');
    }

    /**
     * Set the intended url.
     * 
     * @param  string  $url
     * 
     * @return static
     */
    public function setIntendedUrl($url): static
    {
        $this->session->put('url.intended', $url);
        
        return $this;
    }
}