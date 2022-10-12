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

namespace Syscodes\Components\Auth;

use Closure;
use Syscodes\Components\Auth\Guards\TokenGuard;
use Syscodes\Components\Contracts\Auth\Factory;
use Syscodes\Components\Auth\Concerns\CreatesUserProviders;

/**
 * 
 * 
 * @author Alexander Campo <jalexam@gmail.com> 
 */
class AuthManager implements Factory
{
    use CreatesUserProviders;

    /**
     * The applicaction instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * The registered custom driver creators.
     * 
     * @var array $customCreators
     */
    protected $customCreators = [];

    /**
     * The array of created "drivers".
     * 
     * @var array $guards
     */
    protected $guards = [];

    /**
     * The user resolver shared by various services.
     * 
     * @var \Closure $userResolver
     */
    protected $userResolver;

    /**
     * Constructor. Create a new AuthManager class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->userResolver = function ($guard = null) {
            return $this->guard($guard)->user();
        };
    }

    /**
     * {@inheritdoc}
     */
    public function guard(string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        
        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }
    
    /**
     * Get the default authentication driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['auth.defaults.guard'];
    }
    
    /**
     * Call a custom driver creator.
     * 
     * @param  string  $name
     * @param  array  $config
     * 
     * @return mixed
     */
    protected function callCustomCreator($name, array $config)
    {
        $driver = $config['driver'];
        
        $callback = $this->customCreators[$driver];
        
        return call_user_func($callback, $this->app, $name, $config);
    }
    
    /**
     * Create a token based authentication guard.
     * 
     * @param  string  $name
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Auth\Guards\TokenGuard
     */
    public function createTokenDriver($name, $config)
    {
        $guard = new TokenGuard(
            $this->createUserProvider($config['provider'] ?? null),
                $this->app['request'],
                $config['input_key'] ?? 'api_token',
                $config['storage_key'] ?? 'api_token',
                $config['hash'] ?? false
        );
        
        $this->app->refresh('request', $guard, 'setRequest');
        
        return $guard;
    }
    
    /**
     * Get the guard configuration.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function getConfig($name): array
    {
        return $this->app['config']["auth.guards.{$name}"];
    }
    
    /**
     * {@inheritdoc}
     */
    public function shouldUse(string $name): void
    {
        $name = $name ?: $this->getDefaultDriver();
        
        $this->setDefaultDriver($name);
        
        $this->userResolver = function ($name = null) {
            return $this->guard($name)->user();
        };
    }

    /**
     * set the default authentication driver name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultDriver($name): void
    {
        $this->app['config']['auth.defaults.guard'] = $name;
    }
    
    /**
     * Get the user resolver callback.
     * 
     * @return \Closure
     */
    public function userResolver()
    {
        return $this->userResolver;
    }
    
    /**
     * Set the callback to be used to resolve users.
     * 
     * @param  \Closure  $userResolver
     * 
     * @return self
     */
    public function resolveUsersUsing(Closure $userResolver): self
    {
        $this->userResolver = $userResolver;
        
        return $this;
    }
    
    /**
     * Register a custom driver creator Closure.
     * 
     * @param  string  $driver
     * @param  \Closure  $callback
     * 
     * @return self
     */
    public function extend($driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;
        
        return $this;
    }
    
    /**
     * Register a custom provider creator Closure.
     * 
     * @param  string  $name
     * @param  \Closure  $callback
     * 
     * @return self
     */
    public function provider($name, Closure $callback): self
    {
        $this->customProviderCreators[$name] = $callback;
        
        return $this;
    }
    
    /**
     * Determines if any guards have already been resolved.
     * 
     * @return bool
     */
    public function hasResolvedGuards(): bool
    {
        return count($this->guards) > 0;
    }
    
    /**
     * Flush all of the resolved guard instances.
     * 
     * @return self
     */
    public function flushGuards(): self
    {
        $this->guards = [];
        
        return $this;
    }
    
    /**
     * Set the application instance used by the manager.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return self
     */
    public function setApplication($app): self
    {
        $this->app = $app;
        
        return $this;
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
        return $this->guard()->{$method}(...$parameters);
    }
}