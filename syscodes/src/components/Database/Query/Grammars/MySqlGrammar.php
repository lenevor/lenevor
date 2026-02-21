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

use InvalidArgumentException;
use Syscodes\Components\Database\Query\Builder;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Str;

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
     * Compile a "where like" clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereLike(Builder $builder, $where): string
    {
        $where['operator'] = $where['not'] ? 'not ' : '';

        $where['operator'] .= $where['caseSensitive'] ? 'like binary' : 'like';

        return $this->whereBasic($builder, $where);
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNull(Builder $builder, $where): string
    {
        $columnValue = (string) $this->getValue($where['column']);

        if ($this->isJsonSelector($columnValue)) {
            [$field, $path] = $this->wrapJsonFieldAndPath($columnValue);

            return '(json_extract('.$field.$path.') is null OR json_type(json_extract('.$field.$path.')) = \'NULL\')';
        }

        return parent::whereNull($builder, $where);
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereNotNull(Builder $builder, $where): string
    {
        $columnValue = (string) $this->getValue($where['column']);

        if ($this->isJsonSelector($columnValue)) {
            [$field, $path] = $this->wrapJsonFieldAndPath($columnValue);

            return '(json_extract('.$field.$path.') is not null AND json_type(json_extract('.$field.$path.')) != \'NULL\')';
        }

        return parent::whereNotNull($builder, $where);
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
        $columns = $this->columnize($where['columns']);

        $value = $this->parameter($where['value']);

        $mode = ($where['options']['mode'] ?? []) === 'boolean'
            ? ' in boolean mode'
            : ' in natural language mode';

        $expanded = ($where['options']['expanded'] ?? []) && ($where['options']['mode'] ?? []) !== 'boolean'
            ? ' with query expansion'
            : '';

        return "match ({$columns}) against (".$value."{$mode}{$expanded})";
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $values
     * 
     * @return string
     */
    public function compileInsertOrIgnore(Builder $builder, array $values): string
    {
        return Str::replaceFirst('insert', 'insert ignore', $this->compileInsert($builder, $values));
    }

    /**
     * Compile an insert ignore statement using a subquery into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $columns
     * @param  string  $sql
     * 
     * @return string
     */
    public function compileInsertOrIgnoreUsing(Builder $builder, array $columns, string $sql): string
    {
        return Str::replaceFirst('insert', 'insert ignore', $this->compileInsertUsing($builder, $columns, $sql));
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param  string  $column
     * @param  string  $value
     * 
     * @return string
     */
    protected function compileJsonContains($column, $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return 'json_contains('.$field.', '.$value.$path.')';
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

        return 'ifnull(json_contains_path('.$field.', \'one\''.$path.'), 0)';
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

        return 'json_length('.$field.$path.') '.$operator.' '.$value;
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
        return 'cast('.$value.' as json)';
    }


    /**
     * Compile the random statement into SQL.
     * 
     * @param  string|int  $seed
     * 
     * @return string
     * 
     * @throws \InvalidArgumentException
     */
    public function compileRandom($seed): string
    {
        if ($seed === '' || $seed === null) {
            return 'RAND()';
        }
        
        if ( ! is_numeric($seed)) {
            throw new InvalidArgumentException('The seed value must be numeric.');
        }
        
        return 'RAND('.(int) $seed.')';
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
     * Compile the columns for an update statement.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $values
     * 
     * @return string
     */
    protected function compileUpdateColumns(Builder $builder, array $values): string
    {
        return (new Collection($values))->map(function ($value, $key) {
            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($key, $value);
            }

            return $this->wrap($key).' = '.$this->parameter($value);
        })->implode(', ');
    }

    /**
     * Prepare a JSON column being updated using the JSON_SET function.
     *
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return string
     */
    protected function compileJsonUpdateColumn($key, $value): string
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            $value = 'cast(? as json)';
        } else {
            $value = $this->parameter($value);
        }

        [$field, $path] = $this->wrapJsonFieldAndPath($key);

        return "{$field} = json_set({$field}{$path}, {$value})";
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
        
        return $sql;
    }

    /**
     * Prepare the bindings for an update statement.
     *
     * @param  array  $bindings
     * @param  array  $values
     * 
     * @return array
     */
    #[\Override]
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        $values = (new Collection($values))
            ->reject(fn ($value, $column) => $this->isJsonSelector($column) && is_bool($value))
            ->map(fn ($value) => is_array($value) ? json_encode($value) : $value)
            ->all();

        return parent::prepareBindingsForUpdate($bindings, $values);
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
    protected function compileDeleteWithoutJoins(Builder $builder, $table, $where): string
    {
        $sql = parent::compileDeleteWithoutJoins($builder, $table, $where);
        
        // When using MySQL, delete statements may contain order by statements and limits
        // so we will compile both of those here.
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
     * Wrap the given JSON selector.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapJsonSelector($value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);
        
        return 'json_unquote(json_extract('.$field.$path.'))';
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
        [$field, $path] = $this->wrapJsonFieldAndPath($value);
        
        return 'json_extract('.$field.$path.')';
    }
}