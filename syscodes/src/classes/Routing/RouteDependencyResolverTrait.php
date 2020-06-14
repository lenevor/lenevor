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
 * @since       0.7.0
 */

namespace Syscodes\Routing;

use ReflectionMethod;
use ReflectionParameter;
use Syscodes\Support\Arr;
use ReflectionFunctionAbstract;

/**
 * This trait resolver the methods given for the dependencies.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait RouteDependencyResolverTrait
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
    protected function resolveObjectMethodDependencies(array $parameters, $instance, $method)
    {
        if ( ! method_exists($instance, $method))
        {
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
    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflection)
    {
        $count = 0;

        $values = array_values($parameters);

        foreach ($reflection->getParameters() as $key => $parameter)
        {
            $instance = $this->transformGivenDependency($parameter, $parameters);

            if ( ! is_null($instance))
            {
                $count++;

                $this->spliceOnParameters($parameter, $key, $instance);
            }
            elseif ( ! isset($values[$key - $count]) && $parameter->isDefaultValueAvailable())
            {
                $this->spliceOnParameters($parameter, $key, $parameter->getDefaultValue());
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
        $class = $parameter->getClass();

        if ( ! is_null($class) && ! $this->getInParameters($className = $class->name, $parameters))
        {
            return $parameter->isDefaultValueConstant() ? null : $this->container->make($className);
        }
    }

    /**
     * Determine if an object of the given class is in a list of parameters.
     * 
     * @param  string  $class
     * @param  array  $parameters
     * 
     * @return
     */
    protected function getInParameters($class, array $parameters)
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
     * return void
     */
    protected function spliceOnParameters(array $parameters, $key, $instance)
    {
        array_splice($parameters, $key, 0, array($instance));
    }
}