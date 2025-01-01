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

namespace Syscodes\Components\Routing\Concerns;

use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Syscodes\Components\Support\Arr;

/**
 * This trait resolver the methods given for the dependencies.
 */
trait RouteDependencyResolver
{
    /**
     * Resolve the object method's with a type of dependencies.
     * 
     * @param  array  $parameters
     * @param  object  $instance
     * @param  string  $method
     * 
     * @return array
     */
    protected function resolveObjectMethodDependencies(array $parameters, $instance, $method): array
    {
        if ( ! method_exists($instance, $method)) {
            return $parameters;
        }

        return $this->resolveMethodDependencies(
            $parameters, new ReflectionMethod($instance, $method)
        );
    }

    /**
     * Resolve the object method's with a type of dependencies.
     * 
     * @param  array  $parameters
     * @param  \ReflectionFunctionAbstract  $reflection
     * 
     * @return array
     */
    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflection): array
    {
        $count = 0;

        $values = array_values($parameters);

        foreach ($reflection->getParameters() as $key => $parameter) {
            $instance = $this->transformGivenDependency($parameter, $parameters);

            if ( ! is_null($instance)) {
                $count++;

                $this->spliceOnParameters($parameters, $key, $instance);
            } elseif ( ! isset($values[$key - $count]) && $parameter->isDefaultValueAvailable()) {
                $this->spliceOnParameters($parameters, $key, $parameter->getDefaultValue());
            }            
        }

        return $parameters;
    }

    /**
     * Attempt to transform the given parameter into a class instance.
     * 
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * 
     * @return mixed
     */
    protected function transformGivenDependency(ReflectionParameter $parameter, array $parameters)
    {
        $class = $parameter->getType();

        if ( ! is_null($class) && ! $this->getInParameters($className = $class->getName(), $parameters)) {
            return $parameter->isDefaultValueAvailable() ? null : $this->container->make($className);
        }
    }

    /**
     * Determine if an object of the given class is in a list of parameters.
     * 
     * @param  string  $class
     * @param  array  $parameters
     * 
     * @return bool
     */
    protected function getInParameters($class, array $parameters): bool
    {
        return ! is_null(Arr::first($parameters, function ($value) use ($class) {
            return $value instanceof $class;
        }));
    }

    /**
     * Splice the given value into the parameter list.
     * 
     * @param  array  $parameters
     * @param  string  $key
     * @param  mixed  $instance
     * 
     * @return void
     */
    protected function spliceOnParameters(array &$parameters, $key, $instance): void
    {
        array_splice($parameters, $key, 0, [$instance]);
    }
}