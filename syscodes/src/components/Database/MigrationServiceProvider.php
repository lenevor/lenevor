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

namespace Syscodes\Components\Database;

use Syscodes\Components\Contracts\Support\Deferrable;
use Syscodes\Components\Database\Console\Migrations\InstallCommand;
use Syscodes\Components\Database\Migrations\DatabaseMigrationRepository;
use Syscodes\Components\Database\Migrations\Migrator;
use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the classes from the container of services.
 */
class MigrationServiceProvider extends ServiceProvider implements Deferrable
{
    /**
     * The commands to be registered.
     * 
     * @var array
     */
    protected $commands = [
        'MigrateInstall' => InstallCommand::class,
    ];

    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register()
    {
        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCommands($this->commands);
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton('migration.repository', function ($app) {
            $migrations = $app['config']['database.migrations'];

            $table = is_array($migrations) ? ($migrations['table'] ?? null) : $migrations;

            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }

    /**
     * Register the migrator service.
     *
     * @return void
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application.
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });

        $this->app->bind(Migrator::class, fn ($app) => $app['migrator']);
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateInstallCommand()
    {
        $this->app->singleton(InstallCommand::class, function ($app) {
            return new InstallCommand($app['migration.repository']);
        });
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
        foreach (array_keys($commands) as $command) {
            $this->{"register{$command}Command"}();
        }

        $this->commands(array_values($commands));
    }

    /**
     * Get the services provided by the provider.
     * 
     * @return array
     */
    public function provides(): array
    {
        return array_merge([
            'migrator', 'migration.repository', Migrator::class,
        ], array_values($this->commands));
    }
}