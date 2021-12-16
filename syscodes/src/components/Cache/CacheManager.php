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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Cache;

use Closure;
use Syscodes\Components\Cache\Store\ApcStore;
use Syscodes\Components\Cache\Store\FileStore;
use Syscodes\Components\Cache\Store\NullStore;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Cache\Store\ApcWrapper;
use Syscodes\Components\Cache\Store\ArrayStore;
use Syscodes\Components\Cache\Store\RedisStore;
use Syscodes\Components\Cache\Store\DatabaseStore;
use Syscodes\Components\Cache\Store\MemcachedStore;
use Syscodes\Components\Cache\Exceptions\CacheException;
use Syscodes\Components\Contracts\Cache\Manager as ManagerContract;

/**
 * Class cache manager.
 * 
 * This class is responsible for loading any available cache driver.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class CacheManager implements ManagerContract
{
    /**
     * The application instance.
     * 
     * @var string $app
     */
    protected $app;

    /**
     * The registered custom drivers.
     * 
     * @var array $customDriver
     */
    protected $customDriver;

    /**
     * The cache store implementation.
     * 
     * @var array $stores
     */
    protected $stores = [];

    /**
     * Constructor. Create a new cache manager instance.
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
     * @inheritdoc
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }
    
    /**
     * @inheritdoc
     */
    public function store(string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] = $this->get($name);
    }

    /**
     * @inheritdoc
     */
    public function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given store.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     * 
     * @throws \CacheException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new CacheException(__('cache.storeNotDefined', ['name' => $name]));
        }

        if (isset($this->customDriver[$config['driver']])) {
            return $this->callCustomDriver($config);
        } else {
            $driver = 'create'.ucfirst($config['driver']).'Driver';
    
            if (method_exists($this, $driver)) {
                return $this->{$driver}($config);
            } else {
                throw new CacheException(__('cache.driverNotSupported', ['config' => $config]));
            }
        }
    }

    /**
     * Call a custom driver.
     * 
     * @param  array  $config
     * 
     * @return mixed
     */
    protected function callCustomDriver(array $config)
    {
        return $this->customDriver[$config['default']]($this->app, $config);
    }
    
    /**
     * Get the cache connection configuration.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function getConfig(string $name)
    {
        return $this->app['config']["cache.stores.{$name}"];
    }

    /**
     * Create an instance of the Apc cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createApcDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        return $this->getRepository(new ApcStore(new ApcWrapper), $prefix);
    }

    /**
     * Create an instance of the Array cache driver.
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createArrayDriver()
    {
        return $this->getRepository(new ArrayStore);
    }

    /**
     * Create an instance of the File cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createDatabaseDriver(array $config)
    {
        return;
    }

    /**
     * Create an instance of the File cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createFileDriver(array $config)
    {
        return $this->getRepository(new FileStore($this->app['files'], $config['path']));
    }

    /**
     * Create an instance of the Memcached cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createMemcachedDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        $memcached = $this->app['memcached.connector']->connect(
            $config['servers'],
            $config['persistentID'] ?? null,
            $config['options'] ?? [],
            array_filter($config['sasl'] ?? [])
        );

        return $this->getRepository(new MemcachedStore($memcached, $prefix));
    }

    /**
     * Create an instance of the Null cache driver.
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createNullDriver()
    {
        return $this->getRepository(new NullStore);
    }

    /**
     * Create an instance of the Redis cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createRedisDriver(array $config)
    {
        $redis      = $this->app['redis'];
        $prefix     = $this->getPrefix($config);
        $connection = $config['connection'] ?? 'default';

        return $this->getRepository(new RedisStore($redis, $prefix, $connection));
    }

    /**
     * Get the cache prefix. 
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function getPrefix(array $config): string
    {
        return $config['prefix'] ?? $this->app['config']['cache.prefix'];
    }
    
    /**
     * Create a new cache repository with the given implementation.
     * 
     * @param  \Syscodes\Components\Contracts\Cache\Store  $store
     *
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function getRepository(Store $store)
    {
        return new CacheRepository($store);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDriver(): string
    {
       return $this->app['config']['cache.default'];
    }
    
    /**
     * @inheritdoc
     */
    public function setDefaultDriver(string $name): void
    {
        $this->app['config']['cache.default'] = $name;
    }

    /**
     * @inheritdoc
     */
    public function extend($driver, Closure $callback): self
    {
        $this->customDriver[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Magic method.
     * 
     * Dynamically call the default driver instance.
     * 
     * @param  string  $method
     * @param  array  $params
     * 
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        return $this->store()->$method(...$params);
    }
}