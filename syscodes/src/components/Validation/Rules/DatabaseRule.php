<?php

/**
 * Lenevor Framework
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

namespace Syscodes\Components\Validation\Rules;

use Closure;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Support\Str;

/**
 * Get the database rule.
 */
trait DatabaseRule
{
    /**
     * The column to check on.
     *
     * @var string
     */
    protected $column;

    /**
     * The table to run the query against.
     *
     * @var string
     */
    protected $table;

    /**
     * The array of custom query callbacks.
     *
     * @var array
     */
    protected $using = [];

    /**
     * The extra where clauses for the query.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * Constructor. Create a new rule instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return void
     */
    public function __construct($table, $column = 'NULL')
    {
        $this->column = $column;

        $this->table = $this->resolveTableName($table);
    }

    /**
     * Resolves the name of the table from the given string.
     *
     * @param  string  $table
     * @return string
     */
    public function resolveTableName($table): string
    {
        if ( ! Str::contains($table, '\\') || ! class_exists($table)) {
            return $table;
        }

        if (is_subclass_of($table, Model::class)) {
            $model = new $table;

            if (Str::contains($model->getTable(), '.')) {
                return $table;
            }

            return implode('.', array_map(function (string $part) {
                return trim($part, '.');
            }, array_filter([$model->getConnectionName(), $model->getTable()])));
        }

        return $table;
    }

    /**
     * Set a "where" constraint on the query.
     *
     * @param  \Closure|string  $column
     * @param  array|string|int|null  $value
     * @return static
     */
    public function where($column, $value = null): static
    {
        if (is_array($value)) {
            return $this->whereIn($column, $value);
        }

        if ($column instanceof Closure) {
            return $this->using($column);
        }

        if (is_null($value)) {
            return $this->whereNull($column);
        }

        $this->wheres[] = compact('column', 'value');

        return $this;
    }

    /**
     * Set a "where not" constraint on the query.
     *
     * @param  string  $column
     * @param  array|string  $value
     * @return static
     */
    public function whereNot($column, $value): static
    {
        if (is_array($value)) {
            return $this->whereNotIn($column, $value);
        }

        return $this->where($column, '!'.$value);
    }

    /**
     * Set a "where null" constraint on the query.
     *
     * @param  string  $column
     * @return static
     */
    public function whereNull($column): static
    {
        return $this->where($column, 'NULL');
    }

    /**
     * Set a "where not null" constraint on the query.
     *
     * @param  string  $column
     * @return static
     */
    public function whereNotNull($column): static
    {
        return $this->where($column, 'NOT_NULL');
    }

    /**
     * Set a "where in" constraint on the query.
     *
     * @param  string  $column
     * @param  array  $values
     * @return static
     */
    public function whereIn($column, array $values): static
    {
        return $this->where(function ($query) use ($column, $values) {
            $query->whereIn($column, $values);
        });
    }

    /**
     * Set a "where not in" constraint on the query.
     *
     * @param  string  $column
     * @param  array  $values
     * @return static
     */
    public function whereNotIn($column, array $values): static
    {
        return $this->where(function ($query) use ($column, $values) {
            $query->whereNotIn($column, $values);
        });
    }

    /**
     * Register a custom query callback.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public function using(Closure $callback): static
    {
        $this->using[] = $callback;

        return $this;
    }

    /**
     * Get the custom query callbacks for the rule.
     *
     * @return array
     */
    public function queryCallbacks(): array
    {
        return $this->using;
    }

    /**
     * Format the where clauses.
     *
     * @return string
     */
    protected function formatWheres(): string
    {
        return collect($this->wheres)->map(function ($where) {
            return $where['column'].','.'"'.str_replace('"', '""', $where['value']).'"';
        })->implode(',');
    }
}