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
use Syscodes\Components\Database\Concerns\CompilesJsonPaths;
use Syscodes\Components\Database\Grammar as BaseGrammar;
use Syscodes\Components\Database\Query\Builder;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Database\Query\JoinClause;
use Syscodes\Components\Database\Query\JoinLateralClause;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;

/**
 * Allows make the grammar's for get results of the database.
 */
class Grammar extends BaseGrammar
{
    use CompilesJsonPaths;
    
    /**
     * Get the components for use a select statement.
     * 
     * @var array
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
     * The grammar specific operators.
     *
     * @var array
     */
    protected $operators = [];

    /**
     * Compile a select query into sql.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    public function compileSelect(Builder $query): string
    {
        if (($query->unions || $query->havings) && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }
        
        // If the query does not have any columns set, we'll set the columns to the
        // character to just get all of the columns from the database.
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }
        
        // To compile the query, we'll spin through each component of the query and
        // see if that component exists.
        $sql = trim($this->concatenate(
            $this->compileComponents($query))
        );
        
        if ($query->unions) {
            $sql = $this->wrapUnion($sql).' '.$this->compileUnions($query);
        }

        $query->columns = $original;

        return $sql;
    }

    /**
     * Compile the components necessary for a select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return array
     */
    protected function compileComponents(Builder $query): array
    {
        $sql = [];

        foreach ($this->components as $component) {
            if (isset($query->$component)) {
                $method = 'compile'.ucfirst($component);

                $sql[$component] = $this->$method($query, $query->$component);
            }
        }

        return $sql;
    }

    /**
     * Compile an aggregated select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $aggregate
     * 
     * @return string
     */
    protected function compileAggregate(Builder $query, $aggregate): string
    {
        $column = $this->columnize($aggregate['columns']);
        
        // If the query has a "distinct" constraint and we're not asking for all columns
        // we need to prepend "distinct" onto the column name so that the query.
        if (is_array($query->distinct)) {
            $column = 'distinct '.$this->columnize($query->distinct);
        } elseif ($query->distinct && $column !== '*') {
            $column = 'distinct '.$column;
        }

        return 'select '.$aggregate['function'].'('.$column.') as aggregate';
    }

    /**
     * Compile the "select *" portion of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $columns
     * 
     * @return string|null
     */
    protected function compileColumns(Builder $query, $columns)
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax.
        if ( ! is_null($query->aggregate)) {
            return;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select.$this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $table
     * 
     * @return string
     */
    protected function compileFrom(Builder $query, $table)
    {
        return 'from '.$this->wrapTable($table);
    }

    /**
     * Compile the "join" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $joins
     * 
     * @return string
     */
    protected function compileJoins(Builder $query, $joins): string
    {
        return (new Collection($joins))->map(function ($join) use ($query) {
            $table = $this->wrapTable($join->table);
            
            $nestedJoins = is_null($join->joins) ? '' : ' '.$this->compileJoins($query, $join->joins);
            
            $tableAndNestedJoins = is_null($join->joins) ? $table : '('.$table.$nestedJoins.')';

            if ($join instanceof JoinLateralClause) {
                return $this->compileJoinLateral($join, $tableAndNestedJoins);
            }
            
            return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
        })->implode(' ');
    }

    /**
     * Compile a "lateral join" clause.
     *
     * @param  \Syscodes\Components\Database\Query\JoinLateralClause  $join
     * @param  string  $expression
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileJoinLateral(JoinLateralClause $join, string $expression): string
    {
        throw new RuntimeException('This database engine does not support lateral joins.');
    }

    /**
     * Compile the "where" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    protected function compileWheres(Builder $query): string
    {
        // Each type of where clause has its own compiler function, which is responsible
        // for actually creating the where clauses SQL. 
        if (is_null($query->wheres)) {
            return '';
        }
        
        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience
        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWheresClauses($query, $sql);
        }
        
        return '';
    }

    /**
     * Get an array of all the where clauses for the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return array
     */
    protected function compileWheresToArray($query): array
    {
        return (new collection($query->wheres))
            ->map(fn ($where) => $where['boolean'].' '.$this->{"where{$where['type']}"}($query, $where))
            ->all();
    }

    /**
     * Format the where clause statements into one string.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $sql
     * 
     * @return string
     */
    protected function concatenateWheresClauses($query, $sql): string
    {
        $statement = $query instanceof JoinClause ? 'on' : 'where';

        return $statement.' '.$this->removeStatementBoolean(implode(' ', $sql));
    }

    /**
     * Compile a raw where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereRaw(Builder $query, $where): string
    {
        return $where['sql'] instanceof Expression ? $where['sql']->getValue($this) : $where['sql'];
    }

    /**
     * Compile a basic where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBasic(Builder $query, $where): string
    {
        $operator = str_replace('?', '??', $where['operator']);
        $value    = $this->parameter($where['value']);
       
        return $this->wrap($where['column']).' '.$operator.' '.$value;
    }

    /**
     * Compile a "between" where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBetween(Builder $query, $where): string
    {
        $between = $where['negative'] ? 'not between' : 'between';

        $min = $this->parameter(is_array($where['values']) ? head($where['values']) : $where['values'][0]);
        $max = $this->parameter(is_array($where['values']) ? last($where['values']) : $where['values'][1]);

        return $this->wrap($where['column']).' '.$between.' '.$min.' and '.$max;
    }

    /**
     * Compile a "between" where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    public function whereBetweenColumns(Builder $query, $where): string
    {
        $between = $where['negative'] ? 'not between' : 'between';

        $min = $this->wrap(is_array($where['values']) ? head($where['values']) : $where['values'][0]);
        $max = $this->wrap(is_array($where['values']) ? last($where['values']) : $where['values'][1]);

        return $this->wrap($where['column']).' '.$between.' '.$min.' and '.$max;
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereExists(Builder $query, $where): string
    {
        return 'exists ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a where exists clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotExists(Builder $query, $where): string
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
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereIn(Builder $query, $where): string
    {
        if (empty($where['values'])) return '0 = 1';
        
        return $this->wrap($where['column']).' in ('.$this->parameterize($where['values']).')';
    }

    /**
     * Compile a "where not in" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotIn(Builder $query, $where): string
    {
        if (empty($where['query'])) return '1 = 1';

        return $this->wrap($where['column']).' not in ('.$this->parameterize($where['query']).')';
    }

    /**
     * Compile a "where not in raw" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotInRaw(Builder $query, $where): string
    {
        if (empty($where['values'])) return '1 = 1';
        
        return $this->wrap($where['column']).' not in ('.implode(', ', $where['values']).')';
    }

    /**
     * Compile a "where in raw" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereInRaw(Builder $query, $where): string
    {
        if (empty($where['values'])) return '0 = 1';
        
        return $this->wrap($where['column']).' in ('.implode(', ', $where['values']).')';
    }

    /**
     * Compile a where in sub-select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereInSub(Builder $query, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' in ('.$select.')';
    }

    /**
     * Compile a where not in sub-select clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotInSub(Builder $query, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' not in ('.$select.')';
    }

    /**
     * Compile a "where null" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNull(Builder $query, $where): string
    {
        return $this->wrap($where['column']).' is null';
    }

    /**
     * Compile a "where not null" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotNull(Builder $query, $where): string
    {
        return $this->wrap($where['column']).' is not null';
    }

    /**
     * Compile a "where date" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereDate(Builder $query, $where): string
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereTime(Builder $query, $where): string
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    /**
     * Compile a "where day" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereDay(Builder $query, $where): string
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereMonth(Builder $query, $where): string
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereYear(Builder $query, $where): string
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile a date based where clause.
     * 
     * @param  string  $type
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        return $type.'('.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a nested where clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNested(Builder $query, $where): string
    {
        // Here we will calculate what portion of the string we need to remove. If this
        // is a join clause query, we need to remove the "on" portion of the SQL
        $intClause = $query instanceof JoinClause ? 3 : 6;

        return '('.substr($this->compileWheres($where['query']), $intClause).')';
    }

    /**
     * Compile a where condition with a sub-select.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereSub(Builder $query, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']).' '.$where['operator']." ($select)";
    }

    /**
     * Compile a where clause comparing two columns.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereColumn(Builder $query, $where): string
    {
        return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }
    
    /**
     * Compile a where row values condition.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereRowValues(Builder $query, $where): string
    {
        $columns = $this->columnize($where['columns']);
        
        $values = $this->parameterize($where['values']);
        
        return '('.$columns.') '.$where['operator'].' ('.$values.')';
    }
    
    /**
     * Compile a "where JSON boolean" clause.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereJsonBoolean(Builder $query, $where): string
    {
        $column = $this->wrapJsonBooleanSelector($where['column']);
        
        $value = $this->wrapJsonBooleanValue(
            $this->parameter($where['value'])
        );
        
        return $column.' '.$where['operator'].' '.$value;
    }
    
    /**
     * Compile a "where JSON contains" clause.
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereJsonContains(Builder $query, $where): string
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
    }
    
    /**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param  mixed  $binding
     * 
     * @return string
     */
    public function prepareBindingForJsonContains($binding): string
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
     * Compile a "JSON value cast" statement into SQL.
     *
     * @param  string  $value
     * 
     * @return string
     */
    public function compileJsonValueCast($value): string
    {
        return $value;
    }

    /**
     * Compile a "where fulltext" clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    public function whereFullText(Builder $query, $where): string
    {
        throw new RuntimeException('This database engine does not support fulltext search operations.');
    }

    /**
     * Compile a clause based on an expression.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    public function whereExpression(Builder $query, $where): string
    {
        return $where['column']->getValue($this);
    }
    
    /**
     * Compile the "group by" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $groups
     * 
     * @return string
     */
    protected function compileGroups(Builder $query, $groups): string
    {
        return 'group by '.$this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    protected function compileHavings(Builder $query): string
    {
        return 'having '.$this->removeStatementBoolean((new collection($query->havings))->map(function ($having) {
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
        return match ($having['type']) {
            'Raw' => $having['sql'],
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

        return $column.' '.$between.' '.$min.' and '.$max;
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

        return $column.' '.$having['operator'].' '.$parameter;
    }

    /**
     * Compile the "order by" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $orders
     * 
     * @return string
     */
    protected function compileOrders(Builder $query, $orders): string
    {
        if ( ! empty($orders)) {
            return 'order by '.implode(', ', $this->compileOrderToArray($query, $orders));
        }

        return '';
    }

    /**
     * Compile the query orders to an array.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $orders
     * 
     * @return array
     */
    protected function compileOrderToArray(Builder $query, $orders): array
    {
        return array_map(fn ($order) => $order['sql'] ?? $this->wrap($order['column']).' '.$order['direction'], $orders);
    }

    /**
     * Compile the "limit" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  int  $limit
     * 
     * @return string
     */
    protected function compileLimit(Builder $query, $limit): string
    {
        return 'limit '.(int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  int  $offset
     * 
     * @return string
     */
    protected function compileOffset(Builder $query, $offset): string
    {
        return 'offset '.(int) $offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    protected function compileUnions(Builder $query): string
    {
        $sql = '';

        foreach ($query->unions as $union) {
            $sql .= $this->compileUnion($union);
        }

        if ( ! empty($query->unionOrders)) {
            $sql .= ' '.$this->compileOrders($query, $query->unionOrders);
        }

        if (isset($query->unionLimit)) {
            $sql .= ' '.$this->compileLimit($query, $query->unionLimit);
        }

        if (isset($query->unionOffset)) {
            $sql .= ' '.$this->compileOffset($query, $query->unionOffset);
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
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    protected function compileUnionAggregate(Builder $query): string
    {
        $sql = $this->compileAggregate($query, $query->aggregate);
        
        $query->aggregate = null;
        
        return $sql.' from ('.$this->compileSelect($query).') as '.$this->wrapTable('temp_table');
    }

    /**
     * Compile an exists statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    public function compileExists(Builder $query): string 
    {
        $select = $this->compileSelect($query);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }

    /**
     * Compile an insert statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $values
     * 
     * @return string
     */
    public function compileInsert(Builder $query, array $values): string 
    {
        // Essentially we will force every insert to be treated as a batch insert which
        // simply makes creating the SQL easier.
        $table = $this->wrapTable($query->from);

        if (empty($values)) {
            return "insert into {$table} default values";
        }

        if ( ! is_array(array_first($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(array_first($values)));
        
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
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $values
     * @param  string  $sequence
     * 
     * @return string
     */
    public function compileInsertGetId(Builder $query, $values, $sequence): string
    {
        return $this->compileInsert($query, $values);
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $values
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        throw new RuntimeException('This database engine does not support inserting while ignoring errors.');
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $columns
     * @param  string  $sql
     * 
     * @return string
     */
    public function compileInsertUsing(Builder $query, $columns, $sql): string
    {
        $table = $this->wrapTable($query->from);
        
        if (empty($columns) || $columns === ['*']) {
            return "insert into {$table} $sql";
        }

        return "insert into {$table} ({$this->columnize($columns)}) $sql";
    }

    /**
     * Compile an insert ignore statement using a subquery into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $columns
     * @param  string  $sql
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql): string
    {
        throw new RuntimeException('This database engine does not support inserting while ignoring errors.');
    }

    /**
     * Compile an update statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $values
     * 
     * @return string
     */
    public function compileUpdate(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        $columns = $this->compileUpdateColumns($query, $values);

        $where = $this->compileWheres($query);

        return trim(
            isset($query->joins)
                ? $this->compileUpdateWithJoins($query, $table, $columns, $where)
                : $this->compileUpdateWithoutJoins($query, $table, $columns, $where)
        );
    }

    /**
     * Compile the columns for an update statement.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $values
     * 
     * @return string
     */
    protected function compileUpdateColumns(Builder $query, array $values): string
    {
        return (new Collection($values))
            ->map(fn ($value, $key) => $this->wrap($key).' = '.$this->parameter($value))
            ->implode(', ');
    }

    /**
     * Compile an update statement with joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    protected function compileUpdateWithJoins(Builder $query, $table, $columns, $where): string
    {
        $joins = $this->compileJoins($query, $query->joins);

        return "update {$table} {$joins} set {$columns} {$where}";
    }

    /**
     * Compile an update statement without joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * 
     * @return string
     */
    protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where): string
    {
       return "update {$table} set {$columns} {$where}";
    }

    /**
     * Compile a delete statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return string
     */
    public function compileDelete(Builder $query): string
    {
        $table = $this->wrapTable($query->from);

        $where = $this->compileWheres($query);

        return trim(
            isset($query->joins)
                ? $this->compileDeleteWithJoins($query, $table, $where)
                : $this->compileDeleteWithoutJoins($query, $table, $where)
        );
    }

    /**
     * Compile an delete statement with joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $table
     * @param  string  $where
     * 
     * @return string
     */
    protected function compileDeleteWithJoins(Builder $query, $table, $where): string
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($query, $query->joins);

        return "delete {$alias} from {$table} {$joins} {$where}";
    }

    /**
     * Compile an delete statement without joins into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $table
     * @param  string  $where
     * 
     * @return string
     */
    protected function compileDeleteWithoutJoins(Builder $query, $table, $where): string
    {
       return "delete from {$table} {$where}";
    }

    /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return array
     */
    public function compileTruncate(Builder $query): array
    {
        return ['truncate table '.$this->wrapTable($query->from) => []];
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
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  bool|string  $value
     * 
     * @return string
     */
    public function compileLock(Builder $query, $value): string
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
     * @return string
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

    /**
     * Get the grammar specific operators.
     *
     * @return array
     */
    public function getOperators(): array
    {
        return $this->operators;
    }
}