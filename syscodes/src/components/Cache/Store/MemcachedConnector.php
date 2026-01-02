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

namespace Syscodes\Components\Cache\Store;

use Memcached;
use Syscodes\Components\Core\Http\Exceptions\LenevorException;

/**
 * Allows to connect to the Memcached cache system.
 */
class MemcachedConnector
{
    /**
     * Create new Memcached connection.
     * 
     * @param  array  $servers
     * @param  string  $connectionId
     * @param  array  $options
     * @param  array  $credentials
     * 
     * @return \Memcached
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\LenevorException
     */
    public function connect(array $servers, $connectionId = null, array $options = [], array $credentials = [])
    {
        $memcached = $this->getMemcached($connectionId, $credentials, $options);

        if ( ! $memcached->getServerList()) {
            foreach ($servers as $server) {
                $memcached->addServer(
                    $server['host'],
                    $server['port'],
                    $server['weight']
                );
            }
        }

        if (false === $memcached->getVersion()) {
            throw new LenevorException('Could not establish Memcached connection');
        }

        return $memcached;
    }

    /**
     * Get a new Memcached instance.
     * 
     * @param  int  $connectionId
     * @param  array  $credentials
     * @param  array  $options
     * 
     * @return \Memcached
     */
    protected function getMemcached($connectionId, array $credentials, array $options)
    {
        $memcached = $this->createMemcachedInstance($connectionId);

        if (count($credentials) === 2) {
            $this->setCredentials($memcached, $credentials);
        }

        if (count($options)) {
            $memcached->setOptions($options);
        }

        return $memcached;
    }

    /**
     * Create the Memcached instance.
     * 
     * @param  string|null  $connectionId
     * 
     * @return \Memcached
     */
    protected function createMemcachedInstance($connectionId)
    {
        return empty($connectionId) ? new Memcached : new Memcached($connectionId);
    }

    /**
     * Set the SASL credentials on the Memcached connection.
     * 
     * @param  \Memcached  $memcached
     * @param  array  $credentials
     * 
     * @return void
     */
    protected function setCredentials($memcached, $credentials): void
    {
        [$username, $password] = $credentials;
        
        $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        
        $memcached->setSaslAuthData($username, $password);
    }
}