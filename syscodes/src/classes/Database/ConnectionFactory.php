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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.3
 */

namespace Syscodes\Database;

use PDOException;
use InvalidArgumentException;
use Syscodes\Collections\Arr;
use Syscodes\Contracts\Container\Container;
use Syscodes\Database\Connectors\MysqlConnector;
use Syscodes\Database\Connectors\SQLiteConnector;
use Syscodes\Database\Connectors\PostgresConnector;
use Syscodes\Database\Connectors\SqlServerConnector;

use Syscodes\Database\Connections\MysqlConnection;
use Syscodes\Database\Connections\SQLiteConnection;
use Syscodes\Database\Connections\PostgresConnection;
use Syscodes\Database\Connections\SqlServerConnection;

/**
 * Create a new instance based on the configuration of the database.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class ConnectionFactory
{
    /**
     * The IoC container instance.
     * 
     * @var \Syscodes\Contracts\Container\Container $container
     */
    protected $container;

    /**
     * Constructor. Create a new ConnectionFactory class instance.
     * 
     * @param  \Syscodes\Contracts\Container\Container  $container
     * 
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create a connector instance based on the configuration.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Database\Connectors\ConnectorInterface
     * 
     * @throws \InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        if ( ! isset($config['driver']))
        {
            throw new InvalidArgumentException('A driver must be specified');
        }
        
        if ($this->container->bound($key = "db.connector.{$config['driver']}"))
        {
            return $this->container->make($key);
        }

        switch ($config['driver'])
        {
            case 'mysql':
                return new MysqlConnector;
            case 'pgsql':
                return new PostgresConnector;
            case 'sqlite':
                return new SQLiteConnector;
            case 'sqlsrv':
                return new SqlServerConnector;
        }

        throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
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
     * @return \Syscodes\Database\Connection
     * 
     * @throws \InvalidArgumentException
     */
    public function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($resolver = Connection::getResolver($driver))
        {
            return $resolver($connection, $database, $prefix, $config);
        }

        switch ($driver)
        {
            case 'mysql':
                return new MysqlConnection($connection, $database, $prefix, $config);
            case 'pgsql':
                return new PostgresConnection($connection, $database, $prefix, $config);
            case 'sqlite':
                return new SQLiteConnection($connection, $database, $prefix, $config);
            case 'sqlsrv':
                return new SqlServerConnection($connection, $database, $prefix, $config);
        }

        throw new InvalidArgumentException("Unsupported driver [{$driver}]");
    }
}