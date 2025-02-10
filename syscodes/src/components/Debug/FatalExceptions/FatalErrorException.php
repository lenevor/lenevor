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

namespace Syscodes\Components\Debug\FatalExceptions;

use Throwable;
use ErrorException;
use ReflectionProperty;

/**
 * Fatal Error Exception.
 */
class FatalErrorException extends ErrorException
{
    /**
     * Constructor. Initialize FatalErrorException class.
     * 
     * @param  string  $message
     * @param  int  $code
     * @param  int  $severity
     * @param  string  $filename
     * @param  int  $lineno
     * @param  int|null  $traceOffset
     * @param  bool  $traceArgs
     * @param  array|null  $trace
     * @param  \Throwable  $previous
     * 
     * @return void
     */
    public function __construct(
        string    $message,
        int       $code,
        int       $severity,
        string    $filename,
        int       $lineno,
        ?int       $traceOffset = null,
        bool      $traceArgs = true,
        array     $trace = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);

        if (null !== $trace) {
            if ( ! $traceArgs) {
                foreach ($trace as &$frame) {
                    unset($frame['args'], $frame['this'], $frame);
                }
            }

            $this->setTrace($trace);
        } elseif (null !== $traceOffset) {
            if (function_exists('xdebug_get_function_stack')) {
                $trace = \xdebug_get_function_stack();

                if ($traceOffset > 0) {
                    array_slice($trace, -$traceOffset);
                }

                foreach ($trace as &$frame) {
                    if ( ! isset($frame['type'])) {
                        if (isset($frame['class'])) {
                            $frame['type'] = '::';
                        }
                    } elseif ('dynamic' === $frame['type']) {
                        $frame['type'] = '->';
                    } elseif ('static' === $frame['type']) {
                        $frame['type'] = '::';
                    }

                    if ( ! $traceArgs) {
                        unset($frame['params'], $frame['args']);
                    } elseif (isset($frame['params']) && ! $frame['args']) {
                        $frame['args'] = $frame['params'];
                        unset($frame['params']);
                    }
                }
                
                unset($frame);
                $trace = array_reverse($trace);
            } else {
                $trace = [];
            }

            $this->setTrace($trace);
        }
    }

    /**
     * Gets reports information about of Exception class properties trace.
     * 
     * @param  array  $trace
     * 
     * @return void
     */
    protected function setTrace($trace): void
    {
        $reflection = new ReflectionProperty('Exception', 'trace');
        $reflection->setAccessible(true);
        $reflection->setValue($this, $trace);
    }          
}