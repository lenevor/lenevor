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

namespace Syscodes\Components\Database\Schema\Builders;

/**
 * Allows you to manipulate of databases, tables and columns
 * for the SqlServer database.
 */
class SqlServerBuilder extends Builder
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
     * Drop all tables from the database.
     * 
     * @return void
     */
    public function dropAllTables()
    {
        $this->connection->statement($this->grammar->compileDropAllForeignKeys());
        
        $this->connection->statement($this->grammar->compileDropAllTables());
    }
    
    /**
     * Drop all views from the database.
     * 
     * @return void
     */
    public function dropAllViews()
    {
        $this->connection->statement($this->grammar->compileDropAllViews());
    }
    
    /**
     * Drop all tables from the database.
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