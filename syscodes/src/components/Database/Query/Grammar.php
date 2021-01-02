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
 * @copyright   Copyright (c) 2019-2021 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.0
 */
 
namespace Syscodes\Database\Query;

use Syscodes\Database\Grammar as BaseGrammar;

/**
 * Allows make the grammar's for get results of the database.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
        'unions'
    ];

    /**
     * Compile a select query into sql.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileSelect(Builder $builder)
    {
        if (is_null($builder->columns))
        {
            $builder->columns = ['*'];
        }

        return trim($this->concatenate($this->compileComponents($builder)));
    }

    /**
     * Compile the components necessary for a select clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return array
     */
    protected function compileComponents(Builder $builder)
    {
        $sql = [];

        foreach ($this->components as $component)
        {
            if ( ! is_null($builder->$component))
            {
                $method = 'compile'.ucfirst($component);

                $sql[$component] = $this->$method($builder, $builder->component);
            }
        }

        return $sql;
    }

    /**
     * Compile an aggregated select clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $aggregate
     * 
     * @return string
     */
    protected function compileAggregate(Builder $builder, $aggregate)
    {
        $column = $this->columnize($aggregate['columns']);

        if ($builder->distinct && $column !== '*')
        {
            $column = 'distinct '.$column;
        }

        return 'select '.$aggregate['function'].'('.$column.') as aggregate';
    }

    /**
     * Compile the "select *" portion of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $columns
     * 
     * @return string
     */
    protected function compileColumns(Builder $builder, $columns)
    {
        if ( ! is_null($builder->columns))
        {
            return;
        }

        $select = $builder->distinct ? 'select distinct ' : 'select ';

        return $select.$this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
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
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $joins
     * 
     * @return string
     */
    protected function compileJoins(Builder $builder, $joins)
    {
        $sql = [];

        foreach ((array) $joins as $join)
        {
            $table = $this->wrapTable($join->table);

            $clauses = [];

            foreach ($join->clauses as $clause)
            {
                $clauses[] = $this->compileJoinContraint($clause);
            }

            foreach ($join->bindings as $binding)
            {
                $query->addBinding($binding, 'join');
            }

            $clauses[0] = $this->removeStatementBoolean($clauses[0]);

            $clauses = implode(' ', $clauses);

            $sql[] = "{$join->type} join {$table} on {$clauses}";
        }

        return implode(' ', $sql);
    }

    /**
     * Create a join clause constraint segment.
     * 
     * @param  array  $clause
     * 
     * @return string
     */
    protected function compileJoinContraint(array $clause)
    {
        $first  = $this->wrap($clause['first']);
        $second = $clause['where'] ? '?' : $this->wrap($clause['second']);

        return "{$clause['boolean']} $first {$clause['operator']} $second";
    }

    /**
     * Compile the "where" portions of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileWheres(Builder $builder)
    {
       if (is_null($builder->wheres))
       {
           return '';
       }

       if (count($sql = $this->compileWheresToArray($builder)) > 0)
       {
            return $this->concatenateWheresClauses($builder, $sql);
       }

       return '';
    }

    /**
     * Get an array of all the where clauses for the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $query
     * 
     * @return array
     */
    protected function compileWheresToArray($query)
    {
        $sql = [];

        foreach ($query->wheres as $where)
        {
            $sql[] = $where['boolean'].' '.$this->{"where{$where['type']}"}($query, $where);
        }

        return $sql;
    }

    /**
     * Format the where clause statements into one string.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $sql
     * 
     * @return string
     */
    protected function concatenateWheresClauses($builder, $sql)
    {
        $statement = $builder->joins ? 'on' : 'where';

        return $statement.' '.$this->removeStatementBoolean(implode(' ', $sql));
    }

    /**
     * Compile a raw where clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereRaw(Builder $builder, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBasic(Builder $builder, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "between" where clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBetween(Builder $builder, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        return $this->wrap($where['column']).' '.$between.' ? and ?';
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereExists(Builder $builder, $where)
    {
        return 'exists ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotExists(Builder $builder, $where)
    {
        return 'not exists ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a "where in" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereIn(Builder $builder, $where)
    {
        $values = $this->parameterize($where['query']);

        if ( ! empty($where['query']))
        {
            return $this->wrap($where['column']).' in ('.$values.')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotIn(Builder $builder, $where)
    {
        $values = $this->parameterize($where['query']);

        if ( ! empty($where['query']))
        {
            return $this->wrap($where['column']).' not in ('.$values.')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where not in raw" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotInRaw(Builder $builder, $where)
    {
        if ( ! empty($where['query']))
        {
            return $this->wrap($where['column']).' not in ('.implode(', ', $where['values']).')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where in raw" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereInRaw(Builder $builder, $where)
    {
        if ( ! empty($where['query']))
        {
            return $this->wrap($where['column']).' in ('.implode(', ', $where['values']).')';
        }

        return '0 = 1';
    }

    /**
     * Compile a where in sub-select clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereInSub(Builder $builder, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' in ('.$select.')';
    }

    /**
     * Compile a where not in sub-select clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotInSub(Builder $builder, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' not in ('.$select.')';
    }

    /**
     * Compile a "where null" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNull(Builder $builder, $where)
    {
        return $this->wrap($where['column']).' is null';
    }

    /**
     * Compile a "where not null" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotNull(Builder $builder, $where)
    {
        return $this->wrap($where['column']).' not is null';
    }

    /**
     * Compile a "where date" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereDate(Builder $builder, $where)
    {
        return $this->dateBasedWhere('date', $builder, $where);
    }

    /**
     * Compile a "where time" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereTime(Builder $builder, $where)
    {
        return $this->dateBasedWhere('time', $builder, $where);
    }

    /**
     * Compile a "where day" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereDay(Builder $builder, $where)
    {
        return $this->dateBasedWhere('day', $builder, $where);
    }

    /**
     * Compile a "where month" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereMonth(Builder $builder, $where)
    {
        return $this->dateBasedWhere('month', $builder, $where);
    }

    /**
     * Compile a "where year" clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereYear(Builder $builder, $where)
    {
        return $this->dateBasedWhere('year', $builder, $where);
    }

    /**
     * Compile a date based where clause.
     * 
     * @param  string  $type
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function dateBasedWhere($type, Builder $builder, $where)
    {
        $value = $this->parameter($where['value']);

        return $type.'('.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a nested where clause.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNested(Builder $builder, $where)
    {
        $intClause = $builder instanceof JoinClause ? 3 : 6;

        return '('.substr($this->compileWheres($where['query']), $intClause).')';
    }

    /**
     * Compile a where condition with a sub-select.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereSub(Builder $builder, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' '.$where['operator']." ($select)";
    }

    /**
     * Compile a where clause comparing two columns.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereColumn(Builder $builder, $where)
    {
        return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }

    /**
     * Compile the "group by" portions of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $groups
     * 
     * @return string
     */
    protected function compileGroups(Builder $builder, $groups)
    {
        return 'group by '.$this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $havings
     * 
     * @return string
     */
    protected function compileHavings(Builder $builder, $havings)
    {
        $sql = implode(' ', array_map([$this, 'compileHaving'], $havings));

        return 'having '.$this->removeStatementBoolean($sql);
    }

    /**
     * Compile a single having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHaving(array $having)
    {
        if ($having['type'] === 'raw')
        {
            return $having['boolean'].' '.$having['sql'];
        }
        elseif ($having['type'] === 'between')
        {
            return $this->compileHavingBetween($having);
        }

        return $this->compileBasicHaving($having);
    }

    /**
     * Compile a "between" having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileHavingBetween($having)
    {
        $between = $having['not'] ? 'not between' : 'between';

        $column = $this->wrap($having['column']);

        $min = $this->parameter(head($having['values']));
        $max = $this->parameter(last($having['values']));

        return $having['boolean'].' '.$column.' '.$between.' '.$min.' and '.$max;
    }

    /**
     * Compile a "basic" having clause.
     * 
     * @param  array  $having
     * 
     * @return string
     */
    protected function compileBasicHaving($having)
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['values']);

        return $having['boolean'].' '.$column.' '.$having['operator'].' '.$parameter;
    }

    /**
     * Compile the "order by" portions of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $orders
     * 
     * @return string
     */
    protected function compileOrders(Builder $builder, $orders)
    {
        if ( ! empty($orders))
        {
            return 'order by '.implode(', ', $this->compileOrderToArray($builder, $orders));
        }

        return '';
    }

    /**
     * Compile the query orders to an array.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $orders
     * 
     * @return string
     */
    protected function compileOrderToArray(Builder $builder, $orders)
    {
        return array_map(function ($order) {
            return $order['sql'] ?? $this->wrap($order['column']).' '.$order['direction'];
        }, $orders);
    }

    /**
     * Compile the "limit" portions of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  int  $limit
     * 
     * @return string
     */
    protected function compileLimit(Builder $builder, $limit)
    {
        return 'limit '.(int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  int  $offset
     * 
     * @return string
     */
    protected function compileOffset(Builder $builder, $offset)
    {
        return 'offset '.(int) $offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileUnions(Builder $builder)
    {
        $sql = '';

        foreach ($builder->unions as $union)
        {
            $sql .= $this->compileUnion($union);
        }

        if ( ! empty($builder->unionOrders))
        {
            $sql .= ' '.$this->compileOrders($builder, $builder->unionOrders);
        }

        if (isset($builder->unionLimit))
        {
            $sql .= ' '.$this->compileLimit($builder, $builder->unionLimit);
        }

        if (isset($builder->unionOffset))
        {
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
    protected function compileUnion(array $union)
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
    protected function wrapUnion($sql)
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
    public function compileRandom($seed)
    {
        return 'RANDOM()';
    }

    /**
     * Compile an exists statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileExists(Builder $builder)
    {
        $select = $this->compileSelect($builder);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }

    /**
     * Compile an insert statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $values
     * 
     * @return string
     */
    public function compileInsert(Builder $builder, array $values)
    {
        $table = $this->wrapTable($builder->from);

        if (empty($values))
        {
            return "insert into {$table} default values";
        }

        if ( ! is_array(head($values)))
        {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(head($values)));

        $parameters = $this->parameterize(head($values));

        $value = array_fill(0, count($values), "($parameters)");

        $parameters = implode(', ', $value);

        return "insert into $table ($columns) values $parameters";
    }

    /**
     * Compile an insert and get ID statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $values
     * @param  string  $sequence
     * 
     * @return string
     */
    public function compileInsertGetId(Builder $builder, $values, $sequence)
    {
        return $this->compileInsert($builder, $values);
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $columns
     * @param  string  $sql
     * 
     * @return string
     */
    public function compileInsertUsing(Builder $builder, $columns, $sql)
    {
        return "insert into {$this->wrapTable($builder->from)} ({$this->columnize($columns)}) $sql";
    }

    /**
     * Compile an update statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $values
     * 
     * @return string
     */
    public function compileUpdate(Builder $builder, array $values)
    {
        $table = $this->wrapTable($builder->from);

        $columns = $this->getUpdateColumns($values);

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
    public function getUpdateColumns(array $values)
    {
        $columns = [];

        foreach ($values as $key => $value)
        {
            $columns[] = $this->wrap($key).' = '.$this->parameter($value);
        }

        return implode(', ', $columns);
    }

    /**
     * Compile an update statement with joins into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    public function compileUpdateWithJoins(Builder $builder, $table, $columns, $where)
    {
        $joins = $this->compileJoins($builder, $builder->joins);

        return "update {$table} {$joins} set {$columns} {$where}";
    }

    /**
     * Compile an update statement without joins into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    public function compileUpdateWithoutJoins(Builder $builder, $table, $columns, $where)
    {
       return "update {$table} set {$columns} {$where}";
    }

    /**
     * Compile a delete statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileDelete(Builder $builder)
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
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    public function compileDeleteWithJoins(Builder $builder, $table, $where)
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($builder, $builder->joins);

        return "delete {$alias} from {$table} {$joins} {$where}";
    }

    /**
     * Compile an delete statement without joins into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $where
     * 
     * @return string
     */
    public function compileDeleteWithoutJoins(Builder $builder, $table, $where)
    {
       return "delete from {$table} {$where}";
    }

    /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return array
     */
    public function compileTruncate(Builder $builder)
    {
        return ['truncate table '.$this->wrapTable($builder->from) => []];
    }

    /**
     * Compile the lock into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  bool|string  $value
     * 
     * @return string
     */
    public function compileLock(Builder $builder, $value)
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Concatenate an array of segments, removing empties.
     * 
     * @param  array  $segments
     * 
     * @return string
     */
    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    /**
     * Remove the leading boolean from a statement.
     * 
     * @param  string  $value
     * 
     * @return
     */
    protected function removeStatementBoolean($value)
    {
        return preg_replace('/and |or /', '', $value, 1);
    }
}