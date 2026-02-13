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

namespace Syscodes\Components\Database\Connection;

use Syscodes\Components\Database\Connections\MySqlConnection;
use Syscodes\Components\Database\Query\Processors\MariaDbProcessor;
use Syscodes\Components\Database\Schema\Builders\MariaDbBuilder;
use Syscodes\Components\Database\Schema\Grammars\MariaDbGrammar as QueryGrammar;
use Syscodes\Components\Database\Schema\Grammars\MariaDbGrammar as SchemaGrammar;

class MariaDbConnection extends MySqlConnection
{
    /**
     * {@inheritdoc}
     */
    public function getDriverTitle()
    {
        return 'MariaDB';
    }
    
    /**
     * Determine if the connected database is a MariaDB database.
     * 
     * @return bool
     */
    public function isMaria(): bool
    {
        return true;
    }

     /**
     * Get the default query grammar instance.
     * 
     * @return \Syscodes\Components\Database\Schema\Grammars\MariaDbGrammar
     */
    public function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }

    /**
     * Get the default post processor instance.
     * 
     * @return \Syscodes\Components\Database\Query\Processors\MariaDbProcessor
     */
    public function getDefaultPostProcessor()
    {
        return new MariaDbProcessor;
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Syscodes\Components\Database\Schema\Builders\MariaDbBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new MariaDbBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Syscodes\Components\Database\Schema\Grammars\MariaDbGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return new SchemaGrammar($this);
    }
}