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
 
namespace Syscode\Database;

use Syscode\Database\Query\Expression;

/**
 * Allows make the grammar's for get results of the database.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
abstract class Grammar
{
    /**
     * The grammar table prefix.
     * 
     * @var string $tablePrefix
     */
    protected $tablePrefix = '';

    /**
     * Wrap a table in keyword identifiers.
     * 
     * @param  string  $table
     * 
     * @return string
     */
    public function wrapTable($table)
    {
        if ( ! $this->isExpression($table))
        {
            return $this->wrap($this->tablePrefix.$table, true);
        }

        return $this->getValue($table);
    }

    /**
     * Wrap a value in keyword identifiers.
     * 
     * @param  \Syscode\Database\Query\Expression|string  $value
     * @param  bool  $prefix
     * 
     * @return string
     */
    public function wrap($value, $prefix = false)
    {
        if ($this->isExpression($value))
        {
            return $this->getValue($value);
        }

        if (strpos($value, ' as ') !== false)
        {
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
    protected function wrapAliasedValues($value, $prefix = false)
    {
        $segments = explode(' ', $value);

        if ($prefix)
        {
            $segments[2] = $this->tablePrefix.$segments[2];
        }

        return $this->wrap($segments[0].' AS '.$this->wrapValue($segments[2]));
    }

    /**
     * Wrap the given value segments.
     * 
     * @param  array  $segments
     * 
     * @return string
     */
    protected function wrapSegments($segments)
    {
        $wrapped = [];

        foreach ($segments as $key => $segment)
        {
            $wrapped[] = ($key == 0 && count($segment) > 1)
                        ? $this->wrapTable($segment)
                        : $this->wrapValue($segment);
        }

        return implode('.', $wrapped);
    }

    /**
     * Wrap a single string in keyword identifiers.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*')
        {
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
    public function columnize(array $columns)
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
    public function parameterize(array $values)
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
    public function parameter($value)
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }

    /**
     * Get the value of a raw expression.
     * 
     * @param  \Syscode\Database\Query\Expression  $expression
     * 
     * @return string
     */
    public function getValue(Expression $expression)
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
    public function isExpression($value)
    {
        return $value instanceof Expression;
    }

    /**
     * Get the format for database stored dates.
     * 
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the grammar's table prefix.
     * 
     * @return void
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     * 
     * @param  string  $tablePrefix
     * 
     * @return $this
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;

        return $this;
    }
}