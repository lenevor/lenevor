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
 * @since       0.1.0
 */

use Syscodes\Version;
use Syscodes\Support\Arr;
use Syscodes\Support\Str;
use Syscodes\Core\Http\Lenevor;
use Syscodes\Support\HigherOrderTakeProxy;

if ( ! function_exists('array_add'))
{
    /**
     * Unsets dot-notated key from an array.
     *
     * @param  array   $array  
     * @param  string  $key  
     * @param  mixed   $value 
     *
     * @return array
     *
     * @uses   Arr::add
     */
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if ( ! function_exists('array_except'))
{
    /**
     * Get all of the given array except for a specified array of items.
     * 
     * @param  array  $array
     * @param  string|array  $key
     * 
     * @return array
     * 
     * @uses   Arr::except
     */
    function array_except($array, $key)
    {
        return Arr::except($array, $key);
    }
}

if ( ! function_exists('array_erase'))
{
    /**
     * Unsets dot-notated key from an array.
     *
     * @param  array  $array
     * @param  mixed  $key
     *
     * @return mixed
     *
     * @uses   Arr::erase
     */
    function array_erase(&$array, $key)
    {
        return Arr::erase($array, $key);
    }
}

if ( ! function_exists('array_first'))
{
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array     $array 
     * @param  \Closure  $callback
     * @param  mixed     $default
     *
     * @return mixed
     *
     * @uses   Arr::first
     */
    function array_first($array, $callback, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if ( ! function_exists('array_get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     * 
     * @uses   Arr::get
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if ( ! function_exists('array_last'))
{
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array     $array 
     * @param  \Closure  $callback
     * @param  mixed     $default 
     *
     * @return mixed
     *
     * @uses   Arr::last
     */
    function array_last($array, $callback, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if ( ! function_exists('array_set'))
{
    /**
     * Sets a value in an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     *
     * @uses   Arr::set
     */
    function array_set(& $array, $key, $default = null)
    {
        return Arr::set($array, $key, $default);
    }
}

if ( ! function_exists('camel_case'))
{
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

if ( ! function_exists('classBasename')) 
{
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * 
     * @return string
     */
    function classBasename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if ( ! function_exists('dd')) 
{
    /**
     * Generate test of variables.
     * 
     * @param  mixed
     * 
     * @return void
     */
    function dd()
    {
        array_map(function ($x)
        {
            var_dump($x);
        },  func_get_args());
            
        die(1);
    }
}

if ( ! function_exists('env')) 
{
    /**
     * Gets the value of an environment variable.
     * 
     * @param  string  $key
     * @param  mixed   $default
     * 
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false)
        {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? false;
        }

        if ($value === false)
        {
            return value($default);
        }

        // Handle any boolean values
        switch (strtolower($value))
        {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if ( ! function_exists('head'))
{
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array  $array
     *
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}

if ( ! function_exists('last'))
{
    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     *
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}

if ( ! function_exists('str_dash'))
{
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

if ( ! function_exists('str_humanize'))
{
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

if ( ! function_exists('str_smallcase'))
{
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

if ( ! function_exists('str_underscore'))
{
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

if ( ! function_exists('studly_caps'))
{
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

if ( ! function_exists('take'))
{
    /**
     * Call the given Closure if this activated then return the value.
     * 
     * @param  string         $value
     * @param  \Closure|null  $callback
     * 
     * @return mixed
     * 
     * @uses   \Syscodes\Support\HigherOrderTakeProxy
     */
    function take($value, $callback = null)
    {
        if (is_null($callback))
        {
            return new HigherOrderTakeProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if ( ! function_exists('value')) 
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * 
     * @return mixed
     * 
     * @uses   \Syscodes\Contracts\Core\Lenevor
     */
    function value($value)
    {
        return Lenevor::value($value);
    }
}

if ( ! function_exists('version'))
{
    /**
     * Return number version of the Lenevor.
     * 
     * @return string
     */
    function version()
    {
        return Version::RELEASE;
    }
}

if ( ! function_exists('winOS'))
{
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