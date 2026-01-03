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

namespace Syscodes\Components\Database\Connectors;

use PDO;

/**
 * A PDO based PostgreSQL Database Connector.
 */
class PostgresConnector extends Connector implements ConnectorInterface
{
    /**
     * The default PDO connection options.
     * 
     * @var array $options
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Establish a database connection.
     * 
     * @param  array  $config
     * 
     * @return \PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        $connection = $this->createConnection($dsn, $config, $options);

        $this->configureEncoding($connection, $config);

        $this->configureTimezone($connection, $config);

        $this->configureSchema($connection, $config);

        return $connection;
    }

     /**
     * Create a DSN string from a configuration.
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function getDsn(array $config): string
    {
        extract($config, EXTR_SKIP);

        $host = isset($config['host']) ? "host={$config['host']}" : '';

        $dsn = "pgsql:{$host}";

        if (isset($config['database'])) {
            $dsn .= ";dbname={$config['database']}";
        }

        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        return $this->addSslOptions($dsn, $config);
    }

    /**
     * Add the SSL options to the DSN.
     *
     * @param  string  $dsn
     * @param  array  $config
     * 
     * @return string
     */
    protected function addSslOptions($dsn, array $config): string
    {
        foreach (['sslmode', 'sslcert', 'sslkey', 'sslrootcert'] as $option)
        {
            if (isset($config[$option])) {
                $dsn .= ";{$option}={$config[$option]}";
            }
        }

        return $dsn;
    }

    /**
     * Set the connection character set and collation.
     * 
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureEncoding($connection, array $config)
    {
        if ( ! isset($config['charset'])) {
            return;
        }

        $connection->prepare("set names '{$config['charset']}'")->execute();
    }

    /**
     * Get the timezone on the connection.
     * 
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureTimezone($connection, array $config): void
    {
        if (isset($config['timezone'])) {
            $connection->prepare("set time zone {$config['timezone']}")->execute();
        }
    }

    /**
     * Get the schema on the connection.
     * 
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureSchema($connection, array $config): void
    {
        if (isset($config['schema'])) {
            $connection->prepare("set search_path to {$config['schema']}")->execute();
        }
    }
}