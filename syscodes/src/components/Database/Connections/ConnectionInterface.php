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

namespace Syscodes\Components\Database\Connections;

use Closure;

/**
 * Allows establish a query for return results in connection with database.
 */
interface ConnectionInterface
{
    /**
     * Begin a fluent query against a database table.
     * 
     * @param  \Closure|\Syscodes\Components\Database\Query\Builder|string  $table
     * @param  string|null  $as 
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function table($table, string $as = null);

    /**
     * Get a new raw query expression.
     * 
     * @param  mixed  $value
     * 
     * @return \Syscodes\Components\Database\Query\Expression
     */
    public function raw(mixed $value);

    /**
     * Run a select statement and return a single result.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return mixed
     */
    public function selectOne(string $query, array $bindings = []): mixed;

    /**
     * Run a select statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo 
     * 
     * @return array
     */
    public function select(string $query, array $bindings = [], bool $useReadPdo = true): array;

    /**
     * Execute an SQL statement and return the boolean result.
     * 
     * @return \PDOStatement
     */
    public function query();

    /**
     * Run an insert statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return bool
     */
    public function insert(string $query, array $bindings = []): bool;

    /**
     * Run an update statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function update(string $query, array $bindings = []): int;

    /**
     * Run an delete statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function delete(string $query, array $bindings = []): int;

    /**
     * Prepare the query bindings for execution.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function prepareBindings(array $bindings): array;

    /**
     * Execute a Closure within a transaction.
     * 
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Throwable
     */
    public function transaction(Closure $callback);

    /**
     * Start a new database transaction.
     * 
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the active database transaction.
     * 
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the active database transaction.
     * 
     * @return void
     */
    public function rollback();

    /**
     * Get the number of active transactions.
     * 
     * @return int
     */
    public function transactionLevel(): int;

    /**
     * Execute the given callback in "dry run" mode.
     * 
     * @param  \Closure  $callback
     * 
     * @return array
     */
    public function prepend(Closure $callback): array;
}