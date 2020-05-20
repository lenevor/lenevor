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
 * @since       0.2.0
 */

namespace Syscodes\Support;

/**
 * Allows a new take proxy instance.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class HigherOrderTakeProxy
{
    /**
     * The target being taked.
     * 
     * @var mixed $target
     */
    public $target;

    /**
     * Constructor. Create a new take proxy instance.
     * 
     * @param  mixed  $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Dynamically pass method calls to the target.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->target->{$method}(...$parameters);

        return $this->target;
    }
}