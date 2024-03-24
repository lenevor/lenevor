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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail;

use Closure;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Contracts\Mail\Factory as FactoryContract;

/**
 * Allows the connection to servers of mail.
 */
class MailManager implements FactoryContract
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
     * @var array $cumstomCreators
     */
    protected $customCreators = [];
    
    /**
     * The array of resolved mailers.
     * 
     * @var array $mailers
     */
    protected $mailers = [];

    /**
     * Constructor. Create a new MailManager class instance.
     * 
     * @param  \Syscodes\Components\Core\Application  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a mailer instance by name.
     *
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Contracts\Mail\Mailer
     */
    public function mailer($name = null)
    {

    }
    
    /**
     * Get the mail connection configuration.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function getConfig(string $name): array
    {
        $config = $this->app['config']['mail.driver']
                ? $this->app['config']['mail']
                : $this->app['config']["mail.mailers.{$name}"];
                
        if (isset($config['url'])) {
            $config['transport'] = Arr::pull($config, 'driver');
        }
        
        return $config;
    }
    
    /**
     * Get the default mail driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['mail.driver'] ??
               $this->app['config']['mail.default'];
    }
    
    /**
     * Set the default mail driver name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultDriver(string $name): void
    {
        if ($this->app['config']['mail.driver']) {
            $this->app['config']['mail.driver'] = $name;
        }
        
        $this->app['config']['mail.default'] = $name;
    }
    
    /**
     * Disconnect the given mailer and remove from local cache.
     * 
     * @param  string|null  $name
     * 
     * @return void
     */
    public function purge($name = null): void
    {
        $name = $name ?: $this->getDefaultDriver();
        
        unset($this->mailers[$name]);
    }
    
    /**
     * Register a custom transport creator Closure.
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
     * Get the application instance used by the manager.
     * 
     * @return \Syscodes\Components\Contracts\Core\Application
     */
    public function getApplication()
    {
        return $this->app;
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
     * Forget all of the resolved mailer instances.
     * 
     * @return static
     */
    public function forget(): static
    {
        $this->mailers = [];
        
        return $this;
    }
    
    /**
     * Method magic.
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
        return $this->mailer()->$method(...$parameters);
    }
}