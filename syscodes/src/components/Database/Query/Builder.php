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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Components\Database\Query;

use Closure;
use RuntimeException;
use DateTimeInterface;
use BadMethodCallException;
use InvalidArgumentException;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Collections\Collection;
use Syscodes\Components\Database\DatabaseCache;
use Syscodes\Components\Database\Query\Grammars\Grammar;
use Syscodes\Components\Database\Query\Processors\Processor;
use Syscodes\Components\Database\Connections\ConnectionInterface;

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
     * @var \Syscodes\Components\Database\ConnectionInterface $connection
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
     * @var \Syscodes\Components\Database\Query\Grammar $grammar
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
     * @var \Syscodes\Components\Database\Query\Processor $processor
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
     * @param  \Syscodes\Components\Database\Connections\ConnectionInterface  $connection
     * @param  \Syscodes\Components\Database\Query\Grammar  $grammar  
     * @param  \Syscodes\Components\Database\Query\Processor  $processor  
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
     * @return self
     */
    public function select($columns = ['*']): self
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
     * @param  \Syscodes\Components\Database\Query\Builder|string  $builder
     * @param  string  $as
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function selectSub($builder, $as): self
    {
        [$builder, $bindings] = $this->makeSub($builder);

        return $this->selectRaw(
            '('.$builder.') as '.$this->grammar->wrap($as), $bindings
        );
    }

    /**
     * Makes a subquery and parse it.
     * 
     * @param  \Closure|\Syscodes\Components\Database\Query\Builder|string  $builder
     * 
     * @return array
     */
    protected function makeSub($builder): array
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
    protected function parseSub($builder): array
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
     * @return self
     */
    public function selectRaw($expression, array $bindings = []): self
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
     * @return self
     */
    public function addSelect($column): self
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->columns = array_merge((array) $this->columns, $column);

        return $this;
    }

    /**
     * Allows force the query for return distinct results.
     * 
     * @return self
     */
    public function distinct(): self
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
     * @return self
     */
    public function from($table, $as = null): self
    {
        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    /**
     * Add a basic where clause to the query.
     * 
     * @param  mixed  $column
     * @param  mixed  $operator  
     * @param  mixed  $value  
     * @param  mixed  $boolean  
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): self
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

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        if ( ! $value instanceof Expression) {
            $this->addBinding($this->flattenValue($value), 'where');
        }
        
        return $this;
    }

    /**
     * Add an array of where clauses to the query.
     * 
     * @param  string  $column
     * @param  string  $boolean
     * @param  string  $method  
     * 
     * @return self
     */
    protected function addArrayWheres($column, $boolean, $method = 'where'): self
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ((array) $column as $key => $value) {
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
    public function prepareValueAndOperator($value, $operator, $useDefault = false): array
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
    protected function invalidOperatorValue($operator, $value): bool
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
    protected function invalidOperator($operator): bool
    {
        return ! in_array(strtolower($operator), $this->operators, true);
    }

    /**
     * Add an "or where" clause to the query.
     * 
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return self
     */
    public function orWhere($column, $operator = null, $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     * 
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string|null  $boolean
     * 
     * @return self
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and'): self
    {
        if (is_array($first)) {
            return $this->addArrayWheres($first, $boolean, 'whereColumn');
        }

        if ($this->invalidOperator($operator)) {
            [$second, $operator] = [$operator, '='];
        }

        $type = 'column';

        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );

        return $this;
    }

    /**
     * Add a "or where" clause comparing two columns to the query.
     * 
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * 
     * @return self
     */
    public function orWhereColumn($first, $operator = null, $second = null): self
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * Add a raw where clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and'): self
    {
        $this->where[] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean
        ];

        $this->addBinding((array) $bindinggs,  'where');

        return $this;
    }

    /**
     * Add a raw where clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return self
     */
    public function orWhereRaw($sql, $bindings = []): self
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return self
     */
    public function whereIn($column, $values, $boolean = 'and', $negative = false): self
    {
        $type = $negative ? 'NotIn' : 'In';

        $this->wheres[] = compact(
            'type', 'column', 'values', 'boolean'
        );

        $this->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * 
     * @return self
     */
    public function orWhereIn($column, $values): self
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereNotIn($column, $values, $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * 
     * @return self
     */
    public function orWhereNotIn($column, $values): self
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a nested where statement to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean  
     * 
     * @return self
     */
    public function whereNested(Closure $callback, $boolean = 'and'): self
    {
        $query = $this->forNestedWhere();

        call_user_func($callback, $query);

        return $this->addNestedWhere($query, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function forNestedWhere()
    {
        return $this->newBuilder()->from($this->from);
    }

    /**
     * Add a query builder different from the current one.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $boolean  
     * 
     * @return self
     */
    public function addNestedWhere($query, $boolean = 'and'): self
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
     * @return self
     */
    protected function whereSub($column, $operator, Closure $callback, $boolean): self
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
     * @param  string  $boolean  
     * @param  bool  $negative 
     * 
     * @return self
     */
    public function whereNull($columns, $boolean = 'and', $negative = false): self
    {
        $type = $negative ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     * 
     * @param  string|array  $columns
     * 
     * @return self
     */
    public function orWhereNull($columns): self
    {
        return $this->whereNull($columns, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     * 
     * @param  string|array  $columns
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereNotNull($columns, $boolean = 'and'): self
    {
        return $this->whereNull($columns, $boolean, true);
    }

    /**
     * Add an exists clause to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return self
     */
    public function whereExist(Closure $callback, $boolean = 'and', $negative = false): self
    {
        $query = $this->forSubBuilder();

        call_user_func($callback, $query);

        return $this->addWhereExist($query, $boolean, $negative);
    }

    /**
     * Add an exists clause to the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder $query
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return self
     */
    protected function addWhereExist(self $query, $boolean = 'and', $negative = false): self
    {
        $type = $negative ? 'NotExists' : 'Exists';

        $this->wheres[] = compact('type', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add an or exists clause to the query.
     * 
     * @param  \Closure  $callback
     * @param  bool  $negative
     * 
     * @return self
     */
    public function orWhereExist(Closure $callback, $negative = false): self
    {
        return $this->whereExist($callback, 'or', $negative);
    }

    /**
     * Add a where not exists clause to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereNotExist(Closure $callback, $boolean = 'and'): self
    {
        return $this->whereExist($callback, $boolean, true);
    }

    /**
     * Add an where not exists clause to the query.
     * 
     * @param  \Closure  $callback
     * 
     * @return self
     */
    public function orWhereNotExist(Closure $callback): self
    {
        return $this->orWhereExist($callback, true);
    }

    /**
     * Add a where between statement to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return self
     */
    public function whereBetween($column, array $values, $boolean = 'and', $negative = false): self
    {
        $type = 'between';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'negative');

        $this->addBinding($values, 'where');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     * 
     * @param  string  $column
     * @param array  $values
     * 
     * @return self
     */
    public function orWhereBetween($column, array $values): self
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereNotBetween($column, array $values, $boolean = 'and'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return self
     */
    public function orWhereNotBetween($column, array $values): self
    {
        return $this->whereNotBetween($column, $array, 'or');
    }

    /**
     * Add a where between statement using columns to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return self
     */
    public function whereBetweenColumns($column, array $values, $boolean = 'and', $negative = false): self
    {
        $type = 'betweenColumns';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'negative');

        return $this;
    }

    /**
     * Add an or where between statement using columns to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return self
     */
    public function orWhereBetweenColumns($column, array $values): self
    {
        return $this->whereBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a where not between statement using columns to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereNotBetweenColumns($column, array $values, $boolean = 'and'): self
    {
        return $this->whereBetweenColumns($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement using columns to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return self
     */
    public function orWhereNotBetweenColumns($column, array $values): self
    {
        return $this->whereNotBetweenColumns($column, $array, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereDate($column, $operator, $value = null, $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        $value = $this->flattenValue($value);
        
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }
        
        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "or where date" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * 
     * @return self
     */
    public function orWhereDate($column, $operator, $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDate($column, $operator, $value, 'or');
    }

    /**
     * Add a "where time" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereTime($column, $operator, $value = null, $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        $value = $this->flattenValue($value);
        
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i:s');
        }
        
        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "or where time" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * 
     * @return self
     */
    public function orWhereTime($column, $operator, $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereTime($column, $operator, $value, 'or');
    }

    /**
     * Add a "where day" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        $value = $this->flattenValue($value);
        
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('d');
        }
        
        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "or where day" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * 
     * @return self
     */
    public function orWhereDay($column, $operator, $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDay($column, $operator, $value, 'or');
    }

    /**
     * Add a "where month" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        $value = $this->flattenValue($value);
        
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('m');
        }
        
        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "or where month" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * 
     * @return self
     */
    public function orWhereMonth($column, $operator, $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereMonth($column, $operator, $value, 'or');
    }

    /**
     * Add a "where Year" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    public function whereYear($column, $operator, $value = null, $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        $value = $this->flattenValue($value);
        
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y');
        }
        
        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "or where year" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * 
     * @return self
     */
    public function orWhereYear($column, $operator, $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereYear($column, $operator, $value, 'or');
    }

    /**
     * Add a date based (year, month, day) statement to the query.
     * 
     * @param  string  $type
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and'): self
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        if ( ! $value instanceof Expression) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Get the SQL representation of the query.
     * 
     * @return string
     */
    public function getSql(): string
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * Get a new join clause.
     * 
     * @param  string  $type
     * @param  string  $table
     * 
     * @return \Syscodes\Components\Database\Query\JoinClause
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
     * @return self
     */
    public function offset($value): self
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
     * @return self
     */
    public function limit($value): self
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
     * @param  \Syscodes\Components\Database\Query\Builder|\Closure  $builder
     * @param  bool  $all  
     * 
     * @return self
     */
    public function union($builder, $all = false): self
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
     * @param  \Syscodes\Components\Database\Query\Builder|\Closure  $builder
     * 
     * @return self
     */
    public function unionAll($builder): self
    {
        return $this->union($builder, true);
    }

    /**
     * Lock the selected rows in the table.
     * 
     * @param  bool  $value  (true by default)
     * 
     * @return self
     */
    public function lock($value = true): self
    {
        $this->lock = $value;

        return $this;
    }

    /**
     * Lock the selected rows in the table for updating.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function lockRowsUpdate()
    {
        return $this->lock(true);
    }

    /**
     * Share lock the selected rows in the table.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
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
        $result = $this->first([$column]);

        if ( ! is_null($result)) {
            $result = (array) $result;

            return (count($result) > 0) ? headItem($result) : null;
        }
    }

    /**
     * Get an array with the values of a given column.
     * 
     * @param  string  $column
     * @param  string|null  $key
     * 
     * @return array
     */
    public function lists($column, $key = null): array
    {
        $columns = is_null($key) ? [$column] : [$column, $key];

        $results = $this->get($columns);

        return Arr::pluck($results, $column, $key);
    }

    /**
     * Execute the query and get the first result.
     * 
     * @param  array  $columns
     * 
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->limit(1)->get($columns);

        return count($results) > 0 ? headItem($results) : null;
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
        return $this->where($this->from.'_id', '=', $id)->first($columns);
    }
    
    /**
     * Execute the query as a "select" statement.
     * 
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Collections\Collection
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
    public function insert(array $values): bool
    {
        if (empty($values)) {
            return true;
        }

        if ( ! is_array(headItem($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
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
     * 
     * @return array
     */
    private function buildInsertBinding(array $values): array
    {
        $bindings = [];

        foreach ($values as $record) {
            foreach ((array) $record as $value) {
                $bindings[] = $value;
            }
        }

        return $bindings;
    }

    /**
     * Insert a new record and get the value of the primary key.
     * 
     * @param  array  $values
     * @param  string  $sequence  
     * 
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $sql    = $this->grammar->compileInsertGetId($this, $values, $sequence);
        $values = $this->cleanBindings($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }

    /**
     * Update a record in the database.
     * 
     * @param  array  $values
     * 
     * @return int
     */
    public function update(array $values)
    {
        $sql      = $this->grammar->compileUpdate($this, $values);
        $bindings = array_values(array_merge($values, $this->bindings));

        return $this->connection->update($sql, $this->cleanBindings($bindings));
    }

    /**
     * Increment a column's value by a given amount.
     * 
     * @param  string  $column
     * @param  int  $amount  
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
     * @param  int  $amount  
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
     * Delete a record from the database.
     * 
     * @param  mixed  $id
     * 
     * @return int
     */
    public function delete($id = null)
    {
        if ( ! is_null($id)) {
            $this->where($this->from.'_id', '=', $id);
        }

        $sql      = $this->grammar->compileDelete($this);
        $bindings = $this->cleanBindings($this->getBindings());

        return $this->connection->delete($sql, $bindings);
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
     * @return self
     */
    public function groupBy(...$groups): self
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
     * 
     * @return self
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and'): self
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
     * @param  string  $direction 
     * 
     * @return self
     */
    public function orderBy($column, $direction = 'asc'): self
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
     * @return self
     */
    public function orderByDesc($column): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     * 
     * @param  string  $column  
     * 
     * @return self
     */
    public function latest($column = 'created_at'): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     * 
     * @param  string  $column  
     * 
     * @return self
     */
    public function oldest($column = 'created_at'): self
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Add a raw "order by" clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return self
     */
    public function orderByRaw($sql, $bindings = []): self
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
     * @return \Syscodes\Components\Database\Query\Expression
     */
    public function raw($value)
    {
        return $this->connection->raw($value);
    }

    /**
     * Create a new Builder instance for a sub-Builder.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function forSubBuilder()
    {
        return $this->newBuilder();
    }

    /**
     * Get a new instance of the query builder.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function newBuilder()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Remove all of the expressions from a lists of bindings.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function cleanBindings(array $bindings): array
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
    public function getBindings(): array
    {
        return Arr::Flatten($this->bindings);
    }

    /**
     * Get the raw array of bindings.
     * 
     * @return array
     */
    public function getRawBindings(): array
    {
        return $this->bindings;
    }

    /**
     * /**
     * Set the bindings on the query sql.
     * 
     * @param  mixed  $value
     * @param  string  $type  
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function setBindings($value, $type = 'where'): self
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
     * @param  string  $type  
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where'): self
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
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return self
     */
    public function mergeBindings(Builder $builder): self
    {
        $this->bindings = array_merge_recursive($this->bindings, $builder->bindings);

        return $this;
    }

    /**
     * Get a scalar type value from an unknown type of input.
     * 
     * @param  mixed  $value
     * 
     * @return mixed
     */
    protected function flattenValue($value)
    {
        return is_array($value) ? headItem(Arr::Flatten($value)) : $value;
    }

    /**
     * Get the database connection instance.
     * 
     * @return \Syscodes\Components\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the database query processor instance.
     * 
     * @return \Syscodes\Components\Database\Query\Processor
     */
    public function getQueryProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the database query grammar instance.
     * 
     * @return \Syscodes\Components\Database\Query\Grammar
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
    public function dd(): void
    {
        dd($this->getSql(), $this->getBindings());
    }

    /**
     * Magic Method.
     * 
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