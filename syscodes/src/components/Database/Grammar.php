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
 
namespace Syscodes\Components\Database;

use RuntimeException;
use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Allows make the grammar's for get results of the database.
 */
abstract class Grammar
{
    use Macroable;

    /**
     * The connection used for escaping values.
     *
     * @var \Syscodes\Components\Database\Connections\Connection
     */
    protected $connection;

    /**
     * Constructor. Create a new grammar instance.
     *
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Wrap a table in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression|string  $table
     * @param  string|null  $prefix
     * 
     * @return string
     */
    public function wrapTable($table, $prefix = null): string
    {
        if ($this->isExpression($table)) {
            return $this->getValue($table);
        }

        $prefix ??= $this->connection->getTablePrefix();
        
        // If the table being wrapped has an alias we'll need to separate the pieces
        // so we can prefix the table and then wrap each of the segments on their own.
        if (stripos($table, ' as ') !== false) {
            return $this->wrapAliasedTable($table, $prefix);
        }
        
        // If the table being wrapped has a custom schema name specified, we need to
        // prefix the last segment as the table name then wrap each segment alone.
        if (str_contains($table, '.')) {
            $table = substr_replace($table, '.'.$prefix, strrpos($table, '.'), 1);
            
            return (new Collection(explode('.', $table)))
                ->map($this->wrapValue(...))
                ->implode('.');
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
        return array_map($this->wrap(...), $values);
    }

    /**
     * Wrap a value in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression|string  $value
     * 
     * @return string
     */
    public function wrap($value): string
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }
        
        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on its own.
        if (strpos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value);
        }
        
        // If the given value is a JSON selector we will wrap it differently than a
        // traditional value. We will need to split this path and wrap each part
        // wrapped, etc.
        
        if ($this->isJsonSelector($value)) {
            return $this->wrapJsonSelector($value);
        }

        return $this->wrapSegments(explode('.', $value));
    }

    /**
     * Wrap a value that has an alias.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapAliasedValue($value): string
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        return $this->wrap($segments[0]).' as '.$this->wrapValue($segments[1]);
    }
    
    /**
     * Wrap a table that has an alias.
     * 
     * @param  string  $value
     * @param  string|null  $prefix
     * 
     * @return string
     */
    protected function wrapAliasedTable($value, $prefix = null): string
    {
        $segments = preg_split('/\s+as\s+/i', $value);
        
        $prefix ??= $this->connection->getTablePrefix();
        
        return $this->wrapTable($segments[0], $prefix).' as '.$this->wrapValue($prefix.$segments[1]);
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
        return (new Collection($segments))->map(function ($segment, $key) use ($segments) {
            return $key == 0 && count($segments) > 1
                ? $this->wrapTable($segment)
                : $this->wrapValue($segment);
        })->implode('.');
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
     * Wrap the given JSON selector.
     * 
     * @param  string  $value
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected function wrapJsonSelector($value): string
    {
        throw new RuntimeException('This database engine does not support JSON operations.');
    }
    
    /**
     * Determine if the given string is a JSON selector.
     * 
     * @param  string  $value
     * 
     * @return bool
     */
    protected function isJsonSelector($value): bool
    {
        return str_contains($value, '->');
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
        return implode(', ', array_map($this->wrap(...), $values));
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
     * @param  \Syscodes\Components\Database\Query\Expression|string|int|float  $expression
     * 
     * @return string|int|float
     */
    public function getValue($expression): string|int|float
    {
        if ($this->isExpression($expression)) {
            return $this->getValue($expression->getValue($this));
        }

        return $expression;
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
     * Escapes a value for safe SQL embedding.
     *
     * @param  string|float|int|bool|null  $value
     * @param  bool  $binary
     * 
     * @return string
     */
    public function escape($value, $binary = false): string
    {
        return $this->connection->escape($value, $binary);
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
        return $this->connection->getTablePrefix();
    }

    /**
     * Set the grammar's table prefix.
     * 
     * @param  string  $prefix
     * 
     * @return static
     */
    public function setTablePrefix($prefix): static
    {
        $this->connection->setTablePrefix($prefix);

        return $this;
    }
}