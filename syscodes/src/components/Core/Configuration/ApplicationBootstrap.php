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

use Syscodes\Components\Contracts\Core\Application;

/**
 * Allows the bootstrap of the application.
 */
class ApplicationBootstrap
{
    /**
     * Constructor. Create a new aplication bootstrap instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
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
            \Syscodes\Components\Contracts\Http\Lenevor::class, 
            \App\Http\Lenevor::class
        );
        
        $this->app->singleton(
            \Syscodes\Components\Contracts\Console\Lenevor::class, 
            \App\Console\Lenevor::class
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
            \App\Exceptions\Handler::class
        );
        
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