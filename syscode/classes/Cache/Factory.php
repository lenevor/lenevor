<?php

namespace Syscode\Cache;

use Syscode\Config\Configure;
use Syscode\Cache\Drivers\MemcachedStore;
use Syscode\Cache\Exceptions\CacheDriverException;

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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */
 class CacheFactory
 {
    /**
     * The fileSystem instance.
     * 
     * @var string $file
     */
    protected  $files;

    /**
     * The cache store implementation.
     * 
     * @var array $stores
     */
    protected $stores = [];

    /**
     * Constructor. Create a new cache manager instance.
     * 
     * @param  \Syscode\FileSystem\FileSystem  $files
     * 
     * @return void  
     */
    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * Create an instance of the Apc cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscode\Cache\Drivers\ApcStore
     */
    protected function createApcDriver($config)
    {
        $prefix = $this->getPrefix($config);

        $cache = $this->getFullManagerPath('apc');

        return new $cache($config, $prefix);
    }

    /**
     * Create an instance of the Array cache driver.
     * 
     * @return \Syscode\Cache\Drivers\ArrayStore
     */
    protected function createArrayDriver()
    {
        $cache = $this->getFullManagerPath('array');

        return new $cache();
    }

    /**
     * Create an instance of the File cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscode\Cache\Drivers\DatabaseStore
     */
    protected function createDatabaseDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        $cache  = $this->getFullManagerPath('database');

        return new $cache($config['connection'], $config['table'], $prefix);
    }

    /**
     * Create an instance of the File cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscode\Cache\Drivers\FileStore
     */
    protected function createFileDriver(array $config)
    {
        $cache = $this->getFullManagerPath('file');

        return new $cache($this->files, $config['path']);
    }

    /**
     * Create an instance of the Memcached cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscode\Cache\Drivers\MemcachedStore
     */
    protected function createMemcachedDriver(array $config)
    {
        $prefix = getPrefix($config);

        $cache = $this->getFullManagerPath('memcached');

        return new $cache($config, $prefix);
    }

    /**
     * Create an instance of the Redis cache driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscode\Cache\Drivers\RedisStore
     */
    protected function createRedisDriver(array $config)
    {
        $prefix = getPrefix($config);

        $cache = $this->getFullManagerPath('redis');

        return new $cache($config, $prefix);
    }

    /**
     * Get a cache driver instance.
     * 
     * @param  string|null
     * 
     * @return mixed
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Get the store from the local cache.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    public function get($name)
    {
        return isset($this->stores[$name]) ? $this->stores[$name] : $this->resolve($name);
    }

    /**
     * Get the cache connection configuration.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function getConfig($name)
    {
        return Configure::get("cache.stores.{$name}");
    }

    /**
     * Get the default cache driver name.
     * 
     * @return  array
     */
    public function getDefaultDriver()
    {
       return Configure::get('cache.driver');
    }

    /**
     * Get manager path cache.
     * 
     * @param  string  $cacheManager
     *
     * @return string
     */
    protected function getFullManagerPath($cacheManager)
    {
        $cache = ucfirst($cacheManager);

        return "\\Lenevor\\Sys\\Cache\\Drivers\\{$cache}Store";
    }

    /**
     * Get the cache prefix. 
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function getPrefix(array $config)
    {
        return array_get($config, 'prefix') ?: Configure::get('cache.prefix');
    }
    
    /**
     * Set the default cache driver name.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    public function setDefaultDriver($name)
    {
        return Configure::set('cache.driver', $name);
    }

    /**
     * Get a cache store instance by name.
     * 
     * @param  string|null  $name
     * 
     * @return mixed
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] = $this->get($name);
    }

    /**
     * Resolve the given store.
     * 
     * @param  string  $name
     * 
     * @return mixed
     * 
     * @throws  CacheDriverException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config))
        {
            throw new CacheDriverException(__('cache.storeNotDefined'));
        }

        $driver = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driver))
        {
            return $this->{$driver}($config);
        }
        else
        {
            throw new CacheDriverException(__('cache.driverNotSupported'));
        }
    }

    /**
     * Dynamically call the default driver instance.
     * 
     * @param  string  $method
     * @param  array   $params
     * 
     * @return mixed
     */
    public function __call($method, $params)
    {
        return $this->store()->$method(...$params);
    }
 }