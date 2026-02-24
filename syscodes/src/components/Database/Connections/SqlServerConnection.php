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

use Closure;
use Exception;
use Syscodes\Components\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Syscodes\Components\Database\Query\Processors\SqlServerProcessor as QueryProcessor;
use Syscodes\Components\Database\Schema\Builders\SqlServerBuilder;
use Syscodes\Components\Database\Schema\Grammars\SqlServerGrammar as SchemaGrammar;
use Throwable;

/**
 * SqlServer connection.
 */
class SqlServerConnection extends Connection
{
    /**
     * {@inheritdoc}
     */
    public function getDriverTitle()
    {
        return 'SQL Server';
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @param  int  $attempts
     * 
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($a = 1; $a <= $attempts; $a++) {
            if ($this->getDriverName() === 'sqlsrv') {
                return parent::transaction($callback, $attempts);
            }

            $this->getPdo()->exec('BEGIN TRAN');

            // We'll simply execute the given callback within a try / catch block
            // and if we catch any exception we can rollback the transaction
            // so that none of the changes are persisted to the database.
            try {
                $result = $callback($this);

                $this->getPdo()->exec('COMMIT TRAN');
            }
            // If we catch an exception, we will rollback so nothing gets messed
            // up in the database.
            catch (Throwable $e) {
                $this->getPdo()->exec('ROLLBACK TRAN');

                throw $e;
            }

            return $result;
        }
    }

    /**
     * Escape a binary value for safe SQL embedding.
     *
     * @param  string  $value
     * 
     * @return string
     */
    protected function escapeBinary($value): string
    {
        $hex = bin2hex($value);

        return "0x{$hex}";
    }

    /**
     * Determine if the given database exception was caused by a unique constraint violation.
     *
     * @param  \Exception  $exception
     * 
     * @return bool
     */
    protected function isUniqueConstraintError(Exception $exception): bool
    {
        return (bool) preg_match('#Cannot insert duplicate key row in object#i', $exception->getMessage());
    }

    /**
     * Get the default query grammar instance.
     * 
     * @return Syscodes\Components\Database\Query\SqlServerGrammar
     */
    public function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar($this));
    }

    /**
     * Get the default post processor instance.
     * 
     * @return Syscodes\Components\Database\Query\SqlServerProcessor
     */
    public function getDefaultPostProcessor()
    {
        return new QueryProcessor;
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Syscodes\Components\Database\Schema\Builders\SqlServerBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SqlServerBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Syscodes\Components\Database\Schema\Grammars\SqlServerGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar($this));
    }
}