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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth;

use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the classes from the container of services.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
    }

    /**
     * Register the authenticator services.
     * 
     * @return void
     */
    protected function registerAuthenticator()
    {
        $this->app->singleton('auth', function ($app) {            
            return new AuthManager($app);
        });

    }

    /**
     * Register the authentication guard services.
     * 
     * @return void
     */
    protected function registerAuthenticationGuard()
    {
        $this->app->singleton('auth.driver', function ($app) {
            return $app->make('auth')->guard();
        });
    }
}