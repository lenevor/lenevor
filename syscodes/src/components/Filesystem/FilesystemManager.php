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

namespace Syscodes\Components\Filesystem;

use InvalidArgumentException;
use Syscodes\Components\Contracts\Filesystem\Factory;

/**
 * Allows manage the distint adapters of file system.
 */
class FilesystemManager implements Factory
{
    /**
     * The application instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * The registered custom driver creators.
     * 
     * @var array $cumtomCreators
     */
    protected $customCreators = [];
    
    /**
     * The array of resolved filesystem drivers.
     * 
     * @var array $disks
     */
    protected $disks = [];

    /**
     * Constructor. Create a new FilesystemManager instance.
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
     * Get a filesystem instance.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Contracts\Filesystem\Filesystem 
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        
        return $this->disks[$name] = $this->get($name);
    }
    
    /**
     * Attempt to get the disk from the local cache.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Contracts\Filesystem\Filesystem
     */
    protected function get($name)
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }
    
    /**
     * Resolve the given disk.
     * 
     * @param  string  $name
     * @param  array|null  $config
     * 
     * @return \Syscodes\Components\Contracts\Filesystem\Filesystem
     * 
     * @throws \InvalidArgumentException
     */
    protected function resolve($name, $config = null)
    {
        $config = $config ?? $this->getConfig($name);
        
        if (empty($config['driver'])) {
            throw new InvalidArgumentException("Disk [{$name}] does not have a configured driver");
        }
        
        $name = $config['driver'];

        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator($config);
        }
        
        $driverMethod = 'create'.ucfirst($name).'Driver';
        
        if ( ! method_exists($this, $driverMethod)) {
            throw new InvalidArgumentException("Driver [{$name}] is not supported");
        }
        
        return $this->{$driverMethod}($config);
    }
    
    /**
     * Call a custom driver creator.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Contracts\Filesystem\Filesystem
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }
    
    /**
     * Create an instance of the local driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Contracts\Filesystem\Filesystem
     */
    public function createLocalDriver(array $config)
    {
        $adapter = new Filesystem;

        return new FilesystemAdapter($adapter, $config);
    }
        
    /**
     * Set the given disk instance.
     * 
     * @param  string  $name
     * @param  mixed  $disk
     * 
     * @return static
     */
    public function set($name, $disk): static
    {
        $this->disks[$name] = $disk;
        
        return $this;
    }
    
    /**
     * Get the filesystem connection configuration.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function getConfig($name): string
    {
        return $this->app['config']["filesystems.disks.{$name}"] ?: [];
    }
    
    /**
     * Get the default driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['filesystems.default'];
    }
}