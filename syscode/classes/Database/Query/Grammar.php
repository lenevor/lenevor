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
            $column = 'DISTINCT '.$column;
        }

        return 'SELECT '.$aggregate['function'].'('.$column.') AS AGGREGATE';
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

        $select = $builder->distinct ? 'SELECT DISTINCT ' : 'SELECT ';

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
        return 'FROM '.$this->tablePrefix.$table;
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
}
