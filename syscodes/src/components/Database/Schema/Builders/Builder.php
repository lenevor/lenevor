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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Schema\Builders;

use LogicException;
use Syscodes\Components\Database\Connections\Connection;

/**
 * Creates a Erostrine schema builder.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Builder
{
    /**
     * The database connection instance.
     * 
     * @var \Syscodes\Components\Database\Connections\Connection $connections
     */
    protected $connection;
    
    /**
     * The schema grammar instance.
     * 
     * @var \Syscodes\Components\Database\Schema\Grammars\Grammar $grammar
     */
    protected $grammar;
    
    /**
     * The Dataprint resolver callback.
     * 
     * @var \Closure $resolver
     */
    protected $resolver;

    /**
     * The default string length for migrations.
     * 
     * @var int|null $defaultStringLength
     */
    public static $defaultStringLength = 255;

    /**
     * Constructor. Create a new database schema manager.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar    = $connection->getSchemaGrammar();
    }
    
    /**
     * Create a database in the schema.
     * 
     * @param  string  $name
     * 
     * @return bool
     * 
     * @throws \LogicException
     */
    public function createDatabase($name): bool
    {
        throw new LogicException('This database driver does not support creating databases');
    }
    
    /**
     * Drop a database from the schema if the database exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     * 
     * @throws \LogicException
     */
    public function dropDatabaseIfExists($name): bool
    {
        throw new LogicException('This database driver does not support dropping databases');
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
        
        return count($this->connection->selectFromConnection(
            $this->grammar->compileTableExists(), [$table])
        ) > 0;
    }

}