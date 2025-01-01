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

use Syscodes\Components\Support\Facades\File;

/**
 * Allows you to manipulate of databases, tables and columns
 * for the SQLite database.
 */
class SQLiteBuilder extends Builder
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
        return File::put($name, '') !== false;
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
        return File::exists($name)
                   ? File::delete($name)
                   : true;
    }
    
    /**
     * Drop all tables from the database.
     * 
     * @return void
     */
    public function dropAllTables()
    {
        if ($this->connection->getDatabase() !== ':memory:') {
            return $this->refreshDatabaseFile();
        }
        
        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllTables());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }
    
    /**
     * Drop all views from the database.
     * 
     * @return void
     */
    public function dropAllViews()
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());
        
        $this->connection->select($this->grammar->compileDropAllViews());
        
        $this->connection->select($this->grammar->compileDisableWriteableSchema());
        
        $this->connection->select($this->grammar->compileRebuild());
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

    /**
     * Empty the database file.
     *
     * @return void
     */
    public function refreshDatabaseFile(): void
    {
        file_put_contents($this->connection->getDatabase(), '');
    }
}