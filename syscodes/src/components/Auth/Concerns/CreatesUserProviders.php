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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth\Concerns;

use InvalidArgumentException;
use Syscodes\Components\Auth\Providers\DatabaseUserProvider;
use Syscodes\Components\Auth\Providers\ErostrineUserProvider;

/**
 * Allows the creating of user provider configuration. 
 */
trait CreatesUserProviders
{
    /**
     * The registered custom provider creators.
     * 
     * @var array $customProviderCreators
     */
    protected $customProviderCreators = [];
    
    /**
     * Create the user provider implementation for the driver.
     * 
     * @param  string  $provider
     * 
     * @return \Syscodes\Components\Contracts\Auth\UserProvider
     * 
     * @throws \InvalidArgumentException
     */
    public function createUserProvider($provider)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return;
        }
        
        if (isset($this->customProviderCreators[$driver = ($config['driver'] ?? null)])) {
            return call_user_func(
                $this->customProviderCreators[$driver], $this->app, $config
            );
        }
        
        switch ($driver) {
            case 'database':
                return $this->createDatabaseProvider($config);
            case 'erostrine':
                return $this->createErostrineProvider($config);
            default:
                break;
        }
        
        throw new InvalidArgumentException("Authentication user provider [{$driver}] is not defined.");
    }
    
    /**
     * Get the user provider configuration.
     * 
     * @param  string|null  $provider
     * 
     * @return array|null
     */
    protected function getProviderConfiguration($provider): ?array
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->app['config']['auth.providers.'.$provider];
        }
    }
    
    /**
     * Create an instance of the database user provider.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Auth\DatabaseUserProvider
     */
    protected function createDatabaseProvider($config)
    {
        $connection = $this->app['db']->connection($config['connection'] ?? null);
        
        return new DatabaseUserProvider($connection, $this->app['hash'], $config['table']);
    }
    
    /**
     * Create an instance of the Erostrine user provider.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Auth\ErostrineUserProvider
     */
    protected function createErostrineProvider($config)
    {
        return new ErostrineUserProvider($this->app['hash'], $config['model']);
    }

    /**
     * Get the default user provider name.
     * 
     * @return string
     */
    public function getDefaultUserProvider(): string
    {
        return $this->app['config']['auth.defaults.provider'];
    }
}