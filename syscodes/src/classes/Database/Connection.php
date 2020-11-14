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
use Syscodes\Database\Query\Builder as QueryBuilder;
use Syscodes\Database\Query\Grammar as QueryGrammar;

/**
 * Creates a database connection using PDO.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Connection implements ConnectionInterface
{
    use Concerns\DetectLostConnections;
    
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
     * The query grammar implementation.
     * 
     * @var \Syscodes\Database\Query\Grammar|string
     */
    protected $queryGrammar;
    
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

    }

    /**
     * Prepare the query bindings for execution.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function prepareBindings($bindings = [])
    {

    }

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

    /**
     * Execute the given callback in "dry run" mode.
     * 
     * @param  \Closure  $callback
     * 
     * @return array
     */
    public function prepend(Closure $callback)
    {

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
}