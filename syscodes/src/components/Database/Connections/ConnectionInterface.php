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

namespace Syscodes\Components\Database\Connections;

use Closure;

/**
 * Allows establish a query for return results in connection with database.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface ConnectionInterface
{
    /**
     * Begin a fluent query against a database table.
     * 
     * @param  string  $table
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function table($table);

    /**
     * Get a new raw query expression.
     * 
     * @param  mixed  $value
     * 
     * @return \Syscodes\Components\Database\Query\Expression
     */
    public function raw($value);

    /**
     * Run a select statement and return a single result.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return mixed
     */
    public function selectOne($query, $bindings = []);

    /**
     * Run a select statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return array
     */
    public function select($query, $bindings = []);

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
    public function insert($query, $bindings = []);

    /**
     * Run an update statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function update($query, $bindings = []);

    /**
     * Run an delete statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function delete($query, $bindings = []);

    /**
     * Prepare the query bindings for execution.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function prepareBindings(array $bindings);

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
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     * 
     * @return void
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     * 
     * @return void
     */
    public function rollback();

    /**
     * Checks the connection to see if there is an active transaction.
     * 
     * @return int
     */
    public function inTransaction();

    /**
     * Execute the given callback in "dry run" mode.
     * 
     * @param  \Closure  $callback
     * 
     * @return array
     */
    public function prepend(Closure $callback);
}