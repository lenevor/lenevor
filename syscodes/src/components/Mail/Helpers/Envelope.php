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
use InvalidArgumentException;
use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Mail\Mailables\RawMessage;

/**
 * Allows the creation of envelopes to the send at recipients.
 */
class Envelope
{
    /**
     * Gets the recipients of emails.
     * 
     * @var array $recipients
     */
    private array $recipients = [];

    /**
     * Gets the sender of email.
     * 
     * @var Address $sender
     */
    protected Address $sender;
    
    /**
     * Constructor. Create new a Envelope class instance.
     * 
     * @param Address  $sender
     * @param Address[] $recipients
     * 
     * @return void
     */
    public function __construct(Address $sender, array $recipients)
    {
        $this->setSender($sender);
        $this->setRecipients($recipients);
    }
    
    /**
     * For the send of delayed envelope.
     * 
     * @param  RawMessage  $message
     * 
     * @return self
     */
    public static function create(RawMessage $message): self
    {
        if (RawMessage::class === $message::class) {
            throw new LogicException('Cannot send a RawMessage instance without an explicit Envelope');
        }
        
        return new DelayedEnvelope($message);
    }
    
    /**
     * Sets the sender custom.
     * 
     * @param  Address  $sender
     * 
     * @return void
     */
    public function setSender(Address $sender): void
    {
        $this->sender = $sender;
    }
    
    /**
     * Gets the sender of a mailbox.
     * 
     * @return Address Returns a "mailbox" as specified by RFC 2822
     */
    public function getSender(): Address
    {
        return $this->sender;
    }
    
    /**
     * Sets the recipients custom.
     * 
     * @param Address[] $recipients
     * 
     * @return void
     */
    public function setRecipients(array $recipients): void
    {
        if ( ! $recipients) {
            throw new InvalidArgumentException('An envelope must have at least one recipient');
        }
        
        $this->recipients = [];
        
        foreach ($recipients as $recipient) {
            if ( ! $recipient instanceof Address) {
                throw new InvalidArgumentException(sprintf('A recipient must be an instance of "%s" (got "%s")', 
                    Address::class,
                    get_debug_type($recipient)
                ));
            }
            
            $this->recipients[] = new Address($recipient->getAddress());
        }
    }
    
    /**
     * Gets the recipients of a mailbox.
     * 
     * @return Address[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}