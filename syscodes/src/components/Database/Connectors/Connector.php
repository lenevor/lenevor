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
 * @since       0.7.0
 */
 
namespace Syscodes\Database\Connectors;

use PDO;
use PDOException;
use Syscodes\Database\Exceptions\ConnectionException;

/**
 * The default PDO connection.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
abstract class Connector
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
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * Get the PDO options based on the configuration.
     * 
     * @param  array  $config
     * 
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = $config['options'] ?? [];

        return array_diff_assoc($this->options, $options) + $options;
    }

    /**
     * Create a new PDO connection.
     * 
     * @param  string  $dns
     * @param  array  $config
     * @param  array  $options
     * 
     * @return \PDO
     * 
     * @throws \Syscodes\Database\Exceptions\ConnectionException  
     */
    public function createConnection($dsn, array $config, array $options)
    {
        list($username, $password) = [
            $config['username'] ?? null,
            $config['password'] ?? null
        ];

        try
        {
            return new PDO($dsn, $username, $password, $options);
        }
        catch (PDOException $e)
        {
            throw new ConnectionException("Connection to [ {$dsn} ] failed: ".$e->getMessage(), $e);
        }
    }

    /**
     * Get the default PDO connection options.
     * 
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     * 
     * @param  array  $options
     * 
     * @return void
     */
    public function setDefaultOptions(array $options)
    {
        return $this->options = $options;
    }
}