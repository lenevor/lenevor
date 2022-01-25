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

namespace Syscodes\Components\Support\Traits;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;

/**
 * Trait Macroable.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait Macroable
{
    /**
     * The registered string macros.
     * 
     * @var array $macros
     */
    protected static $macros = [];
    
    /**
     * Register a custom macro.
     * 
     * @param  string  $name
     * @param  object|callable  $macro
     * 
     * @return void
     */
    public static function macro(string $name, $macro): void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Checks if macro is registered.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Mix another object into the class.
     * 
     * @param  object|string  $mixin
     * @param  bool  $bool
     * 
     * @return void
     */
    public static function mixin($mixin, bool $bool = true): void
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($bool || ! static::hasMacro($method)) {
                $method->setAccesible(true);

                static::macro($method->name, $method->invoke($mixin));
            }
        }
    }

    /**
     * Magic method.
     * 
     * Dynamically handle calls to the class.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if ( ! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s() does not exist', static::class, $method 
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return call_user_func_array($macro, $parameters);
    }

    /**
     * Magic method.
     * 
     * Dynamically handle calls to the class.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if ( ! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s() does not exist', static::class, $method 
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return call_user_func_array($macro, $parameters);
    }
}