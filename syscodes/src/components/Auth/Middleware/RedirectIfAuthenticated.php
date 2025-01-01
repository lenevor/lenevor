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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth\Middleware;

use Closure;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Support\Facades\Auth;
use Syscodes\Components\Support\Facades\Route;

/**
 * If have does not authentication, the user is redirected at the page home.
 */
class RedirectIfAuthenticated
{
    /**
     * The callback that should be used to generate the authentication redirect path.
     * 
     * @var callable|null $redirectToCallback
     */
    protected static $redirectToCallback;

    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): \Syscodes\Components\Http\Response|\Syscodes\Components\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * 
     * @return \Syscodes\Components\Http\Response|\Syscodes\Components\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->redirectTo($request));
            }
        }
        
        return $next($request);
    }
    
    /**
     * Get the path the user should be redirected to when they are authenticated.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return string|null 
     */
    protected function redirectTo(Request $request): ?string
    {
        return static::$redirectToCallback
            ? call_user_func(static::$redirectToCallback, $request)
            : $this->defaultRedirectUri();
    }
    
    /**
     * Get the default URI the user should be redirected to when they are authenticated.
     *
     * @return string 
     */
    protected function defaultRedirectUri(): string
    {
        foreach (['dashboard', 'home'] as $uri) {
            if (Route::has($uri)) {
                return route($uri);
            }
        }
        
        $routes = Route::getRoutes()->get('GET');
        
        foreach (['dashboard', 'home'] as $uri) {
            if (isset($routes[$uri])) {
                return '/'.$uri;
            }
        }
        
        return '/';
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