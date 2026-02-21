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
use Syscodes\Components\Database\Query\JoinLateralClause;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Str;

/**
 * Allows make the grammar's for get results of the database
 * using the Postgres database manager.
 */
class PostgresGrammar extends Grammar
{
    /**
     * Indicates if the cascade option should be used when truncating.
     * 
     * @var bool
     */
    protected static $cascadeTruncate = true;

    /**
     * Compile a basic where clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    protected function whereBasic(Builder $builder, $where): string
    {
        if (str_contains(strtolower($where['operator']), 'like')) {
            return sprintf(
                '%s::text %s %s',
                $this->wrap($where['column']),
                $where['operator'],
                $this->parameter($where['value'])
            );
        }

        return parent::whereBasic($builder, $where);
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

        $where['operator'] .= $where['caseSensitive'] ? 'like' : 'ilike';

        return $this->whereBasic($builder, $where);
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
        $column = $this->wrap($where['column']);
        $value = $this->parameter($where['value']);

        if ($this->isJsonSelector($where['column'])) {
            $column = '('.$column.')';
        }

        return $column.'::date '.$where['operator'].' '.$value;
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
        $column = $this->wrap($where['column']);
        $value = $this->parameter($where['value']);

        if ($this->isJsonSelector($where['column'])) {
            $column = '('.$column.')';
        }

        return $column.'::time '.$where['operator'].' '.$value;
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

        return 'extract('.$type.' from '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "where fulltext" clause.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $where
     * 
     * @return string
     */
    public function whereFullText(Builder $builder, $where): string
    {
        $language = $where['options']['language'] ?? 'english';

        if ( ! in_array($language, $this->validFullTextLanguages())) {
            $language = 'english';
        }

        $isVector = $where['options']['vector'] ?? false;

        $columns = (new Collection($where['columns']))
            ->map(fn ($column) => $isVector
                ? $this->wrap($column)
                : "to_tsvector('{$language}', {$this->wrap($column)})")
            ->implode(' || ');

        $mode = 'plainto_tsquery';

        if (($where['options']['mode'] ?? []) === 'phrase') {
            $mode = 'phraseto_tsquery';
        }

        if (($where['options']['mode'] ?? []) === 'websearch') {
            $mode = 'websearch_to_tsquery';
        }

        if (($where['options']['mode'] ?? []) === 'raw') {
            $mode = 'to_tsquery';
        }

        return "({$columns}) @@ {$mode}('{$language}', {$this->parameter($where['value'])})";
    }

    /**
     * Get an array of valid full text languages.
     *
     * @return array
     */
    protected function validFullTextLanguages(): array
    {
        return [
            'simple',
            'arabic',
            'danish',
            'dutch',
            'english',
            'finnish',
            'french',
            'german',
            'hungarian',
            'indonesian',
            'irish',
            'italian',
            'lithuanian',
            'nepali',
            'norwegian',
            'portuguese',
            'romanian',
            'russian',
            'spanish',
            'swedish',
            'tamil',
            'turkish',
        ];
    }

    /**
     * Compile the "select *" portion of the builder.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $columns
     * 
     * @return string|null
     */
    protected function compileColumns(Builder $builder, $columns)
    {
        // If the builder is actually performing an aggregating select, we will 
        // let that compiler handle the building of the select clauses.
        if (! is_null($builder->aggregate)) {
            return;
        }

        if (is_array($builder->distinct)) {
            $select = 'select distinct on ('.$this->columnize($builder->distinct).') ';
        } elseif ($builder->distinct) {
            $select = 'select distinct ';
        } else {
            $select = 'select ';
        }

        return $select.$this->columnize($columns);
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
        $column = str_replace('->>', '->', $this->wrap($column));

        return '('.$column.')::jsonb @> '.$value;
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

        if (filter_var($lastSegment, FILTER_VALIDATE_INT) !== false) {
            $i = $lastSegment;
        } elseif (preg_match('/\[(-?[0-9]+)\]$/', $lastSegment, $matches)) {
            $segments[] = Str::beforeLast($lastSegment, $matches[0]);

            $i = $matches[1];
        }

        $column = str_replace('->>', '->', $this->wrap(implode('->', $segments)));

        if (isset($i)) {
            return vsprintf('case when %s then %s else false end', [
                'jsonb_typeof(('.$column.")::jsonb) = 'array'",
                'jsonb_array_length(('.$column.')::jsonb) >= '.($i < 0 ? abs($i) : $i + 1),
            ]);
        }

        $key = "'".str_replace("'", "''", $lastSegment)."'";

        return 'coalesce(('.$column.')::jsonb ?? '.$key.', false)';
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
        $column = str_replace('->>', '->', $this->wrap($column));

        return 'jsonb_array_length(('.$column.')::jsonb) '.$operator.' '.$value;
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
            return $value ? 'for update' : 'for share';
        }

        return $value;
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
        return $this->compileInsert($builder, $values).' on conflict do nothing';
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
        return $this->compileInsertUsing($builder, $columns, $sql).' on conflict do nothing';
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
            $column = last(explode('.', $key));

            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($column, $value);
            }

            return $this->wrap($column).' = '.$this->parameter($value);
        })->implode(', ');
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
        $values = (new Collection($values))->map(function ($value, $column) {
            return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
                ? json_encode($value)
                : $value;
        })->all();

        $cleanBindings = Arr::except($bindings, 'select');

        $values = Arr::flatten(array_map(fn ($value) => value($value), $values));

        return array_values(
            array_merge($values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile a "lateral join" clause.
     *
     * @param  \Syscodes\Components\Database\Query\JoinLateralClause  $join
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileJoinLateral(JoinLateralClause $join, string $expression): string
    {
        return trim("{$join->type} join lateral {$expression} on true");
    }
    
    /**
     * Compile a delete statement into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    public function compileDelete(Builder $builder): string
    {
        if (isset($builder->joins) || isset($builder->limit)) {
            return $this->compileDeleteWithJoinsOrLimit($builder);
        }

        return parent::compileDelete($builder);
    }
    
    /**
     * Compile a delete statement with joins or limit into SQL.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return string
     */
    protected function compileDeleteWithJoinsOrLimit(Builder $builder): string
    {
        $table = $this->wrapTable($builder->from);

        $alias = last(preg_split('/\s+as\s+/i', $builder->from));

        $selectSql = $this->compileSelect($builder->select($alias.'.ctid'));

        return "delete from {$table} where {$this->wrap('ctid')} in ({$selectSql})";
    }

    /**
     * Compile an insert and get ID statement into SQL.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $values
     * @param  string  $sequence
     * 
     * @return string
     */
    public function compileInsertGetId(Builder $builder, $values, $sequence): string
    {
        return $this->compileInsert($builder, $values).' returning '.$this->wrap($sequence ?: 'id');
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
        return ['truncate '.$this->wrapTable($builder->from).' restart identity'.(static::$cascadeTruncate ? ' cascade' : '') => []];
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
        $path = explode('->', $value);

        $field = $this->wrapSegments(explode('.', array_shift($path)));

        $wrappedPath = $this->wrapJsonPathAttributes($path);

        $attribute = array_pop($wrappedPath);

        if ( ! empty($wrappedPath)) {
            return $field.'->'.implode('->', $wrappedPath).'->>'.$attribute;
        }

        return $field.'->>'.$attribute;
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
        $selector = str_replace(
            '->>', '->',
            $this->wrapJsonSelector($value)
        );

        return '('.$selector.')::jsonb';
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
        return "'".$value."'::jsonb";
    }

    /**
     * Enable or disable the "cascade" option when compiling the truncate statement.
     *
     * @param  bool  $value
     * 
     * @return void
     */
    public static function cascadeOnTruncate(bool $value = true): void
    {
        static::$cascadeTruncate = $value;
    }

    /**
     * @deprecated use cascadeOnTruncate
     */
    public static function cascadeOnTrucate(bool $value = true): void
    {
        self::cascadeOnTruncate($value);
    }
}