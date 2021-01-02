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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.2
 */

namespace Syscodes\Collections\Traits;

/**
 * Trait.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait Enumerates
{
    /**
     * Create a new collection instance if the value isn't one already.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Determine if the given value is callable, but not a string.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    protected function usesAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }
}
