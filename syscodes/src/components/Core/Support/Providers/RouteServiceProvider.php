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

namespace Syscodes\Components\Core\Support\Providers;

use Closure;
use Syscodes\Components\Support\ServiceProvider;

/**
 * The Route service provider facilitates the register of a namespace your 
 * loaded in file route and executed in a group route.
 */
class RouteServiceProvider extends ServiceProvider
{
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
     * Register any application services.
     */
    public function register()
    {
        $this->app->booted(function () {
            $this->app['router']->getRoutes()->refreshNameLookups();
            $this->app['router']->getRoutes()->refreshActionLookups();
        });
    }

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
     * Register the callback that will be used to load the application's routes.
     * 
     * @param  \Closure  $routeCallback
     * 
     * @return static
     */
    protected function routes(Closure $routeCallback): static
    {
        $routeCallback();

        return $this;
    }   
    
    /**
     * Register the callback that will be used to load the application's routes.
     * 
     * @param  \Closure  $routesCallback
     * 
     * @return static
     */
    // protected function routes(Closure $routesCallback): static
    // {
    //     $this->loadRoutesUsing = $routesCallback;
    //
    //     return $this;
    // }
    
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
}