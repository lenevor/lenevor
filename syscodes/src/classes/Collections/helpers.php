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
 * @since       0.7.2
 */

use Syscodes\Collections\Arr;
use Syscodes\Collections\Collection;
use Syscodes\Collections\HigherOrderTakeProxy;

if ( ! function_exists('collect'))
{
    /**
     * Create a collection from the given value.
     * 
     * @param  mixed  $value  (null by default)
     * 
     * @return \Syscodes\Collections\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
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

if ( ! function_exists('take'))
{
    /**
     * Call the given Closure if this activated then return the value.
     * 
     * @param  string  $value
     * @param  \Closure|null  $callback
     * 
     * @return mixed
     * 
     * @uses   \Syscodes\Collections\HigherOrderTakeProxy
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
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}