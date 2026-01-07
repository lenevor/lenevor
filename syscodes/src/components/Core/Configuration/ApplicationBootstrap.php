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

namespace Syscodes\Components\Core\Configuration;

use Closure;
use Syscodes\Components\Contracts\Console\Kernel as ConsoleKernel;
use Syscodes\Components\Contracts\Http\Kernel;
use Syscodes\Components\Core\Application;
use Syscodes\Components\Core\Bootstrap\BootRegisterProviders;
use Syscodes\Components\Core\Support\Providers\EventServiceProvider as AppEventServiceProvider;
use Syscodes\Components\Core\Support\Providers\RouteServiceProvider as AppRouteServiceProvider;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Facades\Route;

/**
 * Allows the bootstrap of the application.
 */
class ApplicationBootstrap
{
    /**
     * The service provider that are marked for registration.
     * 
     * @var array $registerProviders
     */
    protected array $registerProviders = [];

    /**
     * Constructor. Create a new aplication bootstrap instance.
     * 
     * @param  \Syscodes\Components\Core\Application  $app
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
            \Syscodes\Components\Contracts\Http\Kernel::class, 
            \Syscodes\Components\Core\Http\Kernel::class
        );
        
        $this->app->singleton(
            \Syscodes\Components\Contracts\Console\Kernel::class, 
            \Syscodes\Components\Core\Console\Kernel::class
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
        ?string $commands = null,
        string $apiPrefix = 'api',
        ?callable $then = null
    ): static {
        if (is_null($using) && (is_string($web) || is_string($api) || is_callable($then))) {
            $using = $this->makeRoutingCallback($web, $api, $apiPrefix, $then);
        }
        
        AppRouteServiceProvider::loadRoutesUsing($using);
        
        if (is_string($commands) && realpath($commands) !== false) {
            $this->assignCommands([$commands]);
        }
        
        $this->app->booting(function () {
            $this->app->register(AppRouteServiceProvider::class, force: true);
        });
        
        return $this;
    }
    
    /**
     * Create the routing callback for the application.
     * 
     * @param  array|string|null  $web
     * @param  array|string|null  $api
     * @param  string  $apiPrefix
     * @param  callable|null  $then
     * 
     * @return \Closure
     */
    protected function makeRoutingCallback(
        array|string|null $web,
        array|string|null $api,
        string $apiPrefix,
        ?callable $then
    ) {
        return function () use ($web, $api, $apiPrefix, $then) {
            if (is_string($api) || is_array($api)) {
                if (is_array($api)) {
                    foreach ($api as $apiRoute) {
                        if (realpath($apiRoute) !== false) {
                            Route::middleware('api')->prefix($apiPrefix)->group($apiRoute);
                        }
                    }
                } else {
                    Route::middleware('api')->prefix($apiPrefix)->group($api);
                }
            }
            
            if (is_string($web) || is_array($web)) {
                if (is_array($web)) {
                    foreach ($web as $webRoute) {
                        if (realpath($webRoute) !== false) {
                            Route::middleware('web')->group($webRoute);
                        }
                    }
                } else {
                    Route::middleware('web')->group($web);
                }
            }
            
            if (is_callable($then)) {
                $then($this->app);
            }
        };
    }

     /**
     * Register the core event service provider for the application.
     * 
     * @param  array|bool  $discover
     * 
     * @return static
     */
    public function assignEvents(array|bool $discover = []): static
    {
        if ( ! isset($this->registerProviders[AppEventServiceProvider::class])) {
            $this->app->booting(function () {
                $this->app->register(AppEventServiceProvider::class);
            });
        }
        
        $this->registerProviders[AppEventServiceProvider::class] = true;
        
        return $this;
    }
    
    /**
     * Register additional service providers.
     * 
     * @param  array  $providers
     * @param  bool  $assignBootstrapProviders
     * 
     * @return static
     */
    public function assignProviders(array $providers = [], bool $assignBootstrapProviders = true): static
    {
        BootRegisterProviders::merge(
            $providers,
            $assignBootstrapProviders
                ? $this->app->getBootstrapProvidersPath() 
                : null
        );

        return $this;
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
        $this->app->afterResolving(Kernel::class, function ($kernel) use ($callback) {
            $middleware = (new MiddlewareBootstrap)
                ->redirectGuestsTo(fn () => route('login'));
            
            if ( ! is_null($callback)) {
                $callback($middleware);
            }
            
            $kernel->setGlobalMiddleware($middleware->getGlobalMiddleware());
            $kernel->setMiddlewareGroups($middleware->getMiddlewareGroups());
            $kernel->setMiddlewareAliases($middleware->getMiddlewareAliases());
            
            if ($priorities = $middleware->getMiddlewareAliases()) {
                $kernel->setMiddlewarePriority($priorities);
            }
        });
        
        return $this;
    }
    
    /**
     * Register additional Prime commands with the application.
     * 
     * @param  array  $commands
     * 
     * @return static
     */
    public function assignCommands(array $commands = []): static 
    {
        if (empty($commands)) {
            $commands = [$this->app->path('Console/Commands')];
        }
        
        $this->app->afterResolving(ConsoleKernel::class, function ($kernel) use ($commands) {
            [$commands, $paths] = (new Collection($commands))->partition(fn ($command) => class_exists($command));
            [$routes, $paths] = $paths->partition(fn ($path) => is_file($path));
            
            $this->app->booted(static function () use ($kernel, $commands, $paths, $routes) {
                $kernel->addCommands($commands->all());
                $kernel->addCommandPaths($paths->all());
                $kernel->addCommandRoutePaths($routes->all());
            });
        });
        
        return $this;
    }
    
    /**
     * Register additional Prime route paths.
     * 
     * @param  array  $paths
     * 
     * @return static
     */
    protected function assingCommandRouting(array $paths): static
    {
        $this->app->afterResolving(ConsoleKernel::class, function ($kernel) use ($paths) {
            $this->app->booted(fn () => $kernel->addCommandRoutePaths($paths));
        });
        
        return $this;
    }

    /**
     * Register an array of container bindings to be bound when the application is booting.
     *
     * @param  array  $bindings
     * 
     * @return static
     */
    public function withBindings(array $bindings): static
    {
        return $this->registered(function ($app) use ($bindings) {
            foreach ($bindings as $abstract => $concrete) {
                $app->bind($abstract, $concrete);
            }
        });
    }

    /**
     * Register an array of singleton container bindings to be bound when the application is booting.
     *
     * @param  array  $singletons
     * 
     * @return static
     */
    public function withSingletons(array $singletons): static
    {
        return $this->registered(function ($app) use ($singletons) {
            foreach ($singletons as $abstract => $concrete) {
                if (is_string($abstract)) {
                    $app->singleton($abstract, $concrete);
                } else {
                    $app->singleton($concrete);
                }
            }
        });
    }

    /**
     * Register a callback to be invoked when the application's service providers are registered.
     *
     * @param  callable  $callback
     * 
     * @return static
     */
    public function registered(callable $callback): static
    {
        $this->app->registered($callback);

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