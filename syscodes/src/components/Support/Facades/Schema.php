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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the schema builder class facade.
 *
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder create(string $table, \Closure $callback)
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder createDatabase(string $name)
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder drop(string $table)
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder dropDatabaseIfExists(string $name)
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder dropIfExists(string $table)
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder rename(string $from, string $to)
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder table(string $table, \Closure $callback)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static bool dropColumns(string $table, array $columns)
 * @method static bool hasTable(string $table)
 * @method static void defaultStringLength(int $length)
 * @method static array getColumnListing(string $table)
 * @method static \Syscodes\Components\Database\Connections\Connection getConnection()
 * @method static \Syscodes\Components\Database\Schema\Builders\Builder setConnection(\Syscodes\Components\Database\Connections\Connection $connection)
 *
 * @see \Syscodes\Components\Database\Schema\Builders\Builder
 */
class Schema extends Facade
{
    /**
     * Indicates if the resolved facade should be cached.
     * 
     * @var bool $cached
     */
    protected static $cached = false;
    
    /**
     * Get a schema builder instance for a connection.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Database\Schema\Builders\Builder
     */
    public static function connection($name)
    {
        return static::$applications['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'db.schema';
    }
}