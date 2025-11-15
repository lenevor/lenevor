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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Components\Database\Query;

use Closure;
use DateTimeInterface;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Pagination\Paginator;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Support\Traits\ForwardsCalls;
use Syscodes\Components\Database\Concerns\MakeQueries;
use Syscodes\Components\Database\Query\Grammars\Grammar;
use Syscodes\Components\Contracts\Database\Query\Expression;
use Syscodes\Components\Database\Query\Processors\Processor;
use Syscodes\Components\Database\Erostrine\Relations\Relation;
use Syscodes\Components\Database\Connections\ConnectionInterface;
use Syscodes\Components\Database\Erostrine\Builder as ErostrineBuilder;

/**
 * Lenevor database query builder provides a convenient, fluent interface 
 * to creating and running database queries. and works on all supported 
 * database systems.
 */
class Builder
{
    use MakeQueries,
        ForwardsCalls,
        Macroable {
            __call as macroCall;
        }

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
     * @var \Syscodes\Components\Database\Connections\ConnectionInterface $connection
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
     * @var \Syscodes\Components\Database\Query\Grammars\Grammar $grammar
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
     * @var \Syscodes\Components\Database\Query\Processors\Processor $processor
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
     * Whether to use write pdo for the select.
     * 
     * @var bool $useWritePdo
     */
    public $useWritePdo = false;

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
    public function __construct(ConnectionInterface $connection, ?Grammar $grammar = null, ?Processor $processor = null)
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
     * @return static
     */
    public function select($columns = ['*']): static
    {
        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
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
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function selectSub($builder, $as): static
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
        if ($builder instanceof static || $builder instanceof ErostrineBuilder || $builder instanceof Relation) {
            return [$builder->getSql(), $builder->getBindings()];
        } elseif (is_string($builder)) {
            return [$builder, []];
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
     * @return static
     */
    public function selectRaw($expression, array $bindings = []): static
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
     * @return static
     */
    public function addSelect($column): static
    {
        $columns = is_array($column) ? $column : func_get_args();
        
        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                if (is_null($this->columns)) {
                    $this->select($this->from.'.*');
                }
                
                $this->selectSub($column, $as);
            } else {
                if (is_array($this->columns) && in_array($column, $this->columns, true)) {
                    continue;
                }
                
                $this->columns[] = $column;
            }
        }
        
        return $this;
    }

    /**
     * Allows force the query for return distinct results.
     * 
     * @return static
     */
    public function distinct(): static
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
     * @return static
     */
    public function from($table, $as = null): static
    {
        if ($this->isQueryable($table)) {
            return $this->fromSub($table, $as);
        }

        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    /**
     * Makes "from" fetch from a subquery.
     * 
     * @param  \Closure|\Syscodes\Components\Database\Query\Builder|\Syscodes\Components\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function fromSub($query, $as): static
    {
        [$query, $bindings] = $this->makeSub($query);
        
        return $this->fromRaw('('.$query.') as '.$this->grammar->wrapTable($as), $bindings);
    }
    
    /**
     * Add a raw from clause to the query.
     * 
     * @param  string  $expression
     * @param  mixed  $bindings
     * 
     * @return static
     */
    public function fromRaw($expression, $bindings = []): static
    {
        $this->from = new Expression($expression);
        
        $this->addBinding($bindings, 'from');
        
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
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): static
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
        
        if ($this->isQueryable($column) && ! is_null($operator)) {
            [$sub, $bindings] = $this->makeSub($column);
            
            return $this->addBinding($bindings, 'where')
                        ->where(new Expression('('.$sub.')'), $operator, $value, $boolean);
        }

        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        if ($this->isQueryable($value)) {
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
     * @return static
     */
    protected function addArrayWheres($column, $boolean, $method = 'where'): static
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
     * @return static
     */
    public function orWhere($column, $operator = null, $value = null): static
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
     * @return static
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and'): static
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
     * @return static
     */
    public function orWhereColumn($first, $operator = null, $second = null): static
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
     * @return static
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and'): static
    {
        $this->wheres[] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean
        ];

        $this->addBinding((array) $bindings,  'where');

        return $this;
    }

    /**
     * Add a raw where clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return static
     */
    public function orWhereRaw($sql, $bindings = []): static
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
     * @return static
     */
    public function whereIn($column, $values, $boolean = 'and', $negative = false): static
    {
        $type = $negative ? 'NotIn' : 'In';
        
        if ($this->isQueryable($values)) {
            [$query, $bindings] = $this->makeSub($values);
            
            $values = [new Expression($query)];
            
            $this->addBinding($bindings, 'where');
        }
        
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        $this->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * 
     * @return static
     */
    public function orWhereIn($column, $values): static
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
     * @return static
     */
    public function whereNotIn($column, $values, $boolean = 'and'): static
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * 
     * @return static
     */
    public function orWhereNotIn($column, $values): static
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a nested where statement to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean  
     * 
     * @return static
     */
    public function whereNested(Closure $callback, $boolean = 'and'): static
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
     * @return static
     */
    public function addNestedWhere($query, $boolean = 'and'): static
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
     * @return static
     */
    protected function whereSub($column, $operator, Closure $callback, $boolean): static
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
     * @return static
     */
    public function whereNull($columns, $boolean = 'and', $negative = false): static
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
     * @return static
     */
    public function orWhereNull($columns): static
    {
        return $this->whereNull($columns, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     * 
     * @param  string|array  $columns
     * @param  string  $boolean
     * 
     * @return static
     */
    public function whereNotNull($columns, $boolean = 'and'): static
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
     * @return static
     */
    public function whereExist(Closure $callback, $boolean = 'and', $negative = false): static
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
     * @return static
     */
    protected function addWhereExist(self $query, $boolean = 'and', $negative = false): static
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
     * @return static
     */
    public function orWhereExist(Closure $callback, $negative = false): static
    {
        return $this->whereExist($callback, 'or', $negative);
    }

    /**
     * Add a where not exists clause to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean
     * 
     * @return static
     */
    public function whereNotExist(Closure $callback, $boolean = 'and'): static
    {
        return $this->whereExist($callback, $boolean, true);
    }

    /**
     * Add an where not exists clause to the query.
     * 
     * @param  \Closure  $callback
     * 
     * @return static
     */
    public function orWhereNotExist(Closure $callback): static
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
     * @return static
     */
    public function whereBetween($column, array $values, $boolean = 'and', $negative = false): static
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
     * @return static
     */
    public function orWhereBetween($column, array $values): static
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
     * @return static
     */
    public function whereNotBetween($column, array $values, $boolean = 'and'): static
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return static
     */
    public function orWhereNotBetween($column, array $values): static
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add a where between statement using columns to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return static
     */
    public function whereBetweenColumns($column, array $values, $boolean = 'and', $negative = false): static
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
     * @return static
     */
    public function orWhereBetweenColumns($column, array $values): static
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
     * @return static
     */
    public function whereNotBetweenColumns($column, array $values, $boolean = 'and'): static
    {
        return $this->whereBetweenColumns($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement using columns to the query.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return static
     */
    public function orWhereNotBetweenColumns($column, array $values): static
    {
        return $this->whereNotBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     * 
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * 
     * @return static
     */
    public function whereDate($column, $operator, $value = null, $boolean = 'and'): static
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
     * @return static
     */
    public function orWhereDate($column, $operator, $value = null): static
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
     * @return static
     */
    public function whereTime($column, $operator, $value = null, $boolean = 'and'): static
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
     * @return static
     */
    public function orWhereTime($column, $operator, $value = null): static
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
     * @return static
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and'): static
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
     * @return static
     */
    public function orWhereDay($column, $operator, $value = null): static
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
     * @return static
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and'): static
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
     * @return static
     */
    public function orWhereMonth($column, $operator, $value = null): static
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
     * @return static
     */
    public function whereYear($column, $operator, $value = null, $boolean = 'and'): static
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
     * @return static
     */
    public function orWhereYear($column, $operator, $value = null): static
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
     * @return static
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and'): static
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
     * Set the limit and offset for a given page.
     * 
     * @param  int  $page
     * @param  int  $perPage
     * 
     * @return static
     */
    public function forPage(int $page, int $perPage = 15): static
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }
    
    /**
     * Alias to set the "offset" value of the query.
     * 
     * @param  int  $value
     * 
     * @return static
     */
    public function skip($value): static
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
     * 
     * @param  int  $value
     * 
     * @return static
     */
    public function offset($value): static
    {
        $property = $this->unions ? 'unionOffset' : 'offset';

        $this->$property = max(0, $value);
        
        return $this;
    }
    
    /**
     * Alias to set the "limit" value of the query.
     * 
     * @param  int  $value
     * 
     * @return static
     */
    public function take($value): static
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     * 
     * @param  int  $value
     * 
     * @return static
     */
    public function limit($value): static
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->$property = ! is_null($value) ? (int) $value : null;
        }
        
        return $this;
    }
    
    /**
     * Add a union statement to the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder|\Closure  $builder
     * @param  bool  $all  
     * 
     * @return static
     */
    public function union($builder, $all = false): static
    {
        if ($builder instanceof Closure) {
            $builder($builder = $this->newBuilder());
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
     * @return static
     */
    public function unionAll($builder): static
    {
        return $this->union($builder, true);
    }

    /**
     * Lock the selected rows in the table.
     * 
     * @param  bool  $value  (true by default)
     * 
     * @return static
     */
    public function lock($value = true): static
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
     * Get an array with the values of a given column.
     * 
     * @param  string  $column
     * @param  string|null  $key
     * 
     * @return \Syscodes\Components\Collections\Collection
     */
    public function pluck($column, $key = null)
    {
        $columns = is_null($key) ? [$column] : [$column, $key];

        $results = $this->get($columns);

        return $results->pluck($column, $key);
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
     * @return \Syscodes\Components\Support\Collection
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
     * @param  array  $columns
     * @param  \callable  $callback
     * 
     * @return mixed 
     */
    protected function getFresh($columns, Closure $callback)
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
        return $this->connection->select($this->getSql(), $this->getBindings(), ! $this->useWritePdo);
    }
    
    /**
     * Paginate the given query into a simple paginator.
     * 
     * @param  int|\Closure  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @param  \Closure|int|null  $total
     * 
     * @return \Syscodes\Components\Contracts\Pagination\Paginator
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        
        $total = func_num_args() === 5 ? value(func_get_arg(4)) : $this->getCountForPagination();
        
        $perPage = $perPage instanceof Closure ? $perPage($total) : $perPage;
        
        $results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();
        
        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    
    /**
     * Get a paginator only supporting simple next and previous links.
     * 
     * @param  int  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * 
     * @return \Syscodes\Components\Contracts\Pagination\SimplePaginator
     */
    public function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        
        $this->offset(($page - 1) * $perPage)->limit($perPage + 1);
        
        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    
    /**
     * Get the count of the total records for the paginator.
     * 
     * @param  array  $columns
     * 
     * @return int
     */
    public function getCountForPagination($columns = ['*']): int
    {
        $results = $this->runPaginationCountQuery($columns);
        
        if ( ! isset($results[0])) {
            return 0;
        } elseif (is_object($results[0])) {
            return (int) $results[0]->aggregate;
        }
        
        return (int) array_change_key_case((array) $results[0])['aggregate'];
    }

    /**
     * Run a pagination count query.
     *
     * @param  array  $columns
     * @return array
     */
    protected function runPaginationCountQuery($columns = ['*'])
    {
        if ($this->groups || $this->havings) {
            $clone = $this->cloneForPaginationCount();

            if (is_null($clone->columns) && ! empty($this->joins)) {
                $clone->select($this->from.'.*');
            }

            return $this->newQuery()
                ->from(new Expression('('.$clone->toSql().') as '.$this->grammar->wrap('aggregate_table')))
                ->mergeBindings($clone)
                ->setAggregate('count', $this->withoutSelectAliases($columns))
                ->get()->all();
        }

        $without = $this->unions ? ['orders', 'limit', 'offset'] : ['columns', 'orders', 'limit', 'offset'];

        return $this->cloneWithoutProperties($without)
                    ->cloneWithoutBindings($this->unions ? ['order'] : ['select', 'order'])
                    ->setAggregate('count', $this->withoutSelectAliases($columns))
                    ->get()->all();
    }
    
    /**
     * Clone the existing query instance for usage in a pagination subquery.
     * 
     * @return static
     */
    protected function cloneForPaginationCount(): static
    {
        return $this->cloneWithout(['orders', 'limit', 'offset'])
                    ->cloneWithoutBindings(['order']);
    }
    
    /**
     * Remove the column aliases since they will break count queries.
     * 
     * @param  array  $columns
     * 
     * @return array
     */
    protected function withoutSelectAliases(array $columns): array
    {
        return array_map(function ($column) {
            return is_string($column) && ($aliasPosition = stripos($column, ' as ')) !== false
                        ? substr($column, 0, $aliasPosition) : $column;
            }, $columns
        );
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

        $previousSelectBindings = $this->bindings['select'];

        $this->bindings['select'] = [];

        $results = $this->get($columns);

        $this->aggregate = null;

        $this->columns = $previous;

        $this->bindings['select'] = $previousSelectBindings;

        if ( ! $results->isEmpty())  {
            return array_change_key_case((array) $results[0])['aggregate'];
        }
    }
    
    /**
     * Set the aggregate property without running the query.
     * 
     * @param  string  $function
     * @param  array  $columns
     * 
     * @return static
     */
    protected function setAggregate($function, $columns): static
    {
        $this->aggregate = compact('function', 'columns');
        
        if (empty($this->groups)) {
            $this->orders = null;
            
            $this->bindings['order'] = [];
        }
        
        return $this;
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

        if ( ! is_array(head($values))) {
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
    public function insertGetId(array $values, $sequence = null): int
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
    public function update(array $values): int
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        
        return $this->connection->update($sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        ));
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
    public function delete($id = null): int
    {
        if ( ! is_null($id)) {
            $this->where($this->from.'id', '=', $id);
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
     * @return static
     */
    public function groupBy(...$groups): static
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
     * Add a raw groupBy clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return static
     */
    public function groupByRaw($sql, array $bindings = []): static
    {
        $this->groups[] = new Expression($sql);
        
        $this->addBinding($bindings, 'groupBy');
        
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
     * @return static
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        $type = 'basic';
        
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        if ($column instanceof Closure && is_null($operator)) {
            return $this->havingNested($column, $boolean);
        }
        
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if ( ! $value instanceof Expression) {
            $this->addBinding($this->flattenValue($value), 'having');
        }

        return $this;
    }
    
    /**
     * Add an "or having" clause to the query.
     * 
     * @param  \Syscodes\Components\Contracts\Database\Query\Expression|\Closure|string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * 
     * @return static
     */
    public function orHaving($column, $operator = null, $value = null): static
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        
        return $this->having($column, $operator, $value, 'or');
    }
    
    /**
     * Add a nested having statement to the query.
     * 
     * @param  \Closure  $callback
     * @param  string  $boolean
     * 
     * @return static
     */
    public function havingNested(Closure $callback, $boolean = 'and'): static
    {
        $callback($query = $this->forNestedWhere());
        
        return $this->addNestedHavingQuery($query, $boolean);
    }
    
    /**
     * Add another query builder as a nested having to the query builder.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $boolean
     * 
     * @return static
     */
    public function addNestedHavingQuery($query, $boolean = 'and'): static
    {
        if (count($query->havings)) {
            $type = 'Nested';
            
            $this->havings[] = compact('type', 'query', 'boolean');
            
            $this->addBinding($query->getRawBindings()['having'], 'having');
        }
        
        return $this;
    }
    
    /**
     * Add a "having null" clause to the query.
     * 
     * @param  string|array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * 
     * @return static
     */
    public function havingNull($columns, $boolean = 'and', $not = false):static
    {
        $type = $not ? 'NotNull' : 'Null';
        
        foreach (Arr::wrap($columns) as $column) {
            $this->havings[] = compact('type', 'column', 'boolean');
        }
        
        return $this;
    }
    
    /**
     * Add an "or having null" clause to the query.
     * 
     * @param  string  $column
     * 
     * @return static
     */
    public function orHavingNull($column): static
    {
        return $this->havingNull($column, 'or');
    }
    
    /**
     * Add a "having not null" clause to the query.
     * 
     * @param  string|array  $columns
     * @param  string  $boolean
     * 
     * @return static
     */
    public function havingNotNull($columns, $boolean = 'and'): static
    {
        return $this->havingNull($columns, $boolean, true);
    }
    
    /**
     * Add an "or having not null" clause to the query.
     * 
     * @param  string  $column
     * 
     * @return static
     */
    public function orHavingNotNull($column): static
    {
        return $this->havingNotNull($column, 'or');
    }
    
    /**
     * Add a "having between " clause to the query.
     * 
     * @param  string  $column
     * @param  iterable  $values
     * @param  string  $boolean
     * @param  bool  $not
     * 
     * @return static
     */
    public function havingBetween($column, iterable $values, $boolean = 'and', $not = false): static
    {
        $type = 'between';
        
        if ($values instanceof Chronos) {
            $values = [$values->start, $values->end];
        }
        
        $this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');
        
        $this->addBinding(array_slice($this->cleanBindings(Arr::flatten((array) $values)), 0, 2), 'having');
        
        return $this;
    }
    
    /**
     * Add a raw having clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * @param  string  $boolean
     * 
     * @return static
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and'): static
    {
        $type = 'Raw';
        
        $this->havings[] = compact('type', 'sql', 'boolean');
        
        $this->addBinding($bindings, 'having');
        
        return $this;
    }
    
    /**
     * Add a raw or having clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return static
     */
    public function orHavingRaw($sql, array $bindings = []): static
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }
    
    /**
     * Add an "order by" clause to the query.
     * 
     * @param  string  $column
     * @param  string  $direction 
     * 
     * @return static
     */
    public function orderBy($column, $direction = 'asc'): static
    {
        $property = $this->unions ? 'unionOrders' : 'orders';
        
        if ($this->isQueryable($column)) {
            [$query, $bindings] = $this->makeSub($column);
            
            $column = new Expression('('.$query.')');
            
            $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');
        }
        
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
     * @return static
     */
    public function orderByDesc($column): static
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     * 
     * @param  string  $column  
     * 
     * @return static
     */
    public function latest($column = 'created_at'): static
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     * 
     * @param  string  $column  
     * 
     * @return static
     */
    public function oldest($column = 'created_at'): static
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Add a raw "order by" clause to the query.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * 
     * @return static
     */
    public function orderByRaw($sql, $bindings = []): static
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
        return array_values(array_filter($bindings, fn ($binding) => ! $binding instanceof Expression));
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
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function setBindings($value, $type = 'where'): static
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
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where'): static
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
     * @return static
     */
    public function mergeBindings(self $builder): static
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
        return is_array($value) ? head(Arr::Flatten($value)) : $value;
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
     * Clone the query.
     * 
     * @return static
     */
    public function clone(): static
    {
        return clone $this;
    }
    
    /**
     * Use the "write" PDO connection when executing the query.
     * 
     * @return static
     */
    public function useWritePdo(): static
    {
        $this->useWritePdo = true;
        
        return $this;
    }
    
    /**
     * Determine if the value is a query builder instance or a Closure.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    protected function isQueryable($value)
    {
        return $value instanceof self ||
               $value instanceof ErostrineBuilder ||
               $value instanceof Relation ||
               $value instanceof Closure;
    }
    
    /**
     * Clone the query without the given properties.
     * 
     * @param  array  $properties
     * 
     * @return static
     */
    public function cloneWithoutProperties(array $properties): static
    {
        return take($this->clone(), function ($clone) use ($properties) {
            foreach ($properties as $property) {
                $clone->{$property} = null;
            }
        });
    }
    
    /**
     * Clone the query without the given bindings.
     * 
     * @param  array  $except
     * 
     * @return static
     */
    public function cloneWithoutBindings(array $except): static
    {
        return take($this->clone(), function ($clone) use ($except) {
            foreach ($except as $type) {
                $clone->bindings[$type] = [];
            }
        });
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
    public function __call(string $method, array $parameters): mixed
    {
        if ( ! static::hasMacro($method)) {
            static::badMethodCallException($method);
        }
        
        return $this->macroCall($method, $parameters);
    }
}