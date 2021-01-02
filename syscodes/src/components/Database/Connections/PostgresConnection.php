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

namespace Syscodes\Database\Connections;

use Syscodes\Database\Connection;
use Syscodes\Database\Query\PostgresGrammar as QueryGrammar;
use Syscodes\Database\Query\PostgresProcessor as QueryProcessor;

/**
 * Postgres connection.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class PostConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     * 
     * @return Syscodes\Database\QueryMysqlGrammar\
     */
    public function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get the default post processor instance.
     * 
     * @return Syscodes\Database\Query\Post Processor
     */
    public function getDefaultPost()
    {
        return new QueryProcessor;
    }
}
