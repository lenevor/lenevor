<?php 

/**
 * Lenevor PHP Framework
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

use PDO;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Database\Connections\Connection;

/**
 * It is used to instantiate the connection and its respective settings.
 */
class DatabaseManager implements ConnectionResolverInterface
{
    /**
     * The appilcation instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * The active connection instances.
     * 
     * @var array $connections
     */
    protected $connections = [];

    /**
     * The database connection factory instance.
     * 
     * @var \Syscodes\Components\Database\ConnectionFactory $factory
     */
    protected $factory;

    /**
     * The custom connection resolvers.
     * 
     * @var array $extensions
     */
    protected $extensions = [];

    /**
     * Constructor. Create a new DatabaseManager instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * @param  \Syscodes\Components\Database\ConnectionFactory  $factory
     * 
     * @return void
     */
    public function __construct($app, ConnectionFactory $factory)
    {
        $this->app     = $app;
        $this->factory = $factory;
    }
    
    /**
     * Get a Database Connection instance.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public function connection($name = null)
    {
        [$database, $type] = $this->parseConnectionName($name);

        $name = $name ?: $database;

        if ( ! isset($this->connections[$name])) {
            $connection = $this->makeConnection($name);

            $this->connections[$name] = $this->configure($connection, $type);
        }

        return $this->connections[$name];
    }

    /**
     * Parse the connection into an array of the name and read / write type.
     * 
     * @param  string  $name
     * 
     * @return array 
     */
    protected function parseConnectionName($name): array
    {
        $name = $name ?: $this->getDefaultConnection();

        return Str::endsWith($name, ['::read', '::write'])
                ? explode('::', $name, 2)
                : [$name, null];
    }

    /**
     * Make the database connection instance.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Database\Connections\Connection 
     */
    protected function makeConnection($name)
    {
        $config = $this->getConfiguration($name);

        if (isset($this->extensions[$name])) {
            return call_user_func($this->extensions[$name], $config, $name);
        }

        $driver = $config['driver'];

        if (isset($this->extensions[$driver])) {
            return call_user_func($this->extensions[$driver], $config, $name);
        }

        return $this->factory->make($config, $name);
    }

    /**
     * Get the configuration for a connection.
     * 
     * @param  string  $name
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    protected function getConfiguration($name): array
    {
        $name = $name ?: $this->getDefaultConnection();

        $connections = $this->app['config']['database.connections'];

        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("Database connection [{$name}] not configured");
        }

        return $config;
    }

    /**
     * Prepare the database connection instance.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  string  $type
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    protected function configure(Connection $connection, $type)
    {
        $connection = $this->setPdoForType($connection, $type);

        if ($this->app->bound('events')) {
            $connection->setEventDispatcher($this->app['events']);
        }

        $connection->setReconnector(function ($connection) {
            $this->reconnect($connection->getName());
        });

        return $connection;
    }

    /**
     * Prepare the read / write mode for database connection instance.
     * 
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  string|null  $type
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    protected function setPdoForType(Connection $connection, $type)
    {
        if ($type === 'read') {
            $connection->setPdo($connection->getReadPdo());
        } elseif ($type === 'write') {
            $connection->setReadPdo($connection->getPdo());
        }

        return $connection;
    }

    /**
     * Reconnect to the given database.
     * 
     * @param  string|null  $name  
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    public function reconnect($name = null)
    {
        $this->disconnect($name = $name ?: $this->getDefaultConnection());

        if ( ! isset($this->connections[$name])) {
            return $this->connection($name);
        }

        return $this->refreshPdoConnections($name);
    }

    /**
     * Disconnect from the given database.
     * 
     * @param  string|null  $name  
     * 
     * @return void
     */
    public function disconnect($name = null)
    {
        if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()])) {
            $this->connections[$name]->disconnect();
        }
    }

    /**
     * Refresh the PDO connections on a given connection.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    protected function refreshPdoConnections($name)
    {
        $fresh = $this->makeConnection($name);

        return $this->connections[$name]
                ->setPdo($fresh->getRawPdo())
                ->setReadPdo($fresh->getRawReadPdo());
    }

    /**
     * Disconnect from the given database and remove from local cache.
     * 
     * @param  string|null  $name  
     * 
     * @return void
     */
    public function purge($name = null): void
    {
        $name = $name ?: $this->getDefaultConnection();

        $this->disconnect($name);

        unset($this->connections[$name]);
    }

    /**
     * Get the default Connection name.
     * 
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->app['config']['database.default'];
    }

    /**
     * Set the default Connection name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultConnection($name): void
    {
        $this->app['config']['database.default'] = $name;
    }

    /**
     * Register an extension connection resolver.
     * 
     * @param  string  $name
     * @param  \Callable  $resolver
     * 
     * @return void
     */
    public function extend($name, Callable $resolver): void
    {
        $this->extensions[$name] = $resolver;
    }

    /**
     * Return all of the created connections.
     * 
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
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
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}