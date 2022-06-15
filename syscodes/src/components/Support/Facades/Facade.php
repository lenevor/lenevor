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

namespace Syscodes\Components\Support\Facades;

use RuntimeException;

/**
 * Initialize the Facade class.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class Facade
{
    /**
     * The application instance being facaded.
     * 
     * @var array|object $applications
     */
    protected static $applications;
    
    /**
     * Indicates if the resolved instance should be cached.
     * 
     * @var bool $cached
     */
    protected static $cached = true;

    /**
     * Resolved instances of objects in facade.
     * 
     * @var array $resolvedInstance
     */
    protected static $resolvedInstance;

    /** 
     * Clear a resolved facade instance.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public static function clearResolvedInstance($name): void
    {
        unset(static::$resolvedInstance[$name]);
    }

    /** 
     * Clear all of the resolved instances.
     * 
     * @return void
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }
    
    /**
     * Hotswap the underlying instance behind the facade.
     * 
     * @param  mixed  $instance
     * 
     * @return void
     */
    public static function swap($instance): void
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $instance;
        
        if (isset(static::$applications)) {
            static::$applications->instance(static::getFacadeAccessor(), $instance);
        }
    }

    /**
     * Get the registered name facade.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method');
    }

    /**
     * Get the application instance behind the facade.
     * 
     * @return \Syscodes\Components\Contracts\Core\Application
     */
    public static function getFacadeApplication()
    {
        return static::$applications;
    }

    /**
     * Get the root object behind the facade.
     * 
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Resolve the facade root instance.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        if (static::$applications) {
            if (static::$cached) {
                return static::$resolvedInstance[$name] = static::$applications[$name];
            }

            return static::$applications[$name];
        }
    }
    
    /**
     * Get the application default aliases.
     * 
     * @return \Syscodes\Components\Collections\Collection
     */
    public static function defaultAliases()
    {
        return collect([
            'App' => App::class,
            'Cache' => Cache::class,
            'Cookie' => Cookie::class,
            'Config' => Config::class,
            'Crypt' => Crypt::class,
            'DB' => DB::class,
            'Event' => Event::class,
            'File' => File::class,
            'Http' => Http::class,
            'Lang' => Lang::class,
            'Log' => Log::class,
            'Plaze' => Plaze::class,
            'Prime' => Prime::class,
            'Redirect' => Redirect::class,
            'Redis' => Redis::class,
            'Request' => Request::class,
            'Response' => Response::class,
            'Route' => Route::class,
            'Schema' => Schema::class,
            'Session' => Session::class,
            'URL' => URL::class,
            'View' => View::class,
        ]);
    }

    /**
     * Set the application instance.
     * 
     * @param  \Syscodes\Components\Contracts\core\Application  $app
     * 
     * @return void
     */
    public static function setFacadeApplication($app): void
    {
        static::$applications = $app;
    }

    /**
     * Call method in application object.
     * 
     * @param  string  $method
     * @param  array   $args
     * 
     * @return mixed
     * 
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {   
        $instance = static::getFacadeRoot();

        if ( ! $instance) {
            throw new RuntimeException('A facade root has not been set');
        }

        return $instance->$method(...$args);
    }   
}