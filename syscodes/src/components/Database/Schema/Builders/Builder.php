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

use Closure;
use LogicException;
use Syscodes\Components\Container\Container;
use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Creates a Erostrine schema builder.
 */
class Builder
{
    use Macroable;
    
    /**
     * The database connection instance.
     * 
     * @var \Syscodes\Components\Database\Connections\Connection
     */
    protected $connection;
    
    /**
     * The schema grammar instance.
     * 
     * @var \Syscodes\Components\Database\Schema\Grammars\Grammar
     */
    protected $grammar;
    
    /**
     * The Dataprint resolver callback.
     * 
     * @var \Closure
     */
    protected $resolver;

    /**
     * The default string length for migrations.
     * 
     * @var int|null
     */
    public static $defaultStringLength = 255;
    
    /**
     * The default time precision for migrations.
     * 
     * @var int|null
     */
    public static ?int $defaultTimePrecision = 0;

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
     * Set the default string length for migrations.
     * 
     * @param  int  $length
     * 
     * @return void
     */
    public static function defaultStringLength($length): void
    {
        static::$defaultStringLength = $length;
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
            $this->grammar->compileTableListing(), [$table])
        ) > 0;
    }
    
    /**
     * Determine if the given table has a given column.
     * 
     * @param  string  $table
     * @param  string  $column
     * 
     * @return bool
     */
    public function hasColumn($table, $column): bool
    {
        return in_array(
            strtolower($column), array_map('strtolower', $this->getColumnListing($table))
        );
    }
    
    /**
     * Determine if the given table has given columns.
     * 
     * @param  string  $table
     * @param  array  $columns
     * 
     * @return bool
     */
    public function hasColumns($table, array $columns): bool
    {
        $tableColumns = array_map('strtolower', $this->getColumnListing($table));
        
        foreach ($columns as $column) {
            if ( ! in_array(strtolower($column), $tableColumns)) {
                return false;
            }
        }
        
        return true;
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
        
        $results = $this->connection->selectFromConnection($this->grammar->compileColumnListing($table));
        
        return $this->connection->getPostProcessor()->processColumnListing($results);
    }
    
    /**
     * Modify a table on the schema.
     * 
     * @param  string  $table
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function table($table, Closure $callback): void
    {
        $this->build($this->createDataprint($table, $callback));
    }
    
    /**
     * Create a new table on the schema.
     * 
     * @param  string  $table
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function create($table, Closure $callback): void
    {
        $this->build(take($this->createDataprint($table), function ($dataprint) use ($callback) {
            $dataprint->create();
            
            $callback($dataprint);
        }));
    }
    
    /**
     * Drop a table from the schema.
     * 
     * @param  string  $table
     * 
     * @return void
     */
    public function drop($table): void
    {
        $this->build(take($this->createDataprint($table), fn ($dataprint) => $dataprint->drop()));
    }
    
    /**
     * Drop a table from the schema if it exists.
     * 
     * @param  string  $table
     * 
     * @return void
     */
    public function dropIfExists($table): void
    {
        $this->build(take($this->createDataprint($table), fn ($dataprint) => $dataprint->dropIfExists()));
    }
    
    /**
     * Drop columns from a table schema.
     * 
     * @param  string  $table
     * @param  string|array  $columns
     * 
     * @return void
     */
    public function dropColumns($table, $columns): void
    {
        $this->table($table, fn (Dataprint $dataprint) => $dataprint->dropColumn($columns));
    }
    
    /**
     * Drop all tables from the database.
     * 
     * @return void
     * 
     * @throws \LogicException
     */
    public function dropAllTables()
    {
        throw new LogicException('This database driver does not support dropping all tables');
    }
    
    /**
     * Drop all views from the database.
     * 
     * @return void
     * 
     * @throws \LogicException
     */
    public function dropAllViews()
    {
        throw new LogicException('This database driver does not support dropping all views');
    }
    
    /**
     * Get all of the table names for the database.
     * 
     * @return array
     * 
     * @throws \LogicException
     */
    public function getAllTables()
    {
        throw new LogicException('This database driver does not support getting all tables');
    }
    
    /**
     * Rename a table on the schema.
     * 
     * @param  string  $from
     * @param  string  $to
     * 
     * @return void
     */
    public function rename($from, $to): void
    {
        $this->build(take($this->createDataprint($from), fn ($dataprint) => $dataprint->rename($to)));
    }
    
    /**
     * Execute the Dataprint to build / modify the table.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @return void
     */
    protected function build(Dataprint $dataprint): void
    {
        $dataprint->build($this->connection, $this->grammar);
    }
    
    /**
     * Create a new command set with a Closure.
     * 
     * @param  string  $table
     * @param  \Closure|null  $callback
     * 
     * @return \Syscodes\Components\Database\Schema\Dataprint
     */
    protected function createDataprint($table, ?Closure $callback = null)
    {
        $prefix = $this->connection->getConfig('prefix_indexes')
                    ? $this->connection->getConfig('prefix')
                    : '';
        
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }
        
        return Container::getInstance()->make(Dataprint::class, compact('table', 'callback', 'prefix'));
    }
    
    /**
     * Enable foreign key constraints.
     * 
     * @return bool
     */
    public function enableForeignKeyConstraints(): bool
    {
        return $this->connection->statement(
            $this->grammar->compileEnableForeignKeyConstraints()
        );
    }
    
    /**
     * Disable foreign key constraints.
     * 
     * @return bool
     */
    public function disableForeignKeyConstraints(): bool
    {
        return $this->connection->statement(
            $this->grammar->compileDisableForeignKeyConstraints()
        );
    }
    
    /**
     * Get the database connection instance.
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Set the database connection instance.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return static
     */
    public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;
        
        return $this;
    }
    
    /**
     * Set the Schema Dataprint resolver callback.
     * 
     * @param  \Closure  $resolver
     * 
     * @return void
     */
    public function dataprintResolver(Closure $resolver): void
    {
        $this->resolver = $resolver;
    }
}