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
 * A PDO based MySQL Database Connector.
 */
class MySqlConnector extends Connector implements ConnectorInterface
{
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

        if ( ! empty($config['database'])) {
            $connection->exec("use `{$config['database']}`;");
        }

        $this->configureConnection($connection, $config);

        return $connection;
    }

    /**
     * Create a DSN string from a configuration. Chooses socket or host / port based on
     * the 'unix_socket' config value.
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function getDsn(array $config): string
    {
        return $this->hasSocket($config)
                    ? $this->getSocketDsn($config)
                    : $this->getHostDsn($config);
    }

    /**
     * Determine if the given configuration array has a UNIX socket value.
     * 
     * @param  array  $config
     * 
     * @return bool
     */
    protected function hasSocket(array $config): bool
    {
        return isset($config['unix_socket']) && ! empty($config['unix_socket']);
    }

    /**
     * Get the DSN string for a socket configuration.
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function getSocketDsn(array $config): string
    {
        return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
    }

    /**
     * Get the DSN string for a host / port configuration.
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function getHostDsn(array $config): string
    {
        return isset($config['port'])
                ? "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}"
                : "mysql:host={$config['host']};dbname={$config['database']}";
    }

    /**
     * Configure the given PDO connection.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureConnection(PDO $connection, array $config)
    {
        $statements = [];

        if (isset($config['charset'])) {
            if (isset($config['collation'])) {
                $statements[] = sprintf("NAMES '%s' COLLATE '%s'", $config['charset'], $config['collation']);
            } else {
                $statements[] = sprintf("NAMES '%s'", $config['charset']);
            }
        }

        if (isset($config['timezone'])) {
            $statements[] = sprintf("time_zone='%s'", $config['timezone']);
        }

        $sqlMode = $this->getSqlModes($connection, $config);

        if ($sqlMode !== null) {
            $statements[] = sprintf("SESSION sql_mode='%s'", $sqlMode);
        }

        if ($statements !== []) {
            $connection->exec(sprintf('SET %s;', implode(', ', $statements)));
        }
    }

    /**
     * Set the modes for the connection.
     * 
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return string|null
     */
    protected function getSqlModes(PDO $connection, array $config): ?string
    {
        if (isset($config['modes'])) {
            return implode(',', $config['modes']);
        }

        if ( ! isset($config['strict'])) {
            return null;
        }

        if ( ! $config['strict']) {
            return 'NO_ENGINE_SUBSTITUTION';
        }

        $version = $config['version'] ?? $connection->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (version_compare($version, '8.0.11', '>=')) {
            return 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
        }

        return 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
    }
}