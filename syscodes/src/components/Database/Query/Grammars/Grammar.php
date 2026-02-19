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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Components\Database\Query\Grammars;

use RuntimeException;
use Syscodes\Components\Database\Grammar as BaseGrammar;
use Syscodes\Components\Database\Query\Builder;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Database\Query\JoinClause;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;

/**
 * Allows make the grammar's for get results of the database.
 */
class Grammar extends BaseGrammar
{
    /**
     * Get the components for use a select statement.
     * 
     * @var array $components
     */
    protected $components = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',        
        'havings',
        'orders',
        'limit',
        'offset',
        'lock'
    ];

    /**
     * Compile a select query into sql.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileSelect(Builder $builder): string
    {
        if (($builder->unions || $builder->havings) && $builder->aggregate) {
            return $this->compileUnionAggregate($builder);
        }
        
        // If the query does not have any columns set, we'll set the columns to the
        // character to just get all of the columns from the database.
        $original = $builder->columns;

        if (is_null($builder->columns)) {
            $builder->columns = ['*'];
        }
        
        // To compile the query, we'll spin through each component of the query and
        // see if that component exists.
        $sql = trim($this->concatenate(
            $this->compileComponents($builder))
        );
        
        if ($builder->unions) {
            $sql = $this->wrapUnion($sql).' '.$this->compileUnions($builder);
        }

        $builder->columns = $original;

        return $sql;
    }

    /**
     * Compile the components necessary for a select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return array
     */
    protected function compileComponents(Builder $builder): array
    {
        $sql = [];

        foreach ($this->components as $component) {
            if (isset($builder->$component)) {
                $method = 'compile'.ucfirst($component);

                $sql[$component] = $this->$method($builder, $builder->$component);
            }
        }

        return $sql;
    }

    /**
     * Compile an aggregated select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $aggregate
     * 
     * @return string
     */
    protected function compileAggregate(Builder $builder, $aggregate): string
    {
        $column = $this->columnize($aggregate['columns']);
        
        // If the query has a "distinct" constraint and we're not asking for all columns
        // we need to prepend "distinct" onto the column name so that the query.
        if (is_array($builder->distinct)) {
            $column = 'distinct '.$this->columnize($builder->distinct);
        } elseif ($builder->distinct && $column !== '*') {
            $column = 'distinct '.$column;
        }

        return 'select '.$aggregate['function'].'('.$column.') as aggregate';
    }

    /**
     * Compile the "select *" portion of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $columns
     * 
     * @return string|null
     */
    protected function compileColumns(Builder $builder, $columns)
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax.
        if ( ! is_null($builder->aggregate)) {
            return;
        }

        $select = $builder->distinct ? 'select distinct ' : 'select ';

        return $select.$this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $table
     * 
     * @return string
     */
    protected function compileFrom(Builder $builder, $table)
    {
        return 'from '.$this->wrapTable($table);
    }

    /**
     * Compile the "join" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $joins
     * 
     * @return string
     */
    protected function compileJoins(Builder $builder, $joins): string
    {
        return (new Collection($joins))->map(function ($join) use ($builder) {
            $table = $this->wrapTable($join->table);
            
            $nestedJoins = is_null($join->joins) ? '' : ' '.$this->compileJoins($builder, $join->joins);
            
            $tableAndNestedJoins = is_null($join->joins) ? $table : '('.$table.$nestedJoins.')';
            
            return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
        })->implode(' ');
    }

    /**
     * Compile the "where" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileWheres(Builder $builder): string
    {
        // Each type of where clause has its own compiler function, which is responsible
        // for actually creating the where clauses SQL. 
        if (is_null($builder->wheres)) {
            return '';
        }
        
        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience
        if (count($sql = $this->compileWheresToArray($builder)) > 0) {
            return $this->concatenateWheresClauses($builder, $sql);
        }
        
        return '';
    }

    /**
     * Get an array of all the where clauses for the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return array
     */
    protected function compileWheresToArray($builder): array
    {
        return (new collection($builder->wheres))
            ->map(fn ($where) => $where['boolean'].' '.$this->{"where{$where['type']}"}($builder, $where))
            ->all();
    }

    /**
     * Format the where clause statements into one string.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $sql
     * 
     * @return string
     */
    protected function concatenateWheresClauses($builder, $sql): string
    {
        $statement = $builder instanceof JoinClause ? 'on' : 'where';

        return $statement.' '.$this->removeStatementBoolean(implode(' ', $sql));
    }

    /**
     * Compile a raw where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereRaw(Builder $builder, $where): string
    {
        return $where['sql'] instanceof Expression ? $where['sql']->getValue($this) : $where['sql'];
    }

    /**
     * Compile a basic where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBasic(Builder $builder, $where): string
    {
        $operator = str_replace('?', '??', $where['operator']);
        $value    = $this->parameter($where['value']);
       
        return $this->wrap($where['column']).' '.$operator.' '.$value;
    }

    /**
     * Compile a "between" where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBetween(Builder $builder, $where): string
    {
        $between = $where['negative'] ? 'not between' : 'between';

        $min = $this->parameter(is_array($where['values']) ? head($where['values']) : $where['values'][0]);
        $max = $this->parameter(is_array($where['values']) ? last($where['values']) : $where['values'][1]);

        return $this->wrap($where['column']).' '.$between.' '.$min.' and '.$max;
    }

    /**
     * Compile a "between" where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereBetweenColumns(Builder $builder, $where): string
    {
        $between = $where['negative'] ? 'not between' : 'between';

        $min = $this->wrap(is_array($where['values']) ? head($where['values']) : $where['values'][0]);
        $max = $this->wrap(is_array($where['values']) ? last($where['values']) : $where['values'][1]);

        return $this->wrap($where['column']).' '.$between.' '.$min.' and '.$max;
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereExists(Builder $builder, $where): string
    {
        return 'exists ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotExists(Builder $builder, $where): string
    {
        return 'not exists ('.$this->compileSelect($where['query']).')';
    }
    
    /**
     * Compile a "where like" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereLike(Builder $query, $where): string
    {
        if ($where['caseSensitive']) {
            throw new RuntimeException('This database engine does not support case sensitive like operations.');
        }
        
        $where['operator'] = $where['not'] ? 'not like' : 'like';
        
        return $this->whereBasic($query, $where);
    }

    /**
     * Compile a "where in" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereIn(Builder $builder, $where): string
    {
        if (empty($where['values'])) return '0 = 1';
        
        return $this->wrap($where['column']).' in ('.$this->parameterize($where['values']).')';
    }

    /**
     * Compile a "where not in" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotIn(Builder $builder, $where): string
    {
        if (empty($where['query'])) return '1 = 1';

        return $this->wrap($where['column']).' not in ('.$this->parameterize($where['query']).')';
    }

    /**
     * Compile a "where not in raw" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotInRaw(Builder $builder, $where): string
    {
        if (empty($where['values'])) return '1 = 1';
        
        return $this->wrap($where['column']).' not in ('.implode(', ', $where['values']).')';
    }

    /**
     * Compile a "where in raw" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereInRaw(Builder $builder, $where): string
    {
        if (empty($where['values'])) return '0 = 1';
        
        return $this->wrap($where['column']).' in ('.implode(', ', $where['values']).')';
    }

    /**
     * Compile a where in sub-select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereInSub(Builder $builder, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' in ('.$select.')';
    }

    /**
     * Compile a where not in sub-select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotInSub(Builder $builder, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' not in ('.$select.')';
    }

    /**
     * Compile a "where null" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNull(Builder $builder, $where): string
    {
        return $this->wrap($where['column']).' is null';
    }

    /**
     * Compile a "where not null" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotNull(Builder $builder, $where): string
    {
        return $this->wrap($where['column']).' is not null';
    }

    /**
     * Compile a "where date" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereDate(Builder $builder, $where): string
    {
        return $this->dateBasedWhere('date', $builder, $where);
    }

    /**
     * Compile a "where time" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereTime(Builder $builder, $where): string
    {
        return $this->dateBasedWhere('time', $builder, $where);
    }

    /**
     * Compile a "where day" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereDay(Builder $builder, $where): string
    {
        return $this->dateBasedWhere('day', $builder, $where);
    }

    /**
     * Compile a "where month" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereMonth(Builder $builder, $where): string
    {
        return $this->dateBasedWhere('month', $builder, $where);
    }

    /**
     * Compile a "where year" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereYear(Builder $builder, $where): string
    {
        return $this->dateBasedWhere('year', $builder, $where);
    }

    /**
     * Compile a date based where clause.
     * 
     * @param  string  $type
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function dateBasedWhere($type, Builder $builder, $where): string
    {
        $value = $this->parameter($where['value']);

        return $type.'('.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a nested where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNested(Builder $builder, $where): string
    {
        // Here we will calculate what portion of the string we need to remove. If this
        // is a join clause query, we need to remove the "on" portion of the SQL
        $intClause = $builder instanceof JoinClause ? 3 : 6;

        return '('.substr($this->compileWheres($where['query']), $intClause).')';
    }

    /**
     * Compile a where condition with a sub-select.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereSub(Builder $builder, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' '.$where['operator']." ($select)";
    }

    /**
     * Compile a where clause comparing two columns.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereColumn(Builder $builder, $where): string
    {
        return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }
    
    /**
     * Compile a where row values condition.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereRowValues(Builder $builder, $where): string
    {
        $columns = $this->columnize($where['columns']);
        
        $values = $this->parameterize($where['values']);
        
        return '('.$columns.') '.$where['operator'].' ('.$values.')';
    }
    
    /**
     * Compile a "where JSON boolean" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereJsonBoolean(Builder $builder, $where): string
    {
        $column = $this->wrapJsonBooleanSelector($where['column']);
        
        $value = $this->wrapJsonBooleanValue(
            $this->parameter($where['value'])
        );
        
        return $column.' '.$where['operator'].' '.$value;
    }
    
    /**
     * Compile a "where JSON contains" clause.
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereJsonContains(Builder $builder, $where): string
    {
        $not = $where['not'] ? 'not ' : '';
        
        return $not.$this->compileJsonContains(
            $where['column'],
            $this->parameter($where['value'])
        );
    }
    
    /**
     * Compile a "JSON contains" statement into SQL.
     * 
     * @param  string  $column
     * @param  string  $value
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected function compileJsonContains($column, $value): string
    {
        throw new RuntimeException('This database engine does not support JSON contains operations.');
    }/**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param  mixed  $binding
     * @return string
     */
    public function prepareBindingForJsonContains($binding)
    {
        return json_encode($binding, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Compile a "where JSON contains key" clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereJsonContainsKey(Builder $query, $where)
    {
        $not = $where['not'] ? 'not ' : '';

        return $not.$this->compileJsonContainsKey(
            $where['column']
        );
    }

    /**
     * Compile a "JSON contains key" statement into SQL.
     *
     * @param  string  $column
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function compileJsonContainsKey($column)
    {
        throw new RuntimeException('This database engine does not support JSON contains key operations.');
    }

    /**
     * Compile a "where JSON length" clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereJsonLength(Builder $query, $where)
    {
        return $this->compileJsonLength(
            $where['column'],
            $where['operator'],
            $this->parameter($where['value'])
        );
    }

    /**
     * Compile a "JSON length" statement into SQL.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function compileJsonLength($column, $operator, $value)
    {
        throw new RuntimeException('This database engine does not support JSON length operations.');
    }
    
    /**
     * Compile the "group by" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $groups
     * 
     * @return string
     */
    protected function compileGroups(Builder $builder, $groups): string
    {
        return 'group by '.$this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileHavings(Builder $builder): string
    {
        return 'having '.$this->removeLeadingBoolean(collect($builder->havings)->map(function ($having) {
            return $having['boolean'].' '.$this->compileHaving($having);
        })->implode(' '));
    }

    /**
     * Compile a single having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHaving(array $having): string
    {
        return match ($having['tytpe']) {
            'Raw' => $having['boolean'].' '.$having['sql'],
            'between' => $this->compileHavingBetween($having),
            'Null' => $this->compileHavingNull($having),
            'NotNull' => $this->compileHavingNotNull($having),
            'bit' => $this->compileHavingBit($having),
            'Expression' => $this->compileHavingExpression($having),
            'Nested' => $this->compileNestedHavings($having),
            default => $this->compileBasicHaving($having),
        };
    }

    /**
     * Compile a "between" having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHavingBetween($having): string
    {
        $between = $having['not'] ? 'not between' : 'between';

        $column = $this->wrap($having['column']);

        $min = $this->parameter(head($having['values']));
        $max = $this->parameter(last($having['values']));

        return $having['boolean'].' '.$column.' '.$between.' '.$min.' and '.$max;
    }
    
    /**
     * Compile a having null clause.
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHavingNull($having): string
    {
        $column = $this->wrap($having['column']);
        
        return $column.' is null';
    }
    
    /**
     * Compile a having not null clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHavingNotNull($having): string
    {
        $column = $this->wrap($having['column']);
        
        return $column.' is not null';
    }
    
    /**
     * Compile a having clause involving a bit operator.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHavingBit($having): string
    {
        $column = $this->wrap($having['column']);
        
        $parameter = $this->parameter($having['value']);
        
        return '('.$column.' '.$having['operator'].' '.$parameter.') != 0';
    }
    
    /**
     * Compile a having clause involving an expression.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHavingExpression($having): string
    {
        return $having['column']->getValue($this);
    }
    
    /**
     * Compile a nested having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileNestedHavings($having): string
    {
        return '('.substr($this->compileHavings($having['query']), 7).')';
    }

    /**
     * Compile a "basic" having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileBasicHaving($having): string
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'].' '.$column.' '.$having['operator'].' '.$parameter;
    }

    /**
     * Compile the "order by" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $orders
     * 
     * @return string
     */
    protected function compileOrders(Builder $builder, $orders): string
    {
        if ( ! empty($orders)) {
            return 'order by '.implode(', ', $this->compileOrderToArray($builder, $orders));
        }

        return '';
    }

    /**
     * Compile the query orders to an array.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $orders
     * 
     * @return array
     */
    protected function compileOrderToArray(Builder $builder, $orders): array
    {
        return array_map(fn ($order) => $order['sql'] ?? $this->wrap($order['column']).' '.$order['direction'], $orders);
    }

    /**
     * Compile the "limit" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  int  $limit
     * 
     * @return string
     */
    protected function compileLimit(Builder $builder, $limit): string
    {
        return 'limit '.(int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  int  $offset
     * 
     * @return string
     */
    protected function compileOffset(Builder $builder, $offset): string
    {
        return 'offset '.(int) $offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileUnions(Builder $builder): string
    {
        $sql = '';

        foreach ($builder->unions as $union) {
            $sql .= $this->compileUnion($union);
        }

        if ( ! empty($builder->unionOrders)) {
            $sql .= ' '.$this->compileOrders($builder, $builder->unionOrders);
        }

        if (isset($builder->unionLimit)) {
            $sql .= ' '.$this->compileLimit($builder, $builder->unionLimit);
        }

        if (isset($builder->unionOffset)) {
            $sql .= ' '.$this->compileOffset($builder, $builder->unionOffset);
        }

        return ltrim($sql);
    }

    /**
     * Compile a single union statement.
     * 
     * @param  array  $union
     * 
     * @return string
     */
    protected function compileUnion(array $union): string
    {
        $joiner = $union['all'] ? ' union all ' : ' union ';

        return $joiner.$this->wrapUnion($union['query']->tosql());
    }

    /**
     * Wrap a union subquery in parentheses.
     * 
     * @param  string  $sql
     * 
     * @return string
     */
    protected function wrapUnion($sql): string
    {
        return '('.$sql.')';
    }

    /**
     * Compile the random statement into SQL.
     * 
     * @param  string  $seed
     * 
     * @return string
     */
    public function compileRandom($seed): string
    {
        return 'RANDOM()';
    }
    
    /**
     * Compile a union aggregate query into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileUnionAggregate(Builder $builder): string
    {
        $sql = $this->compileAggregate($builder, $builder->aggregate);
        
        $builder->aggregate = null;
        
        return $sql.' from ('.$this->compileSelect($builder).') as '.$this->wrapTable('temp_table');
    }

    /**
     * Compile an exists statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileExists(Builder $builder): string 
    {
        $select = $this->compileSelect($builder);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }

    /**
     * Compile an insert statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $values
     * 
     * @return string
     */
    public function compileInsert(Builder $builder, array $values): string 
    {
        // Essentially we will force every insert to be treated as a batch insert which
        // simply makes creating the SQL easier.
        $table = $this->wrapTable($builder->from);

        if (empty($values)) {
            return "insert into {$table} default values";
        }

        if ( ! is_array(head($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(head($values)));
        
        // We need to build a list of parameter place-holders of values that are bound
        // to the query.
        $parameters = (new collection($values))
                        ->map(fn ($record) => '('.$this->parameterize($record).')')
                        ->implode(', ');

        return "insert into $table ($columns) values $parameters";
    }

    /**
     * Compile an insert and get ID statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $values
     * @param  string  $sequence
     * 
     * @return string
     */
    public function compileInsertGetId(Builder $builder, $values, $sequence): string
    {
        return $this->compileInsert($builder, $values);
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $columns
     * @param  string  $sql
     * 
     * @return string
     */
    public function compileInsertUsing(Builder $builder, $columns, $sql): string
    {
        $table = $this->wrapTable($builder->from);
        
        if (empty($columns) || $columns === ['*']) {
            return "insert into {$table} $sql";
        }

        return "insert into {$table} ({$this->columnize($columns)}) $sql";
    }

    /**
     * Compile an update statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $values
     * 
     * @return string
     */
    public function compileUpdate(Builder $builder, array $values): string
    {
        $table = $this->wrapTable($builder->from);

        $columns = $this->compileUpdateColumns($values);

        $where = $this->compileWheres($builder);

        return trim(
            isset($builder->joins)
                ? $this->compileUpdateWithJoins($builder, $table, $columns, $where)
                : $this->compileUpdateWithoutJoins($builder, $table, $columns, $where)
        );
    }

    /**
     * Compile the columns for an update statement.
     * 
     * @param  array  $values
     * 
     * @return string
     */
    public function compileUpdateColumns(array $values): string
    {
        return (new Collection($values))
            ->map(fn ($value, $key) => $this->wrap($key).' = '.$this->parameter($value))
            ->implode(', ');
    }

    /**
     * Compile an update statement with joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    public function compileUpdateWithJoins(Builder $builder, $table, $columns, $where): string
    {
        $joins = $this->compileJoins($builder, $builder->joins);

        return "update {$table} {$joins} set {$columns} {$where}";
    }

    /**
     * Compile an update statement without joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    public function compileUpdateWithoutJoins(Builder $builder, $table, $columns, $where): string
    {
       return "update {$table} set {$columns} {$where}";
    }

    /**
     * Compile a delete statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileDelete(Builder $builder): string
    {
        $table = $this->wrapTable($builder->from);

        $where = $this->compileWheres($builder);

        return trim(
            isset($builder->joins)
                ? $this->compileDeleteWithJoins($builder, $table, $where)
                : $this->compileDeleteWithoutJoins($builder, $table, $where)
        );
    }

    /**
     * Compile an delete statement with joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    public function compileDeleteWithJoins(Builder $builder, $table, $where): string
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($builder, $builder->joins);

        return "delete {$alias} from {$table} {$joins} {$where}";
    }

    /**
     * Compile an delete statement without joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $where
     * 
     * @return string
     */
    public function compileDeleteWithoutJoins(Builder $builder, $table, $where): string
    {
       return "delete from {$table} {$where}";
    }

    /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return array
     */
    public function compileTruncate(Builder $builder): array
    {
        return ['truncate table '.$this->wrapTable($builder->from) => []];
    }
    
    /**
     * Prepare the bindings for a delete statement.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function prepareBindingsForDelete(array $bindings): array
    {
        return Arr::flatten(
            Arr::except($bindings, 'select')
        );
    }
    
    /**
     * Prepare the bindings for an update statement.
     * 
     * @param  array  $bindings
     * @param  array  $values
     * 
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        $cleanBindings = Arr::except($bindings, ['select', 'join']);
        
        return array_values(
            array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
        );
    }    

    /**
     * Compile the lock into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  bool|string  $value
     * 
     * @return string
     */
    public function compileLock(Builder $builder, $value): string
    {
        return is_string($value) ? $value : '';
    }
    
    /**
     * Determine if the grammar supports savepoints.
     * 
     * @return bool
     */
    public function supportsSavepoints(): bool
    {
        return true;
    }
    
    /**
     * Compile the SQL statement to define a savepoint.
     * 
     * @param  string  $name
     * 
     * @return strin
     */
    public function compileSavepoint($name): string
    {
        return 'SAVEPOINT '.$name;
    }
    
    /**
     * Compile the SQL statement to execute a savepoint rollback.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    public function compileSavepointRollBack($name): string
    {
        return 'ROLLBACK TO SAVEPOINT '.$name;
    }
    
    /**
     * Wrap the given JSON selector for boolean values.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapJsonBooleanSelector($value): string
    {
        return $this->wrapJsonSelector($value);
    }
    
    /**
     * Wrap the given JSON boolean value.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapJsonBooleanValue($value): string
    {
        return $value;
    }

    /**
     * Concatenate an array of segments, removing empties.
     * 
     * @param  array  $segments
     * 
     * @return string
     */
    protected function concatenate($segments): string
    {
        return implode(' ', array_filter($segments, fn ($value) => (string) $value !== ''));
    }

    /**
     * Remove the leading boolean from a statement.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function removeStatementBoolean($value): string
    {
        return preg_replace('/and |or /', '', $value, 1);
    }
}