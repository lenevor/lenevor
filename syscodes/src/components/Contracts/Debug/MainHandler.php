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

namespace Syscodes\Components\Contracts\Debug;

use Syscodes\Components\Debug\FrameHandler\Supervisor;

/**
 * Gets debug interface.
 */
interface MainHandler
{
    /**
     * Given an exception and status code will display the error to the client.
     * 
     * @return \callable|int|null
     */
    public function handle();
    
    /**
     * Sets debug.
     * 
     * @param  \Syscodes\Components\Contracts\Debug\Handler  $debug
     * 
     * @return void
     */
    public function setDebug($debug): void;

    /**
     * Sets exception.
     * 
     * @param  \Throwable  $exception
     * 
     * @return void
     */
    public function setException($exception): void;

    /**
     * Sets supervisor.
     * 
     * @param  \Syscodes\Components\Debug\FrameHandler\Supervisor  $supervisor
     * 
     * @return void
     */
    public function setSupervisor(Supervisor $supervisor): void;
}