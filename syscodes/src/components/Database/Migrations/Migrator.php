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

namespace Syscodes\Components\Database\Migrations;

use Closure;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
use Syscodes\Components\Console\View\Components\BulletList;
use Syscodes\Components\Console\View\Components\Info;
use Syscodes\Components\Console\View\Components\Task;
use Syscodes\Components\Console\View\Components\TwoColumnDetail;
use Syscodes\Components\Database\ConnectionResolverInterface as Resolver;
use Syscodes\Components\Database\Events\MigrationEnded;
use Syscodes\Components\Database\Events\MigrationSkipped;
use Syscodes\Components\Database\Events\MigrationStarted;
use Syscodes\Components\Database\Events\NoPendingMigrations;
use Syscodes\Components\Database\Migrations\Enums\MigrationResult;
use Syscodes\Components\Events\Dispatcher;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Str;

/**
 * Allows the run of a migration in given a path.
 */
class Migrator
{
    /**
     * The name of the default connection.
     *
     * @var string
     */
    protected $connection;

    /**
     * The custom connection resolver callback.
     *
     * @var (\Closure(\Syscodes\Components\Database\ConnectionResolverInterface, ?string): \Syscodes\Components\Database\Connection)|null
     */
    protected static $connectionResolverCallback;

    /**
     * The event dispatcher instance.
     *
     * @var \Syscodes\Components\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The filesystem instance.
     *
     * @var \Syscodes\Components\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The output interface implementation.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The paths to all of the migration files.
     *
     * @var string[]
     */
    protected $paths = [];

    /**
     * The migration repository implementation.
     *
     * @var \Syscodes\Components\Database\Migrations\MigrationRepositoryInterface
     */
    protected $repository;

    /**
     * The paths that have already been required.
     *
     * @var array
     */
    protected static $requiredPathCache = [];

    /**
     * The connection resolver instance.
     *
     * @var \Syscodes\Components\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The pending migrations to skip.
     *
     * @var array
     */
    protected static $withoutMigrations = [];

    /**
     * Constructor. Create a new migrator class instance.
     *
     * @param  \Syscodes\Components\Database\Migrations\MigrationRepositoryInterface  $repository
     * @param  \Syscodes\Components\Database\ConnectionResolverInterface  $resolver
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher|null  $dispatcher
     * 
     * @return void
     */
    public function __construct(
        MigrationRepositoryInterface $repository,
        Resolver $resolver,
        Filesystem $files,
        ?Dispatcher $dispatcher = null,
    ) {
        $this->files = $files;
        $this->events = $dispatcher;
        $this->resolver = $resolver;
        $this->repository = $repository;
    }

    /**
     * Run the pending migrations at a given path.
     *
     * @param  string[]|string  $paths
     * @param  array<string, mixed>  $options
     * 
     * @return string[]
     */
    public function run($paths = [], array $options = []): array
    {
        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $files = $this->getMigrationFiles($paths);

        $this->requireFiles($migrations = $this->pendingMigrations(
            $files, $this->repository->getRan()
        ));

        // Once we have all these migrations that are outstanding we are ready to run
        // we will go ahead and run them "up".
        $this->runPending($migrations, $options);

        return $migrations;
    }

    /**
     * Get the migration files that have not yet run.
     *
     * @param  string[]  $files
     * @param  string[]  $ran
     * 
     * @return string[]
     */
    protected function pendingMigrations($files, $ran): array
    {
        $migrationsToSkip = $this->migrationsToSkip();

        return (new Collection($files))
            ->reject(fn ($file) => in_array($migrationName = $this->getMigrationName($file), $ran) ||
                in_array($migrationName, $migrationsToSkip)
            )
            ->values()
            ->all();
    }

    /**
     * Get list of pending migrations to skip.
     *
     * @return array
     */
    protected function migrationsToSkip(): array
    {
        return (new Collection(self::$withoutMigrations))
            ->map($this->getMigrationName(...))
            ->all();
    }

    /**
     * Run an array of migrations.
     *
     * @param  string[]  $migrations
     * @param  array<string, mixed>  $options
     * 
     * @return void
     */
    public function runPending(array $migrations, array $options = []): void
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($migrations) === 0) {
            $this->fireMigrationEvent(new NoPendingMigrations('up'));

            $this->write(Info::class, 'Nothing to migrate');

            return;
        }

        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution.
        $batch = $this->repository->getNextBatchNumber();

        $pretend = $options['pretend'] ?? false;

        $step = $options['step'] ?? false;

        $this->fireMigrationEvent(new MigrationStarted('up', $options));

        $this->write(Info::class, 'Running migrations.');

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases.
        foreach ($migrations as $file) {
            $this->runUp($file, $batch, $pretend);

            if ($step) {
                $batch++;
            }
        }

        $this->fireMigrationEvent(new MigrationEnded('up', $options));

        $this->output?->writeln('');
    }

    /**
     * Run "up" a migration instance.
     *
     * @param  string  $file
     * @param  int  $batch
     * @param  bool  $pretend
     * 
     * @return void
     */
    protected function runUp($file, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        $shouldRunMigration = $migration instanceof Migration
            ? $migration->canRun()
            : true;

        if (! $shouldRunMigration) {
            $this->fireMigrationEvent(new MigrationSkipped($name));

            $this->write(Task::class, $name, fn () => MigrationResult::Skipped->value);
        } else {
            $this->write(Task::class, $name, fn () => $this->runMigration($migration, 'up'));

            // Once we have run a migrations class, we will log that it was run in this
            // repository so that we don't try to run it next time we do a migration
            // in the application.
            $this->repository->log($name, $batch);
        }
    }

    /**
     * Rollback the last migration operation.
     *
     * @param  string[]|string  $paths
     * @param  array<string, mixed>  $option
     * 
     * @return string[]
     */
    public function rollback($paths = [], array $options = []): array
    {
        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation.
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {
            $this->fireMigrationEvent(new NoPendingMigrations('down'));

            $this->write(Info::class, 'Nothing to rollback.');

            return [];
        }

        return take($this->rollbackMigrations($migrations, $paths, $options), function () {
            $this->output?->writeln('');
        });
    }

    /**
     * Get the migrations for a rollback operation.
     *
     * @param  array<string, mixed>  $options
     * 
     * @return array
     */
    protected function getMigrationsForRollback(array $options): array
    {
        if (($steps = $options['step'] ?? 0) > 0) {
            return $this->repository->getMigrations($steps);
        }

        if (($batch = $options['batch'] ?? 0) > 0) {
            return $this->repository->getMigrationsByBatch($batch);
        }

        return $this->repository->getLast();
    }

    /**
     * Rollback the given migrations.
     *
     * @param  array  $migrations
     * @param  string[]|string  $paths
     * @param  array<string, mixed>  $options
     * 
     * @return string[]
     */
    protected function rollbackMigrations(array $migrations, $paths, array $options): array
    {
        $rolledBack = [];

        $this->requireFiles($files = $this->getMigrationFiles($paths));

        $this->fireMigrationEvent(new MigrationStarted('down', $options));

        $this->write(Info::class, 'Rolling back migrations.');

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order.
        foreach ($migrations as $migration) {
            $migration = (object) $migration;

            if (! $file = Arr::get($files, $migration->migration)) {
                $this->write(TwoColumnDetail::class, $migration->migration, '<fg=yellow;options=bold>Migration not found</>');

                continue;
            }

            $rolledBack[] = $file;

            $this->runDown(
                $file, $migration,
                $options['pretend'] ?? false
            );
        }

        $this->fireMigrationEvent(new MigrationEnded('down', $options));

        return $rolledBack;
    }

    /**
     * Rolls all of the currently applied migrations back.
     *
     * @param  string[]|string  $paths
     * @param  bool  $pretend
     * 
     * @return array
     */
    public function reset($paths = [], $pretend = false): array
    {
        // Next, we will reverse the migration list so we can run them back in the
        // correct order for resetting this database. This will allow us to get
        // the database back into its "empty" state ready for the migrations.
        $migrations = array_reverse($this->repository->getRan());

        if (count($migrations) === 0) {
            $this->write(Info::class, 'Nothing to rollback.');

            return [];
        }

        return take($this->resetMigrations($migrations, Arr::wrap($paths), $pretend), function () {
            $this->output?->writeln('');
        });
    }

    /**
     * Reset the given migrations.
     *
     * @param  string[]  $migrations
     * @param  string[]  $paths
     * @param  bool  $pretend
     * 
     * @return array
     */
    protected function resetMigrations(array $migrations, array $paths, $pretend = false): array
    {
        // Since the getRan method that retrieves the migration name just gives us the
        // migration name, we will format the names into objects with the name as a
        // property on the objects so that we can pass it to the rollback method.
        $migrations = (new Collection($migrations))->map(fn ($m) => (object) ['migration' => $m])->all();

        return $this->rollbackMigrations(
            $migrations, $paths, compact('pretend')
        );
    }

    /**
     * Run "down" a migration instance.
     *
     * @param  string  $file
     * @param  object  $migration
     * @param  bool  $pretend
     * 
     * @return void
     */
    protected function runDown($file, $migration, $pretend)
    {
        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration.
        $instance = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        if ($pretend) {
            return $this->pretendToRun($instance, 'down');
        }

        $this->write(Task::class, $name, fn () => $this->runMigration($instance, 'down'));

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($migration);
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     *
     * @param  object  $migration
     * @param  string  $method
     * 
     * @return void
     */
    protected function runMigration($migration, $method): void
    {
        $connection = $this->resolveConnection(
            $migration->getConnection()
        );

        $callback = function () use ($connection, $migration, $method) {
            if (method_exists($migration, $method)) {
                $this->fireMigrationEvent(new MigrationStarted($migration, $method));

                $this->runMethod($connection, $migration, $method);

                $this->fireMigrationEvent(new MigrationEnded($migration, $method));
            }
        };

        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
            && $migration->withinTransaction
                ? $connection->transaction($callback)
                : $callback();
    }

    /**
     * Pretend to run the migrations.
     *
     * @param  object  $migration
     * @param  string  $method
     * 
     * @return void
     */
    protected function pretendToRun($migration, $method): void
    {
        $name = get_class($migration);

        $reflectionClass = new ReflectionClass($migration);

        if ($reflectionClass->isAnonymous()) {
            $name = $this->getMigrationName($reflectionClass->getFileName());
        }

        $this->write(TwoColumnDetail::class, $name);

        $this->write(
            BulletList::class,
            (new Collection($this->getQueries($migration, $method)))->map(fn ($query) => $query['query'])
        );
    }

    /**
     * Get all of the queries that would be run for a migration.
     *
     * @param  object  $migration
     * @param  string  $method
     * 
     * @return array
     */
    protected function getQueries($migration, $method): array
    {
        // Now that we have the connections we can resolve it and pretend to run the
        // queries against the database returning the array of raw SQL statements
        // that would get fired against the database system for this migration.
        $db = $this->resolveConnection(
            $migration->getConnection()
        );

        return $db->pretend(function () use ($db, $migration, $method) {
            if (method_exists($migration, $method)) {
                $this->runMethod($db, $migration, $method);
            }
        });
    }

    /**
     * Run a migration method on the given connection.
     *
     * @param  \Syscodes\Components\Database\Connection  $connection
     * @param  object  $migration
     * @param  string  $method
     * 
     * @return void
     */
    protected function runMethod($connection, $migration, $method)
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        try {
            $this->resolver->setDefaultConnection($connection->getName());

            $migration->{$method}();
        } finally {
            $this->resolver->setDefaultConnection($previousConnection);
        }
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * 
     * @return object
     */
    public function resolve($file): object
    {
        $class = $this->getMigrationClass($file);

        return new $class;
    }

    /**
     * Resolve a migration instance from a migration path.
     *
     * @param  string  $path
     * 
     * @return object
     */
    protected function resolvePath(string $path): object
    {
        $class = $this->getMigrationClass($this->getMigrationName($path));

        if (class_exists($class) && realpath($path) == (new ReflectionClass($class))->getFileName()) {
            return new $class;
        }

        $migration = static::$requiredPathCache[$path] ??= $this->files->getRequire($path);

        if (is_object($migration)) {
            return method_exists($migration, '__construct')
                ? $this->files->getRequire($path)
                : clone $migration;
        }

        return new $class;
    }

    /**
     * Generate a migration class name based on the migration file name.
     *
     * @param  string  $migrationName
     * 
     * @return string
     */
    protected function getMigrationClass(string $migrationName): string
    {
        return Str::studlycaps(implode('_', array_slice(explode('_', $migrationName), 4)));
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param  string|array  $paths
     * 
     * @return array<string, string>
     */
    public function getMigrationFiles($paths): array
    {
        return (new Collection($paths))
            ->flatMap(fn ($path) => str_ends_with($path, '.php') ? [$path] : $this->files->glob($path.'/*_*.php'))
            ->filter()
            ->values()
            ->keyBy(fn ($file) => $this->getMigrationName($file))
            ->sortBy(fn ($file, $key) => $key)
            ->all();
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param  string[]  $files
     * 
     * @return void
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->getRequireOnce($file);
        }
    }

    /**
     * Get the name of the migration.
     *
     * @param  string  $path
     * 
     * @return string
     */
    public function getMigrationName($path): string
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Register a custom migration path.
     *
     * @param  string  $path
     * 
     * @return void
     */
    public function path($path): void
    {
        $this->paths = array_unique(array_merge($this->paths, [$path]));
    }

    /**
     * Get all of the custom migration paths.
     *
     * @return string[]
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * Set the pending migrations to skip.
     *
     * @param  array  $migrations
     * 
     * @return void
     */
    public static function withoutMigrations(array $migrations)
    {
        static::$withoutMigrations = $migrations;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute the given callback using the given connection as the default connection.
     *
     * @template TReturn
     *
     * @param  string  $name
     * @param  callable  $callback
     * 
     * @return mixed
     */
    public function usingConnection($name, callable $callback)
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        $this->setConnection($name);

        try {
            return $callback();
        } finally {
            $this->setConnection($previousConnection);
        }
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * 
     * @return void
     */
    public function setConnection($name): void
    {
        if (! is_null($name)) {
            $this->resolver->setDefaultConnection($name);
        }

        $this->repository->setSource($name);

        $this->connection = $name;
    }

    /**
     * Resolve the database connection instance.
     *
     * @param  string  $connection
     * 
     * @return \Syscodes\Components\Database\Connection
     */
    public function resolveConnection($connection)
    {
        if (static::$connectionResolverCallback) {
            return call_user_func(
                static::$connectionResolverCallback,
                $this->resolver,
                $connection ?: $this->connection
            );
        } else {
            return $this->resolver->connection($connection ?: $this->connection);
        }
    }

    /**
     * Set a connection resolver callback.
     *
     * @param  \Closure(\Syscodes\Components\Database\ConnectionResolverInterface, ?string): \Syscodes\Components\Database\Connection  $callback
     * 
     * @return void
     */
    public static function resolveConnectionsUsing(Closure $callback): void
    {
        static::$connectionResolverCallback = $callback;
    }

    /**
     * Get the schema grammar out of a migration connection.
     *
     * @param  \Syscodes\Components\Database\Connection  $connection
     * 
     * @return \Syscodes\Components\Database\Schema\Grammars\Grammar
     */
    protected function getSchemaGrammar($connection)
    {
        if (is_null($grammar = $connection->getSchemaGrammar())) {
            $connection->useDefaultSchemaGrammar();

            $grammar = $connection->getSchemaGrammar();
        }

        return $grammar;
    }

    /**
     * Get the migration repository instance.
     *
     * @return \Syscodes\Components\Database\Migrations\MigrationRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists(): bool
    {
        return $this->repository->repositoryExists();
    }

    /**
     * Determine if any migrations have been run.
     *
     * @return bool
     */
    public function hasRunAnyMigrations(): bool
    {
        return $this->repositoryExists() && count($this->repository->getRan()) > 0;
    }

    /**
     * Delete the migration repository data store.
     *
     * @return void
     */
    public function deleteRepository(): void
    {
        $this->repository->deleteRepository();
    }

    /**
     * Get the file system instance.
     *
     * @return \Syscodes\Components\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Set the output implementation that should be used by the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return static
     */
    public function setOutput(OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Write to the console's output.
     *
     * @param  string  $component
     * @param  array<int, string>|string  ...$arguments
     * 
     * @return void
     */
    protected function write($component, ...$arguments)
    {
        if ($this->output && class_exists($component)) {
            (new $component($this->output))->render(...$arguments);
        } else {
            foreach ($arguments as $argument) {
                if (is_callable($argument)) {
                    $argument();
                }
            }
        }
    }

    /**
     * Fire the given event for the migration.
     *
     * @param  \Syscodes\Components\Contracts\Database\Events\MigrationEvent  $event
     * 
     * @return void
     */
    public function fireMigrationEvent($event)
    {
        $this->events?->dispatch($event);
    }
}