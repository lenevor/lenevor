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

/**
 * Allows the bootstrap of the middlewares.
 */
class MiddlewareBootstrap
{
    /**
     * Get the global middleware.
     *
     * @return array
     */
    public function getGlobalMiddleware()
    {
        $middleware = [
            \Syscodes\Components\Core\Http\Middleware\VerifyPostSize::class,
        ];

        return $middleware;
    }

    /**
     * Get the middleware groups.
     *
     * @return array
     */
    public function getMiddlewareGroups()
    {
        $middleware = [
            'web' => [
                \Syscodes\Components\Cookie\Middleware\EncryptCookies::class,
                \Syscodes\Components\Cookie\Middleware\AddQueuedCookiesResponse::class,
                \Syscodes\Components\Session\Middleware\StartSession::class,
                \Syscodes\Components\Core\Http\Middleware\VerifyCsrfToken::class,
            ],

            'api' => array_values(array_filter([])),
        ];

        return $middleware;
    }
    
    /**
     * Get the middleware aliases.
     * 
     * @return array
     */
    public function getMiddlewareAliases(): array
    {
        return array_merge($this->defaultAliases(), []);
    }
    
    /**
     * Get the default middleware aliases.
     * 
     * @return array
     */
    protected function defaultAliases(): array
    {
        $aliases = [
            'auth' => \Syscodes\Components\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Syscodes\Components\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'guest' => \Syscodes\Components\Auth\Middleware\RedirectIfAuthenticated::class,
        ];
        
        return $aliases;
    }
}