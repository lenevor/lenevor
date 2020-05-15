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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.4.0
 */

namespace Syscode\Session;

use Syscode\Support\Manager;
use Syscode\Session\Handlers\{ FileSession, NullSession, CacheBasedSession };

/**
 * Lenevor session storage.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class SessionManager extends Manager
{
    /**
     * Call a custom driver creator.
     * 
     * @param  string  $driver
     * 
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->buildSession(parent::callCustomCreator($driver));
    }
    /**
     * Create an instance of the array session driver.
     * 
     * @return \Syscode\Session\Store
     */
    protected function createArrayDriver()
    {
        return $this->buildSession(new NullSession);
    }

    /**
     * Create an instance of the file session driver.
     * 
     * @return \Syscode\Session\Store
     */
    protected function createFileDriver()
    {
        $lifetime = $this->config->get('session.lifetime');
        $path     = $this->config->get('session.files');

        return $this->buildSession(new FileSession(
                $this->app->make('files'), $path, $lifetime
        ));
    }

    /**
     * Create an instance of the APC session driver.
     * 
     * @return \Syscode\Session\Store
     */
    protected function createApcDriver()
    {
        return $this->createCacheBased('apc');
    }

    /**
     * Create an instance of the Memcached session driver.
     * 
     * @return \Syscode\Session\Store
     */
    protected function createMemcachedDriver()
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Redis session driver.
     * 
     * @return \Syscode\Session\Store
     */
    protected function createRedisDriver()
    {
        $store      = $this->createCacheBased('redis');
        $connection = $this->config->get('session.connection');

        $store->getRedis()->setConnection($connection);

        return $this->buildSession($store);
    }

    /**
     * Create an instance of a cache driven driver.
     * 
     * @param  string  $driver
     * 
     * @return \Syscode\Session\Store
     */
    protected function createCacheBased($driver)
    {
        return $this->buildSession($this->createCacheBased($driver));
    }

    /**
     * Create the cache based session handler instance.
     * 
     * @param  string  $driver
     * 
     * @return \Syscode\Session\Handlers\CacheBasedSession
     */
    protected function createCacheHandler($driver)
    {
        $store = $this->config->get('session.store') ?: $driver;

        return new CacheBasedSession(
            $this->app->make('cache')->driver($store),
            $this->config->get('session.lifetime')
        );
    }

    /**
     * Build the session instance.
     * 
     * @param  \SessionHandlerInterface  $handler
     * 
     * @return \Syscode\Session\Store
     */
    protected function buildSession($handler)
    {
        return new Store($this->config->get('session.cookie'), $handler);
    }

    /**
     * Get the default session driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('session.driver');
    }
}