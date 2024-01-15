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

namespace Syscodes\Components\Mail\Helpers;

use Syscodes\Components\Mail\Mailables\Message;
use Syscodes\Components\Mail\Mailables\RawMessage;

/**
 * Allows the sent of message at recipient's email.
 */
class SentMessage
{
    /**
     * Gets the debug when be send at mailbox.
     * 
     * @var string $debug
     */
    protected string $debug = '';
    
    /**
     * The envelope for send at mailbox.
     * 
     * @var Envelope $envelope
     */
    protected Envelope $envelope;

    /**
     * The original for send at mailbox.
     * 
     * @var RawMessage $original 
     */
    protected RawMessage $original;
    
    /**
     * The raw for send at mailbox.
     * 
     * @var RawMessage $raw 
     */
    protected RawMessage $raw;

    /**
     * Constructor. Create a new SentMessage class instance.
     * 
     * @param  RawMessage  $message
     * @param  Envelope  $envelope
     * 
     * @return void
     */
    public function __construct(RawMessage $message, Envelope $envelope)
    {
        $this->original = $message;
        $this->envelope = $envelope;
        
        if ($message instanceof Message) {
            $message = clone $message;

            $this->raw = $message;
        } else {
            $this->raw = $message;
        }
    }
    
    /**
     * Gets the message for send at mailbox.
     * 
     * @return RawMessage
     */
    public function getMessage(): RawMessage
    {
        return $this->raw;
    }
    
    /**
     * Gets the original message for send at mailbox.
     * 
     * @return RawMessage
     */
    public function getOriginalMessage(): RawMessage
    {
        return $this->original;
    }
    
    /**
     * Gets the envelope for send at mailbox.
     * 
     * @return Envelope
     */
    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    /**
     * Get a message of a string.
     * 
     * @return string
     */
    public function toString(): string
    {
        return $this->raw->toString();
    }

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

    /**
     * Get a message to iterate.
     * 
     * @return iterable
     */
    public function toIterable(): iterable
    {
        return $this->raw->toIterable();
    }
}