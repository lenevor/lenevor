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
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;

/**
 * Allows make the grammar's for get results of the database
 * using the SqlServer database manager.
 */
class SqlServerGrammar extends Grammar
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
        $components = $this->compileComponents($builder);

        if ($builder->offset > 0) {
            return $this->compileAnsiOffset($builder, $components);
        }

        return $this->concatenate($components);
    }

    /**
     * Create a full ANSI offset clause for the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $components
     * 
     * @return string
     */
    protected function compileAnsiOffset(Builder $builder, $components): string
    {
        if ( ! isset($components['orders'])) {
            $components['orders'] = 'order by (select 0)';
        }

        $components['orders'] .= $this->compileOver($components['orders']);

        unset($components['orders']);

        $sql = $this->concatenate($components);
        
        return $this->compileTableExpression($sql, $builder);
    }

    /**
     * Compile the over statement for a table expression.
     * 
     * @param  array  $orderings
     * 
     * @return string
     */
    protected function compileOver($orderings): string
    {
        return ", row_number() over ({$orderings}) as row_num";
    }

    /**
     * Compile a common table expression for a query.
     * 
     * @param  string  $sql
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileTableExpression($sql, $builder): string
    {
        $constraint = $this->compileRowConstraint($builder);

        return "select * from users as temp_table where row_num {$constraint} order by row_num";
    }

    /**
     * Compile the limit / offset row constraint for a query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileRowConstraint(Builder $builder): string
    {
        $start = $builder->offset + 1;

        if ($builder->limit > 0) {
            $finish = $builder->offset + $builder->limit;

            return "between {$start} and {$finish}";
        }

        return "> {$start}";
    }

    /**
     * Compile a delete statement without joins into SQL.
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

        return ! is_null($builder->limit) && $builder->limit > 0 && $builder->offset <= 0
            ? Str::replaceFirst('delete', 'delete top ('.$builder->limit.')', $sql)
            : $sql;
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
        return 'NEWID()';
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
        $limit = (int) $limit;

        if ($limit && $builder->offset > 0) {
            return "fetch next {$limit} rows only";
        }

        return '';
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
        $offset = (int) $offset;

        if ($offset) {
            return "offset {$offset} rows";
        }

        return '';
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
        return 'select * from ('.$sql.') as '.$this->wrapTable('temp_table');
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
        $existsQuery = clone $builder;

        $existsQuery->columns = [];

        return $this->compileSelect($existsQuery->selectRaw('1 [exists]')->limit(1));
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
        return ['truncate table '.$this->wrapTable($builder->from) => []];
    }

    /**
     * Compile an update statement with joins into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * @return string
     */
    protected function compileUpdateWithJoins(Builder $builder, $table, $columns, $where): string
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($builder, $builder->joins);

        return "update {$alias} set {$columns} from {$table} {$joins} {$where}";
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
        $cleanBindings = Arr::except($bindings, 'select');

        $values = Arr::flatten(array_map(fn ($value) => value($value), $values));

        return array_values(
            array_merge($values, Arr::flatten($cleanBindings))
        );
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
        return 'SAVE TRANSACTION '.$name;
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
        return 'ROLLBACK TRANSACTION '.$name;
    }

    /**
     * Get the format for database stored dates.
     * 
     * @return string
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.v';
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
        return ($value === '*') ? $value : '['.str_replace(']', ']]', $value).']';
    }

    /**
     * Compile the "select *" portion of the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $columns
     * 
     * @return string
     */
    protected function compileColumns(Builder $builder, $columns)
    {
        if ( ! is_null($builder->aggregate)) {
            return;
        }

        $select = $builder->distinct ? 'select distinct ' : 'select ';

        // If there is a limit on the query, but not an offset, we will add the top
        // clause to the query, which serves as a "limit" type clause.
        if ($builder->limit > 0 && $builder->offset <= 0) {
            $select .= 'top '.$builder->limit.' ';
        }

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
        $from = parent::compileFrom($builder, $table);

        if (is_string($builder->lock)) {
            return $from.' '.$builder;
        }

        if ( ! is_null($builder->lock)) {
            return $from.' with(rowlock,'.($builder->lock ? 'uplock,' : '').'holdlock)';
        }
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
        $value = $this->parameter($where['value']);

        return 'cast('.$this->wrap($where['column']).' as date) '.$where['operator'].' '.$value;
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
        $value = $this->parameter($where['value']);

        return 'cast('.$this->wrap($where['column']).' as time) '.$where['operator'].' '.$value;
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

        return $value.' in (select [value] from openjson('.$field.$path.'))';
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
        return is_bool($binding) ? json_encode($binding) : $binding;
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
        $segments = explode('->', $column);

        $lastSegment = array_pop($segments);

        if (preg_match('/\[([0-9]+)\]$/', $lastSegment, $matches)) {
            $segments[] = Str::beforeLast($lastSegment, $matches[0]);

            $key = $matches[1];
        } else {
            $key = "'".str_replace("'", "''", $lastSegment)."'";
        }

        [$field, $path] = $this->wrapJsonFieldAndPath(implode('->', $segments));

        return $key.' in (select [key] from openjson('.$field.$path.'))';
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

        return '(select count(*) from openjson('.$field.$path.')) '.$operator.' '.$value;
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
        return 'json_query('.$value.')';
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

        return 'json_value('.$field.$path.')';
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
        return "'".$value."'";
    }
}