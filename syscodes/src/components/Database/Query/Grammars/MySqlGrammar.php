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

use Syscodes\Components\Database\Query\Builder;

/**
 * Allows make the grammar's for get results of the database
 * using the Mysql database manager.
 */
class MySqlGrammar extends Grammar
{
    /**
     * Compile a select query into sql.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileSelect(Builder $builder): string
    {
        $sql = parent::compileSelect($builder);

        if ($builder->unions) {
            $sql = '('.$sql.') '.$this->compileUnions($builder);
        }

        return $sql;
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
        if (empty($values)) {
            $values = [[]];
        }

        return parent::compileInsert($builder, $values);
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
        $sql = parent::compileUpdateWithoutJoins($builder, $table, $columns, $where);

        if ( ! empty($builder->orders)) {
            $sql .= ' '.$this->compileOrders($builder, $builder->orders);
        }

        if (isset($builder->limit)) {
            $sql .= ' '.$this->compileLimit($builder, $builder->limit);
        }
        
        return rtrim($sql);
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
        $sql = parent::compileDeleteWithoutJoins($builder, $table, $where);

        if ( ! empty($builder->orders)) {
            $sql .= ' '.$this->compileOrders($builder, $builder->orders);
        }

        if (isset($builder->limit)) {
            $sql .= ' '.$this->compileLimit($builder, $builder->limit);
        }
        
        return rtrim($sql);
    }

    /**
     * Wrap a single string in keyword identifiers.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapValue($value): string
    {
        return ($value === '*') ? $value : '`'.str_replace('`', '``', $value).'`';
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
        if ( ! is_string($value)) {
            return $value ? 'for update' : 'lock in share mode';
        }

        return $value;
    }
}