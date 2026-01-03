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
 * A PDO based SqlServer Database Connector.
 */
class SqlServerConnector extends Connector implements ConnectorInterface
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

        return $this->createConnection($dsn, $config, $options);
    }

    /**
     * Create a DSN string from a configuration.
     * 
     * @param  array $config
     * 
     * @return string
     */
    protected function getDsn(array $config): string
    {
        extract($config);
        
        $port = isset($config['port']) ? ','.$port : '';
        
        if (in_array('dblib', $this->getAvailableDrivers())) {
            return "dblib:host={$host}{$port};dbname={$database}";
        } else {
            $dbName = $database != '' ? ";Database={$database}" : '';
            
            return "sqlsrv:Server={$host}{$port}{$dbName}";
        }
    }

    /**
     * Get the available PDO drivers.
     * 
     * @return array
     */
    public function getAvailableDrivers(): array
    {
        return PDO::getAvailableDrivers();
    }
}