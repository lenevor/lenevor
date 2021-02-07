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
 */

namespace Syscodes\Debug\Handlers;

use InvalidArgumentException;

/**
 * Wrapper for Closures passed as handlers. Can be used directly, 
 * or will be instantiated automagically by Debug\GDebug if passed 
 * to GDebug::pushHandler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class CallbackHandler extends MainHandler
{
    /**
     * The contents of a variable can be called as a function.
     * 
     * @var \Callable $callable
     */
    protected $callable;

    /**
     * Constructor. The CallableHandler class.
     * 
     * @param  \callable  $callable
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct($callable)
    {
        if ( ! is_callable($callable)) {
            throw new InvalidArgumentException('Argument to '.__METHOD__.' must be valid callable');            
        }

        $this->callable = $callable;
    }

    /**
     * Given an exception and status code will display the error to the client.
     * 
     * @return \callable|int|null
     */
    public function handle()
    {
        $exception  = $this->getException();
        $supervisor = $this->getSupervisor();
        $debug      = $this->getDebug();
        $callable   = $this->callable;

        return $callable($exception, $supervisor, $debug);
    }
}