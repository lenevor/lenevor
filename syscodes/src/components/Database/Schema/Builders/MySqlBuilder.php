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
 * for the Mysql database.
 */
class MySqlBuilder extends Builder
{
    /**
     * Drop all tables from the database.
     * 
     * @return void
     */
    public function dropAllTables()
    {
        $tables = $this->getTableListing($this->getCurrentSchemaListing());
        
        if (empty($tables)) {
            return;
        }
        
        $this->disableForeignKeyConstraints();
        
        try {
            $this->connection->statement(
                $this->grammar->compileDropAllTables($tables)
            );
        } finally {
            $this->enableForeignKeyConstraints();
        }        
    }
    
    /**
     * Drop all views from the database.
     * 
     * @return void
     */
    public function dropAllViews()
    {
        $views = array_column($this->getViews($this->getCurrentSchemaListing()), 'schema_qualified_name');
        
        if (empty($views)) {
            return;
        }
        
        $this->connection->statement(
            $this->grammar->compileDropAllViews($views)
        );
    }
    
    /**
     * Get the names of current schemas for the connection.
     *
     * @return string[]|null
     */
    public function getCurrentSchemaListing(): array
    {
        return [$this->connection->getDatabaseName()];
    }
}