<?php

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing;

use Syscodes\Components\Support\Str;

/**
 * Allows create a route model binding.
 */
class RouteBinding
{
    /**
     * Create a Route model binding for a given callback.
     *
     * @param  \Syscodes\Components\Container\Container  $container
     * @param  \Closure|string  $binder
     * @return \Closure
     */
    public static function forCallback($container, $binder)
    {
        if (is_string($binder)) {
            return static::createClassBinding($container, $binder);
        }

        return $binder;
    }

    /**
     * Create a class based binding using the IoC container.
     *
     * @param  \Syscodes\Components\Container\Container  $container
     * @param  string  $binding
     * @return \Closure
     */
    protected static function createClassBinding($container, $binding)
    {
        return function ($value, $route) use ($container, $binding) {
            // If the binding has an @ sign, we will assume it's being used to delimit
            // the class name from the bind method name.
            [$class, $method] = Str::parseCallback($binding, 'bind');

            $callable = [$container->make($class), $method];

            return $callable($value, $route);
        };
    }
}