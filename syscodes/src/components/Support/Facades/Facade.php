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

namespace Syscodes\Support\Facades;

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
     * @var array $applications
     */
    protected static $applications;

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
    public static function clearResolvedInstance($name)
    {
        unset(static::$resolvedInstance[$name]);
    }

    /** 
     * Clear all of the resolved instances.
     * 
     * @return void
     */
    public static function clearResolvedInstances()
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
    public static function swap($instance)
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $instance;
        
        if (isset(static::$app)) {
            static::$app->instance(static::getFacadeAccessor(), $instance);
        }
    }

    /**
     * Get the registered name facade.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method');
    }

    /**
     * Get the application instance behind the facade.
     * 
     * @return \Syscodes\Contracts\Core\Application
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
     * @return string
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$applications[$name];
    }

    /**
     * Set the application instance.
     * 
     * @param  \Syscodes\Contracts\core\Application  $app
     * 
     * @return void
     */
    public static function setFacadeApplication($app)
    {
        return static::$applications = $app;
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