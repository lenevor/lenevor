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

namespace Syscodes\Components\Database\Console\Migrations;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Database\Migrations\Migrator;
use Throwable;

/**
 * Allows up migrations in the database.
 */
#[AsCommand(name: 'migrate')]
class MigrateCommand extends BaseMigrationCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

     /**
     * The migrator instance.
     *
     * @var \Syscodes\Components\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * The event dispatcher instance.
     *
     * @var \Syscodes\Components\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Constructor. Create a new migration command instance.
     * 
     * @param  \Syscodes\Components\Database\Migrations\Migrator  $migrator
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $dispatcher
     * 
     * @return void
     */
    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->dispatcher = $dispatcher;
    }

     /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Throwable
     */
    public function handle()
    {
        if ( ! $this->confirmToProceed()) {
            return 1;
        }

        try {
            $this->runMigrations();
        } catch (Throwable $e) {
            if ($this->option('graceful')) {
                $this->components->warning($e->getMessage());

                return 0;
            }

            throw $e;
        }

        return 0;
    }

    /**
     * Run the pending migrations.
     *
     * @return void
     */
    protected function runMigrations()
    {
        $this->migrator->usingConnection($this->option('database'), function () {
            $this->prepareDatabase();

            // Next, we will check to see if a path option has been defined..
            $this->migrator->setOutput($this->output)
                ->run($this->getMigrationPaths(), [
                    'pretend' => $this->option('pretend'),
                    'step' => $this->option('step'),
                ]);

            // Finally, if the "seed" option has been given, we will re-run the database
            // seed task to re-populate the database.
            if ($this->option('seed') && ! $this->option('pretend')) {
                $this->call('db:seed', [
                    '--class' => $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
                    '--force' => true,
                ]);
            }
        });
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if ( ! $this->migrator->repositoryExists()) {
            $this->components->info('Preparing database.');

            $this->components->task('Creating migration table', function () {
                return $this->callSilent('migrate:install', array_filter([
                    '--database' => $this->option('database'),
                ])) == 0;
            });

            $this->newLine();
        }
    }

    /**
     * Get the console command options.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],   
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path(s) to the migrations files to be executed', null],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],
            ['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually'],
            ['graceful', null, InputOption::VALUE_NONE, 'Return a successful exit code even if an error occurs'],            
        ];
    }
}