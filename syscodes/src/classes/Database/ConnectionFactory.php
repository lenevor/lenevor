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
use Syscodes\Database\Connectors\MysqlConnectors;
use Syscodes\Database\Connectors\SQLiteConnectors;
use Syscodes\Database\Connectors\PostgresConnectors;
use Syscodes\Database\Connectors\SqlServerConnectors;

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
}