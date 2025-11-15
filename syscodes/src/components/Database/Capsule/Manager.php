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

namespace Syscodes\Components\Database\Capsule;

use Syscodes\Components\Container\Container;
use Syscodes\Components\Database\DatabaseManager;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\ConnectionFactory;
use Syscodes\Components\Database\Concerns\CapsuleManager;

/**
 * Capsules the manager database.
 */
class Manager
{
    use CapsuleManager;
    
    /**
     * The database manager instance.
     * 
     * @var \Syscodes\Components\Database\DatabaseManager $manager
     */
    protected $manager;

    /**
     * Constructor. Create a new Manager instance.
     * 
     * @param  \Syscodes\Components\Container\Container|null  $container
     * 
     * @return void
     */
    public function __construct(?Container $container = null)
    {   
        $this->getContainerManager($container ?: new Container);

        $this->getDefaultConfiguration();

        $this->getManager();
    }

    /**
     * Get the default database configuration options.
     *
     * @return void
     */
    protected function getDefaultConfiguration(): void
    {
        $this->container['config']['database.default'] = 'default';
    }

    /**
     * Get the database manager instance.
     *
     * @return void
     */
    protected function getManager(): void
    {
        $factory = new ConnectionFactory($this->container);

        $this->manager = new DatabaseManager($this->container, $factory);
    }
    
    /**
     * Get a flowing query builder instance.
     * 
     * @param  \Closure|\Syscodes\Components\Database\Query\Builder|string  $table
     * @param  string|null  $as
     * @param  string|null  $connection
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public static function table($table, ?string $as = null, ?string $connection = null)
    {
        return static::$instance->connection($connection)->table($table, $as);
    }
    
    /**
     * Get a connection instance from the global manager.
     * 
     * @param  string|null  $connection
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public static function connection(?string $connection = null)
    {
        return static::$instance->getConnection($connection);
    }
    
    /**
     * Get a registered connection instance.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public function getConnection(?string $name = null)
    {
        return $this->manager->connection($name);
    }
    
    /**
     * Register a connection with the manager.
     * 
     * @param  array  $config
     * @param  string  $name
     * 
     * @return void
     */
    public function addConnection(array $config, string $name = 'default'): void
    {
        $connections = $this->container['config']['database.connections'];
        
        $connections[$name] = $config;
        
        $this->container['config']['database.connections'] = $connections;
    }
    
    /**
     * Get the database manager instance.
     * 
     * @return \Syscodes\Components\Database\DatabaseManager
     */
    public function getDatabaseManager()
    {
        return $this->manager;
    }

    /**
     * Boot the Erostrine query for usage in any app and database.
     * 
     * @return void
     */
    public function getBootErostrine(): void
    {
        Model::setConnectionResolver($this->manager);
    }
    
    /**
     * Magic method.
     * 
     * Dynamically pass methods to the default connection.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::connection()->$method(...$parameters);
    }
}