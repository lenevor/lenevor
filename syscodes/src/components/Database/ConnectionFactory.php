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

namespace Syscodes\Components\Database;

use InvalidArgumentException;
use Syscodes\Components\Container\Container;
use Syscodes\Components\Support\Arr;
// Connector
use Syscodes\Components\Database\Connectors\MariaDbConnector;
use Syscodes\Components\Database\Connectors\MySqlConnector;
use Syscodes\Components\Database\Connectors\PostgresConnector;
use Syscodes\Components\Database\Connectors\SQLiteConnector;
use Syscodes\Components\Database\Connectors\SqlServerConnector;
// Connection
use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Connection\MariaDbConnection;
use Syscodes\Components\Database\Connections\MySqlConnection;
use Syscodes\Components\Database\Connections\PostgresConnection;
use Syscodes\Components\Database\Connections\SQLiteConnection;
use Syscodes\Components\Database\Connections\SqlServerConnection;

/**
 * Create a new instance based on the configuration of the database.
 */
class ConnectionFactory
{
    /**
     * The IoC container instance.
     * 
     * @var \Syscodes\Components\Container\Container
     */
    protected $container;

    /**
     * Constructor. Create a new ConnectionFactory class instance.
     * 
     * @param  \Syscodes\Components\Container\Container
     * 
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Establish a PDO connection based on the configuration.
     * 
     * @param  array   $config
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Database\Connection
     */
    public function make(array $config, $name = null)
    {
        $config = $this->parseConfig($config, $name);
        
        return isset($config['read'])
                    ? $this->createReadWriteConnection($config)
                    : $this->createSingleConnection($config);
    }
    
    /**
     * Create a read / write database connection instance.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Database\Connection
     */
    protected function createReadWriteConnection(array $config)
    {
        $connection = $this->createSingleConnection($this->getWriteConfig($config));
        
        return $connection->setReadPdo($this->createReadPdo($config));
    }
    
    /**
     * Create a single database connection instance.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Database\Connection
     */
    protected function createSingleConnection(array $config)
    {
        $pdo = $this->createConnector($config)->connect($config);
        
        return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
    }

    /**
     * Create a new PDO instance for reading.
     * 
     * @param  array  $config
     * 
     * @return \PDO
     */
    protected function createReadPdo(array $config)
    {
        $readConfig = $this->getReadConfig($config);
        
        return $this->createConnector($readConfig)->connect($readConfig);
    }
    
    /**
     * Get the read configuration for a read / write connection.
     * 
     * @param  array  $config
     * 
     * @return array
     */
    protected function getReadConfig(array $config): array
    {
        $readConfig = $this->getReadWriteConfig($config, 'read');
        
        return $this->mergeReadWriteConfig($config, $readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     * 
     * @param  array  $config
     * 
     * @return array
     */
    protected function getWriteConfig(array $config): array
    {
        $writeConfig = $this->getReadWriteConfig($config, 'write');
        
        return $this->mergeReadWriteConfig($config, $writeConfig);
    }

    /**
     * Get a read / write level configuration.
     * 
     * @param  array  $config
     * @param  string  $type
     * 
     * @return array
     */
    protected function getReadWriteConfig(array $config, $type): array
    {
        return isset($config[$type][0])
                    ? $config[$type][array_rand($config[$type])]
                    : $config[$type];
    }

    /**
     * Merge a configuration for a read / write connection.
     * 
     * @param  array  $config
     * @param  array  $merge
     * 
     * @return array
     */
    protected function mergeReadWriteConfig(array $config, array $merge): array
    {
        return Arr::except(array_merge($config, $merge), ['read', 'write']);
    }

    /**
     * Parse and prepare the database configuration.
     * 
     * @param  array  $config
     * @param  string  $name
     * 
     * @return array
     */
    protected function parseConfig(array $config, $name): array
    {
        return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
    }

    /**
     * Create a connector instance based on the configuration.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Database\Connectors\ConnectorInterface
     * 
     * @throws \InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        if ( ! isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified');
        }
        
        if ($this->container->bound($key = "db.connector.{$config['driver']}")) {
            return $this->container->make($key);
        }

        return match ($config['driver']) {
            'mysql' => new MySqlConnector,
            'mariadb' => new MariaDbConnector,
            'pgsql' => new PostgresConnector,
            'sqlite' => new SQLiteConnector,
            'sqlsrv' => new SqlServerConnector,
            default => throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]"),
        };
    }

    /**
     * Create a new connection instance.
     * 
     * @param  string  $driver
     * @param  \PDO|\Closure  $connection
     * @param  string  $database
     * @param  string  $prefix
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Database\Connection
     * 
     * @throws \InvalidArgumentException
     */
    public function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($resolver = Connection::getResolver($driver)) {
            return $resolver($connection, $database, $prefix, $config);
        }

        return match ($driver) {
            'mysql' => new MySqlConnection($connection, $database, $prefix, $config),
            'mariadb' => new MariaDbConnection($connection, $database, $prefix, $config),
            'pgsql' => new PostgresConnection($connection, $database, $prefix, $config),
            'sqlite' => new SQLiteConnection($connection, $database, $prefix, $config),
            'sqlsrv' => new SqlServerConnection($connection, $database, $prefix, $config),
            default => throw new InvalidArgumentException("Unsupported driver [{$driver}]"),
        };
    }
}