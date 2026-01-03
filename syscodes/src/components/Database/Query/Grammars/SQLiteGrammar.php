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
 * using the SQLite database manager.
 */
class SQLiteGrammar extends Grammar
{
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
        return '';
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
        return 'select * from ('.$sql.')';
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
        return $this->dateBasedWhere('%Y-%m-%d', $builder, $where);
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
        return $this->dateBasedWhere('%H:%M:%S', $builder, $where);
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
        return $this->dateBasedWhere('%d', $builder, $where);
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
        return $this->dateBasedWhere('%m', $builder, $where);
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
        return $this->dateBasedWhere('%Y', $builder, $where);
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

        return "strftime('{$type}', {$this->wrap($where['column'])}) {$where['operator']} cast($value) as text";
    }

    /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return array
     */
    public function truncate(Builder $builder): array
    {
        return [
            'delete from sqlite_sequence where name = ?' => [],
            'delete from '.$this->wrapTable($builder->from) => [],
        ];
    }
}