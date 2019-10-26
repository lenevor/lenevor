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
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

/**
 * The Lenevor Logger of errors.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Logger extends AbstractLogger
{
    /**
     * This holds the file handle for this instance's log file.
     * 
     * @var string $logFileHandler
     */
    protected $logFileHandler;

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
     * Array of log levels.
     * 
     * @var array $loglevels
     */
    protected $loglevels = [
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
     * Anything options not considered 'core' to the logging library should be
     * settable view the third parameter in the constructor.
     * 
     * @var array $options
     */
    protected $options = [
        'extension'      => 'txt',
        'dateFormat'     => 'Y-m-d G:i:s.u',
        'filename'       => false,
        'flushFrequency' => false,
        'prefix'         => 'log_',
        'logFormat'      => false,
        'appendContext'  => true,
    ];

    /**
     * Logs with an arbitrary level.
     * 
     * 
     */
    public function log($level, $message, array $context = [])
    {
        
    }
}