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

namespace Syscodes\Components\Auth;

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Auth\Concerns\CreatesUserProviders;
use Syscodes\Components\Auth\Guards\SessionGuard;
use Syscodes\Components\Auth\Guards\TokenGuard;
use Syscodes\Components\Contracts\Auth\Factory;

/**
 * The Lenevor authentication system for users. 
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

        $this->userResolver = fn ($guard = null) => $this->guard($guard)->user();
    }

    /**
     * Get a guard instance by name.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Contracts\Auth\Guard|\Syscodes\Components\Contracts\Auth\StateGuard
     */
    public function guard(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        
        return $this->guards[$name] ??= $this->resolve($name);
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
     * Resolve the given guard.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Contracts\Auth\Guard|\Syscodes\Components\Contracts\Auth\StateGuard
     * 
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        
        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }
        
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($name, $config);
        }
        
        $method = 'create'.ucfirst($config['driver']).'Driver';
        
        if (method_exists($this, $method)) {
            return $this->{$method}($name, $config);
        }
        
        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
        );
    }
    
    /**
     * Call a custom driver creator.
     * 
     * @param  string  $name
     * @param  array  $config
     * 
     * @return mixed
     */
    protected function callCustomCreator($name, array $config): mixed
    {
        $driver = $config['driver'];
        
        $callback = $this->customCreators[$driver];
        
        return call_user_func($callback, $this->app, $name, $config);
    }
    
    /**
     * Create a session based authentication guard.
     *
     * @param  string  $name
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Auth\SessionGuard
     */
    public function createSessionDriver($name, $config)
    {
        $guard = new SessionGuard(
            $name,
            $this->createUserProvider($config['provider'] ?? null),
            $this->app['session.store'],
        );

        if (method_exists($guard, 'setCookie')) {
            $guard->setCookie($this->app['cookie']);
        }

        if (method_exists($guard, 'setDispatcher')) {
            $guard->setDispatcher($this->app['events']);
        }

        if (method_exists($guard, 'setRequest')) {
            $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
        }

        if (isset($config['remember'])) {
            $guard->setRememberDuration($config['remember']);
        }

        return $guard;
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
    protected function getConfig($name)
    {
        return $this->app['config']["auth.guards.{$name}"];
    }
    
    /**
     * Set the default guard the factory should serve.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function shouldUse(string $name): void
    {
        $name = $name ?: $this->getDefaultDriver();
        
        $this->setDefaultDriver($name);
        
        $this->userResolver = fn ($name = null) => $this->guard($name)->user();
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
    public function userResolver(): Closure
    {
        return $this->userResolver;
    }
    
    /**
     * Set the callback to be used to resolve users.
     * 
     * @param  \Closure  $userResolver
     * 
     * @return static
     */
    public function resolveUsersUsing(Closure $userResolver): static
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
     * @return static
     */
    public function extend($driver, Closure $callback): static
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
     * @return static
     */
    public function provider($name, Closure $callback): static
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
     * @return static
     */
    public function flushGuards(): static
    {
        $this->guards = [];
        
        return $this;
    }
    
    /**
     * Set the application instance used by the manager.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return static
     */
    public function setApplication($app): static
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
    public function __call(string $method, array $parameters): mixed
    {
        return $this->guard()->{$method}(...$parameters);
    }
}