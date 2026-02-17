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
 
namespace Syscodes\Components\Database\Connections;

use PDO;
use Closure;
use DateTime;
use Exception;
use PDOStatement;
use LogicException;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Database\Concerns\DetectLostConnections;
use Syscodes\Components\Database\Concerns\ManagesTransactions;
use Syscodes\Components\Database\Grammar;
use Syscodes\Components\Database\Exceptions\QueryException;
use Syscodes\Components\Database\Events\QueryExecuted;
use Syscodes\Components\Database\Events\TransactionBegin;
use Syscodes\Components\Database\Events\StatementPrepared;
use Syscodes\Components\Database\Events\TransactionRollback;
use Syscodes\Components\Database\Events\TransactionCommitted;
use Syscodes\Components\Database\Exceptions\ConstraintViolationException;
use Syscodes\Components\Database\Query\Builder as QueryBuilder;
use Syscodes\Components\Database\Query\Expression;
use Syscodes\Components\Database\Query\Grammars\Grammar as QueryGrammar;
use Syscodes\Components\Database\Query\Processors\Processor;
use Syscodes\Components\Database\Schema\Builders\Builder as SchemaBuilder;
use Syscodes\Components\Database\Schema\Grammars\Grammar as SchemaGrammar;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Creates a database connection using PDO.
 */
class Connection implements ConnectionInterface
{
    use DetectLostConnections,
        Macroable,
        ManagesTransactions;
    
    /**
     * The database connection configuration options.
     * 
     * @var array
     */
    protected $config = [];

    /**
     * The name of the connected database.
     * 
     * @var string
     */
    protected $database;

    /**
     * The event dispatcher instance.
     * 
     * @var \Syscodes\Components\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The default fetch mode of the connection.
     * 
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;
    
    /**
     * The last retrieved PDO read / write type.
     * 
     * @var null|'read'|'write'
     */
    protected $latestPdoTypeRetrieved = null;

    /**
     * Indicates whether queries are being logged.
     * 
     * @var bool
     */
    protected $loggingQueries = false;
    
    /**
     * The active PDO connection.
     * 
     * @var \PDO
     */
    protected $pdo;

    /**
     * The query post processor implementation.
     * 
     * @var \Syscodes\Components\Database\Query\Processor|string
     */
    protected $postProcessor;

    /**
     * Indicates if the connection is in a "dry run".
     * 
     * @var bool
     */
    protected $pretending = false;

     /**
     * The query grammar implementation.
     * 
     * @var \Syscodes\Components\Database\Query\Grammar|string
     */
    protected $queryGrammar;

    /**
     * All of the queries run against the connection.
     * 
     * @var array
     */
    protected $queryLog = [];

    /**
     * The active PDO connection used for reads.
     * 
     * @var \PDO
     */
    protected $readPdo;
    
    /**
     * The database connection configuration options for reading.
     * 
     * @var array
     */
    protected $readPdoConfig = [];
    
    /**
     * The type of the connection.
     * 
     * @var string|null
     */
    protected $readWriteType;

    /**
     * The reconnector instance for the connection.
     * 
     * @var callable
     */
    protected $reconnector;
    
    /**
     * The schema grammar implementation.
     * 
     * @var \Syscodes\Components\Database\Schema\Grammars\Grammar
     */
    protected $schemaGrammar;

    /**
     * The connection resolvers.
     * 
     * @var array
     */
    protected static $resolvers = [];

    /**
     * The table prefix for the connection.
     * 
     * @var string
     */
    protected $tablePrefix;

    /**
     * The number of active transactions.
     * 
     * @var int
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
    public function __construct($pdo, string $database = '', string $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        $this->useDefaultQueryGrammar();
        
        $this->useDefaultPostProcessor();
    }

    /**
     * Begin a fluent query against a database table.
     * 
     * @param  \Closure|\Syscodes\Components\Database\Query\Builder|string  $table
     * @param  string|null  $as 
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function table($table, ?string $as = null)
    {
        return $this->query()->from($table, $as);
    }

    /**
     * Get a new query builder instance.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
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
     * @return \Syscodes\Components\Database\Query\Expression
     */
    public function raw(mixed $value)
    {
        return new Expression($value);
    }

    /**
     * Run a select statement and return a single result.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo  
     * 
     * @return array 
     */
    public function selectOne(string $query, array $bindings = [], bool $useReadPdo = true): array
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
    public function selectFromConnection(string $query, array $bindings = [])
    {
        return $this->select($query, $bindings, false);
    }

    /**
     * Run a select statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo  
     * 
     * @return array
     */
    public function select(string $query, array $bindings = [], bool $useReadPdo = true): array
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
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
    public function insert(string $query, array $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     * 
     * @param  string  $query
     * @param  array  $bindings
     * 
     * @return int
     */
    public function update(string $query, array $bindings = []): int
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
    public function delete(string $query, array $bindings = []): int
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
    public function statement(string $query, array $bindings = []): bool
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
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
    public function affectingStatement(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
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
    public function prepend(Closure $callback): array
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
     * @return array|Closure
     */
    protected function withFreshQueryLog(Closure $callback): array|Closure
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
    public function bindValues(PDOStatement $statement, array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR
                },
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
     * @throws \Syscodes\Components\Database\Exceptions\QueryException
     */
    protected function run(string $query, array $bindings, Closure $callback): mixed
    {
        $result = '';
        
        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException(
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
     * @throws \Syscodes\Components\Database\Exceptions\QueryException
     */
    protected function runQueryCallback(string $query, array $bindings, Closure $callback): mixed
    {
        try {
            return $callback($query, $bindings);
        } catch (Exception $e) {
            $exceptionType = $this->isConstraintError($e)
                ? ConstraintViolationException::class
                : QueryException::class;

            throw new $exceptionType(
                $this->getNameWithReadWriteType(),
                $query,
                $this->prepareBindings($bindings),
                $e,
                $this->getConnectionDetails(),
            );
        }
    }
    
    /**
     * Determine if the given database exception was caused by a constraint violation.
     * 
     * @param  \Exception  $exception
     * 
     * @return bool
     */
    protected function isConstraintError(Exception $exception)
    {
        return false;
    }

    /**
     * Prepare the query bindings for execution.
     * 
     * @param  array  $bindings
     * 
     * @return array
     */
    public function prepareBindings(array $bindings): array
    {
        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTime) {
                $bindings[$key] = $value->format($this->getQueryGrammar()->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    /**
     * Handle a query exception.
     * 
     * @param  \Syscodes\Components\Database\Exceptions\QueryException  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Database\Exceptions\QueryException
     */
    protected function handleQueryException(QueryException $e, string $query, array $bindings, Closure $callback): mixed
    {
        if ($this->transactions >= 1) {
            throw $e;
        }

        return $this->tryIfAgainCausedByLostConnection(
            $e, $query, $bindings, $callback
        );
    }

    /**
     * Handle a query exception that occurred during query execution.
     * 
     * @param  \Syscodes\Components\Database\Exceptions\QueryException  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Database\Exceptions\QueryException
     */
    protected function tryIfAgainCausedByLostConnection(QueryException $e, string $query, array $bindings, Closure $callback): mixed
    {
        if ($this->causedByLostConnection($e->getPrevious())) {
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
     * @param  float|null  $time 
     * 
     * @return void
     */
    public function logQuery(string $query, array $bindings, ?float $time = null): void
    {
        $readWriteType = $this->latestReadWriteTypeUsed();

        $this->event(new QueryExecuted($query, $bindings, $time, $this, $readWriteType));

        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time', 'readWriteType');
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
        if ( ! $this->events) {
            return;
        }

        return match($event) {
            'beginTransaction' => $this->events->dispatch(new TransactionBegin($this)),
            'committed' => $this->events->dispatch(new TransactionCommitted($this)),
            'rollingback' => $this->events->dispatch(new TransactionRollback($this)),
        };
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     * 
     * @return void
     */
    public function reconnectIfMissingConnection(): void
    {
        if (is_null($this->pdo)) {
            $this->reconnect();
        }
    }

    /**
     * Disconnect from the underlying PDO connection.
     * 
     * @return void
     */
    public function disconnect(): void
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
    public function event($event): void
    {
        if (isset($this->events)) {
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
        if (is_callable($this->reconnector)) {
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
     * @param  bool  $useReadPdo  
     * 
     * @return \PDO
     */
    protected function getPdoForSelect(bool $useReadPdo = true)
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
        $this->latestPdoTypeRetrieved = 'write';

        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }
        
        return $this->pdo;
    }

    /**
     * Get the current PDO connection parameter without executing any reconnect logic.
     * 
     * @return \PDO|\Closure|null
     */
    public function getRawPdo()
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
        if ($this->transactions > 0) {
            return $this->getPdo();
        }
        
        $this->latestPdoTypeRetrieved = 'read';
        
        if ($this->readPdo instanceof Closure) {
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
     * @return static
     */
    public function setPdo($pdo): static
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
     * @return static
     */
    public function setReadPdo($pdo): static
    {
        $this->readPdo = $pdo;

        return $this;
    }

    /**
     * Set the reconnect instance on the connection.
     * 
     * @param  \Callable  $reconnector
     * 
     * @return static
     */
    public function setReconnector(callable $reconnector): static
    {
        $this->reconnector = $reconnector;

        return $this;
    }
    
    /**
     * Get the database connection with its read / write type.
     * 
     * @return string|null
     */
    public function getNameWithReadWriteType()
    {
        $name = $this->getName().($this->readWriteType ? '::'.$this->readWriteType : '');
        
        return empty($name) ? null : $name;
    }

    /**
     * Get the PDO driver name.
     * 
     * @return string
     */
    public function getDriverName()
    {
        return $this->getConfig('driver');
    }
    
    /**
     * Get a human-readable name for the given connection driver.
     * 
     * @return string
     */
    public function getDriverTitle()
    {
        return $this->getDriverName();
    }
    
    /**
     * Get the database connection name.
     * 
     * @return string|null
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get an option from the configuration options.
     * 
     * @param  string|null  $option 
     * 
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }
    
    /**
     * Get the basic connection information as an array for debugging.
     * 
     * @return array
     */
    protected function getConnectionDetails(): array
    {
        $config = $this->latestReadWriteTypeUsed() === 'read'
            ? $this->readPdoConfig
            : $this->config;
            
        return [
            'driver' => $this->getDriverName(),
            'name' => $this->getNameWithReadWriteType(),
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'database' => $config['database'] ?? null,
            'unix_socket' => $config['unix_socket'] ?? null,
        ];
    }

    /**
     * Get the query grammar used by the connection.
     * 
     * @return \Syscodes\Components\Database\Query\Grammars\Grammar
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
    public function useDefaultQueryGrammar(): void
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
     * 
     * @return \Syscodes\Components\Database\Query\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }

    /**
     * Get the query post processor used by the connection.
     * 
     * @return \Syscodes\Components\Database\Query\Processors\Processor
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
    public function useDefaultPostProcessor(): void
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
     * 
     * @return \Syscodes\Components\Database\Query\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor;
    }

    /**
     * Get a schema builder instance for the connection.
     * 
     * @return \Syscodes\Components\Database\Schema\Builders\Builder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SchemaBuilder($this);
    }
    
    /**
     * Set the read PDO connection configuration.
     * 
     * @param  array  $config
     * 
     * @return static
     */
    public function setReadPdoConfig(array $config): static
    {
        $this->readPdoConfig = $config;
        
        return $this;
    }
    
    /**
     * Get the query post processor used by the connection.
     * 
     * @return \Syscodes\Components\Database\Schema\Grammars\Grammar
     */
    public function getSchemaGrammar()
    {
        return $this->schemaGrammar;
    }
    
    /**
     * Set the schema grammar used by the connection.
     * 
     * @param  \Syscodes\Components\Database\Schema\Grammars\Grammar  $grammar
     * 
     * @return $this
     */
    public function setSchemaGrammar(SchemaGrammar $grammar)
    {
        $this->schemaGrammar = $grammar;
        
        return $this;
    }
    
    /**
     * Set the schema grammar to the default implementation.
     * 
     * @return void
     */
    public function useDefaultSchemaGrammar(): void
    {
        $this->schemaGrammar = $this->getDefaultSchemaGrammar();
    }
    
    /**
     * Get the default schema grammar instance.
     * 
     * @return \Syscodes\Components\Database\Schema\Grammars\Grammar
     */
    protected function getDefaultSchemaGrammar() {}

    /**
     * Get the name of the connected database.
     * 
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Set the name of the connected database.
     * 
     * @param  string  $database
     * 
     * @return static
     */
    public function setDatabase($database): static
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Set the event dispatcher instance on the connection.
     * 
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $events
     * 
     * @return static
     */
    public function setEventDispatcher(Dispatcher $events): static
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Determine if the connection is in a "dry run".
     * 
     * @return bool
     */
    public function pretending(): bool
    {
        return $this->pretending === true;
    }

    /**
     * Get the connection query log.
     * 
     * @return array
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     * 
     * @return void
     */
    public function flushQueryLog(): void
    {
        $this->queryLog = [];
    }

    /**
     * Enable the query log on the connection.
     * 
     * @return void
     */
    public function EnableQueryLog(): void
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
     * 
     * @return void
     */
    public function disableQueryLog(): void
    {
        $this->loggingQueries = false;
    }

    /**
     * Determine whether we're logging queries.
     * 
     * @return bool
     */
    public function logging(): bool
    {
        return $this->loggingQueries;
    }
    
    /**
     * Retrieve the latest read / write type used.
     * 
     * @return 'read'|'write'|null
     */
    protected function latestReadWriteTypeUsed()
    {
        return $this->readWriteType ?? $this->latestPdoTypeRetrieved;
    }

    /**
     * Get the table prefix for the connection.
     * 
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * Set the table prefix in use by the connection.
     * 
     * @param  string  $tablePrefix
     * 
     * @return self
     */
    public function setTablePrefix($tablePrefix): self
    {
        $this->tablePrefix = $tablePrefix;

        $this->getQueryGrammar()->setTablePrefix($tablePrefix);

        return $this;
    }

    /**
     * Set the table prefix and return the grammar.
     * 
     * @param  \Syscodes\Components\Database\Grammar  $grammar
     * 
     * @return \Syscodes\Components\Database\Grammar
     */
    public function withTablePrefix(Grammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }

    /**
     * Get the connection resolver for the given driver.
     * 
     * @param  string  $driver
     * 
     * @return mixed
     */
    public static function getResolver($driver)
    {
        return static::$resolvers[$driver] ?? null;
    }
}