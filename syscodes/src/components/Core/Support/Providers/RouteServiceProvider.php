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

namespace Syscodes\Components\Core\Support\Providers;

use Closure;
use Syscodes\Components\Contracts\Routing\UrlGenerator;
use Syscodes\Components\Routing\Router;
use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\Support\Traits\ForwardsCalls;

/**
 * The Route service provider facilitates the register of a namespace your 
 * loaded in file route and executed in a group route.
 */
class RouteServiceProvider extends ServiceProvider
{
    use ForwardsCalls;

    /**
     * The global callback that should be used to load the application's routes.
     * 
     * @var \Closure|null $AlwaysLoadRoutesUsing
     */
    protected static $alwaysLoadRoutesUsing;
    
    /**
     * The callback that should be used to load the application's routes.
     * 
     * @var \Closure|null $loadRoutesUsing
     */
    protected $loadRoutesUsing;

    /**
     * This namespace is applied to your controller routes.
     * 
     * Nota: If desired, uncomment this variable and assign it in 
     *       the 'namespace' method where your parameter is null 
     *       by default. 
     *
     *       Example: Route::middleware('web')
     *                     ->namespace($this->namespace)
     * 
     * @var string|null $namespace
     */
    protected $namespace;

    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot()
    {
        //
    }
    
    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register()
    {
        $this->booting(function () {
            $this->setRootControllerNamespace();
            
            if ($this->routesAreCached()) {
                $this->loadCachedRoutes();
            } else {
                $this->loadRoutes();
                
                $this->app->booted(function () {
                    $this->app['router']->getRoutes()->refreshNameLookups();
                    $this->app['router']->getRoutes()->refreshActionLookups();
                });
            }
        });
    }
    
    /**
     * Register the callback that will be used to load the application's routes.
     * 
     * @param  \Closure  $routesCallback
     * 
     * @return static
     */
    protected function routes(Closure $routesCallback): static
    {
        $this->loadRoutesUsing = $routesCallback;
    
        return $this;
    }
    
    /**
     * Register the callback that will be used to load the application's routes.
     * 
     * @param  \Closure|null  $routesCallback
     * 
     * @return void
     */
    public static function loadRoutesUsing(?Closure $routesCallback)
    {
        self::$alwaysLoadRoutesUsing = $routesCallback;
    }
    
    /**
     * Set the root controller namespace for the application.
     * 
     * @return void
     */
    protected function setRootControllerNamespace()
    {
        if ( ! is_null($this->namespace)) {
            $this->app[UrlGenerator::class]->setRootControllerNamespace($this->namespace);
        }
    }
    
    /**
     * Determine if the application routes are cached.
     * 
     * @return bool
     */
    protected function routesAreCached(): bool
    {
        return $this->app->routesAreCached();
    }
    
    /**
     * Load the cached routes for the application.
     * 
     * @return void
     */
    protected function loadCachedRoutes()
    {
        $this->app->booted(function () {
            require $this->app->getCachedRoutesPath();
        });
    }
    
    /**
     * Load the application routes.
     * 
     * @return void
     */
    protected function loadRoutes()
    {
        if ( ! is_null(self::$alwaysLoadRoutesUsing)) {
            $this->app->call(self::$alwaysLoadRoutesUsing);
        }
        
        if ( ! is_null($this->loadRoutesUsing)) {
            $this->app->call($this->loadRoutesUsing);
        } elseif (method_exists($this, 'map')) {
            $this->app->call([$this, 'map']);
        }
    }
    
    /**
     * Magic method.
     * 
     * Pass dynamic methods onto the router instance.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo(
            $this->app->make(Router::class), $method, $parameters
        );
    }
}