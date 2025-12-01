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

use Closure;
use Throwable;
use SplFileInfo;
use ReflectionClass;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Finder\Finder;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Console\Application as Prime;
use Syscodes\Components\Contracts\Debug\ExceptionHandler;
use Syscodes\Components\Core\Console\Commands\ClosureCommand;
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
     * The paths where Artisan commands should be automatically discovered.
     *
     * @var array
     */
    protected $commandPaths = [];

    /**
     * The paths where Artisan "routes" should be automatically discovered.
     *
     * @var array
     */
    protected $commandRoutePaths = [];

    /**
     * Indicates if the Closure commands have been loaded.
     *
     * @var bool
     */
    protected $commandsLoaded = false;
    
    /**
     * When the currently handled command started.
     * 
     * @var \Syscodes\Components\Support\Chronos|null
     */
    protected $commandStartedAt;

    /**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher $events
	 */
	protected $events;

    /**
     * The commands paths that have been "loaded".
     *
     * @var array
     */
    protected $loadedPaths = [];
    
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
        if ( ! defined('PRIME_BINARY')) {
            define('PRIME_BINARY', 'prime');
        }

        $this->app    = $app;
        $this->events = $events;
    }
    
    /**
     * Handle an incoming console command.
     * 
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
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
     * @param  \Syscodes\Components\Console\Command  $command
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
     * Register a Closure based command with the application.
     *
     * @param  string  $signature
     * @param  \Closure  $callback
     * 
     * @return \Syscodes\Components\Core\Console\Commands\ClosureCommand
     */
    public function command($signature, Closure $callback)
    {
        $command = new ClosureCommand($signature, $callback);

        Prime::starting(function ($prime) use ($command) {
            $prime->add($command);
        });

        return $command;
    }

    /**
     * Discover the commands that should be automatically loaded.
     *
     * @return void
     */
    protected function discoverCommands()
    {
        foreach ($this->commandPaths as $path) {
            $this->load($path);
        }

        foreach ($this->commandRoutePaths as $path) {
            if (file_exists($path)) {
                require $path;
            }
        }
    }

    /**
     * Determine if the kernel should discover commands.
     *
     * @return bool
     */
    protected function shouldDiscoverCommands()
    {
        return get_class($this) === __CLASS__;
    }

    /**
     * Register all of the commands in the given directory.
     *
     * @param  array|string  $paths
     * @return void
     */
    protected function load($paths)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $this->loadedPaths = array_values(
            array_unique(array_merge($this->loadedPaths, $paths))
        );

        $namespace = $this->app->getNamespace();

        foreach (Finder::create()->in($paths)->files() as $file) {
            $command = $this->commandClassFromFile($file, $namespace);

            if (is_subclass_of($command, Command::class) &&
                ! (new ReflectionClass($command))->isAbstract()) {
                Prime::starting(function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }

    /**
     * Extract the command class name from the given file path.
     *
     * @param  \SplFileInfo  $file
     * @param  string  $namespace
     * 
     * @return string
     */
    protected function commandClassFromFile(SplFileInfo $file, string $namespace): string
    {
        return $namespace.str_replace(
            ['/', '.php'],
            ['\\', ''],
            Str::after($file->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
        );
    }
    
    /**
     * Get all of the commands registered with the console.
     * 
     * @return array
     */
    public function all(): array
    {
        $this->bootstrap();
        
        return $this->getPrime()->all();
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

        if ( ! $this->commandsLoaded) {
            $this->commands();

            if ($this->shouldDiscoverCommands()) {
                $this->discoverCommands();
            }

            $this->commandsLoaded = true;
        }
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
        $this->app->finalize();

        if ($this->commandStartedAt === null) {
            return;
        }

        $this->commandStartedAt->setTimezone($this->app['config']->get('app.timezone') ?? 'UTC');

        $this->commandStartedAt = null;
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
                 ->resolveCommands($this->commands)
                 ->setResolveCommandLoader();
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
     * Set the Prime commands provided by the application.
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
     * Set the paths that should have their Prime commands automatically discovered.
     * 
     * @param  array  $paths
     * 
     * @return static
     */
    public function addCommandPaths(array $paths): static
    {
        $this->commandPaths = array_values(array_unique(array_merge($this->commandPaths, $paths)));
        
        return $this;
    }
    
    /**
     * Set the paths that should have their Prime "routes" automatically discovered.
     * 
     * @param  array  $paths
     * 
     * @return static
     */
    public function addCommandRoutePaths(array $paths): static
    {
        $this->commandRoutePaths = array_values(array_unique(array_merge($this->commandRoutePaths, $paths)));
        
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