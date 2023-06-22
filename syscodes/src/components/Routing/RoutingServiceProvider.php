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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing;

use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\Routing\Generators\Redirector;
use Syscodes\Components\Routing\Generators\UrlGenerator;
use Syscodes\Components\Routing\Generators\RouteResponse;
use Syscodes\Components\Contracts\Routing\RouteResponse as ResponseContract;
use Syscodes\Components\Contracts\Routing\UrlGenerator as UrlGeneratorContract;

/**
 * For loading the classes from the container of services.
 */
class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->registerRouter();
        $this->registerRouteResponse();
        $this->registerUrlGenerator();
        $this->registerRedirector();
    }

    /**
     * Register the router instance.
     * 
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', fn ($app) => new Router($app));
    }

    /**
     * Register the route response implementation.
     * 
     * @return void
     */
    protected function registerRouteResponse()
    {
        $this->app->singleton(ResponseContract::class, fn($app) => new RouteResponse($app['view'], $app['redirect']));
    }

    /**
     * Register the URL generator service.
     * 
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app) {            
            $routes = $app['router']->getRoutes();

            return new UrlGenerator($routes, $app['request']);            
        });

        $this->app->extend('url', function (UrlGeneratorContract $url, $app) {
            $url->setSessionResolver(function () use ($app) {
                return $app['session'] ?? null;
            });

            return $url;
        });
    }

    /**
     * Register the URL generator service.
     * 
     * @return void
     */
    protected function registerRedirector()
    {
        $this->app->singleton('redirect', function ($app) {
            $redirector = new Redirector($app['url']);
            
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }

            return $redirector;
        });
    }
}