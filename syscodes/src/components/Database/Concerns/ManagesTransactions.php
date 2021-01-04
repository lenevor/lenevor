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
 * @since       0.7.3
 */

namespace Syscodes\Database\Concerns;

use Closure;
use Throwable;

/**
 * Gets transactions.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait ManagesTransactions
{
    /**
     * Execute a Closure within a transaction.
     * 
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Throwable
     */
    public function transaction(Closure $callback)
    {

    }

    /**
     * Start a new database transaction.
     * 
     * @return void
     */
    public function beginTransaction()
    {

    }

    /**
     * Commit the active database transaction.
     * 
     * @return void
     */
    public function commit()
    {

    }

    /**
     * Rollback the active database transaction.
     * 
     * @return void
     */
    public function rollback()
    {

    }

    /**
     * Checks the connection to see if there is an active transaction.
     * 
     * @return int
     */
    public function inTransaction()
    {

    }
}