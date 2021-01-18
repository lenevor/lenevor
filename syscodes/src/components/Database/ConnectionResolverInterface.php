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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 * @since       0.7.4
 */

namespace Syscodes\Database;

/**
 * Implements the functions that allow generate a connection to database.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface ConnectionResolverInterface
{
    /**
     * Get a Database Connection instance.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Database\Connection
     */
    public function connection($name = null);
    
    /**
     * Get the default Connection name.
     * 
     * @return string
     */
    public function getDefaultConnection();
    
    /**
     * Set the default Connection name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultConnection($name);
}