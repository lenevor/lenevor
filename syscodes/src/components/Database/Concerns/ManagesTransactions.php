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
use Syscodes\Components\Database\DeadlockException;
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
     * @param  int  $attempts
     * 
     * @return mixed
     * 
     * @throws \Throwable
     */
    public function transaction(Closure $callback,  $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            // We'll simply execute the given callback within a try / catch block and if we
            // catch any exception we can rollback this transaction so that none of this
            // gets actually persisted to a database or stored in a permanent fashion.
            try {
                $callbackResult = $callback($this);
            }

            // If we catch an exception we'll rollback this transaction and try again if we
            // are not out of attempts. If we are out of attempts we will just throw the
            // exception back out, and let the developer handle an uncaught exception.
            catch (Throwable $e) {
                $this->handleTransactionException(
                    $e, $currentAttempt, $attempts
                );

                continue;
            }

            try {
                if ($this->transactions == 1) {
                    $this->fireConnectionEvent('committing');
                    $this->getPdo()->commit();
                }

                $this->transactions = max(0, $this->transactions - 1);

                if ($this->afterCommitCallbacksShouldBeExecuted()) {
                    $this->transactionsManager?->commit($this->getName());
                }
            } catch (Throwable $e) {
                $this->handleCommitTransactionException(
                    $e, $currentAttempt, $attempts
                );

                continue;
            }

            $this->fireConnectionEvent('committed');

            return $callbackResult;
        }
    }

    /**
     * Handle an exception encountered when running a transacted statement.
     *
     * @param  \Throwable  $e
     * @param  int  $currentAttempt
     * @param  int  $maxAttempts
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleTransactionException(Throwable $e, $currentAttempt, $maxAttempts)
    {
        // On a deadlock, MySQL rolls back the entire transaction so we can't just
        // retry the query. We have to throw this exception all the way out and
        // let the developer handle it in another way. We will decrement too.
        if ($this->causedByConcurrencyError($e) &&
            $this->transactions > 1) {
            $this->transactions--;

            $this->transactionsManager?->rollback(
                $this->getName(), $this->transactions
            );

            throw new DeadlockException($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 0, $e);
        }

        // If there was an exception we will rollback this transaction and then we
        // can check if we have exceeded the maximum attempt count for this.
        $this->rollBack();

        if ($this->causedByConcurrencyError($e) &&
            $currentAttempt < $maxAttempts) {
            return;
        }

        throw $e;
    }

    /**
     * Start a new database transaction.
     * 
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->createTransaction();

        $this->transactions++;

        $this->transactionsManager?->begin(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('beginTransaction');
    }

    /**
     * Create a transaction within the database.
     *
     * @return void
     *
     * @throws \Throwable
     */
    protected function createTransaction(): void
    {
        if ($this->transactions == 0) {
            $this->reconnectIfMissingConnection();

            try {
                $this->getPdo()->beginTransaction();
            } catch (Throwable $e) {
                $this->handleBeginTransactionException($e);
            }
        } elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->createSavepoint();
        }
    }

    /**
     * Create a save point within the database.
     *
     * @return void
     *
     * @throws \Throwable
     */
    protected function createSavepoint(): void
    {
        $this->getPdo()->exec(
            $this->queryGrammar->compileSavepoint('trans'.($this->transactions + 1))
        );
    }

    /**
     * Handle an exception from a transaction beginning.
     *
     * @param  \Throwable  $e
     * 
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleBeginTransactionException(Throwable $e): void
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            $this->getPdo()->beginTransaction();
        } else {
            throw $e;
        }
    }

    /**
     * Commit the active database transaction.
     * 
     * @return void
     */
    public function commit(): void
    {
        if ($this->transactionLevel() == 1) {
            $this->fireConnectionEvent('committing');
            $this->getPdo()->commit();
        }

        $this->transactions = max(0, $this->transactions - 1);

        if ($this->afterCommitCallbacksShouldBeExecuted()) {
            $this->transactionsManager?->commit($this->getName());
        }

        $this->fireConnectionEvent('committed');
    }

    /**
     * Determine if after commit callbacks should be executed.
     *
     * @return bool
     */
    protected function afterCommitCallbacksShouldBeExecuted(): bool
    {
        return $this->transactions == 0 ||
            ($this->transactionsManager &&
             $this->transactionsManager->callbackApplicableTransactions()->count() === 1);
    }

    /**
     * Handle an exception encountered when committing a transaction.
     *
     * @param  \Throwable  $e
     * @param  int  $currentAttempt
     * @param  int  $maxAttempts
     * 
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleCommitTransactionException(Throwable $e, $currentAttempt, $maxAttempts): void
    {
        $this->transactions = max(0, $this->transactions - 1);

        if ($this->causedByConcurrencyError($e) && $currentAttempt < $maxAttempts) {
            return;
        }

        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;
        }

        throw $e;
    }

    /**
     * Rollback the active database transaction.
     * 
     * @param  int|null  $toLevel
     * 
     * @return void
     */
    public function rollback($toLevel = null): void
    {
        // We allow developers to rollback to a certain transaction level. We will verify
        // that this given transaction level is valid before attempting to rollback to
        // that level. 
        $toLevel = is_null($toLevel)
                    ? $this->transactions - 1
                    : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        // Next, we will actually perform this rollback within this database and fire the
        // rollback event.
        try {
            $this->performRollBack($toLevel);
        } catch (Throwable $e) {
            $this->handleRollBackException($e);
        }

        $this->transactions = $toLevel;

        $this->transactionsManager?->rollback(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('rollingBack');
    }
    
    /**
     * Perform a rollback within the database.
     *
     * @param  int  $toLevel
     * 
     * @return void
     *
     * @throws \Throwable
     */
    protected function performRollBack($toLevel): void
    {
        if ($toLevel == 0) {
            $pdo = $this->getPdo();

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->getPdo()->exec(
                $this->queryGrammar->compileSavepointRollBack('trans'.($toLevel + 1))
            );
        }
    }

    /**
     * Handle an exception from a rollback.
     *
     * @param  \Throwable  $e
     * 
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleRollBackException(Throwable $e): void
    {
        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;

            $this->transactionsManager?->rollback(
                $this->getName(), $this->transactions
            );
        }

        throw $e;
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