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

namespace Syscodes\Components\Core;

use Closure;
use RuntimeException;
use Syscodes\Components\Version;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Container\Container;
use Syscodes\Components\Support\Environment;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Log\LogServiceProvider;
use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\Events\EventServiceProvider;
use Syscodes\Components\Routing\RoutingServiceProvider;
use Syscodes\Components\Core\Http\Exceptions\HttpException;
use Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException;
use Syscodes\Components\Contracts\Core\Application as ApplicationContract;

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
    protected static $phpVersion = \PHP_VERSION;
    
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
     * The custom database path defined by the developer.
     * 
     * @var string $databasePath
     */
    protected $databasePath;

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
     * The custom language path defined by the developer.
     * 
     * @var string $langPath
     */
    protected $langPath;

    /**
     * The names of the loaded service providers.
     * 
     * @var array $loadServiceProviders
     */
    protected $loadServiceProviders = [];

    /**
     * The application namespace.
     * 
     * @var string $namespace
     */
    protected $namespace;

    /**
     * All of the registered services providers.
     * 
     * @var \Syscodes\Components\Support\ServiceProvider[] $serviceProviders
     */
    protected $serviceProviders = [];

    /**
     * The array of shutdown callbacks.
     * 
     * @var callable[] $shutdownCallbacks
     */
    protected $shutdownCallbacks = [];

    /**
     * The custom storage path defined by the developer.
     * 
     * @var string $storagePath
     */
    protected $storagePath;

    /**
     * Constructor. Create a new Application instance.
     * 
     * @param  string|null  $path 
     * 
     * @return void
     */
    public function __construct($path = null)
    {
        if ($path) {
            $this->setBasePath($path);
        }

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
        $this->requerimentPhpVersion(static::$phpVersion);
        $this->getExtensionLoaded(['mbstring']);
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings() 
    {
        static::setInstance($this);
        
        $this->instance('app', $this);
        $this->instance('config', $this[\Syscodes\Components\Config\Configure::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function version(): string
    {
        return Version::RELEASE;
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath(string $path): self
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
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
        
        $this->setLangPath(value(function () {
            if (is_dir($directory = $this->resourcePath('lang'))) {
                return $directory;
            }
            
            return $this->basePath('lang');
        }));
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param  string  $path
     * 
     * @return string
     */
    public function path($path = ''): string
    {
        $appPath = $this->appPath ?: $this->basePath.DIRECTORY_SEPARATOR.'app';
        
        return $appPath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * Set the application directory.
     * 
     * @param  string  $path
     * 
     * @return self
     */
    public function setAppPath($path): self
    {
        $this->appPath = $path;

        $this->instance('path', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function basePath($path = ''): string
    {
        return $this->basePath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }
    
    /**
     * {@inheritdoc}
     */
    public function bootstrapPath($path = ''): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'bootstrap'.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * {@inheritdoc}
     */
    public function configPath($path = ''): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * {@inheritdoc}
     */
    public function databasePath($path = ''): string
    {
        return ($this->databasePath ?: $this->basePath.DIRECTORY_SEPARATOR.'database').($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * Set the database directory.
     * 
     * @param  string  $path
     * 
     * @return self
     */
    public function setDatabasePath($path): self
    {
        $this->databasePath = $path;

        $this->instance('path.database', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function langPath($path = ''): string
    {
        return $this->langPath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * Set the lang directory.
     * 
     * @param  string  $path
     * 
     * @return self
     */
    public function setlangPath($path): self
    {
        $this->langPath = $path;

        $this->instance('path.lang', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function publicPath(): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'public';
    }

    /**
     * {@inheritdoc}
     */
    public function resourcePath($path = ''): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * {@inheritdoc}
     */
    public function storagePath($path = ''): string
    {
        return ($this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage').
                                   ($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * Set the database directory.
     * 
     * @param  string  $path
     * 
     * @return self
     */
    public function setStoragePath($path): self
    {
        $this->storagePath = $path;

        $this->instance('path.storage', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function viewPath($path = ''): string
    {
        $viewPath = $this['config']->get('view.paths')[0];

        return rtrim($viewPath, DIRECTORY_SEPARATOR).($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function skipGoingMiddleware(): bool
    {
        return $this->bound('middleware.disable') &&
               $this->make('middleware.disable') === true;
    }

    /**
     * Set the directory for the environment file.
     * 
     * @param  string  $path
     * 
     * @return self
     */
    public function setEnvironmentPath($path): self
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironmentFile($file): self
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }
    
    /**
     * {@inheritdoc}
     */
    public function environmentFilePath(): string
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
     * {@inheritdoc}
     */
    public function isLocal(): bool
    {
        return $this->env === 'local';
    }
    
    /**
     * {@inheritdoc}
     */
    public function isProduction(): bool
    {
        return $this->env === 'production';
    }
    
    /**
     * {@inheritdoc}
     */
    public function isUnitTests(): bool
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
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * You can empty out this file, if you are certain that you match all requirements.
     * You can remove this if you are confident that your PHP version is sufficient.
     * 
     * @return string
     */
    protected function requerimentPhpVersion($version)
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
     * {@inheritdoc}
     */
    public function registerConfiguredProviders(): void
    {
        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                ->load($this['config']['services.providers']);
    }
    
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function resolveProviderClass($provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     * 
     * @param  \Syscodes\Components\Support\ServiceProvider  $provider
     * 
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
        
        $this->loadServiceProviders[getClass($provider, true)] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function loadDeferredProviders(): void
    {
        foreach ($this->deferredServices as $service => $provider) {
            $this->loadDeferredProvider($service);
        }

        $this->deferredServices = [];
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
     * {@inheritdoc}
     */
    public function bound($id): bool
    {
        return $this->isDeferredService($id) || parent::bound($id);
    }

    /**
     * {@inheritdoc}
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
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
     * @param  \Syscodes\Components\Support\ServiceProvider  $provider
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
     * {@inheritdoc}
     */
    public function booting($callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function booted($callback): void
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
     * {@inheritdoc}
     */
    public function getLoadedProviders(): array
    {
        return $this->loadServiceProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function providerIsLoaded(string $provider): bool
    {
        return isset($this->loadServiceProviders[$provider]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeferredServices(): array
    {
        return $this->deferredServices;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeferredServices(array $services): void
    {
        $this->deferredServices = $services;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeferredService($service): bool
    {
        return isset($this->deferredServices[$service]);
    }

    /**
     * {@inheritdoc}
     */
    public function addDeferredServices(array $services): void
    {
        $this->deferredServices = array_merge($this->deferredServices, $services);
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  array  $headers
     * 
     * @return never
     *
     * @throws \Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException
     * @throws \Syscodes\Components\Core\Http\Exceptions\HttpException
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
     * {@inheritdoc}
     */
    public function currentLocale(): string
    {
        return $this->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this['config']->get('app.locale');
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): void
    {
        $this['config']->set('app.locale', $locale);

        $this['translator']->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale(): string
    {
        return $this['config']->get('app.fallbackLocale');
    }

    /**
     * {@inheritdoc}
     */
    public function setFallbackLocale($fallbackLocale): void
    {
        $this['config']->set('app.fallbackLocale', $fallbackLocale);

        $this['translator']->setFallback($fallbackLocale);
    }

    /**
     * {@inheritdoc}
     */
    public function isLocale($locale): bool
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Register the shutdown callback.
     * 
     * @param  callable|string  $callback
     * 
     * @return self
     */
    public function shutdowning($callback): self
    {
        $this->shutdownCallbacks[] = $callback;

        return $this;
    }

    /**
	 * {@inheritdoc}
	 */
	public function shutdown(): void
	{
		foreach ($this->shutdownCallbacks as $shutdown) {
            $this->call($shutdown);
        }
	}

    /**
     * Register the core class aliases in the container.
     * 
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'app' => [self::class, \Syscodes\Components\Contracts\Container\Container::class, \Syscodes\Components\Contracts\Core\Application::class, \Psr\Container\ContainerInterface::class],
            'auth' => [\Syscodes\Components\Auth\AuthManager::class, \Syscodes\Components\Contracts\Auth\Factory::class],
            'auth.driver' => [\Syscodes\Components\Contracts\Auth\Guard::class],
            'cache' => [\Syscodes\Components\Cache\CacheManager::class, \Syscodes\Components\Contracts\Cache\Manager::class],
            'cache.store' => [\Syscodes\Components\Cache\CacheRepository::class, \Syscodes\Components\Contracts\Cache\Repository::class],
            'config' => [\Syscodes\Components\Config\Configure::class, \Syscodes\Components\Contracts\Config\Configure::class],
            'cookie' => [\Syscodes\Components\Cookie\CookieManager::class, \Syscodes\Components\Contracts\Cookie\Factory::class, \Syscodes\Components\Contracts\Cookie\QueueingFactory::class],
            'db' => [\Syscodes\Components\Database\DatabaseManager::class, \Syscodes\Components\Database\ConnectionResolverInterface::class],
            'db.connection' => [\Syscodes\Components\Database\Connections\Connection::class, \Syscodes\Components\Database\Connections\ConnectionInterface::class],
            'db.schema' => [\Syscodes\Components\Database\Schema\Builders\Builder::class],
            'encrypter' => [\Syscodes\Components\Encryption\Encrypter::class, \Syscodes\Components\Contracts\Encryption\Encrypter::class],
            'events' => [\Syscodes\Components\Events\Dispatcher::class, \Syscodes\Components\Contracts\Events\Dispatcher::class],
            'files' => [\Syscodes\Components\Filesystem\Filesystem::class],
            'hash' => [\Syscodes\Components\Hashing\hashManager::class],
            'hash.driver' => [\Syscodes\Components\Contracts\Hashing\Hasher::class],
            'log' => [\Syscodes\Components\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
            'plaze.transpiler' => [\Syscodes\Components\View\Transpilers\PlazeTranspiler::class],
            'redirect' => [\Syscodes\Components\Routing\Supported\Redirector::class],
            'redis' => [\Syscodes\Components\Redis\RedisManager::class],
            'request' => [\Syscodes\Components\Http\Request::class],
            'router' => [\Syscodes\Components\Routing\Router::class],
            'session' => [\Syscodes\Components\Session\SessionManager::class],
            'session.store' => [\Syscodes\Components\Session\Store::class, \Syscodes\Components\Contracts\Session\Session::class],
            'translator' => [\Syscodes\Components\Translation\Translator::class, \Syscodes\Components\Contracts\Translation\Translator::class],
            'url' => [\Syscodes\Components\Routing\Supported\UrlGenerator::class],
            'view' => [\Syscodes\Components\View\Factory::class, \Syscodes\Components\Contracts\View\Factory::class]
        ] as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Flush the container of all bindings and resolved instances.
     * 
     * @return void
     */
    public function flush(): void
    {
        parent::flush();

        $this->bootedCallbacks = [];
        $this->bootingCallbacks = [];
        $this->deferredServices = [];
        $this->serviceProviders = [];
        $this->loadServiceProviders = [];
    }

    /**
     * Get the application namespace.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    public function getNamespace(): string
    {
        if ( ! is_null($this->namespace)) {
            return $this->namespace;
        }

        $namespaces = autoloader()->getNamespace();

        foreach ((array) $namespaces as $namespace => $path) {
            foreach ((array) $path as $directory) {
                if (realpath($this->path()) === realpath($this->basePath($directory))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace');
    }
}