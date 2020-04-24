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

use Syscode\Database\Grammar as BaseGrammar;

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
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileSelect(Builder $builder)
    {
        if (is_null($builder->columns))
        {
            $builder->columns = ['*'];
        }

        return trim($this->concatenate($this->components($builder)));
    }

    /**
     * Compile the components necessary for a select clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return array
     */
    protected function components(Builder $builder)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $aggregate
     * 
     * @return string
     */
    public function compileAggregate(Builder $builder, $aggregate)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $columns
     * 
     * @return string
     */
    public function compileColumns(Builder $builder, $columns)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  string  $table
     * 
     * @return string
     */
    public function compileFrom(Builder $builder, $table)
    {
        return 'from '.$this->wrapTable($table);
    }

    /**
     * Compile the "join" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  string  $joins
     * 
     * @return string
     */
    public function compileJoin(Builder $builder, $joins)
    {
        $sql = [];

        foreach ($joins as $join)
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
    protected function compileJoinContraint($clause)
    {
        $first  = $this->wrap($clause['first']);
        $second = $clause['where'] ? '?' : $this->wrap($clause['second']);

        return "{$clause['boolean']} $first {$clause['operator']} $second";
    }

    /**
     * Compile the "where" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileWheres(Builder $builder)
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
     * @param  \Syscode\Database\Query\Builder  $query
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
     * @param  \Syscode\Database\Query\Builder  $builder
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereRaw(Builder $builder, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereBasic(Builder $builder, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "between" where clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereBetween(Builder $builder, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        return $this->wrap($where['column']).' '.$between.' ? and ?';
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereExists(Builder $builder, $where)
    {
        return 'exists ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNotExists(Builder $builder, $where)
    {
        return 'not exists ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a "where in" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereIn(Builder $builder, $where)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNotIn(Builder $builder, $where)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNotInRaw(Builder $builder, $where)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereInRaw(Builder $builder, $where)
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereInSub(Builder $builder, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' in ('.$select.')';
    }

    /**
     * Compile a where not in sub-select clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNotInSub(Builder $builder, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' not in ('.$select.')';
    }

    /**
     * Compile a "where null" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNull(Builder $builder, $where)
    {
        return $this->wrap($where['column']).' is null';
    }

    /**
     * Compile a "where not null" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNotNull(Builder $builder, $where)
    {
        return $this->wrap($where['column']).' not is null';
    }

    /**
     * Compile a "where date" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereDate(Builder $builder, $where)
    {
        return $this->dateBasedWhere('date', $builder, $where);
    }

    /**
     * Compile a "where time" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereTime(Builder $builder, $where)
    {
        return $this->dateBasedWhere('time', $builder, $where);
    }

    /**
     * Compile a "where day" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereDay(Builder $builder, $where)
    {
        return $this->dateBasedWhere('day', $builder, $where);
    }

    /**
     * Compile a "where month" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereMonth(Builder $builder, $where)
    {
        return $this->dateBasedWhere('month', $builder, $where);
    }

    /**
     * Compile a "where year" clause.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereYear(Builder $builder, $where)
    {
        return $this->dateBasedWhere('year', $builder, $where);
    }

    /**
     * Compile a date based where clause.
     * 
     * @param  string  $type
     * @param  \Syscode\Database\Query\Builder  $builder
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereNested(Builder $builder, $where)
    {
        $intClause = $builder instanceof JoinClause ? 3 : 6;

        return '('.substr($this->compileWheres($where['query']), $intClause).')';
    }

    /**
     * Compile a where condition with a sub-select.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereSub(Builder $builder, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' '.$where['operator']." ($select)";
    }

    /**
     * Compile a where clause comparing two columns.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereColumn(Builder $builder, $where)
    {
        return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }

    /**
     * Compile the "group by" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $groups
     * 
     * @return string
     */
    public function compileGroups(Builder $builder, $groups)
    {
        return 'group by '.$this->columnize($groups);
    }

    /**
     * Compile the "limit" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  int  $limit
     * 
     * @return string
     */
    public function compileLimit(Builder $builder, $limit)
    {
        return 'limit '.(int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  int  $offset
     * 
     * @return string
     */
    public function compileOffset(Builder $builder, $offset)
    {
        return 'offset '.(int) $offset;
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
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileExists(Builder $builder)
    {
        $select = $this->compileSelect($builder);

        return "select exists({$select}) as {$this->wrap('exists')}";
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