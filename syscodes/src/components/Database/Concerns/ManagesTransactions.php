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

namespace Syscodes\Components\Database\Concerns;

use Closure;
use Throwable;

/**
 * Gets transactions.
 */
trait ManagesTransactions
{
    /**
     * /**
     * Execute a Closure within a transaction.
     * 
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Throwable
     */
    public function transaction(Closure $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);

            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();

            throw $e;
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     * 
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->transactions++;

        if ($this->transactions == 1) {
            $this->getPdo()->beginTransaction();
        }
    }

    /**
     * Commit the active database transaction.
     * 
     * @return void
     */
    public function commit(): void
    {
        if ($this->transactions == 1) {
            $this->getPdo()->commit();
        }
    }

    /**
     * Rollback the active database transaction.
     * 
     * @return void
     */
    public function rollback(): void
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;

            $this->getPdo()->rollback();
        } else {
            $this->transactions--;
        }
    }

    /**
     * Get the number of active transactions.
     * 
     * @return int
     */
    public function transactionLevel(): int
    {
        return $this->transactions;
    }
}