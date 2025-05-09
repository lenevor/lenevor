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
    public function register() {}

    /**
     * Register a new boot listener.
     * 
     * @param  \callable  $callback
     * 
     * @return void
     */
    public function booting($callback)
    {
        $callback();
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