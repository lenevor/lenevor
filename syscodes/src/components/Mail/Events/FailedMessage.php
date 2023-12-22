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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Events;

use Throwable;
use Syscodes\Components\Mail\Mailables\RawMessage;

/**
 * Get the failed message.
 */
class FailedMessage
{
    /**
     * Constructor. Create a new FailedMessage class instance.
     * 
     * @param  RawMessage  $message
     * @param  Throwable  $error
     * 
     * @return void
     */
    public function __construct(
        private RawMessage $message,
        private Throwable $error,
    ) {}
    
    /**
     * Get the raw message for send to mail.
     * 
     * @return RawMessage
     */
    public function getMessage(): RawMessage
    {
        return $this->message;
    }
    
    /**
     * Get error in the send of a message.
     * 
     * @return Throwable
     */
    public function getError(): Throwable
    {
        return $this->error;
    }
}