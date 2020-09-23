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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.2
 */

namespace Syscodes\Core\Support\Providers;

use Closure;
use Syscodes\Support\ServiceProvider;

/**
 * The route service provider facilitates the register of a namespace your 
 * loaded in file route and executed in a group route.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->booted(function ()
        {
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
     * @return $this 
     */
    protected function routes(Closure $routeCallback)
    {
        $routeCallback();

        return $this;
    }
}