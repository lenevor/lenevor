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

namespace Syscodes\Components\Database\Schema\Grammars;

use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Flowing;

/**
 * Allows the compilation of sql sentences for the
 * Mysql database.
 */
class MySqlGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     * 
     * @var string[] $modifiers
     */
    protected $modifiers = [
        'Unsigned', 'Charset', 'Collate', 'Nullable', 'Invisible',
        'Default', 'OnUpdate', 'Increment', 'Comment', 'After', 'First',
    ];
    
    /**
     * The possible column serials.
     * 
     * @var string[] $serials
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * The commands to be executed outside of create or alter command.
     * 
     * @var array
     */
    protected $flowingCommands = ['AutoIncrementStartingValues'];
    
    /**
     * Compile a create database command.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    public function compileCreateDatabase($name): string
    {
        $sql = parent::compileCreateDatabase($name);

        if ($charset = $this->connection->getConfig('charset')) {
            $sql .= sprintf(' default character set %s', $this->wrapValue($charset));
        }

        if ($collation = $this->connection->getConfig('collation')) {
            $sql .= sprintf(' default collate %s', $this->wrapValue($collation));
        }

        return $sql;
    }

    /**
     * Compile a drop database if exists command.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    public function compileDropDatabaseIfExists($name): string
    {
        return sprintf('drop database if exists %s',
            $this->wrapValue($name)
        );
    }

    /**
     * Compile the query to determine the list of tables.
     *
     * @return string
     */
    public function compileTableListing(): string
    {
        return "select * from information_schema.tables where table_schema = ? and table_name = ?";
    }

    /**
     * Compile the query to determine the list of columns.
     *
     * @return string
     */
    public function compileColumnListing(): string
    {
        return 'select column_name as `column_name` from information_schema.columns where table_schema = ? and table_name = ?';
    }

    /**
     * Compile a create table command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return string
     */
    public function compileCreate(Dataprint $dataprint, Flowing $command, Connection $connection): string
    {
        $sql = $this->compileCreateTable(
            $dataprint, $command, $connection
        );

        $sql = $this->compileCreateEncoding(
            $sql, $connection, $dataprint
        );

        return $this->compileCreateEngine(
            $sql, $connection, $dataprint
        );
    }
    
    /**
     * Create the main create table clause.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return string
     */
    protected function compileCreateTable($dataprint, $command, $connection): string
    {
        return trim(sprintf('%s table %s (%s)',
            $dataprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($dataprint),
            implode(', ', $this->getColumns($dataprint))
        ));
    }
    
    /**
     * Append the character set specifications to a command.
     * 
     * @param  string  $sql
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @return string
     */
    protected function compileCreateEncoding($sql, Connection $connection, Dataprint $dataprint): string
    {
        if (isset($dataprint->charset)) {
            $sql .= ' default character set '.$dataprint->charset;
        } elseif ( ! is_null($charset = $connection->getConfig('charset'))) {
            $sql .= ' default character set '.$charset;
        }
        
        if (isset($dataprint->collation)) {
            $sql .= " collate '{$dataprint->collation}'";
        } elseif ( ! is_null($collation = $connection->getConfig('collation'))) {
            $sql .= " collate '{$collation}'";
        }
        
        return $sql;
    }
    
    /**
     * Append the engine specifications to a command.
     * 
     * @param  string  $sql
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @return string
     */
    protected function compileCreateEngine($sql, Connection $connection, Dataprint $dataprint): string
    {
        if (isset($dataprint->engine)) {
            return $sql.' engine = '.$dataprint->engine;
        } elseif ( ! is_null($engine = $connection->getConfig('engine'))) {
            return $sql.' engine = '.$engine;
        }
        
        return $sql;
    }
    
    /**
     * Compile an add column command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileAdd(Dataprint $dataprint, Flowing $command): string
    {
        return sprintf('alter table %s %s',
            $this->wrapTable($dataprint),
            implode(', ', $this->prefixArray('add', $this->getColumns($dataprint)))
        );
    }
    
    /**
     * Compile a primary key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compilePrimary(Dataprint $dataprint, Flowing $command): string
    {
        $command->name(null);
        
        return $this->compileKey($dataprint, $command, 'primary key');
    }
    
    /**
     * Compile a unique key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileUnique(Dataprint $dataprint, Flowing $command): string
    {
        return $this->compileKey($dataprint, $command, 'unique');
    }
    
    /**
     * Compile a plain index key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileIndex(Dataprint $dataprint, Flowing $command): string
    {
        return $this->compileKey($dataprint, $command, 'index');
    }
    
    /**
     * Compile a fulltext index key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileFullText(Dataprint $dataprint, Flowing $command): string
    {
        return $this->compileKey($dataprint, $command, 'fulltext');
    }
    
    /**
     * Compile a spatial index key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileSpatialIndex(Dataprint $dataprint, Flowing $command): string
    {
        return $this->compileKey($dataprint, $command, 'spatial index');
    }
    
    /**
     * Compile an index creation command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * @param  string  $type
     * 
     * @return string
     */
    protected function compileKey(Dataprint $dataprint, Flowing $command, $type): string
    {
        return sprintf('alter table %s add %s %s%s(%s)',
            $this->wrapTable($dataprint),
            $type,
            $this->wrap($command->index),
            $command->option ? ' using '.$command->option : '',
            $this->columnize($command->columns)
        );
    }
    
    /**
     * Compile a drop table command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDrop(Dataprint $dataprint, Flowing $command): string
    {
        return 'drop table '.$this->wrapTable($dataprint);
    }
    
    /**
     * Compile a drop table (if exists) command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropIfExists(Dataprint $dataprint, Flowing $command): string
    {
        return 'drop table if exists '.$this->wrapTable($dataprint);
    }
    
    /**
     * Compile a drop column command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropColumn(Dataprint $dataprint, Flowing $command): string
    {
        $columns = $this->prefixArray('drop', $this->wrapArray($command->columns));
        
        return 'alter table '.$this->wrapTable($dataprint).' '.implode(', ', $columns);
    }
    
    /**
     * Compile a drop primary key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropPrimary(Dataprint $dataprint, Flowing $command): string
    {
        return 'alter table '.$this->wrapTable($dataprint).' drop primary key';
    }
    
    /**
     * Compile a drop unique key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropUnique(Dataprint $dataprint, Flowing $command): string
    {
        $index = $this->wrap($command->index);
        
        return "alter table {$this->wrapTable($dataprint)} drop index {$index}";
    }
    
    /**
     * Compile a drop index command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropIndex(Dataprint $dataprint, Flowing $command): string
    {
        $index = $this->wrap($command->index);
        
        return "alter table {$this->wrapTable($dataprint)} drop index {$index}";
    }
    
    /**
     * Compile a drop fulltext index command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropFullText(Dataprint $dataprint, Flowing $command): string
    {
        return $this->compileDropIndex($dataprint, $command);
    }
    
    /**
     * Compile a drop spatial index command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropSpatialIndex(Dataprint $dataprint, Flowing $command): string
    {
        return $this->compileDropIndex($dataprint, $command);
    }
    
    /**
     * Compile a drop foreign key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileDropForeign(Dataprint $dataprint, Flowing $command): string
    {
        $index = $this->wrap($command->index);
        
        return "alter table {$this->wrapTable($dataprint)} drop foreign key {$index}";
    }
    
    /**
     * Compile a rename table command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileRename(Dataprint $dataprint, Flowing $command): string
    {
        $from = $this->wrapTable($dataprint);
        
        return "rename table {$from} to ".$this->wrapTable($command->to);
    }
    
    /**
     * Compile a rename index command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileRenameIndex(Dataprint $dataprint, Flowing $command): string
    {
        return sprintf('alter table %s rename index %s to %s',
            $this->wrapTable($dataprint),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }
    
    /**
     * Compile the SQL needed to drop all tables.
     * 
     * @param  array  $tables
     * 
     * @return string
     */
    public function compileDropAllTables($tables): string
    {
        return 'drop table '.implode(',', $this->wrapArray($tables));
    }
    
    /**
     * Compile the SQL needed to drop all views.
     * 
     * @param  array  $views
     * 
     * @return string
     */
    public function compileDropAllViews($views): string
    {
        return 'drop view '.implode(',', $this->wrapArray($views));
    }
    
    /**
     * Compile the SQL needed to retrieve all table names.
     * 
     * @return string
     */
    public function compileGetAllTables(): string
    {
        return 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'';
    }
    
    /**
     * Compile the SQL needed to retrieve all view names.
     * 
     * @return string
     */
    public function compileGetAllViews(): string
    {
        return 'SHOW FULL TABLES WHERE table_type = \'VIEW\'';
    }
    
    /**
     * Compile the command to enable foreign key constraints.
     * 
     * @return string
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return 'SET FOREIGN_KEY_CHECKS=1;';
    }
    
    /**
     * Compile the command to disable foreign key constraints.
     * 
     * @return string
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'SET FOREIGN_KEY_CHECKS=0;';
    }
    
    /**
     * Create the column definition for a char type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeChar(Flowing $column): string
    {
        return "char({$column->length})";
    }
    
    /**
     * Create the column definition for a string type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeString(Flowing $column): string
    {
        return "varchar({$column->length})";
    }
    
    /**
     * Create the column definition for a tiny text type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeTinyText(Flowing $column): string
    {
        return 'tinytext';
    }
    
    /**
     * Create the column definition for a text type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeText(Flowing $column): string
    {
        return 'text';
    }
    
    /**
     * Create the column definition for a medium text type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeMediumText(Flowing $column): string
    {
        return 'mediumtext';
    }
    
    /**
     * Create the column definition for a long text type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeLongText(Flowing $column): string
    {
        return 'longtext';
    }
    
    /**
     * Create the column definition for a big integer type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeBigInteger(Flowing $column): string
    {
        return 'bigint';
    }
    
    /**
     * Create the column definition for an integer type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeInteger(Flowing $column): string
    {
        return 'int';
    }
    
    /**
     * Create the column definition for a medium integer type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeMediumInteger(Flowing $column): string
    {
        return 'mediumint';
    }
    
    /**
     * Create the column definition for a tiny integer type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeTinyInteger(Flowing $column): string
    {
        return 'tinyint';
    }
    
    /**
     * Create the column definition for a small integer type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeSmallInteger(Flowing $column): string
    {
        return 'smallint';
    }
    
    /**
     * Create the column definition for a float type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeFloat(Flowing $column): string
    {
        return $this->typeDouble($column);
    }
    
    /**
     * Create the column definition for a double type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeDouble(Flowing $column): string
    {
        if ($column->total && $column->places) {
            return "double({$column->total}, {$column->places})";
        }
        
        return 'double';
    }
   
    /**
     * Create the column definition for a decimal type.
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeDecimal(Flowing $column): string
    {
        return "decimal({$column->total}, {$column->places})";
    }
    
    /**
     * Create the column definition for a boolean type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeBoolean(Flowing $column): string
    {
        return 'tinyint(1)';
    }
    
    /**
     * Create the column definition for an enum type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeEnum(Flowing $column): string
    {
        return sprintf('enum(%s)', $this->quoteString($column->allowed));
    }
    
    /**
     * Create the column definition for a set enumeration type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeSet(Flowing $column): string
    {
        return sprintf('set(%s)', $this->quoteString($column->allowed));
    }
    
    /**
     * Create the column definition for a json type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeJson(Flowing $column): string
    {
        return 'json';
    }
    
    /**
     * Create the column definition for a jsonb type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeJsonb(Flowing $column): string
    {
        return 'json';
    }
    
    /**
     * Create the column definition for a date type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeDate(Flowing $column): string
    {
        return 'date';
    }
    
    /**
     * Create the column definition for a date-time type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeDateTime(Flowing $column): string
    {
        $columnType = $column->precision ? "datetime($column->precision)" : 'datetime';
        
        $current = $column->precision ? "CURRENT_TIMESTAMP($column->precision)" : 'CURRENT_TIMESTAMP';
        
        $columnType = $column->useCurrent ? "$columnType default $current" : $columnType;
        
        return $column->useCurrentOnUpdate ? "$columnType on update $current" : $columnType;
    }
    
    /**
     * Create the column definition for a date-time (with time zone) type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeDateTimeTz(Flowing $column): string
    {
        return $this->typeDateTime($column);
    }
    
    /**
     * Create the column definition for a time type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeTime(Flowing $column): string
    {
        return $column->precision ? "time($column->precision)" : 'time';
    }
    
    /**
     * Create the column definition for a time (with time zone) type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeTimeTz(Flowing $column): string
    {
        return $this->typeTime($column);
    }
    
    /**
     * Create the column definition for a timestamp type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeTimestamp(Flowing $column): string
    {
        $columnType = $column->precision ? "timestamp($column->precision)" : 'timestamp';
        
        $current = $column->precision ? "CURRENT_TIMESTAMP($column->precision)" : 'CURRENT_TIMESTAMP';
        
        $columnType = $column->useCurrent ? "$columnType default $current" : $columnType;
        
        return $column->useCurrentOnUpdate ? "$columnType on update $current" : $columnType;
    }

    /**
     * Create the column definition for a timestamp (with time zone) type.
     * 
     * @param  \Syscodes\Components\Support\Flowing $column
     * 
     * @return string
     */
    protected function typeTimestampTz(Flowing $column): string
    {
        return $this->typeTimestamp($column);
    }
    
    /**
     * Create the column definition for a year type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeYear(Flowing $column): string
    {
        return 'year';
    }
    
    /**
     * Create the column definition for a binary type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeBinary(Flowing $column): string
    {
        return 'blob';
    }
    
    /**
     * Create the column definition for a uuid type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeUuid(Flowing $column): string
    {
        return 'char(36)';
    }
    
    /**
     * Get the SQL for an unsigned column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyUnsigned(Dataprint $dataprint, Flowing $column)
    {
        if ($column->unsigned) return ' unsigned';
    }

    /**
     * Get the SQL for a character set column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyCharset(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->charset)) {
            return ' character set '.$column->charset;
        }
    }

    /**
     * Get the SQL for a collation column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyCollate(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->collation)) {
            return " collate '{$column->collation}'";
        }
    }
    
    /**
     * Get the SQL for a nullable column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyNullable(Dataprint $dataprint, Flowing $column): string
    {
        if ($column->nullable === false) {
            return ' not null';
        }

        return $column->nullable ? ' null' : ' not null';
    }

    /**
     * Get the SQL for an invisible column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyInvisible(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->invisible)) {
            return ' invisible';
        }
    }
    
    /**
     * Get the SQL for a default column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyDefault(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->default))  {
            return " default ".$this->getDefaultValue($column->default);
        }
    }
    
    /**
     * Get the SQL for an auto-increment column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyIncrement(Dataprint $dataprint, Flowing $column)
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' auto_increment primary key';
        }
    }

    /**
     * Get the SQL for an "comment" column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyComment(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->comment)) {
            return ' comment "'.$column->comment.'"';
        }
    }
    
    /**
     * Get the SQL for an "after" column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyAfter(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->after)) {
            return ' after '.$this->wrap($column->after);
        }
    }
    
    /**
     * Get the SQL for a "first" column modifier.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string|null
     */
    protected function modifyFirst(Dataprint $dataprint, Flowing $column)
    {
        if ( ! is_null($column->first)) {
            return ' first';
        }
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
            return '`'.str_replace('`', '``', $value).'`';
        }
        
        return $value;
    }
}