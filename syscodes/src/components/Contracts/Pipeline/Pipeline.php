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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Pipeline;

use Closure;

/**
 * Allows sending objects in classes and return the resulting value.
 */
interface Pipeline
{
    /**
     * Set the given object being sent on the pipeline.
     * 
     * @param  mixed  $sender
     * 
     * @return self
     */
    public function send($sender): self;

    /**
     * Set the array of pipes.
     * 
     * @param  array|mixed  $pipes
     * 
     * @return self
     */
    public function through($pipes): self;

    /**
     * Set the method to call on the stops.
     * 
     * @param  string  $method
     * 
     * @return self
     */
    public function method($method): self;

    /**
     * Run the pipeline with a final destination callback.
     * 
     * @param  \Closure  $destination
     * 
     * @return mixed
     */
    public function then(Closure $destination);
}