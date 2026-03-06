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

use Syscodes\Components\Database\Concerns\ParsesSearchPath;

/**
 * Allows you to manipulate of databases, tables and columns
 * for the PostgreSQl database.
 */
class PostgresBuilder extends Builder
{
    use ParsesSearchPath;

    /**
     * Drop all tables from the database.
     * 
     * @return void
     */
    public function dropAllTables()
    {
        $tables = [];
        
        $excludedTables = $this->grammar->escapeNames(
            $this->connection->getConfig('dont_drop') ?? ['spatial_ref_sys']
        );
        
        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;
            
            if (empty(array_intersect($this->grammar->escapeNames($row), $excludedTables))) {
                $tables[] = $row['qualifiedname'] ?? reset($row);
            }
        }
        
        if (empty($tables)) {
            return;
        }
        
        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );
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
     * Drop all types from the database.
     *
     * @return void
     */
    public function dropAllTypes()
    {
        $types = [];
        $domains = [];

        foreach ($this->getTypes($this->getCurrentSchemaListing()) as $type) {
            if ( ! $type['implicit']) {
                if ($type['type'] === 'domain') {
                    $domains[] = $type['schema_qualified_name'];
                } else {
                    $types[] = $type['schema_qualified_name'];
                }
            }
        }

        if ( ! empty($types)) {
            $this->connection->statement($this->grammar->compileDropAllTypes($types));
        }

        if ( ! empty($domains)) {
            $this->connection->statement($this->grammar->compileDropAllDomains($domains));
        }
    }

    /**
     * Get the current schemas for the connection.
     *
     * @return string[]
     */
    public function getCurrentSchemaListing(): array
    {
        return array_map(
            fn ($schema) => $schema === '$user' ? $this->connection->getConfig('username') : $schema,
            $this->parseSearchPath(
                $this->connection->getConfig('search_path')
                    ?: $this->connection->getConfig('schema')
                    ?: 'public'
            )
        );
    }
}