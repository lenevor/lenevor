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

namespace Syscodes\Components\Database\Schema\Builders;

/**
 * Allows you to manipulate of databases, tables and columns
 * for the Mysql database.
 */
class MySqlBuilder extends Builder
{
    /**
     * Create a database in the schema.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function createDatabase($name): bool
    {
        return $this->connection->statement(
           $this->grammar->compileCreateDatabase($name, $this->connection)
        );
    }
    
    /**
     * Drop a database from the schema if the database exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function dropDatabaseIfExists($name): bool
    {
        return $this->connection->statement(
            $this->grammar->compileDropDatabaseIfExists($name)
        );
    }
    
    /**
     * Determine if the given table exists.
     * 
     * @param  string  $table
     * 
     * @return bool
     */
    public function hasTable($table): bool
    {
        $table = $this->connection->getTablePrefix().$table;
        
        return count($this->connection->select(
            $this->grammar->compileTableListing(), [$this->connection->getDatabase(), $table]
        )) > 0;
    }
    
    /**
     * Get the column listing for a given table.
     * 
     * @param  string  $table
     * 
     * @return array
     */
    public function getColumnListing($table): array
    {
        $table = $this->connection->getTablePrefix().$table;
        
        $results = $this->connection->select(
            $this->grammar->compileColumnListing(), [$this->connection->getDatabase(), $table] 
        );
        
        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Drop all tables from the database.
     * 
     * @return void
     */
    public function dropAllTables()
    {
        $tables = [];
        
        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;
            
            $tables[] = head($row);
        }
        
        if (empty($tables)) {
            return;
        }
        
        $this->disableForeignKeyConstraints();
        
        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );
        
        $this->enableForeignKeyConstraints();
    }
    
    /**
     * Drop all views from the database.
     * 
     * @return void
     */
    public function dropAllViews()
    {
        $views = [];
        
        foreach ($this->getAllViews() as $row) {
            $row = (array) $row;
            
            $views[] = reset($row);
        }
        
        if (empty($views)) {
            return;
        }
        
        $this->connection->statement(
            $this->grammar->compileDropAllViews($views)
        );
    }
    
    /**
     * Get all of the table names for the database.
     * 
     * @return array
     */
    public function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }
    
    /**
     * Get all of the view names for the database.
     * 
     * @return array
     */
    public function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }
}