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

namespace Syscodes\Components\Database\Schema\Builders;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Facades\File;

/**
 * Allows you to manipulate of databases, tables and columns
 * for the SQLite database.
 */
class SQLiteBuilder extends Builder
{
    /**
     * Create a database in the schema.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function createDatabase($name): bool
    {
        return File::put($name, '') !== false;
    }
    
    /**
     * Drop a database from the schema if the database exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function dropDatabaseIfExists($name): bool
    {
        return ! File::exists($name) || File::delete($name);
    }

    /** @inheritDoc */
    public function getViews($schema = null): array
    {
        $schema ??= array_column($this->getSchemas(), 'name');

        $views = [];

        foreach (Arr::wrap($schema) as $name) {
            $views = array_merge($views, $this->connection->selectFromConnection(
                $this->grammar->compileViews($name)
            ));
        }

        return $this->connection->getPostProcessor()->processViews($views);
    }

    /** @inheritDoc */
    public function getColumns($table): array
    {
        [$schema, $table] = $this->parseSchemaAndTable($table);

        $table = $this->connection->getTablePrefix().$table;

        return $this->connection->getPostProcessor()->processColumns(
            $this->connection->selectFromConnection($this->grammar->compileColumns($schema, $table)),
            $this->connection->scalar($this->grammar->compileSqlCreateStatement($schema, $table))
        );
    }
    
    /**
     * Drop all tables from the database.
     * 
     * @return void
     */
    public function dropAllTables()
    {
        foreach ($this->getCurrentSchemaListing() as $schema) {
            $database = $schema === 'main'
                ? $this->connection->getDatabaseName()
                : (array_column($this->getSchemas(), 'path', 'name')[$schema] ?: ':memory:');

            if ($database !== ':memory:' &&
                ! str_contains($database, '?mode=memory') &&
                ! str_contains($database, '&mode=memory')
            ) {
                $this->refreshDatabaseFile($database);
            } else {
                $this->pragma('writable_schema', 1);

                $this->connection->statement($this->grammar->compileDropAllTables($schema));

                $this->pragma('writable_schema', 0);

                $this->connection->statement($this->grammar->compileRebuild($schema));
            }
        }
    }
    
    /**
     * Drop all views from the database.
     * 
     * @return void
     */
    public function dropAllViews()
    {
        foreach ($this->getCurrentSchemaListing() as $schema) {
            $this->pragma('writable_schema', 1);

            $this->connection->statement($this->grammar->compileDropAllViews($schema));

            $this->pragma('writable_schema', 0);

            $this->connection->statement($this->grammar->compileRebuild($schema));
        }
    }

    /**
     * Get the value for the given pragma name or set the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function pragma($key, $value = null)
    {
        return is_null($value)
            ? $this->connection->scalar($this->grammar->pragma($key))
            : $this->connection->statement($this->grammar->pragma($key, $value));
    }

    /**
     * Empty the database file.
     * 
     * @param  string|null  $path
     *
     * @return void
     */
    public function refreshDatabaseFile($path = null): void
    {
        file_put_contents($path ?? $this->connection->getDatabase(), '');
    }

    /**
     * Get the names of current schemas for the connection.
     *
     * @return string[]|null
     */
    public function getCurrentSchemaListing(): array
    {
        return ['main'];
    }
}