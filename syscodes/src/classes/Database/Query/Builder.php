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
 
namespace Syscodes\Database\Query;

use Closure;
use RuntimeException;
use DateTimeInterface;
use Syscodes\Support\Arr;
use Syscodes\Support\Str;
use BadMethodCallException;
use InvalidArgumentException;
use Syscodes\Database\DatabaseCache;
use Syscodes\Database\Query\Grammar;
use Syscodes\Database\Query\Processor;
use Syscodes\Database\Query\Expression;
use Syscodes\Database\Query\JoinClause;
use Syscodes\Database\ConnectionInterface;

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
     * The cache driver to be used.
     * 
     * @var string $cacheDriver
     */
    protected $cacheDriver;

    /**
     * The key that should be used when caching the query.
     * 
     * @var string $cacheKey
     */
    protected $cacheKey;

    /**
     * The number of minutes to cache the query
     * 
     * @var int $cacheMinutes
     */
    protected $cacheMinutes;

    /**
     * The tags for the query cache.
     * 
     * @var array $cacheTags
     */
    protected $cacheTags;

    /**
     * Get the columns of a table.
     * 
     * @var array $columns
     */
    public $columns;

    /**
     * The database connection instance.
     * 
     * @var \Syscodes\Database\ConnectionInterface $connection
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
     * @var \Syscodes\Database\Query\Grammar $grammar
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
     * @var \Syscodes\Database\Query\Processor $processor
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
     * @param  \Syscodes\Database\ConnectionInterface  $connection
     * @param  \Syscodes\Database\Query\Grammar  $grammar  (null by default)
     * @param  \Syscodes\Database\Query\Processor  $processor  (null by default)
     * 
     * return void
     */
    public function __construct(ConnectionInterface $connection, Grammar $grammar = null, Processor $processor = null)
    {
        $this->processor = $processor ?: $this->getQueryProcessor();
        $this->grammar = $grammar ?: $this->getQueryGrammar();
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
     * Execute the query as a "select" statement.
     * 
     * @param  array  $columns
     * 
     * @return array|static[]
     */
    public function get($columns = ['*'])
    {
        return $this->getFreshStatement($columns);
    }    

    /**
     * Execute the query as a fresh "select" statement.
     * 
     * @param  array  $columns
     * 
     * @return array|static[]
     */
    public function getFreshStatement($columns = ['*'])
    {
        if (is_null($this->columns))
        {
            $this->columns = $columns;
        }

        return $this->processor->processSelect($this, $this->runOnSelectStatement());
    }

    /**
     * Run the query as a "select" statement against the connection.
     * 
     * @return array
     */
    public function runOnSelectStatement()
    {
        return $this->connection->select($this->getSql(), $this->getBinding());
    }

    /**
     * Execute an aggregate function on the database.
     * 
     * 
     */

    /**
     * Insert a new record into the database.
     * 
     * @param  array  $values
     * 
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values))
        {
            return true;
        }

        if ( ! is_array(reset($values)))
        {
            $values = [$values];
        }
        else
        {
            foreach ($values as $key => $value)
            {
                ksort($value);

                $values[$key] = $value;
            }
        }

        $sql      = $this->grammar->compileInsert($this, $values);
        $bindings = $this->cleanBindings($this->buildInsertBinding($values));

        return $this->connection->insert($sql, $bindings);
    }

    /**
     * It insert like a batch data so we can easily insert each 
     * records into the database consistenly.
     * 
     * @param  array  $values
     */
    private function buildInsertBinding(array $values)
    {
        $bindings = [];

        foreach ($values as $record)
        {
            foreach ($record as $value)
            {
                $bindings[] = $value;
            }
        }

        return $bindings;
    }

    /**
     * Insert a new record and get the value of the primary key.
     * 
     * @param  array  $values
     * @param  string  $sequence  (null by default)
     * 
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        $values = $this->cleanBindings($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }

    /**
     * Update a record in the database.
     * 
     * @param  array  $values
     * 
     * @return \PDOStatement
     */
    public function update(array $values)
    {
        $bindings = array_values(array_merge($values, $this->bindings));

        $sql = $this->grammar->compileUpdate($this, $values);

        return $this->connection->query($sql, $this->cleanBindings($bindings));
    }

    /**
     * Increment a column's value by a given amount.
     * 
     * @param  string  $column
     * @param  int  $amount  (1 by default)
     * @param  array  $extra
     * 
     * @return \PDOStatement
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        if ( ! is_numeric($amount))
        {
            throw new InvalidArgumentException("Non-numeric value passed to increment method");
        }

        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("$wrapped + $amount"), $extra]);

        return $this->update($columns);
    }

    /**
     * Decrement a column's value by a given amount.
     * 
     * @param  string  $column
     * @param  int  $amount  (1 by default)
     * @param  array  $extra
     * 
     * @return \PDOStatement
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        if ( ! is_numeric($amount))
        {
            throw new InvalidArgumentException("Non-numeric value passed to decrement method");
        }

        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("$wrapped - $amount"), $extra]);

        return $this->update($columns);
    }

    /**
     * Get run a truncate statment on the table.
     * 
     * @return void
     */
    public function truncate()
    {
        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings)
        {
            $this->connection->query($sql, $bindings);
        }
    }

    /**
     * Indicate that the results, if cached, should use the given cache driver.
     * 
     * @param  string  $cacheDriver
     * 
     * @return $this
     */
    public function getCacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * Indicate that the results, if cached, should use the given cache tags.
     * 
     * @param  array|mixed  $cacheTags
     * 
     * @return $this
     */
    public function getCacheTags($cacheTags)
    {
        $this->cacheTags = $cacheTags;

        return $this;
    }

    /**
     * Create a raw database expression.
     *
     * @param  mixed  $value
     * 
     * @return \Syscodes\Database\Query\Expression
     */
    public function raw($value)
    {
        return $this->connection->raw($value);
    }

    /**
     * Get a new instance of the query builder.
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    public function newQuery()
    {
        return new Builder($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Remove all of the expressions from a lists of bindings.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function cleanBindings(array $bindings)
    {
        return array_values(array_filter($bindings, function () {
            return ! bindings instanceof Expression;
        }));
    }

    /**
     * Get the current query value bindings in a flattened array.
     * 
     * @return array
     */
    public function getBindings()
    {
        return Arr::Flatten($this->bindings);
    }

    /**
     * Get the raw array of bindings.
     * 
     * @return array
     */
    public function getRawBindings()
    {
        return $this->bindings;
    }

    /**
     * /**
     * Set the bindings on the query sql.
     * 
     * @param  mixed  $value
     * @param  string  $type  ('where' by default)
     * 
     * @return $this
     * 
     * @throws \InvalidArgumentException
     */
    public function setBindings($value, $type = 'where')
    {
        if ( ! array_key_exists($type, $this->bindings))
        {
            throw new InvalidArgumentException("Invalid binding type: {$type}");
        }

        $this->bindings[$type] = $value;

        return $this;
    }

    /**
     * Add a binding to the query sql.
     * 
     * @param  mixed  $value
     * @param  string  $type  ('where' by default)
     * 
     * @return $this
     * 
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where')
    {
        if ( ! array_key_exists($type, $this->bindings))
        {
            throw new InvalidArgumentException("Invalid binding type: {$type}");
        }

        if (is_array($value))
        {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        }
        else
        {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * Merge an array of bindings into our bindings.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return $this
     */
    public function mergeBindings(Builder $builder)
    {
        $this->bindings = array_merge_recursive($this->bindings, $builder->bindings);

        return $this;
    }

    /**
     * Get the database connection instance.
     * 
     * @return \Syscodes\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the database query processor instance.
     * 
     * @return \Syscodes\Database\Query\Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the database query grammar instance.
     * 
     * @return \Syscodes\Database\Query\Grammar
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