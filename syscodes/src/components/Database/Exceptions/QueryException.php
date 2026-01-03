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
     * The SQL for the query.
     * 
     * @var string $sql
     */
    protected $sql;

    /**
     * Constructor. Create new a QueryException class instance.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Throwable  $previous
     * 
     * @return void
     */
    public function __construct($sql, array $bindings, Throwable $previous)
    {
        parent::__construct('', 0, $previous);

        $this->sql      = $sql;
        $this->bindings = $bindings;
        $this->code     = $previous->getCode();
        $this->message  = $this->formatMessage($sql, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message.
     * 
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Throwable  $previous
     * 
     * @return string
     */
    protected function formatMessage($sql, array $bindings, Throwable $previous): string
    {
        return $previous->getMessage().' (SQL: '.Str::replaceArray('?', $bindings, $sql).')';
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
}