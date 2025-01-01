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

namespace Syscodes\Components\Database;

/**
 * Implements the functions that allow generate a connection to database.
 */
interface ConnectionResolverInterface
{
    /**
     * Get a Database Connection instance.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public function connection($name = null);

    /**
     * Reconnect to the given database.
     * 
     * @param  string|null  $name  
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public function reconnect($name = null);

    /**
     * Disconnect from the given database.
     * 
     * @param  string|null  $name  
     * 
     * @return void
     */
    public function disconnect($name = null);

    /**
     * Disconnect from the given database and remove from local cache.
     * 
     * @param  string|null  $name  
     * 
     * @return void
     */
    public function purge($name = null): void;
    
    /**
     * Get the default Connection name.
     * 
     * @return string
     */
    public function getDefaultConnection(): string;
    
    /**
     * Set the default Connection name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultConnection($name): void;

    /**
     * Register an extension connection resolver.
     * 
     * @param  string  $name
     * @param  \Callable  $resolver
     * 
     * @return void
     */
    public function extend($name, Callable $resolver): void;

    /**
     * Return all of the created connections.
     * 
     * @return array
     */
    public function getConnections(): array;
}