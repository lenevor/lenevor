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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace App\Providers;

use Syscodes\Components\Support\Facades\Route;
use Syscodes\Components\Core\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * The route service provider facilitates the register of a namespace your 
 * loaded in file route and executed in a group route.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{   
    /**
     * This namespace is applied to your controller routes.
     * 
     * Nota: If desired, uncomment this variable and assign it in 
     * the 'namespace' method where your parameter is null by default.
     * It is found in the methods of this class: 
     * loadApiRoute() and loadWebRoute().
     *  
     * Used until version 0.7.4 of framework.
     * 
     * @var string $namespace
     */
    //protected $namespace = 'App\Http\Controllers';

    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            $this->loadApiRoute();
            $this->loadWebRoute();
        });
    }

    /**
     * Define the "api" routes for the application.
     * 
     * @return void
     */
    protected function loadApiRoute()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace()
             ->group(basePath('routes/api.php'));
    }

    /**
     * Define the "web" routes for the application.
     * 
     * @return void
     */
    protected function loadWebRoute()
    {
        Route::middleware('web')
             ->namespace()
             ->group(basePath('routes/web.php'));
    }    
}