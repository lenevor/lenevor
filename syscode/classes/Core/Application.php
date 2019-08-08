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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Core;

use Syscode\Container\Container;
use Syscode\Core\Http\Exceptions\{ 
    HttpException, 
    NotFoundHttpException 
};
use Syscode\Contracts\Core\Application as ApplicationContract;

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
        $this->registerCoreContainerAliases();
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * 
     * @return void
     *
     * @throws \Syscode\Core\Http\Exceptions\NotFoundHttpException
     * @throws \Syscode\Core\Http\Exceptions\HttpException
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
    public function make(string $id, array $parameters = [])
    {
        $id = $this->getAlias($id);
       
        return parent::make($id, $parameters);
    }
    
    /**
     * Register a service provider.
     * 
     * @param  string  $provider
     * 
     * @return $this
     */
    public function register($provider)
    {
        $provider->register($this);
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
        $this->instance('config', $this[\Syscode\Config\Configure::class]);
        $this->instance('http', $this[\Syscode\Http\Http::class]);
        $this->instance('redirect', $this[\Syscode\Routing\Redirector::class]);
        $this->instance('request', $this[\Syscode\Http\Request::class]);
        $this->singleton('response', function() {
            return new \Syscode\Routing\RouteResponse($this['view'], $this['redirect']);
        });
        $this->instance('router', $this[\Syscode\Routing\Router::class]);
        $this->instance('translator', $this[\Syscode\Translation\Translator::class]);
        $this->instance('url', $this[\Syscode\Routing\UrlGenerator::class]);
        $this->singleton('view', function () {
            return new \Syscode\View\View;
        });
    }

    /**
     * Register the core class aliases in the container.
     * 
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'app'        => [\Syscode\Core\Application::class, \Syscode\Contracts\Container\Container::class,
                             \Syscode\Contracts\Core\Application::class, \Psr\Container\ContainerInterface::class],
            'config'     => [\Syscode\Config\Configure::class, \Syscode\Contracts\Config\Configure::class], 
            'http'       => [\Syscode\Http\Http::class],
            'redirect'   => [\Syscode\Routing\Redirector::class],
            'request'    => [\Syscode\Http\Request::class],
            'response'   => [\Syscode\Routing\RouteResponse::class],
            'router'     => [\Syscode\Routing\Router::class],
            'translator' => [\Syscode\Translation\Translator::class],
            'url'        => [\Syscode\Routing\UrlGenerator::class],
            'view'       => [\Syscode\View\View::class, \Syscode\Contracts\View\View::class, \Syscode\Contracts\View\Factory::class]
        ] as $key => $aliases) 
        {
            foreach ((array) $aliases as $alias) 
            {
                $this->alias($key, $alias);
            }
        }
    }
}