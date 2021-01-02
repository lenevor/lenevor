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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.0
 */

namespace Syscodes\Database\Connectors;

use PDO;

/**
 * A PDO based SqlServer Database Connector.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class SqlServerConnector
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
    protected function getDsn(array $config)
    {
        extract($config);
        
        $port = isset($config['port']) ? ','.$port : '';
        
        if (in_array('dblib', $this->getAvailableDrivers()))
        {
            return "dblib:host={$host}{$port};dbname={$database}";
        } 
        else 
        {
            $dbName = $database != '' ? ";Database={$database}" : '';
            
            return "sqlsrv:Server={$host}{$port}{$dbName}";
        }
    }

    /**
     * Get the available PDO drivers.
     * 
     * @return array
     */
    public function getAvailableDrivers()
    {
        return PDO::getAvailableDrivers();
    }

}