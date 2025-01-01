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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Components\Database;

use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Database\Query\Expression;

/**
 * Allows make the grammar's for get results of the database.
 */
abstract class Grammar
{
    use Macroable;

    /**
     * The grammar table prefix.
     * 
     * @var string $tablePrefix
     */
    protected $tablePrefix = '';

    /**
     * Wrap a table in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression|string  $table
     * 
     * @return string
     */
    public function wrapTable($table): string
    {
        if ( ! $this->isExpression($table)) {
            return $this->wrap($this->tablePrefix.$table, true);
        }

        return $this->getValue($table);
    }
    
    /**
     * Wrap an array of values.
     * 
     * @param  array  $values
     * 
     * @return array
     */
    public function wrapArray(array $values): array
    {
        return array_map([$this, 'wrap'], $values);
    }

    /**
     * Wrap a value in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression|string  $value
     * @param  bool  $prefix
     * 
     * @return string
     */
    public function wrap($value, $prefix = false): string
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        if (strpos($value, ' as ') !== false) {
            return $this->wrapAliasedValues($value, $prefix);
        }

        return $this->wrapSegments(explode('.', $value));
    }

    /**
     * Wrap a value that has an alias.
     * 
     * @param  string  $value
     * @param  bool  $prefix
     * 
     * @return string
     */
    protected function wrapAliasedValues($value, $prefix = false): string
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        if ($prefix) {
            $segments[1] = $this->tablePrefix.$segments[1];
        }

        return $this->wrap($segments[0].' as '.$this->wrapValue($segments[1]));
    }

    /**
     * Wrap the given value segments.
     * 
     * @param  array  $segments
     * 
     * @return string
     */
    protected function wrapSegments($segments): string
    {
        return collect($segments)->map(function ($segment, $key) use ($segments) {
                    return $key == 0 && count($segments) > 1
                        ? $this->wrapTable($segment)
                        : $this->wrapValue($segment);
                    }
               )->implode('.');
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
        if ($value !== '*') {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    /**
     * An array of column names.
     * 
     * @param  array  $columns
     * 
     * @return string
     */
    public function columnize(array $columns): string
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * Create query parameter place-holders for an array.
     * 
     * @param  mixed  $values
     * 
     * @return string
     */
    public function parameterize(array $values): string
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     * 
     * @param  mixed  $value
     * 
     * @return string
     */
    public function parameter($value): string
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }

    /**
     * Get the value of a raw expression.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression  $expression
     * 
     * @return string
     */
    public function getValue(Expression $expression): string
    {
        return $expression->getValue();
    }

    /**
     * Determine if the given value is a raw expression.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function isExpression($value): bool
    {
        return $value instanceof Expression;
    }
    
    /**
     * Quote the given string literal.
     * 
     * @param  string|array  $value
     * 
     * @return string
     */
    public function quoteString($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, __FUNCTION__], $value));
        }
        
        return "'$value'";
    }

    /**
     * Get the format for database stored dates.
     * 
     * @return string
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the grammar's table prefix.
     * 
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     * 
     * @param  string  $tablePrefix
     * 
     * @return static
     */
    public function setTablePrefix($tablePrefix): static
    {
        $this->tablePrefix = $tablePrefix;

        return $this;
    }
}