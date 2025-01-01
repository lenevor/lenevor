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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation;

use Closure;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Database\ConnectionResolverInterface;

/**
 * Allows the connection with the database for validated to data.
 */
class DatabasePresence implements DatabasePresenceInterface
{
    /**
     * The database connection instance.
     * 
     * @var \Syscodes\Components\Database\ConnectionResolverInterface $db
     */
    protected $db;
    
    /**
     * The database connection to use.
     * 
     * @var string $connection
     */
    protected $connection;
    
    /**
     * Constructor. Create a new database presence class instance.
     * 
     * @param  \Syscodes\Components\Database\ConnectionResolverInterface  $db
     * 
     * @return void
     */
    public function __construct(ConnectionResolverInterface $db)
    {
        $this->db = $db;
    }
    
    /**
     * Count the number of objects in a collection having the given value.
     * 
     * @param  string  $collection
     * @param  string  $column
     * @param  string  $value
     * @param  int|null  $excludeId
     * @param  string|null  $idColumn
     * @param  array  $extra
     * 
     * @return int
     */
    public function getCount(
        $collection, 
        $column, 
        $value, 
        $excludeId = null, 
        $idColumn = null, 
        array $extra = []
    ): int {
        $query = $this->table($collection)->where($column, '=', $value);
        
        if ( ! is_null($excludeId) && $excludeId !== 'NULL') {
            $query->where($idColumn ?: 'id', '<>', $excludeId);
        }
        
        return $this->addConditions($query, $extra)->count();
    }
    
    /**
     * Count the number of objects in a collection with the given values.
     * 
     * @param  string  $collection
     * @param  string  $column
     * @param  array  $values
     * @param  array  $extra
     * 
     * @return int
     */
    public function getMultiCount(
        $collection, 
        $column, 
        array $values, 
        array $extra = []
    ): int {
        $query = $this->table($collection)->whereIn($column, $values);
        
        return $this->addConditions($query, $extra)->distinct()->count($column);
    }
    
    /**
     * Add the given conditions to the query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  array  $conditions
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function addConditions($query, $conditions)
    {
        foreach ($conditions as $key => $value) {
            if ($value instanceof Closure) {
                $query->where(function ($query) use ($value) {
                    $value($query);
                });
            } else {
                $this->addWhere($query, $key, $value);
            }
        }
        
        return $query;
    }
    
    /**
     * Add a "where" clause to the given query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $query
     * @param  string  $key
     * @param  string  $extraValue
     * 
     * @return void
     */
    protected function addWhere($query, $key, $extraValue): void
    {
        if ($extraValue === 'NULL') {
            $query->whereNull($key);
        } elseif ($extraValue === 'NOT_NULL') {
            $query->whereNotNull($key);
        } elseif (Str::startsWith($extraValue, '!')) {
            $query->where($key, '!=', Str::substr($extraValue, 1));
        } else {
            $query->where($key, $extraValue);
        }
    }
    
    /**
     * Get a query builder for the given table.
     * 
     * @param  string  $table
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function table($table)
    {
        return $this->db->connection($this->connection)->table($table)->useWritePdo();
    }
    
    /**
     * Set the connection to be used.
     * 
     * @param  string  $connection
     * 
     * @return void
     */
    public function setConnection($connection): void
    {
        $this->connection = $connection;
    }
}