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

namespace Syscodes\Components\Session;

use Syscodes\Components\Session\Handlers\ArraySessionHandler;
use Syscodes\Components\Session\Handlers\CacheSessionHandler;
use Syscodes\Components\Session\Handlers\CookieSessionHandler;
use Syscodes\Components\Session\Handlers\DatabaseSessionHandler;
use Syscodes\Components\Session\Handlers\FileSessionHandler;
use Syscodes\Components\Session\Handlers\NullSessionHandler;
use Syscodes\Components\Support\Manager;

/**
 * Lenevor session storage.
 */
class SessionManager extends Manager
{
    /**
     * Call a custom driver creator.
     * 
     * @param  string  $driver
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function callCustomCreator($driver)
    {
        return $this->buildSession(parent::callCustomCreator($driver));
    }

    /**
     * Create an instance of the "null" session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createNullDriver()
    {
        return $this->buildSession(new NullSessionHandler);
    }

    /**
     * Create an instance of the "array" session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createArrayDriver()
    {
        return $this->buildSession(new ArraySessionHandler(
            $this->config->get('session.lifetime')
        ));
    }
    
    /**
     * Create an instance of the "cookie" session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createCookieDriver()
    {
        return $this->buildSession(new CookieSessionHandler(
            $this->container->make('cookie'), 
            $this->config->get('session.lifetime'),
            $this->config->get('session.expireOnClose')
        ));
    }

    /**
     * Create an instance of the "file" session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createFileDriver()
    {
        $path     = $this->config->get('session.files');
        $lifetime = $this->config->get('session.lifetime');

        return $this->buildSession(new FileSessionHandler(
            $this->container->make('files'), $path, $lifetime
        ));
    }

    /**
     * Create an instance of the "database" session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createDatabaseDriver()
    {
        $table    = $this->config->get('session.table');
        $lifetime = $this->config->get('session.lifetime') ;

        return $this->buildSession(new DatabaseSessionHandler(
            $this->getDatabaseConnection(),
            $table,
            $lifetime,
            $this->container
        ));
    }

    /**
     * Get the database connection for the database driver.
     * 
     * @return \Syscodes\Components\Database\Connections\Connection
     */
    protected function getDatabaseConnection()
    {
        $connection = $this->config->get('session.connection');

        return $this->container->make('db')->connection($connection);
    }

    /**
     * Create an instance of the APC session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createApcDriver()
    {
        return $this->createCacheBased('apc');
    }

    /**
     * Create an instance of the Memcached session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createMemcachedDriver()
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Redis session driver.
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createRedisDriver()
    {
        $store = $this->createCacheHandler('redis');

        $store->getCache()->getStore()->setConnection(
            $this->config->get('session.connection')
        );

        return $this->buildSession($store);
    }

    /**
     * Create an instance of a cache driven driver.
     * 
     * @param  string  $driver
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function createCacheBased($driver)
    {
        return $this->buildSession($this->createCacheHandler($driver));
    }

    /**
     * Create the cache session handler instance.
     * 
     * @param  string  $driver
     * 
     * @return \Syscodes\Components\Session\Handlers\CacheSessionHandler
     */
    protected function createCacheHandler($driver)
    {
        $store = $this->config->get('session.store') ?: $driver;

        return new CacheSessionHandler(
            clone $this->container->make('cache')->store($store),
            $this->config->get('session.lifetime')
        );
    }

    /**
     * Build the session instance.
     * 
     * @param  \SessionHandlerInterface  $handler
     * 
     * @return \Syscodes\Components\Session\Store
     */
    protected function buildSession($handler)
    {
        return new Store(
            $this->config->get('session.cookie'),
            $handler,
            $id = null,
            $this->config->get('session.serialization', 'php')
        );
    }
    
    /**
     * Get the session configuration.
     * 
     * @return array
     */
    public function getSessionConfig()
    {
        return $this->config->get('session');
    }

    /**
     * Get the default driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('session.driver');
    }
    
    /**
     * Set the default session driver name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultDriver($name): void
    {
        $this->config->set('session.driver', $name);
    }
}