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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Container\Container;

/**
 * This class manage the creation of driver based components.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class Manager
{
    /**
     * The application instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * The configuration repository instance.
     * 
     * @var \Syscodes\Components\Contracts\Config\Configure $config
     */
    protected $config;

    /**
     * The registered custom driver creators.
     * 
     * @var array $customCreators
     */
    protected $customCreators = [];

    /**
     * The array of created drivers.
     * 
     * @var array $drivers
     */
    protected $drivers = [];

    /**
     * Constructor. The Manager class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
     * 
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->app    = $container;
        $this->config = $container->make('config');
    }

    /**
     * Get the default driver name.
     * 
     * @return string
     */
    abstract public function getDefaultDriver();

    /**
     * Get a driver instance.
     * 
     * @param  string|null  $driver
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf('Unable to resolve NULL driver for [%s].', static::class));
        }

        if ( ! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create a new driver instance.
     * 
     * @param  string  $driver
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } else {
            $method = 'create'.Str::studlycaps($driver).'Driver';

            if (method_exists($this, $method)) {
                return $this->$method();
            }
        }

        throw new InvalidArgumentException("Driver [{$driver}] not supported.");
    }

    /**
     * Call a custom driver creator.
     * 
     * @param  string  $driver
     * 
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->customCreators[$driver]($this->app);
    }

    /**
     * Register a custom driver creator Closure.
     * 
     * @param  string  $driver
     * @param  \Closure  $callback
     * 
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created drivers.
     * 
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
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