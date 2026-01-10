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

namespace Syscodes\Components\Session\Middleware;

use Closure;
use Syscodes\Components\Contracts\Session\Middleware\AuthenticateSession as AuthenticateSessionContract;
use Syscodes\Components\Auth\Exceptions\AuthenticationException;
use Syscodes\Components\Http\Request;

use Syscodes\Components\Contracts\Auth\Factory as AuthFactory;

/**
 * This middleware of session allows authenticate logged on users.
 */
class AuthenticateSession implements AuthenticateSessionContract
{
    /**
     * The authentication factory implementation.
     * 
     * @var \Syscodes\Components\Contracts\Auth\Factory
     */
    protected $auth;
    
    /**
     * The callback that should be used to generate the authentication redirect path.
     * 
     * @var callable
     */
    protected static $redirectToCallback;
    
    /**
     * Constructor. Create a new Authenticate session instance class.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Factory  $auth
     * 
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! $request->hasSession() || ! $request->user()) {
            return $next($request);
        }
        
        if ($this->guard()->viaRemember()) {
            $passwordHashCookie = explode('|', $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;
            
            if ( ! $passwordHashCookie || 
                 ! $this->validatePasswordHash($request->user()->getAuthPassword(), $passwordHashCookie)) {
                $this->logout($request);
            }
        }
        
        if ( ! $request->session()->has('password_hash_'.$this->auth->getDefaultDriver())) {
            $this->storePasswordHashInSession($request);
        }

        $sessionPasswordHash = $request->session()->get('password_hash_'.$this->auth->getDefaultDriver());
        
        if ( ! $this->validatePasswordHash($request->user()->getAuthPassword(), $sessionPasswordHash)) {
            $this->logout($request);
        }
        
        return take($next($request), function () use ($request) {
            if ( ! is_null($this->guard()->user())) {
                $this->storePasswordHashInSession($request);
            }
        });
    }
    
    /**
     * Store the user's current password hash in the session.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return void
     */
    protected function storePasswordHashInSession($request): void
    {
        if ( ! $request->user()) {
            return;
        }
        
        $request->session()->put([
            'password_hash_'.$this->auth->getDefaultDriver() => $this->guard()->hashPasswordForCookie($request->user()->getAuthPassword()),
        ]);
    }

    /**
     * Validate the password hash against the stored value.
     *
     * @param  string  $passwordHash
     * @param  string  $storedValue
     * 
     * @return bool
     */
    protected function validatePasswordHash($passwordHash, $storedValue): bool
    {
        // Try new HMAC format first...
        if (hash_equals($this->guard()->hashPasswordForCookie($passwordHash), $storedValue)) {
            return true;
        }

        // Fall back to raw password hash format for backward compatibility...
        return hash_equals($passwordHash, $storedValue);
    }
    
    /**
     * Log the user out of the application.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return void
     * 
     * @throws \Syscodes\Components\Auth\Exceptions\AuthenticationException
     */
    protected function logout($request): void
    {
        $this->guard()->logout();
        
        $request->session()->flush();
        
        throw new AuthenticationException(
            'Unauthenticated.', [$this->auth->getDefaultDriver()], $this->redirectTo($request)
        );
    }
    
    /**
     * Get the guard instance that should be used by the middleware.
     * 
     * @return \Syscodes\Components\Contracts\Auth\Factory|\Syscodes\Components\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return $this->auth;
    }
    
    /**
     * Get the path the user should be redirected to when their session is not authenticated.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return string|null
     */
    protected function redirectTo(Request $request)
    {
        if (static::$redirectToCallback) {
            return call_user_func(static::$redirectToCallback, $request);
        }
    }
    
    /**
     * Specify the callback that should be used to generate the redirect path.
     * 
     * @param  callable  $redirectToCallback
     * 
     * @return void
     */
    public static function redirectUsing(callable $redirectToCallback): void
    {
        static::$redirectToCallback = $redirectToCallback;
    }
}