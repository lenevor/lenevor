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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.4.0
 */

namespace Syscode\Log;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Syscode\Support\Chronos;
use Syscode\Log\Exceptions\LogException;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * The Lenevor Logger of errors.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Logger implements PsrLoggerInterface
{
    use LoggerTrait;

    /**
     * The application implementation.
     *
     * @var \Syscode\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * Format of the timestamp for log files.
     * 
     * @var string $logDateFormat
     */
    protected $logDateFormat = 'Y-m-d H:i:s';

    /**
     * Path to the log file.
     * 
     * @var string $logFilePath
     */
    protected $logFilePath;

    /**
     * Octal notation for default permissions of the log file.
     * 
     * @var int $logFilePermissions
     */
    protected $logFilePermissions = 0644;

    /**
     * Array of levels to be logged.
     * 
     * @var int $loggableLevels
     */
    protected $loggableLevels = [];

    /**
     * Caches instances of the handlers.
     * 
     * @var array $logHandlers
     */
    protected $logHandlers = [];

    /**
     * Holds the configuration for each handler.
     * 
     * @var array $logHandlerConfig
     */
    protected $logHandlerConfig = [];

    /**
     * Array of log levels.
     * 
     * @var array $loglevels
     */
    protected $loglevels = [
        LogLevel::EMERGENCY => 1,
        LogLevel::ALERT     => 2,
        LogLevel::CRITICAL  => 3,
        LogLevel::ERROR     => 4,
        LogLevel::WARNING   => 5,
        LogLevel::NOTICE    => 6,
        LogLevel::INFO      => 7,
        LogLevel::DEBUG     => 8,
    ];

    /**
     * Constructor. The Logger class instance.
     * 
     * @param  \Syscode\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app           = $app;
        $this->logDateFormat = $this->app['config']->get('logger.logDateFormat') ?? $this->logDateFormat;
    }

    public function debug($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     * 
     * @param  string  $level
     * @
     */
    public function log($level, $message = null, array $context = [])
    {
        $level = ENVIRONMENT.'.'.strtoupper($level);
        $message = "[{$this->getTimestamp()}] [ {$level} ]: {$message}";

        echo $message.PHP_EOL;
    }
    
    /**
     * Gets the correctly formatted Date/Time for the log entry.
     * 
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     * 
     * @return string
     */
    private function getTimestamp()
    {
        $originalTime = microtime(true);
        $micro        = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date         = new Chronos(date('Y-m-d H:i:s.'.$micro, $originalTime));
        
        return $date->format($this->logDateFormat);
    }
}