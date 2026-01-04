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

namespace Syscodes\Components\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Syscodes\Components\Contracts\Log\Handler;
use Syscodes\Components\Log\Exceptions\LogException;

/**
 * The Lenevor Logger of errors.
 */
class Logger implements LoggerInterface
{
    /**
     * The underlying logger implementation.
     * 
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * Array of log levels.
     * 
     * @var array $loglevels
     */
    protected $logLevels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
    ];

    /**
     * Constructor. The Logger class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Log\Handler  $logger
     * 
     * @return void
     */
    public function __construct(Handler $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log a warning message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a alert message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a critical message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a error message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a warning message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a notice message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a info message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a debug message to the logs.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     * 
     * @param  string  $level
     * @param  string|null  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function log($level, $message = null, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Write a message to the log.
     * 
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    protected function writeLog($level, $message, array $context = [])
    {
        if (is_numeric($level)) {
            $level = array_search((int) $level, $this->logLevels);
        } 
        
        if ( ! array_key_exists($level, $this->logLevels)) {
            throw new LogException(__('response.notFoundLevel', ['level' => $level]));
        }

        $this->logger->log($level, $message, $context);
    }
    
    /**
     * Magic method.
     * 
     * Dynamically proxy method calls to the underlying logger.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ( ! array_key_exists($method, $this->logLevels)) {
            throw new LogException(__('response.notFoundLevel', ['level' => $method]));
        }

        return $this->logger->{$method}(...$parameters);
    }
}