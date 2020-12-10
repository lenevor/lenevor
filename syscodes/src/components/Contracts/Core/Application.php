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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Contracts\Core;

use Syscodes\Contracts\Container\Container;

/**
 * Allows the loading of service providers and functions to activate 
 * routes, environments and calls of main classes.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface Application extends Container
{
    /**
     * Get the base path of the Lenevor installation.
     *
     * @param  string  $path  Optionally, a path to append to the base path
     * 
     * @return string
     */
    public function basePath($path = '');

    /**
     * Get the path to the bootstrap directory.
     *
     * @param  string  $path  Optionally, a path to append to the bootstrap path
     * 
     * @return string
     */
    public function bootstrapPath($path = '');

    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path  Optionally, a path to append to the config path
     * 
     * @return string
     */
    public function configPath($path = '');

    /**
     * Get the path to the database directory.
     *
     * @param  string  $path  Optionally, a path to append to the database path
     * 
     * @return string
     */
    public function databasePath($path = '');

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path $path  Optionally, a path to append to the resources path
     * @return string
     */
    public function resourcePath($path = '');

    /**
     * Get the path to the storage directory.
     * 
     * @return string
     */
    public function storagePath();

    /**
     * Run the given array of bootstap classes.
     * 
     * @param  string[]  $bootstrappers
     * 
     * @return void
     */
    public function bootstrapWith(array $bootstrappers);

    /**
     * Get the environment file the application is using.
     * 
     * @return string
     */
    public function environmentFile();

    /**
     * Get the fully qualified path to the environment file.
     * 
     * @return string
     */
    public function environmentFilePath();

    /**
     * Determine if the application has been bootstrapped before.
     * 
     * @return bool
     */
    public function hasBeenBootstrapped();

    /**
     * Register all of the configured providers.
     * 
     * @return void
     */
    public function registerConfiguredProviders();

    /**
     * Register a service provider.
     * 
     * @param  \Syscodes\Support\ServiceProvider|string  $provider
     * @param  bool  $force
     * 
     * @return \Syscodes\Support\ServiceProvider
     */
    public function register($provider, $force = false);

    /**
     * Resolve a service provider instance from the class name.
     * 
     * @param  string  $provider
     * 
     * @return \Syscodes\Support\ServiceProvider
     */
    public function resolveProviderClass($provider);

    /**
     * Determine if the application has booted.
     * 
     * @return bool
     */
    public function isBooted();

    /**
     * Boot the application´s service providers.
     * 
     * @return void
     */
    public function boot();

    /**
     * Register a new boot listener.
     * 
     * @param  callable  $callback
     * 
     * @return void
     */
    public function booting($callback);

    /**
     * Register a new 'booted' listener.
     * 
     * @param  callable  $callback
     * 
     * @return void
     */
    public function booted($callback);
}