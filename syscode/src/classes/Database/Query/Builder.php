<?php 

/**
 * Lenevor PHP Framework
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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.0
 */
 
namespace Syscode\Database\Query;

use Closure;
use RuntimeException;
use DateTimeInterface;
use Syscode\Support\Str;
use BadMethodCallException;
use InvalidArgumentException;
use Syscode\Database\Query\Access;
use Syscode\Database\Query\Grammar;
use Syscode\Database\Query\Expression;
use Syscode\Database\Query\JoinClause;
use Syscode\Database\ConnectionInterface;

/**
 * Lenevor database query builder provides a convenient, fluent interface 
 * to creating and running database queries. and works on all supported 
 * database systems.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Builder
{
    /**
     * An aggregate function and column to be run.
     * 
     * @var array $aggregate
     */
    public $aggregate;

    /**
     * The current query value bindings.
     * 
     * @var array $bindings
     */
    public $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
    ];

    /**
     * Get the columns of a table.
     * 
     * @var array $columns
     */
    public $columns;

    /**
     * The database connection instance.
     * 
     * @var \Syscode\Database\ConnectionInterface $connection
     */
    protected $connection;

    /**
     * Indicates if the query returns distinct results.
     * 
     * @var bool $distinct
     */
    public $distinct = false;

    /**
     * Get the table name for the query.
     * 
     * @var string $from
     */
    public $from;

    /**
     * The database query grammar instance.
     * 
     * @var \Syscode\Database\Query\Grammar $grammar
     */
    protected $grammar;

    /**
     * Get the grouping for the query.
     * 
     * @var array $groups
     */
    public $groups;

    /**
     * Get the having constraints for the query.
     * 
     * @var array $havings
     */
    public $havings;

    /**
     * Get the table joins for the query.
     * 
     * @var array $joins
     */
    public $joins;

    /**
     * Get the maximum number of records to return.
     * 
     * @var int $limit
     */
    public $limit;

    /**
     * Indicates whether row locking is being used.
     * 
     * @var string|bool $lock
     */
    public $lock;

    /**
     * Get the number of records to skip.
     * 
     * @var int $offset
     */
    public $offset;

    /**
     * Get the orderings for the query.
     * 
     * @var array $orders
     */
    public $orders;

    /**
     * The database query post processor instance.
     * 
     * @var \Syscode\Database\Query\Processor $processor
     */
    protected $processor;

    /**
     * Get the query union statements.
     * 
     * @var array $unions
     */
    public $unions;

    /**
     * Get the maximum number of union records to return.
     * 
     * @var int $unionLimit
     */
    public $unionLimit;

    /**
     * Get the number of union records to skip.
     * 
     * @var int $unionOffset
     */
    public $unionOffset;

    /**
     * Get the orderings for the union query.
     * 
     * @var array $unionOrders
     */
    public $unionOrders;

    /**
     * Get the where constraints for the query.
     * 
     * @var array $wheres
     */
    public $wheres;

    /**
     * Constructor. Create a new query builder instance.
     * 
     * @param  \Syscode\Database\ConnectionInterface  $connection
     * @param  \Syscode\Database\Query\Grammar  $grammar
     * @param  \Syscode\Database\Query\Processor  $processor
     * 
     * return void
     */
    public function __construct(ConnectionInterface $connection, Grammar $grammar = null, Processor $processor = null)
    {
        $this->grammar = $grammar ?: $this->getQueryGrammar();
        $this->processor = $processor ?: $this->getQueryProcessor();
        $this->connection = $connection;
    }

    /**
     * Set the columns to be selected.
     * 
     * @param  array  $columns
     * 
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Allows force the query for return distinct results.
     * 
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }
    
    /**
     * Set the table which the query.
     * 
     * @param  string  $table
     * 
     * @return $this
     */
    public function from($table)
    {
        $this->from = $table;

        return $this;
    }

    /**
     * Get the SQL representation of the query.
     * 
     * @return string
     */
    public function getSql()
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * Get a new instance of the query builder.
     * 
     * @return \Syscode\Database\Query\Builder
     */
    public function newQuery()
    {
        return new Builder($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Add a binding to the query sql.
     * 
     * @param  mixed  $value
     * @param  string  $type  ('where' by default)
     * 
     * @return $this
     */
    public function addBinding($value, $type = 'where')
    {
        
    }

    /**
     * Get the database connection instance.
     * 
     * @return \Syscode\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the database query processor instance.
     * 
     * @return \Syscode\Database\Query\Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the database query grammar instance.
     * 
     * @return \Syscode\Database\Query\Grammar
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * Dynamically handle calls to methods on the class.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        $classname = get_class($this);

        throw new BadMethodCallException("Call to undefined method {$classname}::{$method}()");
    }
}