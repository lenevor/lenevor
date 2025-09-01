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

namespace Syscodes\Components\Container;

use Closure;
use ReflectionMethod;
use ReflectionFunction;
use InvalidArgumentException;
use ReflectionFunctionAbstract;
use Syscodes\Components\Support\Str;

/**
 * Allows get a closure / class@method with dependencies.
 */
class CallBoundMethod
{
    /**
     * Call the given Closure / class@method and inject its dependencies.
     * 
     * @param  \Syscodes\Components\Container\Container  $container
     * @param  \callable|string  $callback
     * @param  array  $parameters
     * @param  string|null  $defaultMethod
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public static function call(
        Container $container, 
        $callback, 
        array $parameters = [], 
        ?string $defaultMethod = null
    ): mixed {
        if (is_string($callback)) {
            $callback = static::resolveStringCallback($container, $callback, $defaultMethod);
        }

        if ($callback instanceof Closure) {
            $reflector = new ReflectionFunction($callback);
        } elseif (is_array($callback)) {
            $reflector = new ReflectionMethod($callback[0], $callback[1]);
        } else {
            throw new InvalidArgumentException('Invalid callback provided');
        }

        return call_user_func_array(
            $callback, static::getMethodDependencies($container, $parameters, $reflector)
        );
    }

    /**
     * Resolve a string callback.
     * 
     * @param  \Syscodes\Container\Container  $container
     * @param  string  $callback
     * @param  string|null  $defaultMethod
     * 
     * @return array
     */
    protected static function resolveStringCallback($container, $callback, ?string $defaultMethod = null): array
    {
        [$class, $method] = Str::parseCallback($callback, $defaultMethod);

        if (empty($method) || ! class_exists($class)) {
            throw new InvalidArgumentException('Invalid callback provided.');
        }

        return [$container->make($class), $method];
    }

    /**
     * Get all dependencies for a given method.
     * 
     * @param  \Syscodes\Container\Container  $container
     * @param  array  $dependencies
     * @param  \ReflectionFunctionAbstract  $reflector
     * 
     * @return array
     */
    protected static function getMethodDependencies(Container $container, array $parameters, ReflectionFunctionAbstract $reflector): array
    {
        $dependencies = [];
        
        foreach ($reflector->getParameters() as $parameter) {
            if (array_key_exists($name = $parameter->getName(), $parameters)) {
                $dependencies[] = $parameters[$name];
                
                unset($parameters[$name]);
            // The dependency does not exists in parameters.
            } else if ( ! is_null($class = $parameter->getType())) {
                $className = $class->getName();
                
                $dependencies[] = $container->make($className);
            } else if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            }
        }
        
        return array_merge($dependencies, $parameters);
    }
}