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

namespace Syscodes\Components\Database\Schema;

use Closure;
use BadMethodCallException;
use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Connections\SQLiteConnection;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Database\Schema\Builders\Builder;
use Syscodes\Components\Database\Schema\Grammars\Grammar;
use Syscodes\Components\Support\Flowing;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Extracts the column names to using in creating migrations.
 */
class Dataprint
{
    use Macroable;
    
    /**
     * The column to add new columns after.
     * 
     * @var string $after
     */
    public $after;

    /**
     * The default character set that should be used for the table.
     * 
     * @var string $charset
     */
    public $charset;
    
    /**
     * The collation that should be used for the table.
     * 
     * @var string $collation
     */
    public $collation;

    /**
     * The columns that should be added to the table.
     * 
     * @var array $columns
     */
    protected $columns = [];

    /**
     * The commands that should be run for the table.
     * 
     * @var array $commands
     */
    protected $commands = [];

    /**
     * The storage engine that should be used for the table.
     * 
     * @var string $engine
     */
    public $engine;

    /**
     * The prefix of the table.
     * 
     * @var string $prefix
     */
    protected $prefix;

    /**
     * The table the blueprint describes.
     * 
     * @var string $table
     */
    protected $table;
    
    /**
     * Whether to make the table temporary.
     * 
     * @var bool $temporary
     */
    public $temporary = false;

    /**
     * Constructor. Create a new schema Dataprint instance.
     * 
     * @param  string  $table
     * @param  \Closure|null  $callback
     * @param  string  $prefix
     * 
     * @return void
     */
    public function __construct($table, ?Closure $callback = null, $prefix = '')
    {
        $this->table = $table;
        $this->prefix = $prefix;

        if ( ! is_null($callback)) $callback($this);
    }

    /**
     * Execute the dataprint against the database.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  \Syscodes\Components\Database\Schema\Grammars\Grammar  $grammar
     * 
     * @return void
     */
    public function build(Connection $connection, Grammar $grammar): void
    {
        foreach ($this->toSql($connection, $grammar) as $statement) {
            $connection->statement($statement);
        }
    }

    /**
     * Get the raw SQL statements for the blueprint.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  \Syscodes\Components\Database\Schema\Grammars\Grammar  $grammar
     * 
     * @return array
     */
    public function toSql(Connection $connection, Grammar $grammar): array
    {
        $this->addImpliedCommands();

        $statements = [];

        $this->ensureCommandsAreValid($connection);

        foreach ($this->commands as $command) {
            $method = 'compile'.ucfirst($command->name);

            if (method_exists($grammar, $method) || $grammar::hasMacro($method)) {
                if ( ! is_null($sql = $grammar->$method($this, $command, $connection))) {
                    $statements[] = array_merge($statements, (array) $sql);
                }
            }
        }

        return $statements;
    }
    
    /**
     * Ensure the commands on the dataprint are valid for the connection type.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * 
     * @return void
     * 
     * @throws \BadMethodCallException
     */
    protected function ensureCommandsAreValid(Connection $connection): void
    {
        if ($connection instanceof SQLiteConnection) {
            if ($this->commandsNamed(['dropColumn', 'renameColumn'])->count() > 1) {
                throw new BadMethodCallException(
                    "SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification"
                );
            }
            
            if ($this->commandsNamed(['dropForeign'])->count() > 0) {
                throw new BadMethodCallException(
                    "SQLite doesn't support dropping foreign keys (you would need to re-create the table)"
                );
            }
        }
    }
    
    /**
     * Get all of the commands matching the given names.
     * 
     * @param  array  $names
     * 
     * @return \Syscodes\Components\Collections\Collection
     */
    protected function commandsNamed(array $names)
    {
        return collect($this->commands)->filter(fn ($command) => in_array($command->name, $names));
    }

    /**
     * Add the commands that are implied by the data print.
     * 
     * @return void
     */
    protected function addImpliedCommands(): void
    {
        if (count($this->getAddedColumns()) > 0 && ! $this->creating()) {
            array_unshift($this->commands, $this->createCommand('add'));
        }

        if (count($this->getChangedColumns()) > 0 && ! $this->creating()) {
            array_unshift($this->commands, $this->createCommand('change'));
        }

        $this->addFlowingIndexes();
    }

    /**
     * Add the index commands fluently specified on columns.
     * 
     * @return void
     */
    protected function addFlowingIndexes(): void
    {
        foreach ($this->columns as $column) {
            foreach (['primary', 'unique', 'index', 'fulltext', 'fullText', 'spatialIndex'] as $index) {
                if ($column->{$index} === true) {
                    $this->{$index}($column->name);

                    $column->{$index} = false;
                    
                    continue 2;
                } elseif (isset($column->{$index})) {
                    $this->{$index}($column->name, $column->{$index});

                    $column->{$index} = false;
                    
                    continue 2;
                }
            }
        }
    }
    
    /**
     * Determine if the data print has a create command.
     * 
     * @return bool
     */
    public function creating(): bool
    {
        return collect($this->commands)->contains(fn ($command) => $command->name === 'create');
    }
    
    /**
     * Indicate that the table needs to be created.
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function create()
    {
        return $this->addCommand('create');
    }
    
    /**
     * Indicate that the table needs to be temporary.
     * 
     * @return void
     */
    public function temporary(): void
    {
        $this->temporary = true;
    }
    
    /**
     * Indicate that the table should be dropped.
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function drop()
    {
        return $this->addCommand('drop');
    }
    
    /**
     * Indicate that the table should be dropped if it exists.
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropIfExists()
    {
        return $this->addCommand('dropIfExists');
    }
    
    /**
     * Indicate that the given columns should be dropped.
     * 
     * @param  string|array  $columns
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropColumn($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        
        return $this->addCommand('dropColumn', compact('columns'));
    }
    
    /**
     * Indicate that the given columns should be renamed.
     * 
     * @param  string  $from
     * @param  string  $to
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function renameColumn($from, $to)
    {
        return $this->addCommand('renameColumn', compact('from', 'to'));
    }
    
    /**
     * Indicate that the given primary key should be dropped.
     * 
     * @param  string|array|null  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropPrimary($index = null)
    {
        return $this->dropIndexCommand('dropPrimary', 'primary', $index);
    }
    
    /**
     * Indicate that the given unique key should be dropped.
     * 
     * @param  string|array  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropUnique($index)
    {
        return $this->dropIndexCommand('dropUnique', 'unique', $index);
    }
    
    /**
     * Indicate that the given index should be dropped.
     * 
     * @param  string|array  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropIndex($index)
    {
        return $this->dropIndexCommand('dropIndex', 'index', $index);
    }
    
    /**
     * Indicate that the given fulltext index should be dropped.
     * 
     * @param  string|array  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropFullText($index)
    {
        return $this->dropIndexCommand('dropFullText', 'fulltext', $index);
    }
    
    /**
     * Indicate that the given spatial index should be dropped.
     * 
     * @param  string|array  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropSpatialIndex($index)
    {
        return $this->dropIndexCommand('dropSpatialIndex', 'spatialIndex', $index);
    }
    
    /**
     * Indicate that the given foreign key should be dropped.
     * 
     * @param  string|array  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function dropForeign($index)
    {
        return $this->dropIndexCommand('dropForeign', 'foreign', $index);
    }
    
    /**
     * Indicate that the timestamp columns should be dropped.
     * 
     * @return void
     */
    public function dropTimestamps(): void
    {
        $this->dropColumn('created_at', 'updated_at');
    }
    
    /**
     * Indicate that the timestamp columns should be dropped.
     * 
     * @return void
     */
    public function dropTimestampsTz(): void
    {
        $this->dropTimestamps();
    }
    
    /**
     * Indicate that the soft delete column should be dropped.
     * 
     * @param  string  $column
     * 
     * @return void
     */
    public function dropSoftDeletes($column = 'deleted_at'): void
    {
        $this->dropColumn($column);
    }
    
    /**
     * Rename the table to a given name.
     * 
     * @param  string  $to
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function rename($to)
    {
        return $this->addCommand('rename', compact('to'));
    }
    
    /**
     * Indicate that the given indexes should be renamed.
     * 
     * @param  string  $from
     * @param  string  $to
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function renameIndex($from, $to)
    {
        return $this->addCommand('renameIndex', compact('from', 'to'));
    }

    /**
     * Specify the primary key(s) for the table.
     * 
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $option
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function primary($columns, $name = null, $option = null)
    {
        return $this->indexCommand('primary', $columns, $name, $option);
    }
    
    /**
     * Specify a unique index for the table.
     * 
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $option
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function unique($columns, $name = null, $option = null)
    {
        return $this->indexCommand('unique', $columns, $name, $option);
    }
    
    /**
     * Specify an index for the table.
     * 
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $option
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function index($columns, $name = null, $option = null)
    {
        return $this->indexCommand('index', $columns, $name, $option);
    }
    
    /**
     * Specify an fulltext for the table.
     * 
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $option
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function fullText($columns, $name = null, $option = null)
    {
        return $this->indexCommand('fulltext', $columns, $name, $option);
    }
    
    /**
     * Specify a spatial index for the table.
     * 
     * @param  string|array  $columns
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function spatialIndex($columns, $name = null)
    {
        return $this->indexCommand('spatialIndex', $columns, $name);
    }
    
    /**
     * Specify a raw index for the table.
     * 
     * @param  string  $expression
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function rawIndex($expression, $name)
    {
        return $this->index([new Expression($expression)], $name);
    }
    
    /**
     * Specify a foreign key for the table.
     * 
     * @param  string|array  $columns
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    public function foreign($columns, $name = null)
    {
        return $this->indexCommand('foreign', $columns, $name);
    }
    
    /**
     * Create a new auto-incrementing big integer (8-byte) column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function id($column = 'id')
    {
        return $this->bigIncrements($column);
    }
    
    /**
     * Create a new auto-incrementing integer (4-byte) column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function increments($column)
    {
        return $this->unsignedInteger($column, true);
    }
    
    /**
     * Create a new auto-incrementing integer (4-byte) column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function integerIncrements($column)
    {
        return $this->unsignedInteger($column, true);
    }
    
    /**
     * Create a new auto-incrementing tiny integer (1-byte) column on the table.
     *
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function tinyIncrements($column)
    {
        return $this->unsignedTinyInteger($column, true);
    }
    
    /**
     * Create a new auto-incrementing small integer (2-byte) column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function smallIncrements($column)
    {
        return $this->unsignedSmallInteger($column, true);
    }
    
    /**
     * Create a new auto-incrementing medium integer (3-byte) column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function mediumIncrements($column)
    {
        return $this->unsignedMediumInteger($column, true);
    }
    
    /**
     * Create a new auto-incrementing big integer (8-byte) column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function bigIncrements($column)
    {
        return $this->unsignedBigInteger($column, true);
    }
    
    /**
     * Create a new char column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $length
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function char($column, $length = null)
    {
        $length = ! is_null($length) ? $length : Builder::$defaultStringLength;
        
        return $this->addColumn('char', $column, compact('length'));
    }
    
    /**
     * Create a new string column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $length
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function string($column, $length = null)
    {
        $length = $length ?: Builder::$defaultStringLength;
        
        return $this->addColumn('string', $column, compact('length'));
    }
    
    /**
     * Create a new tiny text column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function tinyText($column)
    {
        return $this->addColumn('tinyText', $column);
    }
    
    /**
     * Create a new text column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function text($column)
    {
        return $this->addColumn('text', $column);
    }
    
    /**
     * Create a new medium text column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function mediumText($column)
    {
        return $this->addColumn('mediumText', $column);
    }
    
    /**
     * Create a new long text column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function longText($column)
    {
        return $this->addColumn('longText', $column);
    }
    
    /**
     * Create a new integer (4-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function integer($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
    }
    
    /**
     * Create a new tiny integer (1-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function tinyInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('tinyInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    
    /**
     * Create a new small integer (2-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function smallInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('smallInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    
    /**
     * Create a new medium integer (3-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function mediumInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('mediumInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    
    /**
     * Create a new big integer (8-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function bigInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    
    /**
     * Create a new unsigned integer (4-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedInteger($column, $autoIncrement = false)
    {
        return $this->integer($column, $autoIncrement, true);
    }

    /**
     * Create a new unsigned tiny integer (1-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedTinyInteger($column, $autoIncrement = false)
    {
        return $this->tinyInteger($column, $autoIncrement, true);
    }
    
    /**
     * Create a new unsigned small integer (2-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedSmallInteger($column, $autoIncrement = false)
    {
        return $this->smallInteger($column, $autoIncrement, true);
    }
    
    /**
     * Create a new unsigned medium integer (3-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedMediumInteger($column, $autoIncrement = false)
    {
        return $this->mediumInteger($column, $autoIncrement, true);
    }
    
    /**
     * Create a new unsigned big integer (8-byte) column on the table.
     * 
     * @param  string  $column
     * @param  bool  $autoIncrement
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedBigInteger($column, $autoIncrement = false)
    {
        return $this->bigInteger($column, $autoIncrement, true);
    }
    
    /**
     * Create a new float column on the table.
     * 
     * @param  string  $column
     * @param  int  $total
     * @param  int  $places
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function float($column, $total = 8, $places = 2, $unsigned = false)
    {
        return $this->addColumn('float', $column, compact('total', 'places', 'unsigned'));
    }
    
    /**
     * Create a new double column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $total
     * @param  int|null  $places
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function double($column, $total = null, $places = null, $unsigned = false)
    {
        return $this->addColumn('double', $column, compact('total', 'places', 'unsigned'));
    }
    
    /**
     * Create a new decimal column on the table.
     * 
     * @param  string  $column
     * @param  int  $total
     * @param  int  $places
     * @param  bool  $unsigned
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function decimal($column, $total = 8, $places = 2, $unsigned = false)
    {
        return $this->addColumn('decimal', $column, compact('total', 'places', 'unsigned'));
    }
    
    /**
     * Create a new unsigned float column on the table.
     * 
     * @param  string  $column
     * @param  int  $total
     * @param  int  $places
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedFloat($column, $total = 8, $places = 2)
    {
        return $this->float($column, $total, $places, true);
    }
    
    /**
     * Create a new unsigned double column on the table.
     * 
     * @param  string  $column
     * @param  int  $total
     * @param  int  $places
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedDouble($column, $total = null, $places = null)
    {
        return $this->double($column, $total, $places, true);
    }
    
    /**
     * Create a new unsigned decimal column on the table.
     * 
     * @param  string  $column
     * @param  int  $total
     * @param  int  $places
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function unsignedDecimal($column, $total = 8, $places = 2)
    {
        return $this->decimal($column, $total, $places, true);
    }
    
    /**
     * Create a new boolean column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function boolean($column)
    {
        return $this->addColumn('boolean', $column);
    }
    
    /**
     * Create a new enum column on the table.
     *
     * @param  string  $column
     * @param  array  $allowed
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function enum($column, array $allowed)
    {
        return $this->addColumn('enum', $column, compact('allowed'));
    }
    
    /**
     * Create a new set column on the table.
     * 
     * @param  string  $column
     * @param  array  $allowed
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function set($column, array $allowed)
    {
        return $this->addColumn('set', $column, compact('allowed'));
    }
    
    /**
     * Create a new json column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function json($column)
    {
        return $this->addColumn('json', $column);
    }
    
    /**
     * Create a new jsonb column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function jsonb($column)
    {
        return $this->addColumn('jsonb', $column);
    }
    
    /**
     * Create a new date column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function date($column)
    {
        return $this->addColumn('date', $column);
    }
    
    /**
     * Create a new date-time column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function dateTime($column, $precision = 0)
    {
        return $this->addColumn('dateTime', $column, compact('precision'));
    }
    
    /**
     * Create a new date-time column (with time zone) on the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function dateTimeTz($column, $precision = 0)
    {
        return $this->addColumn('dateTimeTz', $column, compact('precision'));
    }
    
    /**
     * Create a new time column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function time($column, $precision = 0)
    {
        return $this->addColumn('time', $column, compact('precision'));
    }
    
    /**
     * Create a new time column (with time zone) on the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function timeTz($column, $precision = 0)
    {
        return $this->addColumn('timeTz', $column, compact('precision'));
    }
    
    /**
     * Create a new timestamp column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function timestamp($column, $precision = 0)
    {
        return $this->addColumn('timestamp', $column, compact('precision'));
    }
    
    /**
     * Create a new timestamp (with time zone) column on the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function timestampTz($column, $precision = 0)
    {
        return $this->addColumn('timestampTz', $column, compact('precision'));
    }
    
    /**
     * Add nullable creation and update timestamps to the table.
     * 
     * @param  int|null  $precision
     * 
     * @return void
     */
    public function timestamps($precision = 0): void
    {
        $this->timestamp('created_at', $precision)->nullable();
        
        $this->timestamp('updated_at', $precision)->nullable();
    }
    
    /**
     * Add nullable creation and update timestamps to the table.
     * 
     * Alias for self::timestamps().
     * 
     * @param  int|null  $precision
     * 
     * @return void
     */
    public function nullableTimestamps($precision = 0): void
    {
        $this->timestamps($precision);
    }
    
    /**
     * Add creation and update timestampTz columns to the table.
     * 
     * @param  int|null  $precision
     * 
     * @return void
     */
    public function timestampsTz($precision = 0): void
    {
        $this->timestampTz('created_at', $precision)->nullable();
        
        $this->timestampTz('updated_at', $precision)->nullable();
    }
    
    /**
     * Add a "deleted at" timestamp for the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     *
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function softDeletes($column = 'deleted_at', $precision = 0)
    {
        return $this->timestamp($column, $precision)->nullable();
    }
    
    /**
     * Add a "deleted at" timestampTz for the table.
     * 
     * @param  string  $column
     * @param  int|null  $precision
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function softDeletesTz($column = 'deleted_at', $precision = 0)
    {
        return $this->timestampTz($column, $precision)->nullable();
    }
    
    /**
     * Create a new year column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function year($column)
    {
        return $this->addColumn('year', $column);
    }
    
    /**
     * Create a new binary column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function binary($column)
    {
        return $this->addColumn('binary', $column);
    }
    
    /**
     * Create a new uuid column on the table.
     * 
     * @param  string  $column
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    public function uuid($column = 'uuid')
    {
        return $this->addColumn('uuid', $column);
    }
    
    /**
     * Create a new drop index command on the data print.
     * 
     * @param  string  $command
     * @param  string  $type
     * @param  string|array  $index
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    protected function dropIndexCommand($command, $type, $index)
    {
        $columns = [];

        if (is_array($index)) {
            $columns = $index;
            
            $index = $this->createIndexName($type, $columns);
        }
        
        return $this->indexCommand($command, $columns, $index);
    }
    
    /**
     * Add a new index command to the data print.
     * 
     * @param  string  $type
     * @param  string|array  $columns
     * @param  string  $index
     * @param  string|null  $option
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    protected function indexCommand($type, $columns, $index, $option = null)
    {
        $columns = (array) $columns;
        
        $index = $index ?: $this->createIndexName($type, $columns);
        
        return $this->addCommand(
            $type, compact('index', 'columns', $option)
        );
    }
    
    /**
     * Create a default index name for the table.
     * 
     * @param  string  $type
     * @param  array  $columns
     * 
     * @return string
     */
    protected function createIndexName($type, array $columns): string
    {
        $index = strtolower($this->prefix.$this->table.'_'.implode('_', $columns).'_'.$type);
        
        return str_replace(['-', '.'], '_', $index);
    }
    
    /**
     * Add a new column to the data print.
     * 
     * @param  string  $type
     * @param  string  $name
     * @param  array   $parameters
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    protected function addColumn($type, $name, array $parameters = [])
    {
        $attributes = array_merge(compact('type', 'name'), $parameters);
        
        return $this->addColumnDefinition(new ColumnDefinition($attributes));
    }
    
    /**
     * Add a new column definition to the dataprint.
     * 
     * @param  \Syscodes\Components\Database\Schema\ColumnDefinition  $definition
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition
     */
    protected function addColumnDefinition($definition)
    {
        $this->columns[] = $definition;
        
        if ($this->after) {
            $definition->after($this->after);
            
            $this->after = $definition->name;
        }
        
        return $definition;
    }
    
    /**
     * Remove a column from the schema data print.
     * 
     * @param  string  $name
     * 
     * @return static
     */
    public function removeColumn($name): static
    {
        $this->columns = array_values(array_filter($this->columns, fn ($column) => $column['name'] != $name));
        
        return $this;
    }
    
    /**
     * Add a new command to the data print.
     * 
     * @param  string  $name
     * @param  array  $parameters
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    protected function addCommand($name, array $parameters = array())
    {
        $this->commands[] = $command = $this->createCommand($name, $parameters);
        
        return $command;
    }
    
    /**
     * Create a new Flowing command.
     * 
     * @param  string  $name
     * @param  array   $parameters
     * 
     * @return \Syscodes\Components\Support\Flowing
     */
    protected function createCommand($name, array $parameters = array())
    {
        return new Flowing(array_merge(compact('name'), $parameters));
    }
    
    /**
     * Get the table the data print describes.
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get the columns that should be added.
     * 
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    /**
     * Get the commands on the data print.
     * 
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
    
    /**
     * Get the columns on the data print that should be added.
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition[]
     */
    public function getAddedColumns()
    {
        return array_filter($this->columns, fn ($column) => ! $column->change);
    }
    
    /**
     * Get the columns on the data print that should be changed.
     * 
     * @return \Syscodes\Components\Database\Schema\ColumnDefinition[]
     */
    public function getChangedColumns()
    {
        return array_filter($this->columns, fn ($column) => (bool) $column->change);
    }
}