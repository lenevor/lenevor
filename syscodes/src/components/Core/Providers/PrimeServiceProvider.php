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

namespace Syscodes\Components\Core\Providers;

use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\Contracts\Support\Deferrable;
use Syscodes\Components\Core\Console\Commands\AboutCommand;
use Syscodes\Components\Core\Console\Commands\ServeCommand;
use Syscodes\Components\Core\Console\Commands\ViewMakeCommand;
use Syscodes\Components\Routing\Console\ControllerMakeCommand;
use Syscodes\Components\Routing\Console\MiddlewareMakeCommand;
use Syscodes\Components\Core\Console\Commands\ClassMakeCommand;
use Syscodes\Components\Core\Console\Commands\EventMakeCommand;
use Syscodes\Components\Core\Console\Commands\TraitMakeCommand;
use Syscodes\Components\Core\Console\Commands\ViewClearCommand;
use Syscodes\Components\Core\Console\Commands\ApiInstallCommand;
use Syscodes\Components\Core\Console\Commands\ConfigMakeCommand;
use Syscodes\Components\Core\Console\Commands\ConfigCacheCommand;
use Syscodes\Components\Core\Console\Commands\ConfigClearCommand;
use Syscodes\Components\Core\Console\Commands\EnvironmentCommand;
use Syscodes\Components\Core\Console\Commands\KeyGenerateCommand;
use Syscodes\Components\Core\Console\Commands\RequestMakeCommand;
use Syscodes\Components\Core\Console\Commands\ResourceMakeCommand;
use Syscodes\Components\Core\Console\Commands\ClearCompiledCommand;
use Syscodes\Components\Core\Console\Commands\InterfaceMakeCommand;

/**
 * The Prime service provider allows the register of a namespace of 
 * all the commands necessary for operate the framewore from the CLI.
 */
class PrimeServiceProvider extends ServiceProvider implements Deferrable
{
    /**
     * The commands to be registered.
     * 
     * @var array $commands
     */
    protected $commands = [
        'About' => AboutCommand::class,
        'ClearCompiled' => ClearCompiledCommand::class,
        'ConfigCache' => ConfigCacheCommand::class,
        'ConfigClear' => ConfigClearCommand::class,
        'Environment' => EnvironmentCommand::class,
        'KeyGenerate' => KeyGenerateCommand::class,
        'ViewClear' => ViewClearCommand::class,
    ];

    /**
     * The commands to be registered.
     * 
     * @var array $devCommands
     */
    protected $devCommands = [
        'ApiInstall' => ApiInstallCommand::class,
        'ClassMake' => ClassMakeCommand::class,
        'ConfigMake' => ConfigMakeCommand::class,
        'ControllerMake' => ControllerMakeCommand::class,
        'EventMake' => EventMakeCommand::class,
        'InterfaceMake' => InterfaceMakeCommand::class,
        'MiddlewareMake' => MiddlewareMakeCommand::class,
        'RequestMake' => RequestMakeCommand::class,
        'ResourceMake' => ResourceMakeCommand::class,
        'Serve' => ServeCommand::class,
        'TraitMake' => TraitMakeCommand::class,
        'ViewMake' => ViewMakeCommand::class,
    ];

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->registerCommands(array_merge(
            $this->commands,
            $this->devCommands,
        ));
    }
    
    /**
     * Register the given commands.
     * 
     * @param  array  $commands
     * 
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach ($commands as $commandName => $command) {
            $method = "register{$commandName}Command";

            if (method_exists($this, $method)) {
                $this->{$method}();
            } else {
                $this->app->singleton($command);
            }
        }

        $this->commands(array_values($commands));
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerAboutCommand()
    {
        $this->app->singleton(AboutCommand::class, fn () => new AboutCommand());
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerClassMakeCommand()
    {
        $this->app->singleton(ClassMakeCommand::class, function ($app) {
            return new ClassMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerConfigCacheCommand()
    {
        $this->app->singleton(ConfigCacheCommand::class, function ($app) {
            return new ConfigCacheCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerConfigClearCommand()
    {
        $this->app->singleton(ConfigClearCommand::class, function ($app) {
            return new ConfigClearCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerConfigMakeCommand()
    {
        $this->app->singleton(ConfigMakeCommand::class, function ($app) {
            return new ConfigMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerControllerMakeCommand()
    {
        $this->app->singleton(ControllerMakeCommand::class, function ($app) {
            return new ControllerMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerEventMakeCommand()
    {
        $this->app->singleton(EventMakeCommand::class, function ($app) {
            return new EventMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerInterfaceMakeCommand()
    {
        $this->app->singleton(InterfaceMakeCommand::class, function ($app) {
            return new InterfaceMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMiddlewareMakeCommand()
    {
        $this->app->singleton(MiddlewareMakeCommand::class, function ($app) {
            return new MiddlewareMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerRequestMakeCommand()
    {
        $this->app->singleton(RequestMakeCommand::class, function ($app) {
            return new RequestMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerResourceMakeCommand()
    {
        $this->app->singleton(ResourceMakeCommand::class, function ($app) {
            return new ResourceMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerServeCommand()
    {
        $this->app->singleton(ServeCommand::class, fn () => new ServeCommand());
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerTraitMakeCommand()
    {
        $this->app->singleton(TraitMakeCommand::class, function ($app) {
            return new TraitMakeCommand($app['files']);
        });
    }
    
    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerViewClearCommand()
    {
        $this->app->singleton(ViewClearCommand::class, function ($app) {
            return new ViewClearCommand($app['files']);
        });
    }
    
    /**
     * Get the services provided by the provider.
     * 
     * @return array
     */
    public function provides(): array
    {
        return array_merge(array_values($this->commands), array_values($this->devCommands));
    }
}