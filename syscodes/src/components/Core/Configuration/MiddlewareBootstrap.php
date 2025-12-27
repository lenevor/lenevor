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

namespace Syscodes\Components\Core\Configuration;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Auth\Middleware\Authenticate;
use Syscodes\Components\Cookie\Middleware\EncryptCookies;
use Syscodes\Components\Auth\Exceptions\AuthenticationException;
use Syscodes\Components\Auth\Middleware\RedirectIfAuthenticated;

/**
 * Allows the bootstrap of the middlewares.
 */
class MiddlewareBootstrap
{
    /**
     * Indicates the API middleware group's throttle limiter.
     * 
     * @var string
     */
    protected $apiLimiter;

    /**
     * The middleware that should be appended to the global middleware stack.
     * 
     * @var array $appends
     */
    protected $appends = [];
    
    /**
     * The custom middleware aliases.
     * 
     * @var array $customAliases
     */
    protected $customAliases = [];
    
    /**
     * The user defined global middleware stack.
     * 
     * @var array $global
     */
    protected $global = [];
    
    /**
     * The middleware that should be prepended to the global middleware stack.
     * 
     * @var array $prepends
     */
    protected $prepends = [];
    
    /**
     * The custom middleware priority definition.
     * 
     * @var array $priority
     */
    protected $priority = [];
    
    /**
     * The middleware that should be removed from the global middleware stack.
     * 
     * @var array $removals
     */
    protected $removals = [];
    
    /**
     * The middleware that should be replaced in the global middleware stack.
     * 
     * @var array $replacements
     */
    protected $replacements = [];
    
    /**
     * Indicates if Redis throttling should be applied.
     * 
     * @var bool
     */
    protected $throttleWithRedis = false;
    
    /**
     * Prepend middleware to the application's global middleware stack.
     * 
     * @param  array|string  $middleware
     * 
     * @return static
     */
    public function prepend(array|string $middleware): static
    {
        $this->prepends = array_merge(
            Arr::wrap($middleware),
            $this->prepends
        );
        
        return $this;
    }
    
    /**
     * Append middleware to the application's global middleware stack.
     * 
     * @param  array|string  $middleware
     * 
     * @return static
     */
    public function append(array|string $middleware): static
    {
        $this->appends = array_merge(
            $this->appends,
            Arr::wrap($middleware)
        );
        
        return $this;
    }
    
    /**
     * Remove middleware from the application's global middleware stack.
     * 
     * @param  array|string  $middleware
     * 
     * @return static
     */
    public function remove(array|string $middleware): static
    {
        $this->removals = array_merge(
            $this->removals,
            Arr::wrap($middleware)
        );
        
        return $this;
    }
    
    /**
     * Define the global middleware for the application.
     * 
     * @param  array  $middleware
     * 
     * @return static
     */
    public function use(array $middleware): static
    {
        $this->global = $middleware;
        
        return $this;
    }
    
    /**
     * Specify a middleware that should be replaced with another middleware.
     * 
     * @param  string  $search
     * @param  string  $replace
     * 
     * @return static
     */
    public function replace(string $search, string $replace): static
    {
        $this->replacements[$search] = $replace;
        
        return $this;
    }
    
    /**
     * Register additional middleware aliases.
     * 
     * @param  array  $aliases
     * 
     * @return static
     */
    public function alias(array $aliases): static
    {
        $this->customAliases = $aliases;
        
        return $this;
    }
    
    /**
     * Define the middleware priority for the application.
     * 
     * @param  array  $priority
     * 
     * @return static
     */
    public function priority(array $priority): static
    {
        $this->priority = $priority;
        
        return $this;
    }

    /**
     * Get the global middleware.
     *
     * @return array
     */
    public function getGlobalMiddleware(): array
    {
        $middleware =  $this->global ?: array_values(array_filter([
            \Syscodes\Components\Core\Http\Middleware\VerifyPostSize::class,
        ]));
        
        $middleware = array_map(function ($middleware) {
            return isset($this->replacements[$middleware])
                ? $this->replacements[$middleware]
                : $middleware;
        }, $middleware);
        
        return array_values(array_filter(
            array_diff(
                array_unique(array_merge($this->prepends, $middleware, $this->appends)),
                $this->removals
            )
        ));
    }

    /**
     * Get the middleware groups.
     *
     * @return array
     */
    public function getMiddlewareGroups(): array
    {
        $middleware = [
            'web' => array_values(array_filter([
                \Syscodes\Components\Cookie\Middleware\EncryptCookies::class,
                \Syscodes\Components\Cookie\Middleware\AddQueuedCookiesResponse::class,
                \Syscodes\Components\Session\Middleware\StartSession::class,
                \Syscodes\Components\View\Middleware\ShareErrorsSession::class,
                \Syscodes\Components\Core\Http\Middleware\VerifyCsrfToken::class,
            ])),

            'api' => array_values(array_filter([
                $this->apiLimiter ? 'throttle:'.$this->apiLimiter : null,
            ])),
        ];

        return $middleware;
    }
    
    /**
     * Get the middleware aliases.
     * 
     * @return array
     */
    public function getMiddlewareAliases(): array
    {
        return array_merge($this->defaultAliases(), $this->customAliases);
    }
    
    /**
     * Get the middleware priority for the application.
     * 
     * @return array
     */
    public function getPriority(): array
    {
        return $this->priority;
    }
    
    /**
     * Configure where guests are redirected by the "auth" middleware.
     * 
     * @param  callable|string  $redirect
     * 
     * @return static
     */
    public function redirectGuestsTo(callable|string $redirect): static
    {
        return $this->redirectTo(guests: $redirect);
    }
    
    /**
     * Configure where users are redirected by the "guest" middleware.
     * 
     * @param  callable|string  $redirect
     * 
     * @return static
     */
    public function redirectUsersTo(callable|string $redirect): static
    {
        return $this->redirectTo(users: $redirect);
    }
    
    /**
     * Configure where users are redirected by the authentication and guest middleware.
     * 
     * @param  callable|string  $guests
     * @param  callable|string  $users
     * 
     * @return static
     */
    public function redirectTo(callable|string|null $guests = null, callable|string|null $users = null): static
    {
        $guests = is_string($guests) ? fn () => $guests : $guests;
        $users  = is_string($users) ? fn () => $users : $users;
        
        if ($guests) {
            Authenticate::redirectUsing($guests);
            AuthenticationException::redirectUsing($guests);
        }
        
        if ($users) {
            RedirectIfAuthenticated::redirectUsing($users);
        }
        
        return $this;
    }
    
    /**
     * Configure the cookie encryption middleware.
     * 
     * @param  array<int, string>  $except
     * 
     * @return static
     */
    public function encryptCookies(array $except = []): static
    {
        EncryptCookies::except($except);
        
        return $this;
    }
    
    /**
     * Indicate that the API middleware group's throttling middleware should be enabled.
     * 
     * @param  string  $limiter
     * @param  bool  $redis
     * 
     * @return static
     */
    public function throttleApi($limiter = 'api', $redis = false): static
    {
        $this->apiLimiter = $limiter;
        
        if ($redis) {
            $this->throttleWithRedis();
        }
        
        return $this;
    }
    
    /**
     * Indicate that Lenevor's throttling middleware should use Redis.
     *
     * @return static
     */
    public function throttleWithRedis(): static
    {
        $this->throttleWithRedis = true;
        
        return $this;
    }
    
    /**
     * Get the default middleware aliases.
     * 
     * @return array
     */
    protected function defaultAliases(): array
    {
        $aliases = [
            'auth' => \Syscodes\Components\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Syscodes\Components\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'can' => \Syscodes\Components\Auth\Middleware\Authorize::class,
            'guest' => \Syscodes\Components\Auth\Middleware\RedirectIfAuthenticated::class,
            'throttle' => $this->throttleWithRedis
                ? \Syscodes\Componens\Routing\Middleware\ThrottleRequestsWithRedis::class
                : \Syscodes\Componens\Routing\Middleware\ThrottleRequests::class,
        ];
        
        return $aliases;
    }
}