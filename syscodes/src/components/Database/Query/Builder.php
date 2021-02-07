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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Database\Query;

use Closure;
use RuntimeException;
use DateTimeInterface;
use BadMethodCallException;
use InvalidArgumentException;
use Syscodes\Collections\Arr;
use Syscodes\Collections\Str;
use Syscodes\Collections\Collection;
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
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * All of the available clause operators.
     * 
     * @var array $operators
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'not like', 'between', 'ilike',
        '&', '|', '^', '<<', '>>',
    ];

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
        $this->connection = $connection;
        $this->grammar    = $grammar ?: $this->getQueryGrammar();
        $this->processor  = $processor ?: $this->getQueryProcessor();
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
        $this->columns = [];
        $this->bindings['select'] = [];
        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as)) {
                $this->selectSub($column, $as);
            } else {
                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * Add a subselect expression to the query.
     * 
     * @param  \Syscodes\Database\Query\Builder|string  $builder
     * @param  string  $as
     * 
     * @return $this
     * 
     * @throws \InvalidArgumentException
     */
    public function selectSub($builder, $as)
    {
        [$builder, $bindings] = $this->makeSub($builder);

        return $this->selectRaw(
            '('.$builder.') as '.$this->grammar->wrap($as), $bindings
        );
    }

    /**
     * Makes a subquery and parse it.
     * 
     * @param  \Closure|\Syscodes\Database\Query\Builder|string  $builder
     * 
     * @return array
     */
    protected function makeSub($builder)
    {
        if ($builder instanceof Closure) {
            $callback = $builder;

            $callback($builder = $this->newBuilder());
        }

        return $this->parseSub($builder);
    }

    /**
     * Parse the subquery into SQL and bindings.
     * 
     * @param  mixed  $builder
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    protected function parseSub($builder)
    {
        if ($builder instanceof self) {
            return [$builder->getSql(), $builder->getBindings()];
        } elseif (is_string($builder)) {
            return [$builder->getSql(), []];
        } else {
            throw new InvalidArgumentException('A subquery must be a query builder instance, a Closure, or a string');
        }
    }

    /**
     *  Add a new "raw" select expression to the query.
     * 
     * @param  string  $expression
     * @param  array  $bindings
     * 
     * @return $this
     */
    public function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect(new Expression($expression));

        if (! empty($bindings)) {
            $this->addBinding($bindings, 'select');
        }

        return $this;
    }

    /**
     * Add a new select column to the query.
     * 
     * @param  mixed  $column
     * 
     * @return $this
     */
    public function addSelect($column)
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->columns = array_merge((array) $this->columns, $column);

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
     * @param  string|null  $as
     * 
     * @return $this
     */
    public function from($table, $as = null)
    {
        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    /**
     * Add a basic where clause to the query.
     * 
     * @param  mixed  $column
     * @param  mixed  $operator  (null by default)
     * @param  mixed  $value  (null by default)
     * @param  mixed  $boolean  ('and' by default)
     * 
     * @return $this
     * 
     * @throws \InvalidArgumentException
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->addArrayWheres($column, $boolean);
        }
        
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if ($column instanceof Closure && is_null($operator)) {
            return $this->whereNested($column, $boolean);
        }

        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        if ($value instanceof Closure) {
            return $this->whereSub($column, $operator, $value, $boolean);
        }

        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if ($value instanceof Expression) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Add an array of where clauses to the query.
     * 
     * @param  string  $column
     * @param  string  $boolean
     * @param  string  $method  ('where' by default)
     * 
     * @return $this
     */
    protected function addArrayWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->{$method}($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }
    
    /**
     * Prepare the value and operator for a where clause.
     * 
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }
            
        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     * Prevents using Null values with invalid operators.
     * 
     * @param  string  $operator
     * @param  mixed  $value
     * 
     * @return bool
     */
    protected function invalidOperatorValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) && ! in_array($operator, ['=', '<>', '!=']);
    }
    
    /**
     * Determine if the given operator is supported.
     * 
     * @param  string  $operator
     * 
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! in_array(strtolower($operator), $this->operators, true);
    }

    /**
     * Add a nested where statement to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean  ('and' by default)
     * 
     * @return $this
     */
    public function whereNested(Closure $callback, $boolean = 'and')
    {
        $query = $this->forNestedWhere();

        call_user_func($callback, $query);

        return $this->addNestedWhere($query, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    public function forNestedWhere()
    {
        return $this->newBuilder()->from($this->from);
    }

    /**
     * Add a query builder different from the current one.
     * 
     * @param  \Syscodes\Database\Query\Builder  $query
     * @param  string  $boolean  ('and' by default)
     * 
     * @return $this
     */
    public function addNestedWhere($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['where'], 'where');
        }

        return $this;
    }

    /**
     * Add a full sub-select to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \Closure  $callback
     * @param  string  $boolean
     * 
     * @return $this
     */
    protected function whereSub($column, $operator, Closure $callback, $boolean)
    {
        $type = 'Sub';

        call_user_func($callback, $query = $this->forSubBuilder());

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'query', 'boolean'
        );

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     * 
     * @param  string|array  $columns
     * @param  string  $boolean  ('and' by default)
     * @param  bool  $not  (false by default)
     * 
     * @return $this
     */
    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

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
     * Get a new join clause.
     * 
     * @param  string  $type
     * @param  string  $table
     * 
     * @return \Syscodes\Database\Query\JoinClause
     */
    protected function newJoinClause($type, $table)
    {
        return new JoinClause($type, $table);
    }

    /**
     * Set the "offset" value of the query.
     * 
     * @param  int  $value
     * 
     * @return $this
     */
    public function offset($value)
    {
        $property = $this->unions ? 'unionOffset' : 'offset';

        $this->$property = max(0, $value);
        
        return $this;
    }

    /**
     * Set the "limit" value of the query.
     * 
     * @param  int  $value
     * 
     * @return $this
     */
    public function limit($value)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->$property = $value;
        }
        return $this;
    }

    /**
     * Add a union statement to the query.
     * 
     * @param  \Syscodes\Database\Query\Builder|\Closure  $builder
     * @param  bool  $all  (false by default)
     * 
     * @return $this
     */
    public function union($builder, $all = false)
    {
        if ($builder instanceof Closure) {
            call_user_func($builder, $builder = $this->newBuilder());
        }

        $this->unions[] = compact('builder', 'all');

        $this->addBinding($builder->getBindings(), 'union');

        return $this;
    }

    /**
     * Add a union all statement to the query.
     * 
     * @param  \Syscodes\Database\Query\Builder|\Closure  $builder
     * 
     * @return $this
     */
    public function unionAll($builder)
    {
        return $this->union($builder, true);
    }

    /**
     * Lock the selected rows in the table.
     * 
     * @param  bool  $value  (true by default)
     * 
     * @return $this
     */
    public function lock($value = true)
    {
        $this->lock = $value;

        return $this;
    }

    /**
     * Lock the selected rows in the table for updating.
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    public function lockRowsUpdate()
    {
        return $this->lock(true);
    }

    /**
     * Share lock the selected rows in the table.
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    public function shareRowsLock()
    {
        return $this->lock(false);
    }

    /**
     * Pluck a single column's value from the first result of a query.
     * 
     * @param  string  $column
     * 
     * @return mixed
     */
    public function pluck($column)
    {
        $sql = (array) $this->first([$column]);

        return count($sql) > 0 ? reset($sql) : null;
    }

    /**
     * Execute the query and get the first result.
     * 
     * @param  array
     * 
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->limit(1)->get($columns);

        return count($results) > 0 ? head($results) : null;
    }

    /**
     * Execute a query for a single record by ID.
     * 
     * @param  int|string  $id
     * @param  array  $columns
     * 
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }
    
    /**
     * Execute the query as a "select" statement.
     * 
     * @param  array  $columns
     * 
     * @return \Syscodes\Collections\Collection
     */
    public function get($columns = ['*'])
    {
        return collect($this->getFresh(Arr::wrap($columns), function () {
            return $this->getWithStatement();
        }));
    }
    
    /**
     * Execute the given callback while selecting the given columns.
     * 
     * @param  string  $columns
     * @param  \callable  $callback
     * 
     * @return mixed 
     */
    protected function getFresh($columns, $callback)
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $result = $callback();

        $this->columns = $original;

        return $result;
    }

    /**
     * Execute the query with a "select" statement.
     * 
     * @return array|static[]
     */
    protected function getWithStatement()
    {
        return $this->processor->processSelect($this, $this->runOnSelectStatement());
    }

    /**
     * Run the query as a "select" statement against the connection.
     * 
     * @return array
     */
    public function runOnSelectStatement()
    {
        return $this->connection->select($this->getSql(), $this->getBindings());
    }

    /**
     * Retrieve the "count" result of the query.
     * 
     * @param  string  $columns
     * 
     * @return mixed
     */
    public function count($columns = '*')
    {
        return (int) $this->aggregate(__FUNCTION__, Arr::wrap($columns));
    }

    /**
     * Retrieve the max of the values of a given column.
     * 
     * @param  string  $column
     * 
     * @return mixed
     */
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the min of the values of a given column.
     * 
     * @param  string  $column
     * 
     * @return mixed
     */
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
     * 
     * @param  string  $column
     * 
     * @return mixed
     */
    public function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);

        return $result ?: 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     * 
     * @param  string  $column
     * 
     * @return mixed
     */
    public function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Execute an aggregate function on the database.
     * 
     * @param  string  $function
     * @param  array  $columns
     * 
     * @return mixed
     */
    public function aggregate($function, $columns = ['*'])
    {
        $this->aggregate = compact('function', 'columns');

        $previous = $this->columns;

        $results = $this->get($columns);

        $this->aggregate = null;

        $this->columns = $previous;

        if (isset($results[0]))  {
            $result = array_change_key((array) $results[0]);
        }

        return $result['aggregate'];
    }

    /**
     * Insert a new record into the database.
     * 
     * @param  array  $values
     * 
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        if ( ! is_array(reset($values))) {
            $values = [$values]; 
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        $sql = $this->grammar->compileInsert($this, $values);

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

        foreach ($values as $record) {
            foreach ($record as $value) {
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
        if ( ! is_numeric($amount)) {
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
        if ( ! is_numeric($amount)) {
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
        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
            $this->connection->query($sql, $bindings);
        }
    }

    /**
     * Add a "group by" clause to the query.
     * 
     * @param  array|string  ...$groups
     * 
     * @return $this
     */
    public function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    /**
     * Add a "having" clause to the query.
     *
     * @param  string  $column
     * @param  string|null  $operator
     * @param  string|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'basic';

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if ($value instanceof Expression) {
            $this->addBinding($value, 'having');
        }

        return $this;
    }
    
    /**
     * Add an "order by" clause to the query.
     * 
     * @param  string  $column
     * @param  string  $direction  (asc by default)
     * 
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $property = $this->unions ? 'unionOrders' : 'orders';
        
        $direction = strtolower($direction);
        
        if ( ! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc"');
        }
        
        $this->{$property}[] = [
            'column' => $column,
            'direction' => $direction,
        ];
        
        return $this;
    }

    /**
     * Add a descending "order by" clause to the query.
     * 
     * @param  string  $column
     * 
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     * 
     * @param  string  $column  (created_at by default)
     * 
     * @return $this
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     * 
     * @param  string  $column  (created_at by default)
     * 
     * @return $this
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Add a raw "order by" clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return $this
     */
    public function orderByRaw($sql, $bindings = [])
    {
        $type = 'Raw';

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');

        $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');

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
    public function newBuilder()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Create a new Builder instance for a sub-Builder.
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    protected function forSubBuilder()
    {
        return $this->newBuilder();
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
        return array_values(array_filter($bindings, function ($binding) {
            return ! $binding instanceof Expression;
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
        if ( ! array_key_exists($type, $this->bindings)) {
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
        if ( ! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
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
    public function getQueryProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the database query grammar instance.
     * 
     * @return \Syscodes\Database\Query\Grammar
     */
    public function getQueryGrammar()
    {
        return $this->grammar;
    }

    /**
     * Die and dump the current SQL and bindings.
     * 
     * @return void
     */
    public function dd()
    {
        dd($this->getSql(), $this->getBindings());
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