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

namespace Syscodes\Components\Database\Schema\Grammars;

use RuntimeException;
use Syscodes\Components\Support\Flowing;
use Syscodes\Components\Database\Schema\Dataprint;

/**
 * Allows the compilation of sql sentences for the
 * SqlServer database.
 */
class SqlServerGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     * 
     * @var string[] $modifiers
     */
    protected $modifiers = ['Increment', 'Collate', 'Nullable', 'Default', 'Persisted'];
    
    /**
     * The possible column serials.
     * 
     * @var string[] $serials
     */
    protected $serials = ['tinyInteger', 'smallInteger', 'mediumInteger', 'integer', 'bigInteger'];

    /**
     * Compile a create database command.
     * 
     * @param  string  $name
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return string
     */
    public function compileCreateDatabase($name, $connection): string
    {
        return sprintf('create database %s',
            $this->wrapValue($name)
        );
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
        return "select * from sys.sysobjects where id = object_id(?) and xtype in ('U', 'V')";
    }

    /**
     * Compile the query to determine the list of columns.
     * 
     * @param  string  $table
     *
     * @return string
     */
    public function compileColumnListing($table): string
    {
        return "select name from sys.columns where object_id = object_id('$table')";
    }

    /**
     * Compile a create table command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileCreate(Dataprint $dataprint, Flowing $command): string
    {
        $columns = implode(', ', $this->getColumns($dataprint));
        
        return 'create table '.$this->wrapTable($dataprint)." ($columns)";
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
        return sprintf('alter table %s add %s',
                    $this->wrapTable($dataprint),
                    implode(', ', $this->getColumns($dataprint))
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
        return sprintf('alter table %s add constraint %s primary key (%s)',
                    $this->wrapTable($dataprint),
                    $this->wrap($command->index),
                    $this->columnize($command->columns)
               );
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
        return sprintf('create unique index %s on %s (%s)',
                    $this->wrap($command->index),
                    $this->wrapTable($dataprint),
                    $this->columnize($command->columns)
               );
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
        return sprintf('create index %s on %s (%s)',
                    $this->wrap($command->index),
                    $this->wrapTable($dataprint),
                    $this->columnize($command->columns)
               );        
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
        return sprintf('create spatial index %s on %s (%s)',
                    $this->wrap($command->index),
                    $this->wrapTable($dataprint),
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
        return sprintf('if exists (select * from sys.sysobjects where id = object_id(%s, \'U\')) drop table %s',
                    "'".str_replace("'", "''", $this->getTablePrefix().$dataprint->getTable())."'",
                    $this->wrapTable($dataprint)
               );
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
        $columns = $this->wrapArray($command->columns);
        
        return 'alter table '.$this->wrapTable($dataprint).' drop column '.implode(', ', $columns);
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
        $index = $this->wrap($command->index);
        
        return "alter table {$this->wrapTable($dataprint)} drop constraint {$index}";
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
        
        return "drop index {$index} on {$this->wrapTable($dataprint)}";
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
        return $this->compileDropUnique($dataprint, $command);
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
        
        return "alter table {$this->wrapTable($dataprint)} drop constraint {$index}";
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
        
        return "sp_rename {$from}, ".$this->wrapTable($command->to);
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
        return sprintf("sp_rename N'%s', %s, N'INDEX'",
                    $this->wrap($dataprint->getTable().'.'.$command->from),
                    $this->wrap($command->to)
               );
    }
    
    /**
     * Compile the SQL needed to drop all tables.
     * 
     * @return string
     */
    public function compileDropAllTables(): string
    {
        return "EXEC sp_msforeachtable 'DROP TABLE ?'";
    }
    
    /**
     * Compile the command to drop all foreign keys.
     * 
     * @return string
     */
    public function compileDropAllForeignKeys(): string
    {
        return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'ALTER TABLE '
                + QUOTENAME(OBJECT_SCHEMA_NAME(parent_object_id)) + '.' + + QUOTENAME(OBJECT_NAME(parent_object_id))
                + ' DROP CONSTRAINT ' + QUOTENAME(name) + ';'
            FROM sys.foreign_keys;
            EXEC sp_executesql @sql;";
    }
    
    /**
     * Compile the SQL needed to drop all views.
     * 
     * @return string
     */
    public function compileDropAllViews(): string
    {
        return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'DROP VIEW ' + QUOTENAME(OBJECT_SCHEMA_NAME(object_id)) + '.' + QUOTENAME(name) + ';'
            FROM sys.views;
            EXEC sp_executesql @sql;";
    }
    
    /**
     * Compile the SQL needed to retrieve all table names.
     * 
     * @return string
     */
    public function compileGetAllTables(): string
    {
        return "select name, type from sys.tables where type = 'U'";
    }
    
    /**
     * Compile the SQL needed to retrieve all view names.
     * 
     * @return string
     */
    public function compileGetAllViews(): string
    {
        return "select name, type from sys.objects where type = 'V'";
    }
    
    /**
     * Compile the command to enable foreign key constraints.
     * 
     * @return string
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return 'EXEC sp_msforeachtable @command1="print \'?\'", @command2="ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all";';
    }
    
    /**
     * Compile the command to disable foreign key constraints.
     * 
     * @return string
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'EXEC sp_msforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all";';
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
        return "nchar({$column->length})";
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
        return "nvarchar({$column->length})";
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
        return 'nvarchar(max)';
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
        return 'nvarchar(max)';
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
        return 'nvarchar(max)';
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
        return 'nvarchar(max)';
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
        return 'int';
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
        return 'float';
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
        return 'float';
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
        return 'bit';
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
        return 'nvarchar(255)';
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
        return 'datetime';
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
        return 'time';
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
        return $column->useCurrent ? 'datetime default CURRENT_TIMESTAMP' : 'datetime';
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
        return 'varbinary(max)';
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
            return ' identity primary key';
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
            return ' collate '.$column->collation;
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
        return $column->nullable ? ' null' : ' not null';
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
            return ' default '.$this->getDefaultValue($column->default);
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
    protected function modifyPersisted(Dataprint $dataprint, Flowing $column)
    {
        if ($column->persisted) {
            return ' persisted';
        }
    }
    
    /**
     * Wrap a table in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression|string  $table
     * 
     * @return string
     */
    public function wrapTable($table): string
    {
        if ($table instanceof DataPrint && $table->temporary) {
            $this->setTablePrefix('#');
        }
        
        return parent::wrapTable($table);
    }
    
    /**
     * Quote the given string literal.
     * 
     * @param  string|array  $value
     * 
     * @return string
     */
    public function quoteString($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, __FUNCTION__], $value));
        }
        
        return "N'$value'";
    }
}