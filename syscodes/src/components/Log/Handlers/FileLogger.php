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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Log\Handlers;

use DateTime;
use Throwable;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Contracts\Log\Handler;
use Syscodes\Components\Log\Exceptions\LogException;
use Syscodes\Components\Log\Concerns\ParseLogEnvironment;

/**
 * The Lenevor Logger of errors.
 */
class FileLogger implements Handler
{
    use ParseLogEnvironment;
    
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * Format of the timestamp for log files.
     * 
     * @var string $logDateFormat
     */
    protected $logDateFormat = 'Y-m-d H:i:s';

    /**
     * Extension to use for log files.
     * 
     * @var string $logExtension
     */
    protected $logFileExtension;

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
    protected $logFilePermissions;

    /**
     * Caches instances of the handlers.
     * 
     * @var string $logHandler
     */
    protected $logHandler;

    /**
     * Write message in log file.
     * 
     * @var string $logMessage
     */
    protected $logMessage;

    /**
     * Constructor. The FileLogger class instance.
     * 
     * @param  array  $config
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function __construct(array $config, $app)
    {
        $this->app                = $app;
        $this->logFilePath        = $config['path'].DIRECTORY_SEPARATOR ?? $this->app->storagePath().DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR;
        $this->logFileExtension   = ltrim(empty($config['extension']) ? 'log' : $config['extension'], '.');
        $this->logFilePermissions = $config['permission'] ?? 0644;
    }

    /**
     * Magic method.
     * 
     * Destructor. Close.
     * 
     * @return bool
     */
    public function __destruct()
    {
        if ($this->logHandler) {
            fclose($this->logHandler);
        }
    }
    
    /**
     * Logs with an arbitrary level.
     * 
     * @param  mixed  $level
     * @param  string  $message
     * @param  array  $context
     * 
     * @return bool
     */
    public function log($level, $message, array $context = []): bool
    {
        $message = $this->exchangeProcess($message, $context);

        $this->handle($level, $message);

        return true;
    }

    /**
     * Replaces any placeholders in the message with variables
     * from the context.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return mixed
     */
    protected function exchangeProcess($message, array $context = [])
    {
        if ( ! is_string($message)) {
            return $message;
        }
        
        $replace = [];
        
        foreach ($context as $key => $value) {
            if ($key === 'exception' && $value instanceof Throwable) {
                $value = $value->getMessage().' '.$this->cleanFileNames($value->getFile()).':'.$value->getLine();
                // Todo - sanitize input before writing to file?
                $replace["{{$key}}"] = $value;
            } elseif (null === $value || is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $replace["{{$key}}"] = $value;
            } elseif ($value instanceof DateTime) {
                $replace["{{$key}}"] = $value->format(DateTime::RFC3339);
            } elseif (is_object($value)) {
                $replace["{{$key}}"] = '[object '.get_class($value).']';
            } else {
                $replace["{{$key}}"] = '['.gettype($value).']';
            }
        }
        
        // Add special placeholders
        $replace['{postVars}'] = '$_POST: '.print_r($_POST, true);
        $replace['{getVars}']  = '$_GET: '.print_r($_GET, true);
        $replace['{env}']      = '['.$this->getLogEnvironment().']';
        
        return strtr($message, $replace);
    }

    /**
     * Cleans the paths of filenames by replacing APPPATH, SYSPATH
     * with the actual var. i.e.
     * 
     * /var/www/site/app/Http/Controllers/Home.php
     * 
     * becomes:
     * 
     * APPPATH/Http/Controllers/Home.php
     * 
     * @param  string  $file
     * 
     * @return string
     */
    protected function cleanFileNames(string $file)
    {
        $file = str_replace(APP_PATH, 'APPPATH/', $file);
        $file = str_replace(SYS_PATH, 'SYSPATH/', $file);
        
        return $file;
    }

    /**
     * Handles logging the message.
     * 
     * @param  string  $level
     * @param  string  $message
     * 
     * @return bool
     */
    public function handle($level, $message): bool
    {        
        $result = '';
        
        $path = $this->logFilePath.'lenevor-'.date('Y-m-d').'.'.$this->logFileExtension;

        if ( ! is_file($path)) {
            $newFile = true;

            if ($this->logFileExtension === 'php') {
                $this->logMessage .= "<?php // Log file was generated ?>\n\n";
            }
        }

        $this->open($path);

        $this->logMessage($level, $message);
        
        flock($this->logHandler, LOCK_EX);
        
        for ($written = 0, $length = strlen($this->logMessage); $written < $length; $written += $result) {
            if (($result = fwrite($this->logHandler, substr($this->logMessage, $written))) === false) {
                // if we get this far, we'll never see this during travis-ci
                // @codeCoverageIgnoreStart
                break;
                // @codeCoverageIgnoreEnd
            }
        }
        
        flock($this->logHandler, LOCK_UN);
        
        if (isset($newFile) && $newFile === true) {
            chmod($path, $this->logFilePermissions);
        }
        
        return is_int($result);
    }

    /**
     * Opens the current file.
     * 
     * @param  string  $path
     * 
     * @return bool
     */
    private function open($path): bool
    {
        if (false === $this->logHandler = is_resource($path) ? $path : @fopen($path, 'ab')) {
            throw new LogException(sprintf('Unable to open "%s".', $path));
        }

        return true;
    }

    /**
     * Write message of log file.
     * 
     * @param  mixed  $level
     * @param  string  $messsage
     * 
     * @return static
     */
    private function logMessage($level, $message): static
    {
        $level = $this->getLogEnvironment().'.'.strtolower($level);

        $message = ucfirst($message);

        $this->logMessage .= "[{$this->getTimestamp()}] [{$level}] {$message}\n";

        return $this;
    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     * 
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     * 
     * @return string
     */
    private function getTimestamp(): string
    {
        $logDateFormat = $this->app['config']['logger.dateFormat'] ?? $this->logDateFormat;
        $originalTime  = microtime(true);
        $micro         = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date          = new Chronos(date('Y-m-d H:i:s.'.$micro, (int) $originalTime));
        
        return $date->format($logDateFormat);
    }   
}