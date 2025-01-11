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

use Syscodes\Components\Version;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Environment;
use Syscodes\Components\Support\HigherOrderTakeProxy;

if ( ! function_exists('camel_case')) {
    /**
     * Convert the string with spaces or underscore in camelcase notation.
     *
     * @param  string  $string  
     *
     * @return string
     * 
     * @uses   Str::camelcase
     */
    function camel_case($string)
    {
        return Str::camelcase($string);
    }
}

if ( ! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * 
     * @return string
     */
    function class_basename($class)
    {
        $className = is_object($class) ? get_class($class) : $class;

        return basename(
            str_replace('\\', '/', $className)
        );
    }
}

if ( ! function_exists('class_recursive'))
{
    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits
     * 
     * @param  string  $class
     * 
     * @return array
     */
    function class_recursive($class)
    {
        $results = [];
        
        foreach (array_merge(array($class => $class), class_parents($class)) as $class) {
            $results += trait_recursive($class);
        }
        
        return array_unique($results);
    }
}

if ( ! function_exists('dd')) {
    /**
     * Generate test of variables.
     * 
     * @param  mixed
     * 
     * @return void
     */
    function dd()
    {
        array_map(fn ($x) => var_dump($x),  func_get_args());
            
        die(1);
    }
}

if ( ! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     * 
     * @param  string  $key
     * @param  mixed  $default  
     * 
     * @return mixed
     */
    function env($key, $default = null)
    {
        return Environment::get($key, $default);
    }
}

if ( ! function_exists('preg_replace_sub')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     * 
     * @param  string  $pattern
     * @param  array   $replacements
     * @param  string  $subject
     * 
     * @return string
     */
    function preg_replace_sub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, fn ($match) => array_shift($replacements), $subject);
    }
}

if ( ! function_exists('str_dash')) {
    /**
     * Replace in the chain the spaces by dashes.
     *
     * @param  string  $string  
     *
     * @return string
     *
     * @uses   Str::dash
     */
    function str_dash($string)
    {
        return Str::dash($string);
    }
}

if ( ! function_exists('str_humanize')) {
    /**
     * Replace in an string the underscore or dashed by spaces.
     *
     * @param  string  $string
     *
     * @return string
     *
     * @uses   Str::humanize
     */
    function str_humanize($string)
    {
        return Str::humanize($string);
    }
}

if ( ! function_exists('str_smallcase')) {
    /**
     * Converts the CamelCase string into smallcase notation.
     *
     * @param  string  $string
     *
     * @return string
     *
     * @uses   Str::smallcase
     */
    function str_smallcase($string)
    {
        return Str::smallcase($string);
    }
}

if ( ! function_exists('str_underscore')) {
    /**
     * Replace in the string the spaces by low dashes.
     *
     * @param  string  $string
     *
     * @return string
     *
     * @uses   Str::underscore
     */
    function str_underscore($string)
    {
        return Str::underscore($string);
    }
}

if ( ! function_exists('studly_caps')) {
    /**
     * Convert the string with spaces or underscore in StudlyCaps. 
     *
     * @param  string  $string
     *
     * @return string
     *
     * @uses   Str::studlycaps
     */
    function studly_caps($string)
    {
        return Str::studlycaps($string);
    }
}

if ( ! function_exists('take')) {
    /**
     * Call the given Closure if this activated then return the value.
     * 
     * @param  mixed  $value
     * @param  \Closure|null  $callback
     * 
     * @return mixed
     * 
     * @uses   \Syscodes\Components\Support\HigherOrderTakeProxy
     */
    function take(mixed $value, ?\Closure $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTakeProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if ( ! function_exists('title')) {
    /**
     * Generates the letter first of a word in upper.
     * 
     * @param  string  $string
     * 
     * @return string
     * 
     * @uses   Str::title
     */
    function title($string)
    {
        return Str::title($string);
    }
}

if ( ! function_exists('trait_recursive'))
{
    /**
     * Returns all traits used by a trait and its traits.
     * 
     * @param  string  $trait
     * 
     * @return array
     */
    function trait_recursive($trait)
    {
        $traits = class_uses($trait);
        
        foreach ($traits as $trait) {
            $traits += trait_recursive($trait);
        }
        
        return $traits;
    }
}

if ( ! function_exists('uTitle')) {
    /**
     * Convert the given string to title case in UTF-8 format.
     * 
     * @param  string  $string
     * 
     * @return string
     * 
     * @uses   Str::uTitle
     */
    function uTitle($string)
    {
        return Str::uTitle($string);
    }
}

if ( ! function_exists('version')) {
    /**
     * Return number version of the Lenevor.
     * 
     * @return string
     */
    function version()
    {
        return Version::RELEASE.'-'.Version::STATUS;
    }
}

if ( ! function_exists('winOS')) {
    /**
     * Determine whether the current envrionment is Windows based.
     *
     * @return bool
     */
    function winOS()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}

if ( ! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     * 
     * @param  mixed  $value
     * @param  \callable|null  $callback
     * 
     * @return mixed
     */
    function with($value, ?callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}