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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database;

use Syscodes\Components\Support\Collection;

/**
 * Allows the database transaction manager to should be executed.
 */
class DatabaseTransactionsManager
{
    /**
     * All of the recorded transactions.
     *
     * @var \Syscodes\Components\Support\Collection
     */
    protected $transactions;

    /**
     * The database transaction that should be ignored by callbacks.
     *
     * @var \Syscodes\Components\Database\DatabaseTransactionRecord
     */
    protected $callbacksShouldIgnore;

    /**
     * Construct. Create a new database transactions manager instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->transactions = new Collection();
    }

    /**
     * Start a new database transaction.
     *
     * @param  string  $connection
     * @param  int  $level
     * 
     * @return void
     */
    public function begin($connection, $level): void
    {
        $this->transactions->push(
            new DatabaseTransactionRecord($connection, $level)
        );
    }

    /**
     * Rollback the active database transaction.
     *
     * @param  string  $connection
     * @param  int  $level
     * 
     * @return void
     */
    public function rollback($connection, $level): void
    {
        $this->transactions = $this->transactions->reject(
            fn ($transaction) => $transaction->connection == $connection && $transaction->level > $level
        )->values();

        if ($this->transactions->isEmpty()) {
            $this->callbacksShouldIgnore = null;
        }
    }

    /**
     * Commit the active database transaction.
     *
     * @param  string  $connection
     * 
     * @return void
     */
    public function commit($connection): void
    {
        [$forThisConnection, $forOtherConnections] = $this->transactions->partition(
            fn ($transaction) => $transaction->connection == $connection
        );

        $this->transactions = $forOtherConnections->values();

        $forThisConnection->map->executeCallbacks();

        if ($this->transactions->isEmpty()) {
            $this->callbacksShouldIgnore = null;
        }
    }

    /**
     * Register a transaction callback.
     *
     * @param  callable  $callback
     * 
     * @return void
     */
    public function addCallback($callback)
    {
        if ($current = $this->callbackApplicableTransactions()->last()) {
            return $current->addCallback($callback);
        }

        $callback();
    }

    /**
     * Specify that callbacks should ignore the given transaction when determining if they should be executed.
     *
     * @param  \Syscodes\Components\Database\DatabaseTransactionRecord  $transaction
     * 
     * @return static
     */
    public function callbacksShouldIgnore(DatabaseTransactionRecord $transaction): static
    {
        $this->callbacksShouldIgnore = $transaction;

        return $this;
    }

    /**
     * Get the transactions that are applicable to callbacks.
     *
     * @return \Syscodes\Components\Support\Collection
     */
    public function callbackApplicableTransactions()
    {
        return $this->transactions->reject(function ($transaction) {
            return $transaction === $this->callbacksShouldIgnore;
        })->values();
    }

    /**
     * Get all the transactions.
     *
     * @return \Syscodes\Components\Support\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}