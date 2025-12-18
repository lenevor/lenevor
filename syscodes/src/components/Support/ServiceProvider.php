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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

use Closure;
use Syscodes\Components\Console\Application as Prime;
use Syscodes\Components\Contracts\Support\Deferrable;

/**
 * Loads all the services provider of system.
 */
abstract class ServiceProvider 
{
    /**
     * The application instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * All of the registered booted callbacks.
     * 
     * @var array
     */
    protected $bootedCallbacks = [];
    
    /**
     * All of the registered booting callbacks.
     * 
     * @var array
     */
    protected $bootingCallbacks = [];
    
    /**
     * The paths that should be published.
     * 
     * @var array
     */
    public static $publishes = [];
    
    /**
     * The paths that should be published by group.
     * 
     * @var array
     */
    public static $publishGroups = [];

    /**
     * Constructor. Create a new service provider instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Applicacion  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the default services providers for a 
     * Lenevor application.
     * 
     * @return \Syscodes\Components\Support\DefaultCoreProviders
     */
    public static function defaultCoreProviders(): DefaultCoreProviders
    {
        return new DefaultCoreProviders;
    }

    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register()
    {
        //
    }
    
    /**
     * Register a booting callback to be run before the "boot" method is called.
     * 
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function booting(Closure $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }
    
    /**
     * Register a booted callback to be run after the "boot" method is called.
     * 
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function booted(Closure $callback): void
    {
        $this->bootedCallbacks[] = $callback;
    }
    
    /**
     * Call the registered booting callbacks.
     * 
     * @return void
     */
    public function callBootingCallbacks(): void
    {
        $index = 0;
        
        while ($index < count($this->bootingCallbacks)) {
            $this->app->call($this->bootingCallbacks[$index]);
            
            $index++;
        }
    }
    
    /**
     * Call the registered booted callbacks.
     * 
     * @return void
     */
    public function callBootedCallbacks(): void
    {
        $index = 0;
        
        while ($index < count($this->bootedCallbacks)) {
            $this->app->call($this->bootedCallbacks[$index]);
            
            $index++;
        }
    }
    
    /**
     * Register a view file namespace.
     * 
     * @param  string|array  $path
     * @param  string  $namespace
     * 
     * @return void
     */
    protected function loadViewsTo($path, $namespace): void
    {
        $this->callResolving('view', function ($view) use ($path, $namespace) {
            if (isset($this->app->config['view']['paths']) &&
                is_array($this->app->config['view']['paths'])
            ) {
                foreach ($this->app->config['view']['paths'] as $viewPath) {
                    if (is_dir($appPath = $viewPath.'/vendor/'.$namespace)) {
                        $view->addNamespace($namespace, $appPath);
                    }
                }
            }
            
            $view->addNamespace($namespace, $path);
        });
    }
    
    /**
     * Setup an after resolving listener, or fire immediately 
     * if already resolved.
     * 
     * @param  string  $name
     * @param  \Closure  $callback
     * 
     * @return void
     */
    protected function callResolving($name, Closure $callback): void
    {
        $this->app->rebinding($name, $callback);
        
        if ($this->app->resolved($name)) {
            $callback($this->app->make($name), $this->app);
        }
    }
    
    /**
     * Register the package's custom Prime commands.
     * 
     * @param  mixed  $commands
     * 
     * @return void
     */
    public function commands($commands): void
    {
        $commands = is_array($commands) ? $commands : func_get_args();
        
        Prime::starting(function ($prime) use ($commands) {
            $prime->resolveCommands($commands);
        });
    }
    
    /**
     * Register paths to be published by the publish command.
     * 
     * @param  array  $paths
     * @param  mixed  $groups
     * 
     * @return void
     */
    protected function publishes(array $paths, $groups = null): void
    {
        $this->ensurePublishArrayInitialized($class = static::class);
        
        static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);
        
        foreach ((array) $groups as $group) {
            $this->addPublishGroup($group, $paths);
        }
    }
    
    /**
     * Ensure the publish array for the service provider is initialized.
     * 
     * @param  string  $class
     * 
     * @return void
     */
    protected function ensurePublishArrayInitialized($class): void
    {
        if ( ! array_key_exists($class, static::$publishes)) {
            static::$publishes[$class] = [];
        }
    }
    
    /**
     * Add a publish group / tag to the service provider.
     * 
     * @param  string  $group
     * @param  array  $paths
     * 
     * @return void
     */
    protected function addPublishGroup($group, $paths): void
    {
        if ( ! array_key_exists($group, static::$publishGroups)) {
            static::$publishGroups[$group] = [];
        }
        
        static::$publishGroups[$group] = array_merge(
            static::$publishGroups[$group], $paths
        );
    }
    
    /**
     * Get the paths to publish.
     * 
     * @param  string|null  $provider
     * @param  string|null  $group
     * 
     * @return array
     */
    public static function pathsToPublish($provider = null, $group = null): array
    {
        if ( ! is_null($paths = static::pathsForProviderOrGroup($provider, $group))) {
            return $paths;
        }
        
        return collect(static::$publishes)->reduce(function ($paths, $p) {
            return array_merge($paths, $p);
        }, []);
    }
    
    /**
     * Get the paths for the provider or group (or both).
     * 
     * @param  string|null  $provider
     * @param  string|null  $group
     * 
     * @return array
     */
    protected static function pathsForProviderOrGroup($provider, $group)
    {
        if ($provider && $group) {
            return static::pathsForProviderAndGroup($provider, $group);
        } elseif ($group && array_key_exists($group, static::$publishGroups)) {
            return static::$publishGroups[$group];
        } elseif ($provider && array_key_exists($provider, static::$publishes)) {
            return static::$publishes[$provider];
        } elseif ($group || $provider) {
            return [];
        }
    }
    
    /**
     * Get the paths for the provider and group.
     * 
     * @param  string  $provider
     * @param  string  $group
     * 
     * @return array
     */
    protected static function pathsForProviderAndGroup($provider, $group): array
    {
        if ( ! empty(static::$publishes[$provider]) && ! empty(static::$publishGroups[$group])) {
            return array_intersect_key(static::$publishes[$provider], static::$publishGroups[$group]);
        }
        
        return [];
    }
    
    /**
     * Get the service providers available for publishing.
     * 
     * @return array
     */
    public static function publishableProviders(): array
    {
        return array_keys(static::$publishes);
    }
    
    /**
     * Get the groups available for publishing.
     * 
     * @return array
     */
    public static function publishableGroups(): array
    {
        return array_keys(static::$publishGroups);
    }

    /**
     * Get the services provided by the provider.
     * 
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get the events that trigger this service provider to register.
     * 
     * @return array
     */
    public function when()
    {
        return [];
    }

    /**
     * Determine if the provider is deferred.
     * 
     * @return bool
     */
    public function isDeferred()
    {
        return $this instanceof Deferrable;
    }
}