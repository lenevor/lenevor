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
use Syscodes\Components\Database\Concerns\ParsesSearchPath;

/**
 * A PDO based PostgreSQL Database Connector.
 */
class PostgresConnector extends Connector implements ConnectorInterface
{
    use ParsesSearchPath;

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

        // First we'll create the basic DSN and connection instance connecting to the
        // using the configuration option specified by the developer.
        $connection = $this->createConnection($dsn, $config, $options);

        $this->configureEncoding($connection, $config);

        $this->configureTimezone($connection, $config);

        $this->configureSearchPath($connection, $config);

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
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options.
        extract($config, EXTR_SKIP);

        $host = isset($host) ? "host={$host};" : '';

        // Sometimes - users may need to connect to a database that has a different
        // name than the database used for "information_schema" queries.
        $database = $connect_via_database ?? $database ?? null;

        $port = $connect_via_port ?? $port ?? null;

        $dsn = "pgsql:{$host}dbname='{$database}'";

        // If a port was specified, we will add it to this Postgres DSN 
        // connections format.
        if ( ! is_null($port)) {
            $dsn .= ";port={$port}";
        }

        if (isset($charset)) {
            $dsn .= ";client_encoding='{$charset}'";
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
        foreach (['sslmode', 'sslcert', 'sslkey', 'sslrootcert'] as $option) {
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
     * Get the "search_path" on the connection.
     * 
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureSearchPath($connection, array $config): void
    {
        if (isset($config['search_path']) || isset($config['schema'])) {
            $searchPath = $this->quoteSearchPath(
                $this->parseSearchPath($config['search_path'] ?? $config['schema'])
            );

            $connection->prepare("set search_path to {$searchPath}")->execute();
        }
    }

    /**
     * Format the search path for the DSN.
     *
     * @param  array  $searchPath
     * 
     * @return string
     */
    protected function quoteSearchPath($searchPath): string
    {
        return count($searchPath) === 1 ? '"'.$searchPath[0].'"' : '"'.implode('", "', $searchPath).'"';
    }
}