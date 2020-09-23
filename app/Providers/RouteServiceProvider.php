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

namespace App\Providers;

use Syscodes\Support\Facades\Route;
use Syscodes\Core\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * The route service provider facilitates the register of a namespace your 
 * loaded in file route and executed in a group route.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{   
    /**
     * This namespace is applied to your controller routes.
     * 
     * @var string $namespace
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            $this->loadWebRoute();
            $this->loadApiRoute();
        });
    }

    /**
     * Define the "web" routes for the application.
     * 
     * @return void
     */
    protected function loadWebRoute()
    {
        Route::namespace($this->namespace)
             ->group(basePath('routes/web.php'));
    }    
    
    /**
     * Define the "api" routes for the application.
     * 
     * @return void
     */
    protected function loadApiRoute()
    {
        Route::prefix('api')
             ->group(basePath('routes/api.php'));
    }
}