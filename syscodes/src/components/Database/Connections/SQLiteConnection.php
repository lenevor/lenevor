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
 */

namespace Syscodes\Database\Connections;

use Syscodes\Database\Connection;
use Syscodes\Database\Query\SQLiteGrammar as QueryGrammar;
use Syscodes\Database\Query\SQLiteProcessor as QueryProcessor;

/**
 * SQLite connection.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class SQLiteConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     * 
     * @return Syscodes\Database\Query\SQLiteGrammar
     */
    public function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get the default post processor instance.
     * 
     * @return Syscodes\Database\Query\SQLiteProcessor
     */
    public function getDefaultPost()
    {
        return new QueryProcessor;
    }
}