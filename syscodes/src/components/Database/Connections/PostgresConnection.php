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

namespace Syscodes\Components\Database\Connections;

use Syscodes\Components\Database\Schema\Builders\PostgresBuilder;
use Syscodes\Components\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Syscodes\Components\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use Syscodes\Components\Database\Query\Processors\PostgresProcessor as QueryProcessor;

/**
 * Postgres connection.
 */
class PostgresConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     * 
     * @return Syscodes\Components\Database\QueryMysqlGrammar\
     */
    public function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get the default post processor instance.
     * 
     * @return Syscodes\Components\Database\Query\Post Processor
     */
    public function getDefaultPostProcessor()
    {
        return new QueryProcessor;
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Syscodes\Components\Database\Schema\Builders\PostgresBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new PostgresBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Syscodes\Components\Database\Schema\Grammars\PostgresGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }
}