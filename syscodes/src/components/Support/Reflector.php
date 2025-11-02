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

namespace Syscodes\Components\Support;

use ReflectionMethod;

/**
 * Allows the reflection methods for get the specified methods, 
 * parameters, attributes and class.
 */
class Reflector
{
    /**
     * Compatible implementation of is_callable.
     *
     * @param  mixed  $var
     * @param  bool  $syntaxOnly
     * @return bool
     */
    public static function isCallable($var, $syntaxOnly = false)
    {
        if ( ! is_array($var)) {
            return is_callable($var, $syntaxOnly);
        }

        if ( ! isset($var[0], $var[1]) || ! is_string($var[1] ?? null)) {
            return false;
        }

        if ($syntaxOnly &&
            (is_string($var[0]) || is_object($var[0])) &&
            is_string($var[1])) {
            return true;
        }

        $class = is_object($var[0]) ? get_class($var[0]) : $var[0];

        $method = $var[1];

        if ( ! class_exists($class)) {
            return false;
        }

        if (method_exists($class, $method)) {
            return (new ReflectionMethod($class, $method))->isPublic();
        }

        if (is_object($var[0]) && method_exists($class, '__call')) {
            return (new ReflectionMethod($class, '__call'))->isPublic();
        }

        if ( ! is_object($var[0]) && method_exists($class, '__callStatic')) {
            return (new ReflectionMethod($class, '__callStatic'))->isPublic();
        }

        return false;
    }
}