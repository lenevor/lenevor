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

namespace Syscodes\Components\Mail\Exceptions;

use RuntimeException;

/**
 * TransportException.
 */
class TransportException extends RuntimeException
{
    /**
     * Gets the debug when be send at mailbox.
     * 
     * @var string $debug
     */
    protected  string $debug = '';
    
    /**
     * Gets the debug.
     * 
     * @return string
     */
    public function getDebug(): string
    {
        return $this->debug;
    }
    
     /**
     * Appends to debug.
     * 
     * @param  string  $debug
     * 
     * @return void
     */
    public function appendDebug(string $debug): void
    {
        $this->debug .= $debug;
    }
}