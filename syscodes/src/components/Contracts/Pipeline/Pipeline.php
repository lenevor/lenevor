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
 * @since       0.7.3
 */

namespace Syscodes\Contracts\Pipeline;

use Closure;

/**
 * Allows sending objects in classes and return the resulting value.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface Pipeline
{
    /**
     * Set the given object being sent on the pipeline.
     * 
     * @param  mixed  $sender
     * 
     * @return $this
     */
    public function send($sender);

    /**
     * Set the array of pipes.
     * 
     * @param  array|mixed  $pipes
     * 
     * @return $this
     */
    public function through($pipes);

    /**
     * Set the method to call on the stops.
     * 
     * @param  string  $method
     * 
     * @return $this
     */
    public function method($method);

    /**
     * Run the pipeline with a final destination callback.
     * 
     * @param  \Closure  $destination
     * 
     * @return mixed
     */
    public function then(Closure $destination);
}