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

use LogicException;
use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Grammar as BaseGrammar;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Flowing;

/**
 * Allows the compilation of sql sentences for the connection to database.
 */
abstract class Grammar extends BaseGrammar
{
    /**
     * The possible column modifiers.
     * 
     * @var string[]
     */
    protected $modifiers = [];

    /**
     * Compile a create database command.
     * 
     * @param  string  $name
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return string
     * 
     * @throws \LogicException
     */
    public function compileCreateDatabase($name, $connection): string
    {
        throw new LogicException('This database driver does not support creating databases');
    }
    
    /**
     * Compile a drop database if exists command.
     * 
     * @param  string  $name
     * 
     * @return string
     * 
     * @throws \LogicException
     */
    public function compileDropDatabaseIfExists($name): string
    {
        throw new LogicException('This database driver does not support dropping databases');
    }
    
    /**
     * Compile a rename column command.
     * 
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  \Syscodes\Components\Support\Flowing  $command
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return array
     */
    public function compileRenameColumn(Dataprint $dataprint, Flowing $command, Connection $connection)
    {
        //
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
        $sql = sprintf('alter table %s add constraint %s ',
            $this->wrapTable($dataprint),
            $this->wrap($command->index)
        );

        $sql .= sprintf('foreign key (%s) references %s (%s)',
            $this->columnize($command->columns),
            $this->wrapTable($command->on),
            $this->columnize($command->references)
        );
        
        // Once we have the basic foreign key creation statement constructed we can
        // build out the syntax for what should happen on an update or delete of
        // the affected columns, which will get something like "cascade", etc.
        if ( ! is_null($command->onDelete)) {
            $sql .= " on Delete {$command->onDelete}";
        }

        if ( ! is_null($command->onUpdate)) {
            $sql .= " on Delete {$command->onUpdate}";
        }

        return $sql;
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

        foreach ($dataprint->getColumns() as $column) {
            $sql = $this->wrap($column).' '.$this->getType($column);

            $columns[] = $this->addModifiers($sql, $dataprint, $column);
        }

        return $columns;
    }
    
    /**
     * Get the SQL for the column data type.
     * 
     * @param  \Syscodes\Components\Support\Flowing  $column
     * 
     * @return string
     */
    protected function getType(Flowing $column)
    {
        return $this->{'type'.ucfirst($column->type)}($column);
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
            return reset($commands);
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
     * 
     * @return string
     */
    public function wrapTable($table): string
    {
        return parent::wrapTable(
            $table instanceof Dataprint ? $table->getTable() : $table
        );
    }
    
    /**
     * Wrap a value in keyword identifiers.
     * 
     * @param  \Syscodes\Components\Database\Query\Expression|string  $value
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
            return $value;
        }
        
        return is_bool($value)
                    ? "'".(int) $value."'"
                    : "'".(string) $value."'";
    }
}