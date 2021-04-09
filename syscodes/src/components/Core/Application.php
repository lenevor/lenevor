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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Core;

use Closure;
use Syscodes\Support\Str;
use Syscodes\Collections\Arr;
use Syscodes\Container\Container;
use Syscodes\Support\Environment;
use Syscodes\Filesystem\Filesystem;
use Syscodes\Support\ServiceProvider;
use Syscodes\Log\LogServiceProvider;
use Syscodes\Events\EventServiceProvider;
use Syscodes\Routing\RoutingServiceProvider;
use Syscodes\Core\Http\Exceptions\HttpException;
use Syscodes\Core\Http\Exceptions\NotFoundHttpException;
use Syscodes\Contracts\Core\Application as ApplicationContract;

/**
 * Allows the loading of service providers and functions to activate 
 * routes, environments and calls of main classes.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * Php version.
     */
    protected static $phpVersion = '7.3.12';
    
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
     * The deferred services and their providers.
     * 
     * @var array $deferredServices
     */
    protected $deferredServices = [];

    /**
     * Get the current application environment.
     * 
     * @var string
     */
    protected $env;

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
     * Indicates if the application is running in the console.
     * 
     * @var bool|null $isRunningInConsole
     */
    protected $isRunningInConsole;

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
        $this->requerimentVersion(static::$phpVersion);
        $this->getExtensionLoaded(['mbstring']);
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

        if ($code == 404) {
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
        $this->register(new LogServiceProvider($this));
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
     * 
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
     * 
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

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Determine if middleware has been disabled for the application.
     * 
     * @return bool
     */
    public function skipGoingMiddleware()
    {
        return $this->bound('middleware.disable') &&
               $this->make('middleware.disable') === true;
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
     * Get the path to the environment file directory.
     * 
     * @return string
     */
    public function environmentPath()
    {
        return $this->environmentPath ?: $this->basePath;
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
     * Get or check the current application environment.
     * 
     * @param  string|array  ...$environments
     * 
     * @return string|bool
     */
    public function environment(...$environments)
    {
        if (count($environments) > 0) {
            $patterns = is_array($environments[0]) ? $environments[0] : $environments;

            return Str::is($patterns, $this->env);
        }

        return $this->env;
    }

    /**
     * Detect the application's current environment.
     * 
     * @param  \Closure  $callback
     *
     * @return string
     */
    public function detectEnvironment(Closure $callback)
    {
        return $this->env = (new EnvironmentDetector)->detect($callback);
    }
    
    /**
     * Determine if application is in local environment.
     * 
     * @return bool
     */
    public function isLocal()
    {
        return $this->env === 'local';
    }
    
    /**
     * Determine if application is in production environment.
     * 
     * @return bool
     */
    public function isProduction()
    {
        return $this->env === 'production';
    }
    
    /**
     * Determine if the application is unit tests.
     * 
     * @return bool
     */
    public function isUnitTests()
    {
        return $this->env === 'testing';
    }

    /**
     * Determine if the application is running in the console.
     * 
     * @return bool|null
     */
    public function runningInConsole()
    {
        if (null === $this->isRunningInConsole) {
            $this->isRunningInConsole = Environment::get('APP_RUNNING_CONSOLE') ?? isCli();
        }

        return $this->isRunningInConsole;
    }
    
    /**
     * You can load different configurations depending on your
     * current environment. Setting the environment also influences
     * things like logging and error reporting.
     * 
     * This can be set to anything, but default usage is:
     *     local (development)
     *     testing
     *     production
     * 
     * @return string
     */
    public function bootEnvironment()
    {
        if (file_exists(SYS_PATH.'src'.DIRECTORY_SEPARATOR.'environment'.DIRECTORY_SEPARATOR.$this->environment().'.php')) {
            require_once SYS_PATH.'src'.DIRECTORY_SEPARATOR.'environment'.DIRECTORY_SEPARATOR.$this->environment().'.php';
        } else {
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            print('<style>
                    body {
                        align-items: center;
                        background: #FBFCFC;
                        display: flex;
                        font-family: verdana, sans-seif;
                        font-size: .9em;
                        font-weight: 600;
                        justify-content: center;
                    }
                    
                    p {
                        background: #F0F3F4;
                        border-radius: 5px;
                        box-shadow: 0 1px 4px #333333;
                        color: #34495E;
                        padding: 10px;
                        text-align: center;
                        text-shadow: 0 1px 0 #424949;
                        width: 25%;
                    }
                </style>
                <p>The application environment is not set correctly.</p>');
            die(); // EXIT_ERROR
        }
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
     * You can empty out this file, if you are certain that you match all requirements.
     * You can remove this if you are confident that your PHP version is sufficient.
     * 
     * @return string
     */
    protected function requerimentVersion($version)
    {
        if (version_compare(PHP_VERSION, $version) < 0) {
            if (PHP_SAPI == 'cli') {
                $string  = "\033[1;36m";
                $string .= "$version\033[0m";
                trigger_error("Your PHP version must be equal or higher than {$string} to use Lenevor Framework.".PHP_EOL, E_USER_ERROR);
            }
    
            die("Your PHP version must be equal or higher than <b>{$version}</b> to use Lenevor Framework.");
        }
    }

    /**
     * You can remove this if you are confident you have mbstring installed.
     * 
     * @return string
     */
    protected function getExtensionLoaded(array $extensionLoaded)
    {
        foreach ($extensionLoaded as $value) {
            if ( ! extension_loaded($value)) {
                if (PHP_SAPI == 'cli') {
                    $string  = "\033[1;36m";
                    $string .= "$value\033[0m";
                    trigger_error("You must enable the {$string} extension to use Lenevor Framework.".PHP_EOL, E_USER_ERROR);
                }

                die("You must enable the <b>{$value}</b> extension to use Lenevor Framework.");
            }
        }
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
    public function make($id, $parameters = [])
    {
        $this->loadDeferredProviderInstance($id = $this->getAlias($id));

        return parent::make($id, $parameters);
    }

    /**
     * Resolve the given type from the container.
     *
     * (Overriding Container::resolve)
     * 
     * @param  string  $id
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function resolve($id, $parameters = [])
    {
        $this->loadDeferredProviderInstance($id = $this->getAlias($id));
       
        return parent::resolve($id, $parameters);
    }
    
    /**
     * Load the deferred provider if the given type is a deferred service.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function loadDeferredProviderInstance($id)
    {
        if ($this->isDeferredService($id) && ! isset($this->instances[$id])) {
            $this->loadDeferredProvider($id);
        }
    }

    /**
     * Register all of the configured providers.
     * 
     * @return void
     */
    public function registerConfiguredProviders()
    {
        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                ->load($this['config']['services.providers']);
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
        if ($registered = $this->getProvider($provider) && ! $force) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = $this->resolveProviderClass($provider);
        }

        $provider->register();

        $this->markAsRegistered($provider);

        if ($this->isBooted()) {
            $this->bootProviderClass($provider);
        }
        
        return $provider;
    }
    
    /**
     * Get the registered service provider instance if it exists.
     * 
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * 
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider($provider)
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }
    
    /**
     * Get the registered service provider instances if any exist.
     * 
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * 
     * @return array
     */
    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : getClass($provider, true);
        
        return Arr::where($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
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
        
        $this->loadServiceProviders[getClass($provider, true)] = true;
    }

    /**
     * Load and boot all of the remaining deferred providers.
     *
     * @return void
     */
    public function loadDeferredProviders()
    {
        foreach ($this->deferredServices as $service => $provider) {
            $this->loadDeferredProvider($service);
        }

        $this->deferredServices = array();
    }

    /**
     * Load the provider for a deferred service.
     *
     * @param  string  $service
     * @return void
     */
    protected function loadDeferredProvider($service)
    {
        if ( ! $this->isDeferredService($service)) {
            return;
        }

        $provider = $this->deferredServices[$service];

        if (! isset($this->loadServiceProviders[$provider])) {
            $this->registerDeferredProvider($provider, $service);
        }
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @param  string  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        if (! is_null($service)) {
            unset($this->deferredServices[$service]);
        }

        $this->register($instance = new $provider($this));

        if ($this->isBooted()) {
            return;
        }

        $this->booting(function() use ($instance) {
            $this->bootProviderClass($instance);
        });
    }
    
    /**
     * Determine if the given id type has been bound.
     * 
     * @param  string  $id
     * 
     * @return bool
     */
    public function bound($id)
    {
        return $this->isDeferredService($id) || parent::bound($id);
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
        if ($this->isbooted()) {
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
        foreach ($callbacks as $callback) {
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
        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
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

        if ($this->isBooted()) {
            $this->bootAppCallbacks([$callback]);
        }
    }

    /**
     * Get the path to the cached services.php file.
     * 
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->normalizeCachePath('APP_SERVICES_CACHE', 'cache/services.php');
    }

    /**
     * Normalize a relative or absolute path to a cache file.
     * 
     * @param  string  $key
     * @param  string  $default
     * 
     * @return string
     */
    protected function normalizeCachePath($key, $default)
    {
        if (is_null($env = Environment::get($key))) {
            return $this->bootstrapPath($default);
        }

        return isset($env) 
                ? $env
                : $this->basePath($env);
    }

    /**
     * Get the service providers.
     * 
     * @return array
     */
    public function getLoadedProviders()
    {
        return $this->loadServiceProviders;
    }

    /**
     * Determine if the given service provider is loaded.
     * 
     * @param  string  $provider
     * 
     * @return bool
     */
    public function providerIsLoaded(string $provider)
    {
        return isset($this->loadServiceProviders[$provider]);
    }

    /**
     * Get the application's deferred services.
     * 
     * @return array
     */
    public function getDeferredServices()
    {
        return $this->deferredServices;
    }

    /**
     * Set the application's deferred services.
     * 
     * @param  array  $services
     * 
     * @return void
     */
    public function setDeferredServices(array $services)
    {
        $this->deferredServices = $services;
    }

    /**
     * Determine if the given service is a deferred service.
     * 
     * @param  string  $service
     * 
     * @return bool
     */
    public function isDeferredService($service)
    {
        return isset($this->deferredServices[$service]);
    }

    /**
     * Add an array of services to the application's deferred services.
     * 
     * @param  array  $services
     * 
     * @return void
     */
    public function addDeferredServices(array $services)
    {
        $this->deferredServices = array_merge($this->deferredServices, $services);
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
     * Register the core class aliases in the container.
     * 
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'app'              => [self::class, \Syscodes\Contracts\Container\Container::class, \Syscodes\Contracts\Core\Application::class, \Psr\Container\ContainerInterface::class],
            'cache'            => [\Syscodes\Cache\CacheManager::class, \Syscodes\Contracts\Cache\Manager::class],
            'cache.store'      => [\Syscodes\Cache\CacheRepository::class, \Syscodes\Contracts\Cache\Repository::class],
            'config'           => [\Syscodes\Config\Configure::class, \Syscodes\Contracts\Config\Configure::class],
            'db'               => [\Syscodes\Database\DatabaseManager::class, \Syscodes\Database\ConnectionResolverInterface::class],
            'db.connection'    => [\Syscodes\Database\Connection::class, \Syscodes\Database\ConnectionInterface::class],
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
        ] as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}