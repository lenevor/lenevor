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

use InvalidArgumentException;
use Syscodes\Components\Database\Exceptions\SQLiteDatabaseDoesNotExistException;

/**
 * A PDO based SQLite Database Connector.
 */
class SQLiteConnector extends Connector implements ConnectorInterface
{
    /**
     * Establish a database connection.
     * 
     * @param  array  $config
     * 
     * @return \PDO
     * 
     * @throws \InvalidArgumentException
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        $path = $this->parseDatabasePath($config['database']);

        $connection = $this->createConnection("sqlite:{$path}", $config, $options);

        $this->configurePragmas($connection, $config);
        $this->configureForeignKeyConstraints($connection, $config);
        $this->configureBusyTimeout($connection, $config);
        $this->configureJournalMode($connection, $config);

        return $connection;
    }

    /**
     * Get the absolute database path.
     *
     * @param  string  $path
     * 
     * @return string
     *
     * @throws \Syscodes\Components\Database\SQLiteDatabaseDoesNotExistException
     */
    protected function parseDatabasePath(string $path): string
    {
        $database = $path;

        // SQLite supports "in-memory" databases that only last as long as the owning
        // connection does. These are useful for tests or for short lifetime store
        // querying.
        if ($path === ':memory:' ||
            str_contains($path, '?mode=memory') ||
            str_contains($path, '&mode=memory')
        ) {
            return $path;
        }

        $path = realpath($path) ?: realpath(base_path($path));

        // Here we'll verify that the SQLite database exists before going any further
        // as the developer probably wants to know if the database exists and this
        // SQLite driver will not throw any exception if it does not by default.
        if ($path === false) {
            throw new SQLiteDatabaseDoesNotExistException($database);
        }

        return $path;
    }

    /**
     * Set miscellaneous user-configured pragmas.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configurePragmas($connection, array $config): void
    {
        if ( ! isset($config['pragmas'])) {
            return;
        }

        foreach ($config['pragmas'] as $pragma => $value) {
            $connection->prepare("pragma {$pragma} = {$value}")->execute();
        }
    }

    /**
     * Enable or disable foreign key constraints if configured.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureForeignKeyConstraints($connection, array $config): void
    {
        if ( ! isset($config['foreign_key_constraints'])) {
            return;
        }

        $foreignKeys = $config['foreign_key_constraints'] ? 1 : 0;

        $connection->prepare("pragma foreign_keys = {$foreignKeys}")->execute();
    }

    /**
     * Set the busy timeout if configured.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureBusyTimeout($connection, array $config): void
    {
        if ( ! isset($config['busy_timeout'])) {
            return;
        }

        $connection->prepare("pragma busy_timeout = {$config['busy_timeout']}")->execute();
    }

    /**
     * Set the journal mode if configured.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * 
     * @return void
     */
    protected function configureJournalMode($connection, array $config): void
    {
        if ( ! isset($config['journal_mode'])) {
            return;
        }

        $connection->prepare("pragma journal_mode = {$config['journal_mode']}")->execute();
    }
}