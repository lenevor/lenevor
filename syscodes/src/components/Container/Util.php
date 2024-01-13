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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Container;

use Closure;
use ReflectionNamedType;

/**
 * @internal
 */
class Util
{
    /**
     * Return the default value of the given value.
     * 
     * @param  mixed  $value
     * @param  mixed  ...$args
     * 
     * @return mixed
     */
    public static function unwrapExistOfClosure($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }

    /**
     * Get the class name of the given parameter's type.
     * 
     * @param  \ReflectionParameter  $parameter
     * 
     * @return string|null
     */
    public static function getParameterClassName($parameter): string|null
    {
        $type = $parameter->getType();
        
        if ( ! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }
        
        $name = $type->getName();
        
        if ( ! is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }
            
            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }
        
        return $name;
    }
}