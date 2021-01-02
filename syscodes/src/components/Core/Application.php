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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.2
 */

namespace Syscodes\Core;

use Syscodes\Collections\Arr;
use Syscodes\Container\Container;
use Syscodes\Support\ServiceProvider;
use Syscodes\Log\LoggerServiceProvider;
use Syscodes\Events\EventServiceProvider;
use Syscodes\Routing\RoutingServiceProvider;
use Syscodes\Core\Http\Exceptions\HttpException;
use Syscodes\Core\Http\Exceptions\NotFoundHttpException;
use Syscodes\Contracts\Core\Application as ApplicationContract;

/**
 * Allows the loading of service providers and functions to activate 
 * routes, environments and calls of main classes.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Application extends Container implements ApplicationContract
{
    /**
     * The current globally available application.
     * 
     * @var string $instance
     */
    protected static $instance;

     /**
     * The custom application path defined by the developer.
     *
     * @var string $appPath
     */
    protected $appPath;

    /**
     * The base path for the Lenevor installation.
     *
     * @var string $basePath
     */
    protected $basePath;

    /**
     * Indicates if the application has 'booted'.
     * 
     * @var bool $booted
     */
    protected $booted = false;

    /**
     * The array of booted callbacks.
     * 
     * @var callable[] $bootedCallbacks
     */
    protected $bootedCallbacks = [];

    /**
     * The array of booting callbacks.
     * 
     * @var callable[] $bootingCallbacks
     */
    protected $bootingCallbacks = [];

    /**
     * The custom environment path defined by the developer.
     *
     * @var string $environmentPath
     */
    protected $environmentPath;

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string $environmentFile
     */
    protected $environmentFile = '.env';

    /** 
     * Indicates if the application has been bootstrapped before.
     * 
     * @var bool $hasBeenBootstrapped
     */
    protected $hasBeenBootstrapped = false;

    /**
     * The names of the loaded service providers.
     * 
     * @var array $loadServiceProviders
     */
    protected $loadServiceProviders = [];

    /**
     * All of the registered services providers.
     * 
     * @var \Syscodes\Support\ServiceProvider[] $serviceProviders
     */
    protected $serviceProviders = [];

    /**
     * Constructor. Create a new Application instance.
     * 
     * @param  string|null  $path 
     * 
     * @return void
     */
    public function __construct($path = null)
    {
        if ($path)
        {
            $this->setBasePath($path);
        }

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  array  $headers
     * 
     * @return void
     *
     * @throws \Syscodes\Core\Http\Exceptions\NotFoundHttpException
     * @throws \Syscodes\Core\Http\Exceptions\HttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        // Convert the first letter in capital
        $message = ucfirst($message);

        if ($code == 404)
        {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    } 

    /**
     * Set the base path for the application.
     *
     * @param  string  $path
     * 
     * @return $this
     */
    public function setBasePath(string $path)
    {
        $this->basePath = rtrim($path, '\/');

        $this->bindContainerPaths();

        return $this;
    }
    
    /**
     * Register all of the base service providers.
     * 
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new LoggerServiceProvider($this));
        $this->register(new RoutingServiceProvider($this));
    }

    /**
     * Bind all of the application paths in the container.
     * 
     * @return void
     */
    protected function bindContainerPaths()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.lang', $this->langPath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param  string  $path
     * @return string
     */
    public function path($path = '')
    {
        $appPath = $this->basePath.DIRECTORY_SEPARATOR.'app';
        
        return $appPath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the base path of the Lenevor installation.
     *
     * @param  string  $path  Optionally, a path to append to the base path
     * 
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    
    /**
     * Get the path to the bootstrap directory.
     *
     * @param  string  $path  Optionally, a path to append to the bootstrap path
     * 
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'bootstrap'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path  Optionally, a path to append to the config path
     * 
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the database directory.
     *
     * @param  string  $path  Optionally, a path to append to the database path
     * 
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'database'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the lang directory.
     * 
     * @return string
     */
    public function langPath()
    {
        return $this->resourcePath().DIRECTORY_SEPARATOR.'lang';
    }

    /**
     * Get the path to the public / web directory.
     * 
     * @return string
     */
    public function publicPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'public';
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path $path  Optionally, a path to append to the resources path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the storage directory.
     * 
     * @return string
     */
    public function storagePath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'storage';
    }

    /**
     * Run the given array of bootstap classes.
     * 
     * @param  string[]  $bootstrappers
     * 
     * @return void
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper)
        {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Set the directory for the environment file.
     * 
     * @param  string  $path
     * 
     * @return $this
     */
    public function setEnvironmentPath($path)
    {
        $this->environmentPath = $path;

        return $this;
    }
    
    /**
     * Set the environment file to be loaded during bootstrapping.
     * 
     * @param  string  $file
     * 
     * @return $this
     */
    public function setEnvironmentFile($file)
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * Get the path to the environment file directory.
     * 
     * @return string
     */
    public function environmentPath()
    {
        return $this->environmentPath ?: $this->basePath;
    }

    /**
     * Get the environment file the application is using.
     * 
     * @return string
     */
    public function environmentFile()
    {
        return $this->environmentFile ?: '.env';
    }
    
    /**
     * Get the fully qualified path to the environment file.
     * 
     * @return string
     */
    public function environmentFilePath()
    {
        return $this->environmentPath().DIRECTORY_SEPARATOR.$this->environmentFile();
    }

    /**
     * Determine if the application has been bootstrapped before.
     * 
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Resolve the given type from the container.
     *
     * (Overriding Container::make)
     * 
     * @param  string  $id
     * @param  array   $parameters
     * 
     * @return mixed
     */
    public function make($id, array $parameters = [])
    {
        $id = $this->getAlias($id);
       
        return parent::make($id, $parameters);
    }

    /**
     * Register all of the configured providers.
     * 
     * @return void
     */
    public function registerConfiguredProviders()
    {
        (new ProviderRepository($this))
                ->load($this['config']->get('services.providers'));
    }
    
    /**
     * Register a service provider.
     * 
     * @param  \Syscodes\Support\ServiceProvider|string  $provider
     * @param  bool  $force
     * 
     * @return \Syscodes\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        if (($registered = $this->getProviderHasBeenLoaded($provider)) && ! $force)
        {
            return $registered;
        }

        if (is_string($provider))
        {
            $provider = $this->resolveProviderClass($provider);
        }
        
        $provider->register();

        $this->markAsRegistered($provider);

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     * 
     * @param  \Syscodes\Support\ServiceProvider|string  $provider
     * 
     * @return \Syscodes\Support\ServiceProvider
     */
    protected function getProviderHasBeenLoaded($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        if (array_key_exists($name, $this->loadServiceProviders))
        {
            return Arr::first($this->serviceProviders, function($key, $value) use ($name) {
                return get_class($value) == $name;
            });
        }
    }

    /**
     * Resolve a service provider instance from the class name.
     * 
     * @param  string  $provider
     * 
     * @return \Syscodes\Support\ServiceProvider
     */
    public function resolveProviderClass($provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     * 
     * @param  \Syscodes\Support\ServiceProvider  $provider
     * 
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
        
        $this->loadServiceProviders[get_class($provider)] = true;
    }

    /**
     * Determine if the application has booted.
     * 
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Boot the application´s service providers.
     * 
     * @return void
     */
    public function boot()
    {
        if ($this->isbooted())
        {
            return;
        }

        $this->bootAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($provider) {
            $this->bootProviderClass($provider);
        });

        $this->booted = true;

        $this->bootAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Call the booting callbacks for the application.
     * 
     * @param  callable[]  $callbacks
     * 
     * @return void
     */
    protected function bootAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback)
        {
            $callback($this);
        }
    }

    /**
     * Boot the given service provider.
     * 
     * @param  \Syscodes\Support\ServiceProvider  $provider
     * 
     * @return mixed
     */
    protected function bootProviderClass(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot'))
        {
            $provider->boot();
        }
    }

    /**
     * Register a new boot listener.
     * 
     * @param  callable  $callback
     * 
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new 'booted' listener.
     * 
     * @param  callable  $callback
     * 
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted())
        {
            $this->bootAppCallbacks([$callback]);
        }
    }

    /**
     * Get the current application locale.
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Get the current application fallback locale.
     * 
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this['config']->get('app.fallbackLocale');
    }

    /**
     * Determine if application locale is the given locale.
     * 
     * @param  string  $locale
     * 
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    public function registerBaseBindings() 
    {
        static::setInstance($this);
        
        $this->instance('app', $this);
        
        $this->instance('config', $this[\Syscodes\Config\Configure::class]);
    }

    /**
     * Register the core class aliases in the container.
     * 
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'app'              => [self::class, \Syscodes\Contracts\Container\Container::class, \Syscodes\Contracts\Core\Application::class, \Psr\Container\ContainerInterface::class],
            'cache'            => [\Syscodes\Cache\CacheManager::class, \Syscodes\Contracts\Cache\Manager::class],
            'cache.store'      => [\Syscodes\Cache\CacheRepository::class],
            'config'           => [\Syscodes\Config\Configure::class, \Syscodes\Contracts\Config\Configure::class],
            'db'               => [\Syscodes\Database\DatabaseManager::class],
            'encrypter'        => [\Syscodes\Encryption\Encrypter::class, \Syscodes\Contracts\Encryption\Encrypter::class],
            'events'           => [\Syscodes\Events\Dispatcher::class, \Syscodes\Contracts\Events\Dispatcher::class],
            'files'            => [\Syscodes\Filesystem\Filesystem::class],
            'log'              => [\Syscodes\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
            'plaze.transpiler' => [\Syscodes\View\Transpilers\PlazeTranspiler::class],
            'redirect'         => [\Syscodes\Routing\Redirector::class],
            'redis'            => [\Syscodes\Redis\RedisManager::class],
            'request'          => [\Syscodes\Http\Request::class],
            'router'           => [\Syscodes\Routing\Router::class],
            'session'          => [\Syscodes\Session\SessionManager::class],
            'session.store'    => [\Syscodes\Session\Store::class, \Syscodes\Contracts\Session\Session::class],
            'translator'       => [\Syscodes\Translation\Translator::class],
            'url'              => [\Syscodes\Routing\UrlGenerator::class],
            'view'             => [\Syscodes\View\Factory::class, \Syscodes\Contracts\View\Factory::class]
        ] as $key => $aliases) 
        {
            foreach ((array) $aliases as $alias) 
            {
                $this->alias($key, $alias);
            }
        }
    }
}