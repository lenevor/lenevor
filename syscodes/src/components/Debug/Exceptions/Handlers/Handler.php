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

namespace Syscodes\Components\Debug\Handlers;

use Syscodes\Components\Contracts\Debug\MainHandler;
use Syscodes\Components\Debug\FrameHandler\Supervisor;

/**
 * Abstract implementation of a Handler.
 */
abstract class Handler implements MainHandler
{ 
    /**
     * The Handler has handled the Throwable in some way, and wishes to skip any other Handler.
     * Execution will continue.
     */
    const LAST_HANDLER = 0x20;

    /**
     * The Handler has handled the Throwable in some way, and wishes to quit/stop execution.
     */
    const QUIT = 0x30;

    /**
     * Get debug.
     * 
     * @var \Syscodes\Components\Contracts\Debug\Handler $debug
     */
    protected $debug;
    
    /**
     * Get exception.
     * 
     * @var \Throwable $exception
     */
    protected $exception;
    
    /**
     * Get supervisor.
     * 
     * @var string $supervisor
     */
    protected $supervisor;

    /**
     * Gets Debug class with you interface.
     * 
     * @return \Syscodes\Components\Contracts\Debug\Handler  Interface
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Sets debug.
     * 
     * @param  \Syscodes\Components\Contracts\Debug\Handler  $debug
     * 
     * @return void
     */
    public function setDebug($debug): void
    {
        $this->debug = $debug;
    }
    
    /**
     * Gets exception already specified.
     * 
     * @return \Throwable
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Sets exception.
     * 
     * @param  \Throwable  $exception
     * 
     * @return void
     */
    public function setException($exception): void
    {
        $this->exception = $exception;
    }
    
    /**
     * Gets supervisor already specified.
     * 
     * @return \Syscodes\Components\Debug\FrameHandler\Supervisor
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * Sets supervisor.
     * 
     * @param  \Syscodes\Components\Debug\FrameHandler\Supervisor  $supervisor
     * 
     * @return void
     */
    public function setSupervisor(Supervisor $supervisor): void
    {
        $this->supervisor = $supervisor;
    }
}