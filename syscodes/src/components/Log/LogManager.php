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

namespace Syscodes\Components\Log;

use Psr\Log\LoggerInterface;
use Syscodes\Components\Contracts\Log\Handler;
use Syscodes\Components\Log\Handlers\FileLogger;
use Syscodes\Components\Log\Exceptions\LogException;

/**
 * The Lenevor Logger of errors.
 */
class LogManager implements LoggerInterface
{
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * The array of resolved logges.
     * 
     * @var array $logges
     */
    protected $logges = [];

    /**
     * Constructor. The LogManager class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a log driver instance.
     * 
     * @param  string|null  $driver  
     * 
     * @return mixed
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }
    
    /**
     * Get a log store instance by name.
     * 
     * @param  string|null  $name
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function store(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->logges[$name] = $this->get($name);
    }

    /**
     * Get the log from the local cache.
     * 
     * @param  string  $name
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function get($name)
    {
        return $this->logges[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given log.
     * 
     * @param  string  $name
     * 
     * @return \Psr\Log\LoggerInterface
     * 
     * @throws \Syscodes\Components\Log\Exceptions\LogException
     */
    protected function resolve($name)
    {
        $config = $this->configurationLogger($name);

        if (is_null($config)) {
            throw new LogException(__('logger.LogNotDefined', ['name' => $name]));
        }

        $driver = 'create'.ucfirst($config['driver']).'Driver';
    
        if (method_exists($this, $driver)) {
            return $this->{$driver}($config, $this->app);
        }
        
        throw new LogException(__('logger.driverNotSupported', ['config' => $config]));
    }

    /**
     * Get the log connection configuration.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function configurationLogger(string $name): array
    {
        return $this->app['config']["logger.handlers.{$name}"];
    }

    /**
     * Create an instance of the File log driver.
     * 
     * @param  array  $config
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return \Psr\Log\LoggerInterface
     */
    protected function createFileDriver(array $config, $app)
    {
        return $this->getLogger(new FileLogger($config, $app));
    }

    /**
     * Create a new log with the given implementation.
     * 
     * @param  \Syscodes\Components\Contracts\Log\Handler  $logger
     *
     * @return \Syscodes\Components\Contracts\Log\Handler
     */
    public function getLogger(Handler $logger)
    {
        return new Logger($logger);
    }

    /**
     * Get the default log driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['logger.default'];
    }
    
    /**
     * Set the default log driver name.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    public function setDefaultDriver(string $name)
    {
        $this->app['config']['logger.default'] = $name;
    }

    /**
     * System is unusable.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->driver()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->driver()->alert($message, $context);
    }

    /**
     * Critical conditions.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->driver()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->driver()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->driver()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->driver()->notice($message, $context);
    }

    /**
     * Interesting events.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->driver()->info($message, $context);
    }

    /**
     * Detailed debug information.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->driver()->debug($message, $context);
    }

    /**
     * Log a message to the logs.
     * 
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->driver()->log($level, $message, $context);
    }

    /**
     * Magic method.
     * 
     * Dynamically call the default driver instance.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->{$method}(...$parameters);
    }
}