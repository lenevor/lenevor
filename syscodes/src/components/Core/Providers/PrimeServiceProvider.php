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
use Syscodes\Components\Core\Console\Commands\KeyGenerateCommand;

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
        'KeyGenerate' => KeyGenerateCommand::class,
    ];

    /**
     * The commands to be registered.
     * 
     * @var array $devCommands
     */
    protected $devCommands = [
        'Serve' => ServeCommand::class,
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
        $this->app->singleton(AboutCommand::class, function () {
            return new AboutCommand();
        });
    }

    /**
     * Register the command.
     * 
     * @return void
     */
    protected function registerServeCommand()
    {
        $this->app->singleton(ServeCommand::class, function () {
            return new ServeCommand();
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