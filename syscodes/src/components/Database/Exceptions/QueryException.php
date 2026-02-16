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
 
namespace Syscodes\Components\Database\Exceptions;

use PDOException;
use Throwable;
use Syscodes\Components\Support\Str;

/**
 * Get a query exception.
 */
class QueryException extends PDOException
{
    /**
     * The bindings for the query.
     * 
     * @var array $bindings
     */
    protected $bindings;

    /**
     * The connection details for the query (host, port, database, etc.).
     * 
     * @var array
     */
    protected $connectionDetails = [];
    
    /**
     * The database connection name.
     * 
     * @var string
     */
    public $connectionName;

    /**
     * The SQL for the query.
     * 
     * @var string $sql
     */
    protected $sql;

    /**
     * Constructor. Create new a QueryException class instance.
     * 
     * @param  string  $connectionName
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Throwable  $previous
     * @param  array  $connectionDetails
     * 
     * @return void
     */
    public function __construct($connectionName, $sql, array $bindings, Throwable $previous, array $connectionDetails = [])
    {
        parent::__construct('', 0, $previous);
        
        $this->connectionName = $connectionName;
        $this->sql      = $sql;
        $this->bindings = $bindings;
        $this->code     = $previous->getCode();
        $this->message  = $this->formatMessage($connectionName, $sql, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message.
     * 
     * @param  string  $connectionName
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Throwable  $previous
     * 
     * @return string
     */
    protected function formatMessage($connectionName, $sql, array $bindings, Throwable $previous): string
    {
        $details = $this->formatConnectionDetails();

        return $previous->getMessage().' (Connection: '.$connectionName.$details.', SQL: '.Str::replaceArray('?', $bindings, $sql).')';
    }
    
    /**
     * Format the connection details for the error message.
     * 
     * @return string
     */
    protected function formatConnectionDetails(): string
    {
        if (empty($this->connectionDetails)) {
            return '';
        }
        
        $driver = $this->connectionDetails['driver'] ?? '';
        
        $segments = [];
        
        if ($driver !== 'sqlite') {
            if ( ! empty($this->connectionDetails['unix_socket'])) {
                $segments[] = 'Socket: '.$this->connectionDetails['unix_socket'];
            } else {
                $host = $this->connectionDetails['host'] ?? '';
                
                $segments[] = 'Host: '.(is_array($host) ? implode(', ', $host) : $host);
                $segments[] = 'Port: '.($this->connectionDetails['port'] ?? '');
            }
        }
        
        $segments[] = 'Database: '.($this->connectionDetails['database'] ?? '');
        
        return ', '.implode(', ', $segments);
    }
    
    /**
     * Get the connection name for the query.
     * 
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * Get the SQL for the query.
     * 
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Get the bindings for the query.
     * 
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    /**
     * Get information about the connection such as host, port, database, etc.
     * 
     * @return array
     */
    public function getConnectionDetails(): array
    {
        return $this->connectionDetails;
    }
}