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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Events;

use Syscodes\Components\Mail\Helpers\Envelope;
use Syscodes\Components\Mail\Mailables\RawMessage;

/**
 * Get the message of mail.
 */
class Message
{
    /**
     * Constructor. Create a new Message class instance.
     * 
     * @param  RawMessage  $message
     * @param  Envelope  $envelope
     * @param  string  $transport
     * 
     * @return void
     */
    
    public function __construct(
        protected RawMessage $message,
        protected Envelope $envelope,
        protected string $transport
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
     * Set the raw message for send to mail.
     * 
     * @param  RawMessage  $message
     * 
     * @return void
     */
    public function setMessage(RawMessage $message): void
    {
        $this->message = $message;
    }
    
    /**
     * Get the envelope for send to mail.
     * 
     * @return Envelope
     */
    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }
    
    /**
     * Set the envelope for send to mail.
     * 
     * @param  Envelope  $envelope
     * 
     * @return void
     */
    public function setEnvelope(Envelope $envelope): void
    {
        $this->envelope = $envelope;
    }
    
    /**
     * Get the transport for send of mail.
     * 
     * @return string
     */
    public function getTransport(): string
    {
        return $this->transport;
    }
}