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

use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Flowing;

/**
 * Allows the compilation of sql sentences for the
 * PostgreSQL database.
 */
class PostgresGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     * 
     * @var string[] $modifiers
     */
    protected $modifiers = ['Collate', 'Nullable', 'Default', 'Increment'];
    
    /**
     * The possible column serials.
     * 
     * @var string[] $serials
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];
    
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
        return sprintf('create database %s enconding %s',
                    $this->wrapValue($name),
                    $this->wrapValue($connection->getConfig('charset')),
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
        return sprintf('drop database if exists %s', $this->wrapValue($name));
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
     * 
     * @return string
     */
    public function compileCreate(Dataprint $dataprint, Flowing $command): string
    {
        return sprintf('%s table %s (%s)',
                    $dataprint->temporary ? 'create temporary' : 'create',
                    $this->wrapTable($dataprint),
                    implode(', ', $this->getColumns($dataprint))
               );
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
                    implode(', ', $this->prefixArray('add column', $this->getColumns($dataprint)))
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
        $columns = $this->columnize($command->columns);

        return 'alter table '.$this->wrapTable($dataprint)." add primary key ({$columns})";        
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
        return sprintf('alter table %s add constraint %s unique (%s)',
                    $this->wrapTable($dataprint),
                    $this->wrap($command->index),
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
                    $command->option ? ' using '.$command->option : '',
                    $this->columnize($command->columns)
               );        
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
        return sprintf('create index %s on %s using gin ((%s))',
                    $this->wrap($command->index),
                    $this->wrapTable($dataprint),
                    implode(' || ', $command->columns)
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
        $command->option = 'gist';
        
        return $this->compileIndex($dataprint, $command);
    }
    
    /**
     * Compile a foreign key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileForeign(Dataprint $dataprint, Flowing $command)
    {
        $sql = parent::compileForeign($dataprint, $command);
        
        if ( ! is_null($command->deferrable)) {
            $sql .= $command->deferrable ? ' deferrable' : ' not deferrable';
        }
        
        if ($command->deferrable && ! is_null($command->initiallyImmediate)) {
            $sql .= $command->initiallyImmediate ? ' initially immediate' : ' initially deferred';
        }
        
        return $sql;
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
        $columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));
        
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
        $index = $this->wrap("{$dataprint->getTable()}_pkey");
        
        return 'alter table '.$this->wrapTable($dataprint)." drop constraint {$index}";
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
        
        return "alter table {$this->wrapTable($dataprint)} drop constraint {$index}";
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
        return "drop index {$this->wrap($command->index)}";
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
        $table = $this->wrap($dataprint);
        
        return "alter table {table} drop foreign key {$command->index}"; 
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
        
        return "alter table {$from} rename to ".$this->wrapTable($command->to);
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
        return sprintf('alter table %s rename to %s',
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
        return 'drop table "'.implode('","', $tables).'" cascade';
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
        return 'drop view "'.implode('","', $views).'" cascade';
    }
    
    /**
     * Compile the SQL needed to retrieve all table names.
     * 
     * @param  array|string  $schema
     * 
     * @return string
     */
    public function compileGetAllTables($schema): string
    {
        return "select tablename from pg_catalog.pg_tables where schemaname in ('".implode("','", (array) $schema)."')";
    }
    
    /**
     * Compile the SQL needed to retrieve all view names.
     * 
     * @param  array|string  $schema
     * 
     * @return string
     */
    public function compileGetAllViews($schema): string
    {
        return "select viewname from pg_catalog.pg_views where schemaname in ('".implode("','", (array) $schema)."')";
    }
    
    /**
     * Compile the command to enable foreign key constraints.
     * 
     * @return string
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return 'SET CONSTRAINTS ALL IMMEDIATE;';
    }
    
    /**
     * Compile the command to disable foreign key constraints.
     * 
     * @return string
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'SET CONSTRAINTS ALL DEFERRED;';
    }
    
    /**
     * Compile a comment command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileComment(Dataprint $dataprint, Flowing $command): string
    {
        return sprintf('comment on column %s.%s is %s',
                    $this->wrapTable($dataprint),
                    $this->wrap($command->column->name),
                    "'".str_replace("'", "''", $command->value)."'"
               );
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
        if ($column->length) {
            return "char({$column->length})";
        }

        return 'char';
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
        if ($column->length) {
            return "varchar({$column->length})";
        }

        return 'varchar';
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
        return 'varchar(255)';
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
        return 'text';
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
        return 'text';
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
        return $column->autoIncrement ? 'bigserial' : 'bigint';
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
        return $column->autoIncrement ? 'serial' : 'integer';
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
        return 'integer';
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
        return 'smallint';
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
        return 'double precision';
    }
    
    /**
     * Create the column definition for a real type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeReal(Flowing $column): string
    {
        return 'real';
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
        return 'boolean';
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
        $allowed = array_map(function($a) { return "'".$a."'"; }, $column->allowed);
        
        return "varchar(255) check (\"{$column->name}\" in (".implode(', ', $allowed)."))";
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
        return $this->typeTimestamp($column);
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
        return $this->typeTimestampTz($column);
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
        return 'time'.(is_null($column->precision) ? '' : "($column->precision)").' without time zone';
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
        return 'time'.(is_null($column->precision) ? '' : "($column->precision)").' with time zone';
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
        $type = 'timestamp'.(is_null($column->precision) ? '' : "($column->precision)").' without time zone';
        
        return $column->useCurrent ? "$type default CURRENT_TIMESTAMP" : $type;
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
        $type = 'timestamp'.(is_null($column->precision) ? '' : "($column->precision)").' with time zone';
        
        return $column->useCurrent ? "$type default CURRENT_TIMESTAMP" : $type;
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
        return $this->typeInteger($column);
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
        return 'bytea';
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
        return 'uuid';
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
            return ' collate '.$this->wrapValue($column->collation);
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
            return ' primary key';
        }
    }
}