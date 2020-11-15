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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.3
 */
 
namespace Syscodes\Database;

use PDO;
use Closure;
use DateTime;
use Exception;
use PDOStatement;
use LogicException;
use Syscodes\Collections\Arr;
use Syscodes\Database\Query\Processor;
use Syscodes\Database\Query\Expression;
use Syscodes\Contracts\Events\Dispatcher;
use Syscodes\Database\Events\QueryExecuted;
use Syscodes\Database\Events\TransactionBegin;
use Syscodes\Database\Events\StatementPrepared;
use Syscodes\Database\Exceptions\QueryException;
use Syscodes\Database\Events\TransactionRollback;
use Syscodes\Database\Events\TransactionCommitted;
use Syscodes\Database\Query\Builder as QueryBuilder;
use Syscodes\Database\Query\Grammar as QueryGrammar;

/**
 * Creates a database connection using PDO.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Connection implements ConnectionInterface
{
    use Concerns\ManagesTransations,
        Concerns\DetectLostConnections;
    
    /**
     * The database connection configuration options.
     * 
     * @var array $config
     */
    protected $config = [];

    /**
     * The name of the connected database.
     * 
     * @var string $database
     */
    protected $database;

    /**
     * The event dispatcher instance.
     * 
     * @var \Syscodes\Contracts\Events\Dispatcher  $events
     */
    protected $events;

    /**
     * The default fetch mode of the connection.
     * 
     * @var int $fetchMode
     */
    protected $fetchMode = PDO::FETCH_OBJ; 

    /**
     * The query grammar implementation.
     * 
     * @var \Syscodes\Database\Query\Grammar|string
     */
    protected $queryGrammar;

    /**
     * All of the queries run against the connection.
     * 
     * @var array $queryLog
     */
    protected $queryLog = [];

    /**
     * Indicates whether queries are being logged.
     * 
     * @var bool $loggingQueries
     */
    protected $loggingQueries = false;
    
    /**
     * The active PDO connection.
     * 
     * @var \PDO $pdo
     */
    protected $pdo;

    /**
     * The query post processor implementation.
     * 
     * @var \Syscodes\Database\Query\Processor|string $postProcessor
     */
    protected $postProcessor;

    /**
     * Indicates if the connection is in a "dry run".
     * 
     * @var bool $pretending
     */
    protected $pretending = false;

    /**
     * The active PDO connection used for reads.
     * 
     * @var \PDO $readPdo
     */
    protected $readPdo;

    /**
     * The reconnector instance for the connection.
     * 
     * @var callable $reconnector
     */
    protected $reconnector;

    /**
     * The table prefix for the connection.
     * 
     * @var string $tablePrefix
     */
    protected $tablePrefix;

    /**
     * The number of active transactions.
     * 
     * @var int $transactions
     */
    protected $transactions = 0;

    /**
     * Constructor. Create new a Database connection instance.
     * 
     * @param  \PDO|Closure  $pdo
     * @param  string  $database
     * @param  string  $tablePrefix
     * @param  array  $config
     * 
     * @return  void 
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        $this->database = $database;

        $this->tablePrefix = $prefix;

        $this->config = $config;

        $this->useDefaultQueryGrammar();
        
        $this->useDefaultPostProcessor();
    }

    /**
     * Begin a fluent query against a database table.
     * 
     * @param  \Closure|Syscodes\Database\Query\Builder|string  $table
     * @param  string  $as  (null by default)
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    public function table($table, $as = null)
    {
        return $this->query()->from($table, $as);
    }

    /**
     * Get a new query builder instance.
     * 
     * @return \Syscodes\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Get a new raw query expression.
     * 
     * @param  mixed  $value
     * 
     * @return \Syscodes\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Run a select statement and return a single result.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo  (true by default)
     * 
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings, $useReadPdo);

        return array_shift($records);
    }

    /**
     * Run a select statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return array
     */
    public function selectFromConnection($query, $bindings)
    {
        return $this->select($query, $bindings, false);
    }

    /**
     * Run a select statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo  (true by default)
     * 
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {

            if ($this->pretending())
            {
                return [];
            }

            $statement = $this->prepared(
                $this->getPdoForSelect($useReadPdo)->prepare($query)
            );

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            return $statement->fetchAll();

        });
    }

    /**
     * Run an insert statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run an delete statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {

            if ($this->pretending())
            {
                return true;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            return $statement->execute();

        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {

            if ($this->pretending())
            {
                return 0;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            $count = $statement->rowCount() > 0;

            return $count;

        });
    }

    /**
     * Execute the given callback in "dry run" mode.
     * 
     * @param  \Closure  $callback
     * 
     * @return array
     */
    public function prepend(Closure $callback)
    {
        return $this->withFreshQueryLog(function () use ($callback) {

            $this->pretending = true;

            $callback($this);

            $this->pretending = false;

            return $this->queryLog;

        });
    }
    
    /**
     * Execute the given callback in "dry run" mode.
     * 
     * @param  \Closure  $callback
     * 
     * @return array
     */
    protected function withFreshQueryLog($callback)
    {
        $loggingQueries = $this->loggingQueries;
        
        $this->enableQueryLog();
        
        $this->queryLog = [];
        
        $result = $callback();
        
        $this->loggingQueries = $loggingQueries;
        
        return $result;
    }

    /**
     * Bind values to their parameters in the given statement.
     * 
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * 
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value)
        {
            $statement->bindValue(
                is_string(key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Run a SQL statement and log its execution context.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Database\Exceptions\QueryException
     */
    protected function run($query, $bindings, Closure $callback)
    {
        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        try
        {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        }
        catch (QueryException $e)
        {
            $result = handleQueryException(
                $e, $query, $bindings, $callback
            );
        }

        $this->logQuery(
            $query, $bindings, $this->getElapsedTime($start)
        );

        return $result;
    }

    /**
     * Run a SQL statement.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Database\Exceptions\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        try
        {
            $result = $callback($query, $bindings);
        }
        catch (Exception $e)
        {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e 
            );
        }

        return $result;
    }

    /**
     * Prepare the query bindings for execution.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value)
        {
            if ($value instanceof DateTime)
            {
                $bindings[$key] = $value->format($this->getQueryGrammar()->getDateFormat());
            }
            elseif (is_bool($value))
            {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    /**
     * Handle a query exception.
     * 
     * @param  \Syscodes\Database\Exceptions\QueryException  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Database\Exceptions\QueryException
     */
    protected function handleQueryException(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->transactions >= 1)
        {
            throw $e;
        }

        return tryIfAgainCausedByLostConnection(
            $e, $query, $bindings, $callback
        );
    }

    /**
     * Handle a query exception that occurred during query execution.
     * 
     * @param  \Syscodes\Database\Exceptions\QueryException  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Database\Exceptions\QueryException
     */
    protected function tryIfAgainCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        if (causedByLostConnections($e->getPrevious()))
        {
            $this->reconnect();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }

    /**
     * Log a query in the connection's query log.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  float|null  $time  (null by default)
     * 
     * @return void
     */
    public function logQuery($query, $bindings, $time = null)
    {
        $this->event(new QueryExecuted($query, $bindings, $time, $this));

        if ($this->loggingQueries)
        {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    /**
     * Fire an event for this connection.
     * 
     * @param  string  $event
     * 
     * @return array|null
     */
    protected function fireConnectionEvent($event)
    {
        if ( ! $this->events)
        {
            return;
        }

        switch($event)
        {
            case 'beginTransaction':
                return $this->events->dispatch(new TransactionBegin($this));
            case 'committed':
                return $this->events->dispatch(new TransactionCommitted($this));
            case 'rollingback':
                return $this->events->dispatch(new TransactionRollback($this));
        }
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     * 
     * @return void
     */
    public function reconnectIfMissingConnection()
    {
        if (is_null($this->pdo))
        {
            $this->reconnect();
        }
    }

    /**
     * Disconnect from the underlying PDO connection.
     * 
     * @return void
     */
    public function disconnect()
    {
        $this->setPdo(null)->$this->setReadPdo(null);
    }

    /**
     * Get the elapsed time since a given starting point.
     * 
     * @param  int  $start
     * 
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Fire the given event if possible.
     * 
     * @param  mixed  $event
     * 
     * @return void
     */
    public function event($event)
    {
        if (isset($this->events))
        {
            $this->events->dispatch($event);
        }
    }

    /**
     * Reconnect to the database.
     * 
     * @return void
     * 
     * @throws \LogicException
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector))
        {
            return call_user_func($this->reconnector, $this);
        }

        throw new LogicException('Lost connection and no reconnector available');
    }

    /**
     * Configure the PDO prepared statement.
     * 
     * @param  \PDOStatement  $statement
     * 
     * @return \PDOStatement
     */
    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);

        $this->event(
            new statementPrepared($this, $statement)
        );

        return $statement;
    }

    /**
     * Get the PDO connection to use for a select query.
     * 
     * @param  bool  $useReadPdo  (true by default)
     * 
     * @return \PDO
     */
    protected function getPdoForSelect($useReadPdo = true)
    {
        return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
    }

    /**
     * Get the PDO instance.
     * 
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Get the current PDO connection used for reading.
     * 
     * @return \PDO
     */
    public function getReadPdo()
    {
        if ($this->transactions > 0)
        {
            return $this->getPdo();
        }
        
        if ($this->readPdo instanceof Closure)
        {
            return $this->readPdo = call_user_func($this->readPdo);
        }
        
        return $this->readPdo ?: $this->getPdo();
    }

    /**
     * Get the current read PDO connection parameter without executing any reconnect logic.
     * 
     * @return \PDO|\Closure|null
     */
    public function getRawReadPdo()
    {
        return $this->readPdo;
    }

    /**
     * Set the PDO connection.
     * 
     * @param  \PDO|\Closure|null  $pdo
     * 
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->transactions = 0;

        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Set the PDO connection used for reading.
     * 
     * @param  \PDO|\Closure|null  $pdo
     * 
     * @return $this
     */
    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;

        return $this;
    }

    /**
     * Set the reconnect instance on the connection.
     * 
     * @param  Callablle  $reconnector
     * 
     * @return $this
     */
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;

        return $this;
    }

    /**
     * Get the PDO driver name.
     * 
     * @return string
     */
    public function getConfigDriver()
    {
        return $this->getConfig('driver');
    }

    /**
     * Get the database connection name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get an option from the configuration options.
     * 
     * @param  string|null  $option  (null by default)
     * 
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }

    /**
     * Get the query grammar used by the connection.
     * 
     * @return \Syscodes\Database\Query\Grammar
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Set the query grammar to the default implementation.
     * 
     * @return void
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
     * 
     * @return \Syscodes\Database\Query\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }

    /**
     * Get the query post processor used by the connection.
     * 
     * @return \Syscodes\Database\Query\Processor
     */
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }
    /**
     * Set the query post processor to the default implementation.
     * 
     * @return void
     */
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultProcessor();
    }

    /**
     * Get the default post processor instance.
     * 
     * @return \Syscodes\Database\Query\Processor
     */
    protected function getDefaultProcessor()
    {
        return new Processor;
    }

    /**
     * Get the name of the connected database.
     * 
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the name of the connected database.
     * 
     * @param  string  $database
     * 
     * @return $this
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Determine if the connection is in a "dry run".
     * 
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending === true;
    }

    /**
     * Get the connection query log.
     * 
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     * 
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * Enable the query log on the connection.
     * 
     * @return void
     */
    public function EnableQueryLog()
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
     * 
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    /**
     * Determine whether we're logging queries.
     * 
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }

    /**
     * Get the table prefix for the connection.
     * 
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the table prefix in use by the connection.
     * 
     * @param  string  $tablePrefix
     * 
     * @return $this
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;

        $this->getQueryGrammar()->setTablePrefix($tablePrefix);

        return $this;
    }

    /**
     * Set the table prefix and return the grammar.
     * 
     * @param  \Syscodes\Database\Grammar  $grammar
     * 
     * @return \Syscodes\Database\Grammar
     */
    public function withTablePrefix(Grammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }
}