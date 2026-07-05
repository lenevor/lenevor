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

namespace Syscodes\Components\Cache;

use Closure;
use Syscodes\Components\Cache\Store\ApcStore;
use Syscodes\Components\Cache\Store\ApcWrapper;
use Syscodes\Components\Cache\Store\ArrayStore;
use Syscodes\Components\Cache\Store\DatabaseStore;
use Syscodes\Components\Cache\Store\FileStore;
use Syscodes\Components\Cache\Store\NullStore;
use Syscodes\Components\Cache\Store\MemcachedStore;
use Syscodes\Components\Cache\Store\RedisStore;
use Syscodes\Components\Cache\Store\SessionStore;
use Syscodes\Components\Cache\Exceptions\CacheException;
use Syscodes\Components\Contracts\Cache\Factory as FactoryContract;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Contracts\Events\Dispatcher as DispatcherContract;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Traits\RebindsCallbacksToSelf;
use ReflectionException;
use RuntimeException;

use function Syscodes\Components\Support\enum_value;

/**
 * Class cache manager.
 * 
 * This class is responsible for loading any available cache driver.
 */
class CacheManager implements FactoryContract
{
    use RebindsCallbacksToSelf;

    /**
     * The application instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application
     */
    protected $app;

    /**
     * The registered custom drivers.
     * 
     * @var array
     */
    protected $customDriver;

    /**
     * The cache store implementation.
     * 
     * @var array
     */
    protected $stores = [];

    /**
     * Constructor. Create a new cache manager instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app 
     * @return void  
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a cache driver instance.
     * 
     * @param  \UnitEnum|string|null  $driver 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }
    
    /**
     * Get a cache store instance by name.
     * 
     * @param  string|null  $name 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function store($name = null)
    {
        $name = enum_value($name) ?? $this->getDefaultDriver();

        return $this->stores[$name] ??= $this->resolve($name);
    }

    /**
     * Resolve the given store.
     * 
     * @param  string  $name 
     * @return \Syscodes\Components\Cache\CacheRepository
     * 
     * @throws \Syscodes\Components\Cache\Exceptions\CacheException
     */
    protected function resolve(string $name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new CacheException("Cache store [{$name}] is not defined.");
        }

        $config = Arr::add($config, 'store', $name);

        return $this->build($config);
    }

    /**
     * Build a cache repository with the given configuration.
     *
     * @param  array  $config 
     * @return \Syscodes\Components\Cache\CacheRepository
     *
     * @throws \Syscodes\Components\Cache\Exceptions\CacheException
     */
    public function build(array $config)
    {
        $config = Arr::add($config, 'store', $config['name'] ?? 'ondemand');

        if (isset($this->customDriver[$config['driver']])) {
            return $this->callCustomDriver($config);
        }

        $driver = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driver)) {
            return $this->{$driver}($config);
        }

        throw new CacheException(__('cache.driverNotSupported', ['config' => $config['driver']]));
    }

    /**
     * Call a custom driver.
     * 
     * @param  array  $config 
     * @return mixed
     */
    protected function callCustomDriver(array $config)
    {
        return $this->customDriver[$config['driver']]($this->app, $config);
    }
    
    /**
     * Get the cache connection configuration.
     * 
     * @param  string  $name 
     * @return array|null
     */
    protected function getConfig(string $name): array|null
    {
        return $name !== 'null'
            ? $this->app['config']["cache.stores.{$name}"]
            : ['driver' => 'null'];
    }

    /**
     * Create an instance of the Apc cache driver.
     * 
     * @param  array  $config 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createApcDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        return $this->getRepository(
            new ApcStore(new ApcWrapper, $prefix),
            $config
        );
    }

    /**
     * Create an instance of the Array cache driver.
     * 
     * @param  array  $config 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createArrayDriver(array $config)
    {
        return $this->getRepository(
            new ArrayStore($config['serialize'] ?? false),
            $config
        );
    }

    /**
     * Create an instance of the File cache driver.
     * 
     * @param  array  $config 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createDatabaseDriver(array $config)
    {
        $connection = $this->app['db']->connection($config['connection'] ?? null);
        
        return $this->repository(
            new DatabaseStore(
                $connection,
                $config['table'],
                $this->getPrefix($config)
            ),
            $config
        );
    }

    /**
     * Create an instance of the File cache driver.
     * 
     * @param  array  $config 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createFileDriver(array $config)
    {
        return $this->getRepository(
            new FileStore($this->app['files'], $config['path']),
            $config
        );
    }

    /**
     * Create an instance of the Memcached cache driver.
     * 
     * @param  array  $config 
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
        return $this->getRepository(new NullStore, []);
    }

    /**
     * Create an instance of the Redis cache driver.
     * 
     * @param  array  $config
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createRedisDriver(array $config)
    {
        $redis = $this->app['redis'];
        $prefix = $this->getPrefix($config);
        $connection = $config['connection'] ?? 'default';

        return $this->getRepository(new RedisStore($redis, $prefix, $connection));
    }

    /**
     * Create an instance of the session cache driver.
     *
     * @param  array  $config 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    protected function createSessionDriver(array $config)
    {
        return $this->getRepository(
            new SessionStore(
                $this->getSession(),
                $config['key'] ?? '_cache',
            ),
            $config
        );
    }

    /**
     * Get the session store implementation.
     *
     * @return \Syscodes\Components\Contracts\Session\Session
     *
     * @throws \InvalidArgumentException
     */
    protected function getSession()
    {
        $session = $this->app['session'] ?? null;

        if ( ! $session) {
            throw new CacheException('Session store requires session manager to be available in container.');
        }

        return $session;
    }

    /**
     * Get the cache prefix. 
     * 
     * @param  array  $config 
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
     * @param  array  $config
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function getRepository(Store $store, array $config = [])
    {
        return take(new CacheRepository($store, Arr::only($config, ['store'])), function ($repository) use ($config) {
            if ($config['events'] ?? true) {
                $this->setEventDispatcher($repository);
            }
        });
    }

    /**
     * Set the event dispatcher on the given repository instance.
     *
     * @param  \Syscodes\Components\Cache\CacheRepository  $repository 
     * @return void
     */
    protected function setEventDispatcher(CacheRepository $repository)
    {
        if ( ! $this->app->bound(DispatcherContract::class)) {
            return;
        }

        $repository->setEventDispatcher(
            $this->app[DispatcherContract::class]
        );
    }

    /**
     * Get the default cache driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
       return $this->app['config']['cache.default'] ?? 'null';
    }
    
    /**
     * Set the default cache driver name.
     * 
     * @param  string  $name 
     * @return void
     */
    public function setDefaultDriver(string $name): void
    {
        $this->app['config']['cache.default'] = enum_value($name);
    }

    /**
     * Register a custom driver creator Closure.
     * 
     * @param  string  $driver
     * @param  \Closure  $callback 
     * @return static
     */
    public function extend(string $driver, Closure $callback): static
    {
        try {
            $callback = $this->bindCallbackToSelf($callback) ?? throw new RuntimeException('Unable to bind custom driver callback');
        } catch (ReflectionException $e) {
            throw new RuntimeException('Unable to bind custom driver callback', previous: $e);
        }

        $this->customDriver[$driver] = $callback;

        return $this;
    }

    /**
     * Magic method.
     * 
     * Dynamically call the default driver instance.
     * 
     * @param  string  $method
     * @param  array  $parameters 
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->store()->$method(...$parameters);
    }
}