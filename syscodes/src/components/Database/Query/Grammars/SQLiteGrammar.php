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
use Syscodes\Components\Support\Str;

/**
 * Allows make the grammar's for get results of the database
 * using the SQLite database manager.
 */
class SQLiteGrammar extends Grammar
{
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
     * Compile a basic where clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBasic(Builder $query, $where): string
    {
        if ($where['operator'] === '<=>') {
            $column = $this->wrap($where['column']);
            $value = $this->parameter($where['value']);

            return "{$column} IS {$value}";
        }

        return parent::whereBasic($query, $where);
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
        if ($where['caseSensitive'] == false) {
            return parent::whereLike($query, $where);
        }
        $where['operator'] = $where['not'] ? 'not glob' : 'glob';

        return $this->whereBasic($query, $where);
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
        return $this->dateBasedWhere('%Y-%m-%d', $query, $where);
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
        return $this->dateBasedWhere('%H:%M:%S', $query, $where);
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
        return $this->dateBasedWhere('%d', $query, $where);
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
        return $this->dateBasedWhere('%m', $query, $where);
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
        return $this->dateBasedWhere('%Y', $query, $where);
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

        return "strftime('{$type}', {$this->wrap($where['column'])}) {$where['operator']} cast($value) as text";
    }

    /**
     * Compile a "JSON length" statement into SQL.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * 
     * @return string
     */
    protected function compileJsonLength($column, $operator, $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return 'json_array_length('.$field.$path.') '.$operator.' '.$value;
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param  string  $column
     * @param  mixed  $value
     * 
     * @return string
     */
    protected function compileJsonContains($column, $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return 'exists (select 1 from json_each('.$field.$path.') where '.$this->wrap('json_each.value').' is '.$value.')';
    }

    /**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param  string  $binding
     * 
     * @return string
     */
    public function prepareBindingForJsonContains($binding): string
    {
        return $binding;
    }

    /**
     * Compile a "JSON contains key" statement into SQL.
     *
     * @param  string  $column
     * 
     * @return string
     */
    protected function compileJsonContainsKey($column): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return 'json_type('.$field.$path.') is not null';
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $values
     * 
     * @return string
     */
    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        return Str::replaceFirst('insert', 'insert or ignore', $this->compileInsert($query, $values));
    }

    /**
     * Compile an insert ignore statement using a subquery into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $columns
     * @param  string  $sql
     * 
     * @return string
     */
    public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql): string
    {
        return Str::replaceFirst('insert', 'insert or ignore', $this->compileInsertUsing($query, $columns, $sql));
    }

    /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * 
     * @return array
     */
    public function truncate(Builder $query): array
    {
        [$schema, $table] = $query->getConnection()->getSchemaBuilder()->parseSchemaAndTable($query->from);

        $schema = $schema ? $this->wrapValue($schema).'.' : '';

        return [
            'delete from '.$schema.'sqlite_sequence where name = ?' => [$query->getConnection()->getTablePrefix().$table],
            'delete from '.$this->wrapTable($query->from) => [],
        ];
    }

    /**
     * Wrap the given JSON selector.
     *
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapJsonSelector($value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'json_extract('.$field.$path.')';
    }
}