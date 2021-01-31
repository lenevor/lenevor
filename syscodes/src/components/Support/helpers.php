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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 * @since       0.7.3
 */

use Syscodes\Version;
use Syscodes\Support\Str;
use Syscodes\Support\Environment;

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
     * @param  mixed  $default  (null by default)
     * 
     * @return mixed
     */
    function env($key, $default = null)
    {
        return Environment::get($key, $default);
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

if ( ! function_exists('version'))
{
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