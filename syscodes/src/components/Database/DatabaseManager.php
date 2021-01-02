<?php 

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2019-2021 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.3
 */

namespace Syscodes\Database;

use PDO;
use Syscodes\Support\Str;
use Syscodes\Collections\Arr;
use InvalidArgumentException;

/**
 * It is used to instantiate the connection and its respective settings.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class DatabaseManager implements ConnectionResolverInterface
{
    /**
     * The appilcation instance.
     * 
     * @var \Syscodes\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * The active connection instances.
     * 
     * @var array $connections
     */
    protected $connections = [];

    /**
     * The database connection factory instance.
     * 
     * @var \Syscodes\Database\ConnectionFactory $factory
     */
    protected $factory;

    /**
     * The custom connection resolvers.
     * 
     * @var array $extensions
     */
    protected $extensions = [];

    /**
     * Constructor. Create a new DatabaseManager instance.
     * 
     * @param  \Syscodes\Contracts\Core\Application  $app
     * @param  \Syscodes\Database\ConnectionFactory  $factory
     * 
     * @return void
     */
    public function __construct($app, ConnectionFactory $factory)
    {
        $this->app     = $app;
        $this->factory = $factory;
    }

    
}