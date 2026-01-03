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

use RuntimeException;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Flowing;

/**
 * Allows the compilation of sql sentences for the
 * SQLite database.
 */
class SQLiteGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     * 
     * @var string[] $modifiers
     */
    protected $modifiers = ['Nullable', 'Default', 'Increment'];
    
    /**
     * The possible column serials.
     * 
     * @var string[] $serials
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine the list of tables.
     *
     * @return string
     */
    public function compileTableListing(): string
    {
        return "select * from sqlite_master where type = 'table' and name = ?";
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
        return 'pragma table_info('.$this->wrap(str_replace('.', '__', $table)).')';
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
        return sprintf('%s table %s (%s%s%s)',
                    $dataprint->temporary ? 'create temporary' : 'create',
                    $this->wrapTable($dataprint),
                    implode(', ', $this->getColumns($dataprint)),
                    (string) $this->addForeignKeys($dataprint),
                    (string) $this->addPrimaryKeys($dataprint)
               );
    }
    
    /**
     * Get the foreign key syntax for a table creation statement.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @return string|null
     */
    protected function addForeignKeys(Dataprint $dataprint)
    {
        $foreigns = $this->getCommandsByName($dataprint, 'foreign');
        
        return collect($foreigns)->reduce(function ($sql, $foreign) {
            $sql .= $this->getForeignKey($foreign);
            
            if ( ! is_null($foreign->onDelete)) {
                $sql .= " on delete {$foreign->onDelete}";
            }
            
            if ( ! is_null($foreign->onUpdate)) {
                $sql .= " on update {$foreign->onUpdate}";
            }
            
            return $sql;
        }, '');
    }
    
    /**
     * Get the SQL for the foreign key.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $foreign
     * 
     * @return string
     */
    protected function getForeignKey($foreign): string
    {
        return sprintf(', foreign key(%s) references %s(%s)',
                    $this->columnize($foreign->columns),
                    $this->wrapTable($foreign->on),
                    $this->columnize((array) $foreign->references)
               );
    }
    
    /**
     * Get the primary key syntax for a table creation statement.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @return string|null
     */
    protected function addPrimaryKeys(Dataprint $dataprint)
    {
        if ( ! is_null($primary = $this->getCommandByName($dataprint, 'primary'))) {
            return ", primary key ({$this->columnize($primary->columns)})";
        }
    }
    
    /**
     * Compile an add column command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return array
     */
    public function compileAdd(Dataprint $dataprint, Flowing $command): array
    {
        $columns = $this->prefixArray('add column', $this->getColumns($dataprint));

        return collect($columns)->map(function ($column) use ($dataprint) {
            return 'alter table '.$this->wrapTable($dataprint).' '.$column;
        })->all();
        
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
     * @return void
     * 
     * @throws \RuntimeException
     */
    public function compileSpatialIndex(Dataprint $dataprint, Flowing $command): void
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes');
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
        // Handled on table creation...
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
        
        return "drop index {$index}";
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
        
        return "drop index {$index}";
    }
    
    /**
     * Compile a drop spatial index command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return void
     * 
     * @throws \RuntimeException
     */
    public function compileDropSpatialIndex(Dataprint $dataprint, Flowing $command): void
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes');
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
     * Compile the SQL needed to rebuild the database.
     * 
     * @return string
     */
    public function compileRebuild(): string
    {
        return 'vacuum';
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
        return "delete from sqlite_master where type in ('table', 'index', 'trigger')";
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
        return "delete from sqlite_master where type in ('view')";
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
        return 'select type, name from sqlite_master where type = \'table\' and name not like \'sqlite_%\'';
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
        return 'select type, name from sqlite_master where type = \'view\'';
    }
    
    /**
     * Compile the command to enable foreign key constraints.
     * 
     * @return string
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return 'PRAGMA foreign_keys = ON;';
    }
    
    /**
     * Compile the command to disable foreign key constraints.
     * 
     * @return string
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'PRAGMA foreign_keys = OFF;';
    }
    
    /**
     * Compile the SQL needed to enable a writable schema.
     * 
     * @return string
     */
    public function compileEnableWriteableSchema(): string
    {
        return 'PRAGMA writable_schema = 1;';
    }
    
    /**
     * Compile the SQL needed to disable a writable schema.
     * 
     * @return string
     */
    public function compileDisableWriteableSchema(): string
    {
        return 'PRAGMA writable_schema = 0;';
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
        return 'varchar';
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
        return 'text';
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
        return 'integer';
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
        return 'integer';
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
        return 'integer';
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
        return 'integer';
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
        return "numeric";
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
        return sprintf('varchar check ("%s" in (%s))',
                    $column->name,
                    $this->quoteString($column->allowed)
               );
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
        return 'text';
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
        return 'text';
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
        return 'time';
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
        return $this->typeDateTime($column);
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
        return 'varchar';
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
            return ' primary key autoincrement';
        }
    }
}