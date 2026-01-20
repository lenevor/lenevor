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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Concerns;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use stdClass;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Reflector;

/**
 * This trait resolver the methods given for the dependencies.
 */
trait DependencyResolver
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
     * @param  \ReflectionFunctionAbstract  $reflector
     * 
     * @return array
     */
    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector): array
    {
        $count = 0;

        $values = array_values($parameters);

        $value = new stdClass;

        foreach ($reflector->getParameters() as $key => $parameter) {
            $instance = $this->transformGivenDependency($parameter, $parameters, $value);

            if ($instance !== $value) {
                $count++;

                $this->spliceOnParameters($parameters, $key, $instance);
            } elseif ( ! isset($values[$key - $count]) && $parameter->isDefaultValueAvailable()) {
                $this->spliceOnParameters($parameters, $key, $parameter->getDefaultValue());
            }
            
            $this->container->fireAfterResolvingAttributeCallbacks($parameter->getAttributes(), $instance);
        }

        return $parameters;
    }

    /**
     * Attempt to transform the given parameter into a class instance.
     * 
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * @param  object  $value
     * 
     * @return mixed
     */
    protected function transformGivenDependency(ReflectionParameter $parameter, array $parameters, $value)
    {
        $className = Reflector::getParameterClassName($parameter);

        if ($className && ! $this->getInParameters($className, $parameters)) {
            $isEnum = (new ReflectionClass($className))->isEnum();

            return $parameter->isDefaultValueAvailable()
                ? ($isEnum ? $parameter->getDefaultValue() : null)
                : $this->container->make($className);
        }
        
        return $value;
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
        return ! is_null(Arr::first($parameters, fn ($value) => $value instanceof $class));
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