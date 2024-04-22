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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Configuration;

use Closure;
use Syscodes\Components\Support\Facades\Route;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Contracts\Http\Lenevor as LenevorCore;
use Syscodes\Components\Core\Support\Providers\RouteServiceProvider;

/**
 * Allows the bootstrap of the application.
 */
class ApplicationBootstrap
{
    /**
     * Constructor. Create a new aplication bootstrap instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Register the standard core classes for the application.
     * 
     * @return static
     */
    public function assignCores(): static
    {
        $this->app->singleton(
            \Syscodes\Components\Contracts\Http\Lenevor::class, 
            \Syscodes\Components\Core\Http\Lenevor::class
        );
        
        $this->app->singleton(
            \Syscodes\Components\Contracts\Console\Lenevor::class, 
            \Syscodes\Components\Core\Console\Lenevor::class
        );

        return $this;
    }
    
    /**
     * Register and configure the application's exception handler.
     * 
     * @param  callable|null  $using
     * 
     * @return static
     */
    public function assignExceptions(?callable $using = null): static
    {
        $this->app->singleton(
            \Syscodes\Components\Contracts\Debug\ExceptionHandler::class, 
            \Syscodes\Components\Core\Exceptions\Handler::class
        );
        
        $using ??= fn () => true;
        
        $this->app->afterResolving(
            \Syscodes\Components\Core\Exceptions\Handler::class,
            fn ($handler) => $using(new ExceptionBootstrap($handler)),
        );
        
        return $this;
    }
    
    /**
     * Register the routing services for the application.
     * @param  \Closure|null  $using
     * @param  string|null  $web
     * @param  string|null  $api
     * @param  string  $apiPrefix
     * @param  callable|null  $then
     * 
     * @return static
     */
    public function assignRouting(
        ?Closure $using = null,
        ?string $web = null,
        ?string $api = null,
        string $apiPrefix = 'api',
        ?callable $then = null
    ): static {
        if (is_null($using) && (is_string($web) || is_string($api) || is_callable($then))) {
            $using = $this->makeRoutingCallback($web, $api, $apiPrefix, $then);
        }
        
        RouteServiceProvider::loadRoutesUsing($using);
        
        $this->app->booting(function () {
            $this->app->register(RouteServiceProvider::class, force: true);
        });
        
        return $this;
    }
    
    /**
     * Create the routing callback for the application.
     * 
     * @param  string|null  $web
     * @param  string|null  $api
     * @param  string  $apiPrefix
     * @param  callable|null  $then
     * 
     * @return \Closure
     */
    protected function makeRoutingCallback(
        ?string $web,
        ?string $api,
        string $apiPrefix,
        ?callable $then
    ) {
        return function () use ($web, $api, $apiPrefix, $then) {
            if (is_string($api) && realpath($api) !== false) {
                Route::middleware('api')->prefix($apiPrefix)->group($api);
            }
            
            if (is_string($web) && realpath($web) !== false) {
                Route::middleware('web')->group($web);
            }
            
            if (is_callable($then)) {
                $then($this->app);
            }
        };
    }
    
    /**
     * Register the global middleware, middleware groups, and middleware aliases for the application.
     * 
     * @param  callable|null  $callback
     * 
     * @return static
     */
    public function assignMiddlewares(?callable $callback = null): static
    {
        $this->app->afterResolving(LenevorCore::class, function ($lenevor) use ($callback) {
            $middleware = (new MiddlewareBootstrap);
            
            if ( ! is_null($callback)) {
                $callback($middleware);
            }
            
            $lenevor->setGlobalMiddleware($middleware->getGlobalMiddleware());
            $lenevor->setMiddlewareGroups($middleware->getMiddlewareGroups());
            $lenevor->setMiddlewareAliases($middleware->getMiddlewareAliases());
            
            if ($priorities = $middleware->getMiddlewareAliases()) {
                $lenevor->setMiddlewarePriority($priorities);
            }
        });
        
        return $this;
    }
    
    /**
     * Register a callback to be invoked when the application is "booting".
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function booting(callable $callback): static
    {
        $this->app->booting($callback);
        
        return $this;
    }
    
    /**
     * Register a callback to be invoked when the application is "booted".
     * 
     * @param  callable  $callback
     * 
     * @return static
     */
    public function booted(callable $callback): static
    {
        $this->app->booted($callback);
        
        return $this;
    }
    
    /**
     * Get the application instance.
     * 
     * @return \Syscodes\Components\Contracts\Core\Application
     */
    public function create()
    {
        return $this->app;
    }
}