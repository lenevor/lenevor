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

use LogicException;
use Syscodes\Components\Mail\Headers;
use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Mail\Mailables\Message;

/**
 * Allows the send of a mailbox in delayed envelope.
 */
final class DelayedEnvelope extends Envelope
{
    /**
     * The send message.
     * 
     * @var Message $message
     */
    protected Message $message;

    /**
     * If have active the recipients to send message.
     * 
     * @var bool $recipients
     */
    protected bool $recipients = false;

    /**
     * If have active the sender to send message.
     * 
     * @var bool $sender
     */
    protected bool $sender = false;
    
    /**
     * Constructor. Create a new DelayedEnvelope class instance.
     * 
     * @param  Message  $message
     * 
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }
    
    /**
     * Sets the sender.
     * 
     * @param  Address  $sender
     * 
     * @return void
     */
    public function setSender(Address $sender): void
    {
        parent::setSender($sender);
        
        $this->sender = true;
    }
    
    /**
     * Gets the sender.
     * 
     * @return address
     */
    public function getSender(): Address
    {
        if ( ! $this->sender) {
            parent::setSender(static::getSenderFromHeaders($this->message->getHeaders()));
        }
        
        return parent::getSender();
    }
    
    /**
     * Sets the recipients.
     * 
     * @param  array  $recipients
     * 
     * @return void
     */
    public function setRecipients(array $recipients): void
    {
        parent::setRecipients($recipients);
        
        $this->recipients = (bool) parent::getRecipients();
    }
    
    /**
     * Gets the recipients.
     * 
     * @return Address[]
     */
    public function getRecipients(): array
    {
        if ($this->recipients) {
            return parent::getRecipients();
        }
        
        return static::getRecipientsFromHeaders($this->message->getHeaders());
    }
    
    /**
     * Gets the recipients from headers.
     * 
     * @param  Headers  $headers
     * 
     * @return array
     */
    private static function getRecipientsFromHeaders(Headers $headers): array
    {
        $recipients = [];
        
        foreach (['to', 'cc', 'bcc'] as $name) {
            foreach ($headers->all($name) as $header) {
                foreach ($header->getAddress() as $address) {
                    $recipients[] = $address;
                }
            }
        }
        
        return $recipients;
    }
    
    /**
     * Gets the sender from headers.
     * 
     * @param  Headers  $headers
     * 
     * @return Address
     */
    private static function getSenderFromHeaders(Headers $headers): Address
    {
        if ($sender = $headers->get('Sender')) {
            return $sender->getAddress();
        }
        
        if ($return = $headers->get('Return-Path')) {
            return $return->getAddress();
        }
        
        if ($from = $headers->get('From')) {
            return $from->getAddress()[0];
        }
        
        throw new LogicException('Unable to determine the sender of the message');
    }   
}