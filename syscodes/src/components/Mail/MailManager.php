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
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Log\LogManager;
use Syscodes\Components\Mail\Transport\LogTransport;
use Syscodes\Components\Mail\Transport\ArrayTransport;
use Syscodes\Components\Mail\Transport\DomainTransport;
use Syscodes\Components\Mail\Transport\SendmailTransport;
use Syscodes\Components\Mail\Transport\Smtp\SocketStream;
use Syscodes\Components\Mail\Transport\Smtp\EsmtpTransport;
use Syscodes\Components\Contracts\Mail\Factory as FactoryContract;
use Syscodes\Components\Mail\Transport\Smtp\EsmtpTransportFactory;

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
        $name = $name ?: $this->getDefaultDriver();
        
        return $this->mailers[$name] = $this->get($name);
    }
    
    /**
     * Get a mailer driver instance.
     * 
     * @param  string|null  $driver
     * 
     * @return \Syscodes\Components\Mail\Mailer
     */
    public function driver($driver = null)
    {
        return $this->mailer($driver);
    }
    
    /**
     * Attempt to get the mailer from the local cache.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Mail\Mailer
     */
    protected function get($name)
    {
        return $this->mailers[$name] ?? $this->resolve($name);
    }
    
    /**
     * Resolve the given mailer.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Mail\Mailer
     * 
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        
        if (is_null($config)) {
            throw new InvalidArgumentException("Mailer [{$name}] is not defined");
        }
        
        $mailer = new Mailer(
            $name,
            $this->app['view'],
            $this->createsTransport($config),
            $this->app['events']
        );
        
        return $mailer;
    }
    
    /**
     * Create a new transport instance.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Contracts\Mail\Transport
     * 
     * @throws \InvalidArgumentException
     */
    public function createsTransport(array $config)
    {
        $transport = $config['transport'] ?? $this->app['config']['mail.driver'];
        
        if (isset($this->customCreators[$transport])) {
            return call_user_func($this->customCreators[$transport], $config);
        }
        
        if (trim($transport ?? '') === '' ||
           ! method_exists($this, $method = 'create'.ucfirst(Str::camelcase($transport)).'Transport')) {
            throw new InvalidArgumentException("Unsupported mail transport [{$transport}]");
        }
        
        return $this->{$method}($config);
    }
    
    /**
     * Create an instance of the SMTP Transport driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Mail\Transport\Smtp\EsmtpTransport
     */
    protected function createSmtpTransport(array $config)
    {
        $factory = new EsmtpTransportFactory;
        $scheme  = $config['scheme'] ?? null;
        
        if ( ! $scheme) {
            $scheme = ! empty($config['encryption']) && $config['encryption'] === 'tls'
                    ? (($config['port'] == 465) ? 'smtps' : 'smtp')
                    : '';
        }
        
        $transport = $factory->create(new DomainTransport(
            $scheme,
            $config['host'],
            $config['username'] ?? null,
            $config['password'] ?? null,
            $config['port'] ?? null,
            $config
        ));
        
        return $this->configureSmtpTransport($transport, $config);
    }
    
    /**
     * Configure the additional SMTP driver options.
     * 
     * @param  \Syscodes\Components\Mail\Transport\Smtp\EsmtpTransport  $transport
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Mail\Transport\Smtp\EsmtpTransport
     */
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config)
    {
        $stream = $transport->getStream();
        
        if ($stream instanceof SocketStream) {
            if (isset($config['source_ip'])) {
                $stream->setSourceIp($config['source_ip']);
            }
            
            if (isset($config['timeout'])) {
                $stream->setTimeout($config['timeout']);
            }
        }
        
        return $transport;
    }
    
    /**
     * Create an instance of the Sendmail Transport driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Mail\Transport\SendmailTransport
     */
    protected function createSendmailTransport(array $config)
    {
        return new SendmailTransport(
            $config['path'] ?? $this->app['config']->get('mail.sendmail')
        );
    }
    
    /**
     * Create an instance of the Mail Transport driver.
     * 
     * @return \Syscodes\Components\Mail\Transport\SendmailTransport
     */
    protected function createMailTransport()
    {
        return new SendmailTransport;
    }
    
    /**
     * Create an instance of the Log Transport driver.
     * 
     * @param  array  $config
     * 
     * @return \Syscodes\Components\Mail\Transport\LogTransport
     */
    protected function createLogTransport(array $config)
    {
        $logger = $this->app->make(LoggerInterface::class);
        
        if ($logger instanceof LogManager) {
            $logger = $logger->channel(
                $config['channel'] ?? $this->app['config']->get('mail.log_channel')
            );
        }
        
        return new LogTransport($logger);
    }
    
    /**
     * Create an instance of the Array Transport Driver.
     * 
     * @return \Syscodes\Components\Mail\Transport\ArrayTransport
     */
    protected function createArrayTransport()
    {
        return new ArrayTransport;
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