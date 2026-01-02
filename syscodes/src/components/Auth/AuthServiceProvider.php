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

namespace Syscodes\Components\Auth;

use Syscodes\Components\Auth\Access\Gate;
use Syscodes\Components\Contracts\Auth\Access\Gate as GateContract;
use Syscodes\Components\Contracts\Auth\Authenticatable;
use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the classes from the container of services.
 */
class AuthServiceProvider extends ServiceProvider
{
     /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->registerAuthenticator();
        $this->registerAuthenticationGuard();
        $this->registerUserResolver();
        $this->registerAccessGate();
    }

    /**
     * Register the authenticator services.
     * 
     * @return void
     */
    protected function registerAuthenticator()
    {
        $this->app->singleton('auth', fn ($app) => new AuthManager($app));

    }

    /**
     * Register the authentication guard services.
     * 
     * @return void
     */
    protected function registerAuthenticationGuard()
    {
        $this->app->singleton('auth.driver', fn ($app) => $app['auth']->guard());
    }
    
    /**
     * Register a resolver for the authenticated user.
     * 
     * @return void
     */
    protected function registerUserResolver()
    {
        $this->app->bind(Authenticatable::class, fn ($app) => call_user_func($app['auth']->userResolver()));
    }
    
    /**
     * Register the access gate service.
     * 
     * @return void
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(GateContract::class, function ($app) {
            return new Gate($app, fn() => call_user_func($app['auth']->userResolver()));
        });
    }
}