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
use UnitEnum;
use Syscodes\Components\Database\Concerns\CompilesJsonPaths;
use Syscodes\Components\Database\Grammar as BaseGrammar;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Flowing;

use function Syscodes\Components\Support\enum_value;

/**
 * Allows the compilation of sql sentences for the connection to database.
 */
abstract class Grammar extends BaseGrammar
{
    use CompilesJsonPaths;

    /**
     * The commands to be executed outside of create or alter command.
     * 
     * @var array
     */
    protected $flowingCommands = [];

    /**
     * The possible column modifiers.
     * 
     * @var string[]
     */
    protected $modifiers = [];

    /**
     * If this Grammar supports schema changes wrapped in a transaction.
     *
     * @var bool
     */
    protected $transactions = false;

    /**
     * Compile a create database command.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    public function compileCreateDatabase($name): string
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
     * Compile the query to determine the schemas.
     *
     * @return string
     * 
     * @throws \RuntimeException
     */
    public function compileSchemas(): string
    {
        throw new RuntimeException('This database driver does not support retrieving schemas.');
    }

    /**
     * Compile the query to determine if the given table exists.
     *
     * @param  string|null  $schema
     * @param  string  $table
     * 
     * @return string|null
     */
    public function compileTableExists($schema, $table)
    {
        //
    }

    /**
     * Compile the query to determine the tables.
     *
     * @param  string|string[]|null  $schema
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileTables($schema): string
    {
        throw new RuntimeException('This database driver does not support retrieving tables.');
    }

    /**
     * Compile the query to determine the views.
     *
     * @param  string|string[]|null  $schema
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileViews($schema): string
    {
        throw new RuntimeException('This database driver does not support retrieving views.');
    }

    /**
     * Compile the query to determine the user-defined types.
     *
     * @param  string|string[]|null  $schema
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileTypes($schema): string
    {
        throw new RuntimeException('This database driver does not support retrieving user-defined types.');
    }

    /**
     * Compile the query to determine the columns.
     *
     * @param  string|null  $schema
     * @param  string  $table
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileColumns($schema, $table): string
    {
        throw new RuntimeException('This database driver does not support retrieving columns.');
    }

    /**
     * Compile the query to determine the indexes.
     *
     * @param  string|null  $schema
     * @param  string  $table
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileIndexes($schema, $table): string
    {
        throw new RuntimeException('This database driver does not support retrieving indexes.');
    }

    /**
     * Compile the query to determine the foreign keys.
     *
     * @param  string|null  $schema
     * @param  string  $table
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileForeignKeys($schema, $table): string
    {
        throw new RuntimeException('This database driver does not support retrieving foreign keys.');
    }
    
    /**
     * Compile a rename column command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return array|string
     */
    public function compileRenameColumn(Dataprint $dataprint, Flowing $command): array|string
    {
        return sprintf('alter table %s rename column %s to %s',
            $this->wrapTable($dataprint),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }

    /**
     * Compile a change column command into a series of SQL statements.
     *
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return array|string
     *
     * @throws \RuntimeException
     */
    public function compileChange(Dataprint $dataprint, Flowing $command): array|string
    {
        throw new RuntimeException('This database driver does not support modifying columns.');
    }

    /**
     * Compile a fulltext index key command.
     *
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileFulltext(Dataprint $dataprint, Flowing $command): string
    {
        throw new RuntimeException('This database driver does not support fulltext index creation.');
    }

    /**
     * Compile a drop fulltext index command.
     *
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     *
     * @throws \RuntimeException
     */
    public function compileDropFullText(Dataprint $dataprint, Flowing $command): string
    {
        throw new RuntimeException('This database driver does not support fulltext index removal.');
    }

    /**
     * Compile a foreign key command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * 
     * @return string
     */
    public function compileForeign(Dataprint $dataprint, Flowing $command): string
    {
        // We need to prepare several of the elements of the foreign key definition
        // before we can create the SQL.
        $sql = sprintf('alter table %s add constraint %s ',
            $this->wrapTable($dataprint),
            $this->wrap($command->index)
        );
        
        // Once we have the initial portion of the SQL statement we will add on the
        // key name, table name, and referenced columns.
        $sql .= sprintf('foreign key (%s) references %s (%s)',
            $this->columnize($command->columns),
            $this->wrapTable($command->on),
            $this->columnize((array) $command->references)
        );
        
        // Once we have the basic foreign key creation statement constructed we can
        // build out the syntax for what should happen on an update or delete of
        // the affected columns, which will get something like "cascade", etc.
        if ( ! is_null($command->onDelete)) {
            $sql .= " on delete {$command->onDelete}";
        }

        if ( ! is_null($command->onUpdate)) {
            $sql .= " on update {$command->onUpdate}";
        }

        return $sql;
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
        throw new RuntimeException('This database driver does not support dropping foreign keys');
    }

    /**
     * Compile the dataprint's column definitions.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * 
     * @return array
     */
    protected function getColumns(Dataprint $dataprint): array
    {
        $columns = [];

        foreach ($dataprint->getAddedColumns() as $column) {
            $columns[] = $this->getColumn($dataprint, $column);
        }

        return $columns;
    }

    /**
     * Compile the column definition.
     *
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Database\Schema\ColumnDefinition  $column
     * 
     * @return string
     */
    protected function getColumn(Dataprint $dataprint, $column): string
    {
        // Each of the column types has their own compiler functions, which are tasked
        // with turning the column definition into its SQL format for this platform
        // used by the connection. 
        $sql = $this->wrap($column).' '.$this->getType($column);

        return $this->addModifiers($sql, $dataprint, $column);
    }
    
    /**
     * Get the SQL for the column data type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function getType(Flowing $column): string
    {
        return $this->{'type'.ucfirst($column->type)}($column);
    }

    /**
     * Create the column definition for a raw column type.
     *
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function typeRaw(Flowing $column): string
    {
        return $column->offsetGet('definition');
    }
    
    /**
     * Add the column modifiers to the definition.
     * 
     * @param  string  $sql
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function addModifiers($sql, Dataprint $dataprint, Flowing $column): string
    {
        foreach ($this->modifiers as $modifier) {
            if (method_exists($this, $method = "modify{$modifier}")) {
                $sql .= $this->{$method}($dataprint, $column);
            }
        }
        
        return $sql;
    }
    
    /**
     * Get the primary key command if it exists on the dataprint.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Support\Flowing|null
     */
    protected function getCommandByName(Dataprint $dataprint, $name)
    {
        $commands = $this->getCommandsByName($dataprint, $name);
        
        if (count($commands) > 0) {
            return array_first($commands);
        }
    }
    
    /**
     * Get all of the commands with a given name.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  string  $name
     *
     * @return array
     */
    protected function getCommandsByName(Dataprint $dataprint, $name): array
    {
        return array_filter($dataprint->getCommands(), fn ($value) => $value->name == $name);
    }
    
    /**
     * Determine if a command with a given name exists on the dataprint.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  string  $name
     * 
     * @return bool
     */
    protected function hasCommand(Dataprint $dataprint, $name): bool
    {
        foreach ($dataprint->getCommands() as $command) {
            if ($command->name === $name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Add a prefix to an array of values.
     * 
     * @param  string  $prefix
     * @param  array  $values
     * 
     * @return array
     */
    public function prefixArray($prefix, array $values): array
    {
        return array_map(fn ($value) => $prefix.' '.$value, $values);
    }
    
    /**
     * Wrap a table in keyword identifiers.
     * 
     * @param  mixed  $table
     * @param  string|null  $prefix
     * 
     * @return string
     */
    public function wrapTable($table, $prefix = null): string
    {
        return parent::wrapTable(
            $table instanceof Dataprint ? $table->getTable() : $table,
            $prefix
        );
    }
    
    /**
     * Wrap a value in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Support\Flowing|\Syscodes\Components\Database\Query\Expression|string  $value
     * @param  bool  $prefix
     * 
     * @return string
     */
    public function wrap($value, $prefix = false): string
    {
        return parent::wrap(
            $value instanceof Flowing ? $value->name : $value, $prefix
        );
    }
    
    /**
     * Format a value so that it can be used in "default" clauses.
     * 
     * @param  mixed  $value
     * 
     * @return string
     */
    protected function getDefaultValue($value): string
    {
        if ($value instanceof Expression) {
            return $this->getValue($value);
        }

        if ($value instanceof UnitEnum) {
            return "'".str_replace("'", "''", enum_value($value))."'";
        }
        
        return is_bool($value)
            ? "'".(int) $value."'"
            : "'".(string) $value."'";
    }
    
    /**
     * Get the flowing commands for the grammar.
     * 
     * @return array
     */
    public function getFlowingCommands(): array
    {
        return $this->flowingCommands;
    }

    /**
     * Check if this Grammar supports schema changes wrapped in a transaction.
     *
     * @return bool
     */
    public function supportsSchemaTransactions(): bool
    {
        return $this->transactions;
    }
}