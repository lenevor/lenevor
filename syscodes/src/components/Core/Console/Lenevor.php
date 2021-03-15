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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Core\Console;

use Closure;
use Throwable;
use ReflectionClass;
use Syscodes\Support\Str;
use Syscodes\Support\Finder;
use Syscodes\Collections\Arr;
use Syscodes\Contracts\Core\Application;
use Syscodes\Contracts\Events\Dispatcher;
use Syscodes\Console\Application as Prime;
use Syscodes\Contracts\Debug\ExceptionHandler;
use Syscodes\Contracts\Console\Lenevor as LenevorConsole;

/**
 * The Lenevor class is the heart of the system when use 
 * the console of commands in framework.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Lenevor implements LenevorConsole
{
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * The bootstrap classes for the application.
     * 
     * @var array $bootstrappers
     */
    protected $bootstrappers = [
        \Syscodes\Core\Bootstrap\BootDetectEnvironment::class,
        \Syscodes\Core\Bootstrap\BootConfiguration::class,
        \Syscodes\Core\Bootstrap\BootHandleExceptions::class,
        \Syscodes\Core\Bootstrap\BootRegisterFacades::class,
        \Syscodes\Core\Bootstrap\BootRegisterProviders::class,
        \Syscodes\Core\Bootstrap\BootProviders::class,
    ];

    /**
     * Constructor. Create new console Lenevor instance.
     * 
     * @param  \Syscodes\Contracts\Core\Application $app
     * 
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming console command.
     * 
     * @return int
     */
    public function handle()
    {
        try {
            $this->bootstrap();
            
            return $this->getPrime();
        } catch (Throwable $e) {
            $this->reportException($e);
            
            $this->renderException($e);

            return 1;
        }
        
    }
    
    /**
     * Bootstrap the application for artisan commands.
     * 
     * @return void
     */
    public function bootstrap()
    {
        if ( ! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }

        $this->app->loadDeferredProviders();
    }
    
    /**
     * Get the bootstrap classes for the application.
     * 
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    /**
     * Get the Prime application instance.
     * 
     * @return \Syscodes\Console\Application
     */
    protected function getPrime()
    {
        return (new Prime($this->app))->showHeader();
    }
    
    /**
     * Report the exception to the exception handler.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }
    
    /**
     * Render the exception to a response.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    protected function renderException(Throwable $e)
    {
        $this->app[ExceptionHandler::class]->renderForConsole($e);
    }
}