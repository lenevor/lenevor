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

namespace Syscodes\Components\Core\Configuration;

use Syscodes\Components\Contracts\Debug\ExceptionHandler;

/**
 * Allows the bootstrap of the exception handler.
 */
class ExceptionBootstrap
{
    /**
     * Constructor. Create a new exception handling configuration instance.
     * 
     * @param  \Syscodes\Components\Contracts\Debug\ExceptionHandler  $handler
     * 
     * @return void
     */
    public function __construct(protected ExceptionHandler $handler)
    {        
    }
    
    /**
     * Register a reportable callback.
     * 
     * @param  callable  $using
     * 
     * @return \Syscodes\Components\Core\Exceptions\Handler
     */
    public function report(callable $using)
    {
        return $this->handler->reportable($using);
    }
    
    /**
     * Register a reportable callback.
     * 
     * @param  callable  $reportUsing
     * 
     * @return \Syscodes\Components\Core\Exceptions\Handler
     */
    public function reportable(callable $reportUsing)
    {
        return $this->handler->reportable($reportUsing);
    }
    
    /**
     * Register a renderable callback.
     * 
     * @param  callable  $using
     * 
     * @return static
     */
    public function render(callable $using): static
    {
        $this->handler->renderable($using);
        
        return $this;
    }
    
    /**
     * Register a renderable callback.
     * 
     * @param  callable  $renderUsing
     * 
     * @return static
     */
    public function renderable(callable $renderUsing): static
    {
        $this->handler->renderable($renderUsing);
        
        return $this;
    }
}