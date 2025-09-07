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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Console;

use Throwable;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Contracts\Debug\ExceptionHandler;
use Syscodes\Components\Console\Application as Prime;
use Syscodes\Components\Contracts\Console\Kernel as KernelContract;

/**
 * The Lenevor class is the heart of the system when use 
 * the console of commands in framework.
 */
class Kernel implements KernelContract
{
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * The bootstrap classes for the application.
     * 
     * @var array $bootstrappers
     */
    protected $bootstrappers = [
        \Syscodes\Components\Core\Bootstrap\BootDetectEnvironment::class,
        \Syscodes\Components\Core\Bootstrap\BootConfiguration::class,
        \Syscodes\Components\Core\Bootstrap\BootHandleExceptions::class,
        \Syscodes\Components\Core\Bootstrap\BootRegisterFacades::class,
        \Syscodes\Components\Core\Bootstrap\BootRegisterProviders::class,
        \Syscodes\Components\Core\Bootstrap\BootProviders::class,
    ];
    
    /**
     * The Prime commands provided by the application.
     * 
     * @var array $commands
     */
    protected $commands = [];

    /**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher $events
	 */
	protected $events;
    
    /**
     * The Prime application instance.
     * 
     * @var \Syscodes\Components\Console\Application|null $prime
     */
    protected $prime;
    
    /**
     * Constructor. Create new console Lenevor instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application $app
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $events
     * 
     * @return void
     */
    public function __construct(Application $app, Dispatcher $events)
    {
        $this->app    = $app;
        $this->events = $events;
    }
    
    /**
     * Handle an incoming console command.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output|null  $output
     * 
     * @return int
     */
    public function handle($input, $output = null): int
    {
        try {
            $this->bootstrap();
            
            return $this->getPrime()->run($input, $output);
        } catch (Throwable $e) {
            $this->reportException($e);
            
            $this->renderException($output, $e);
            
            return 1;
        }
    }
    
    /**
     * Register the given command with the console application.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * 
     * @return void
     */
    public function registerCommand($command): void
    {
        $this->getPrime()->add($command);
    }

    /**
     * Register the commands for the application.
     * 
     * @return void
     */
    protected function commands(): void
    {
        //
    }
    
    /**
     * Bootstrap the application for artisan commands.
     * 
     * @return void
     */
    public function bootstrap(): void
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
    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }
    
    /**
     * Shutdown the application.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
	 * @param  int  $status
     * 
     * @return void
     */
    public function finalize($input, int $status): void
    {
        $this->app->shutdown();
    }
    
    /**
     * Get the Prime application instance.
     * 
     * @return \Syscodes\Components\Console\Application
     */
    protected function getPrime()
    {
        if (is_null($this->prime)) {
            $this->prime = (new Prime($this->app, $this->events, $this->app->version()))
                 ->resolveCommands($this->commands);
        }

        return $this->prime;
    }

    /**
     * Set the Prime application instance.
     * 
     * @param  \Syscodes\Components\Console\Application  $prime
     * 
     * @return void
     */
    public function setPrime($prime): void
    {
        $this->prime = $prime;
    }
    
    /**
     * Set the Artisan commands provided by the application.
     * 
     * @param  array  $commands
     * 
     * @return $this
     */
    public function addCommands(array $commands): static
    {
        $this->commands = array_values(array_unique(array_merge($this->commands, $commands)));
        
        return $this;
    }
    
    /**
     * Report the exception to the exception handler.
     * 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    protected function reportException(Throwable $e): void
    {
        $this->app[ExceptionHandler::class]->report($e);
    }
    
    /**
     * Render the exception to a response.
     * 
     * @param  \Syscodes\Contracts\Console\Output  $output 
     * @param  \Throwable  $e
     * 
     * @return void
     */
    protected function renderException($output, Throwable $e): void
    {
        $this->app[ExceptionHandler::class]->renderForConsole($output, $e);
    }
}