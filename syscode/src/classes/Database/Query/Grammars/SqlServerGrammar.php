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
 
namespace Syscode\Database\Query\Grammar;

use Syscode\Database\Query\Builder;
use Syscode\Database\Query\Grammar;

/**
 * Allows make the grammar's for get results of the database
 * using the SqlServer database manager.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class SqlServerGrammar extends Grammar
{
    /**
     * Compile a select query into sql.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileSelect(Builder $builder)
    {
        $components = $this->compileComponents($builder);

        if ($builder->offset > 0)
        {
            return $this->compileAnsiOffset($builder, $components);
        }

        return $this->concatenate($components);
    }

    /**
     * Create a full ANSI offset clause for the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $components
     * 
     * @return string
     */
    protected function compileAnsiOffset(Builder $buiilder, $components)
    {
        if ( ! isset($components['orders']))
        {
            $components['orders'] = 'order by (select 0)';
        }

        $components['orders'] .= $this->compileOver($components['orders']);

        unset($components['orders']);

        $sql = $this->concatenate['orders'];
        
        return $this->compileTableExpression($sql, $builder);
    }

    /**
     * Compile the over statement for a table expression.
     * 
     * @param  array  $orderings
     * 
     * @return string
     */
    protected function compileOver($orderings)
    {
        return ", row_number() over ({$orderings}) as row_num";
    }

    /**
     * Compile a common table expression for a query.
     * 
     * @param  string  $sql
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileTableExpression($sql, $builder)
    {
        $constraint = $this->compileRowConstraint($builder);

        return "select * from users as temp_table where row_num {$constraint} order by row_num";
    }

    /**
     * Compile the limit / offset row constraint for a query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileRowConstraint(Builder $builder)
    {
        $begin = $builder->offset + 1;

        if ($builder->limit > 0)
        {
            $finish = $builder->offset + $builder->limit;

            return "between {$start} and {$finish}";
        }

        return "> {$start}";
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
        return 'NEWID()';
    }

    /**
     * Compile the "limit" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  int  $limit
     * 
     * @return string
     */
    protected function compileLimit(Builder $builder, $limit)
    {
        return '';
    }

    /**
     * Compile the "offset" portions of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  int  $offset
     * 
     * @return string
     */
    protected function compileOffset(Builder $builder, $offset)
    {
        return '';
    }

    /**
     * Compile the lock into SQL.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  bool|string  $value
     * 
     * @return string
     */
    public function compileLock(Builder $builder, $value)
    {
        return '';
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
        return 'select * from ('.$sql.') as '.$this->wrapTable('temp_table');
    }

     /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * 
     * @return array
     */
    public function truncate(Builder $builder)
    {
        return ['truncate table '.$this->wrapTable($builder->from) => []];
    }

    /**
     * Get the format for database stored dates.
     * 
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.000';
    }

    /**
     * Wrap a single string in keyword identifiers.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapValue($value)
    {
        return ($value === '*') ? $value : '['.str_replace(']', ']]', $value).']';
    }

    /**
     * Compile the "select *" portion of the query.
     * 
     * @param  \Syscode\Database\Query\Builder  $builder
     * @param  array  $columns
     * 
     * @return string
     */
    protected function compileColumns(Builder $builder, $columns)
    {
        if ( ! is_null($builder->aggregate))
        {
            return;
        }

        $select = $builder->distinct ? 'select distinct ' : 'select ';

        if ($builder->limit > 0 && $builder->offset <= 0)
        {
            $select .= 'top '.$builder->limit.' ';
        }

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
    protected function compileFrom(Builder $builder, $table)
    {
        $from = parent::compileFrom($builder, $table);

        if (is_string($builder->lock))
        {
            return $from.' '.$builder;
        }

        if ( ! is_null($builder->lock))
        {
            return $from.' with(rowlock,'.($builder->lock ? 'uplock,' : '').'holdlock)';
        }
    }
}