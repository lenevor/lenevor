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

namespace Syscode\Log\Handlers;

use Psr\Log\LoggerTrait;
use Syscode\Support\Chronos;
use Syscode\Contracts\Log\Handler;
use Syscode\Log\Exceptions\LogException;

/**
 * The Lenevor Logger of errors.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class FileLogger implements Handler
{
    use LoggerTrait;

    /**
     * Array of levels to be logged.
     * 
     * @var int $loggableLevels
     */
    protected $loggableLevels = [];

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

    public function log($level, $message = null, array $context = [])
    {
        if (is_numeric($level))
        {
            $level = array_search((int) $level, $this->logLevels);
        }        

        return $this->handle($level, $message);
    }

    /**
     * Handles logging the message.
     * 
     * @param  string  $level
     * @param  string  $message
     * 
     * @return bool
     */
    public function handle($level, $message)
    {
        $level   = ENVIRONMENT.'.'.strtolower($level);
        $message = ucfirst($message);
        $message = "[{$this->getTimestamp()}] [{$level}] {$message}";
        echo $message;
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
        $logDateFormat = app('config')->get('logger.logDateFormat') ?? $this->logDateFormat;
        $originalTime  = microtime(true);
        $micro         = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date          = new Chronos(date('Y-m-d H:i:s.'.$micro, $originalTime));
        
        return $date->format($logDateFormat);
    }
    
}