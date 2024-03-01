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

namespace Syscodes\Components\Mail\Mailables;

use TypeError;
use LogicException;
use DateTimeInterface;
use Syscodes\Components\Mail\Mailables\Address;

/**
 * Allows the send of mail.
 */
class Email extends Message
{
    /**
     * Get the attachements.
     * 
     * @var array $attachments
     */
    protected array $attachments = [];

    /**
     * Get the html.
     * 
     * @var resource|string|null $html
     */
    protected $html;
    
    /**
     * Get the html charset.
     * 
     * @var string|null $htmlCharset
     */
    protected ?string $htmlCharset = null;
    
    /**
     * Get the text.
     * 
     * @var resource|string|null $text
     */
    protected $text;
    
    /**
     * Get the text charset.
     * 
     * @var string|null $textCharset
     */
    protected ?string $textCharset = null;

    /**
     * Set the subject of the message.
     * 
     * @param  string  $subject
     * 
     * @return static
     */
    public function subject(string $subject): static
    {        
        return $this->setHeaderBody('Text', 'Subject', $subject);
    }

    /**
     * Get the subject in the header body.
     * 
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->getHeaders()->getHeaderBody('Subject');
    }

    /**
     * Set the date of the message.
     * 
     * @param  string  $dateTime
     * 
     * @return static
     */
    public function date(DateTimeInterface $dateTime): static
    {        
        return $this->setHeaderBody('Date', 'Date', $dateTime);
    }

    /**
     * Get the date in the header body.
     * 
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->getHeaders()->getHeaderBody('Subject');
    }

    /**
     * Set the return-path of the message.
     * 
     * @param  Address|string  $address
     * 
     * @return static
     */
    public function returnPath(Address|string $address): static
    {        
        return $this->setHeaderBody('Path', 'Return-Path', $address);
    }

    /**
     * Get the return-path in the header body.
     * 
     * @return string|null
     */
    public function getReturnPath(): ?string
    {
        return $this->getHeaders()->getHeaderBody('Return-Path');
    }

    /**
     * Set the sender of the message.
     * 
     * @param  Address|string  $address
     * 
     * @return static
     */
    public function sender(Address|string $address): static
    {        
        return $this->setHeaderBody('Mailbox', 'Sender', $address);
    }

    /**
     * Get the sender in the header body.
     * 
     * @return string|null
     */
    public function getSender(): ?string
    {
        return $this->getHeaders()->getHeaderBody('Sender');
    }
    
    /**
     * Adds the list from of header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function addFrom(Address|string ...$addresses): static
    {
        return $this->addListAddressHeaderBody('From', $addresses);
    }
    
    /**
     * Sets the list from address on header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function from(Address|string ...$addresses): static
    {
        if ( ! $addresses) {
            throw new LogicException('"from()" must be called with at least one address');
        }
        
        return $this->setListAddressHeaderBody('From', $addresses);
    }
    
    /**
     * Gets the from address of header body.
     * 
     * @return Address[]
     */
    public function getFrom(): array
    {
        return $this->getHeaders()->getHeaderBody('From') ?: [];
    }
    
    /**
     * Adds the 'ReplyTo' address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function addReplyTo(Address|string ...$addresses): static
    {
        return $this->addListAddressHeaderBody('Reply-To', $addresses);
    }
    
    /**
     * Sets the list 'ReplyTo' address on header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function replyTo(Address|string ...$addresses): static
    {
        return $this->setListAddressHeaderBody('Reply-To', $addresses);
    }
    
    /**
     * Gets the 'ReplyTo' address to header body.
     * 
     * @return Address[]
     */
    public function getReplyTo(): array
    {
        return $this->getHeaders()->getHeaderBody('Reply-To') ?: [];
    }
    
    /**
     * Adds the address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function addTo(Address|string ...$addresses): static
    {
        return $this->addListAddressHeaderBody('To', $addresses);
    }
    
    /**
     * Sets the address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function to(Address|string ...$addresses): static
    {
        return $this->setListAddressHeaderBody('To', $addresses);
    }
    
    /**
     * Gets the address to header body.
     * 
     * @return Address[]
     */
    public function getTo(): array
    {
        return $this->getHeaders()->getHeaderBody('To') ?: [];
    }
    
    /**
     * Adds the 'Cc' address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function addCc(Address|string ...$addresses): static
    {
        return $this->addListAddressHeaderBody('Cc', $addresses);
    }
    
    /**
     * Sets the 'Cc' address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function cc(Address|string ...$addresses): static
    {
        return $this->setListAddressHeaderBody('Cc', $addresses);
    }
    
    /**
     * Gets the 'Cc' address to header body.
     * 
     * @return Address[]
     */
    public function getCc(): array
    {
        return $this->getHeaders()->getHeaderBody('Cc') ?: [];
    }
    
    /**
     * Adds the 'Bcc' address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function addBcc(Address|string ...$addresses): static
    {
        return $this->addListAddressHeaderBody('Bcc', $addresses);
    }
    
    /**
     * Sets the 'Bcc' address list to header body.
     * 
     * @param  Address|string[]  $addresses
     * 
     * @return static
     */
    public function bcc(Address|string ...$addresses): static
    {
        return $this->setListAddressHeaderBody('Bcc', $addresses);
    }
    
    /**
     * Gets the 'Bcc' address to header body.
     * 
     * @return Address[]
     */
    public function getBcc(): array
    {
        return $this->getHeaders()->getHeaderBody('Bcc') ?: [];
    }
    
    /**
     * The text content in the email.
     * 
     * @param  resource|string|null  $body
     * 
     * @return static
     */
    public function text($body, string $charset = 'utf-8'): static
    {
        if (null !== $body && ! is_string($body) && ! is_resource($body)) {
            throw new TypeError(sprintf('The body must be a string, a resource or null (got "%s")', get_debug_type($body)));
        }
        
        $this->text        = $body;
        $this->textCharset = $charset;
        
        return $this;
    }
    
    /**
     * Get the text content body.
     * 
     * @return resource|string|null
     */
    public function getTextBody()
    {
        return $this->text;
    }
    
    /**
     * Get the text charset.
     * 
     * @return string|null
     */
    public function getTextCharset(): ?string
    {
        return $this->textCharset;
    }
    
    /**
     * The html content in the email.
     * 
     * @param  resource|string|null  $body
     * 
     * @return static
     */
    public function html($body, string $charset = 'utf-8'): static
    {
        if (null !== $body && ! is_string($body) && ! is_resource($body)) {
            throw new TypeError(sprintf('The body must be a string, a resource or null (got "%s")', get_debug_type($body)));
        }
        
        $this->html        = $body;
        $this->htmlCharset = $charset;
        
        return $this;
    }
    
    /**
     * Get the html content body.
     * 
     * @return resource|string|null
     */
    public function getHtmlBody()
    {
        return $this->html;
    }
    
    /**
     * Get the html charset.
     * 
     * @return string|null
     */
    public function getHtmlCharset(): ?string
    {
        return $this->htmlCharset;
    }
    
    /**
     * Set the header body.
     * 
     * @param  string  $type
     * @param  string  $name
     * @param  mixed  $body
     * 
     * @return static
     */
    private function setHeaderBody(string $type, string $name, mixed $body): static
    {
        $this->getHeaders()->setHeaderBody($type, $name, $body);
        
        return $this;
    }
    
    /**
     * Adds the list address a header body.
     * 
     * @param  string  $name
     * @param  string[]  $addresses
     * 
     * @return static
     */
    private function addListAddressHeaderBody(string $name, array $addresses): static
    {
        if ( ! $header = $this->getHeaders()->get($name)) {
            return $this->setListAddressHeaderBody($name, $addresses);
        }
        
        $header->addAddresses(Address::createArray($addresses));
        
        return $this;
    }
    
    /**
     * Sets the list address a header body.
     * 
     * @param  string  $name
     * @param  string[]  $addresses
     * 
     * @return static
     */
    private function setListAddressHeaderBody(string $name, array $addresses): static
    {
        $addresses = Address::createArray($addresses);
        $headers   = $this->getHeaders();
        
        if ($header = $headers->get($name)) {
            $header->setAddresses($addresses);
        } else {
            $headers->addMailboxListHeader($name, $addresses);
        }
        
        return $this;
    }
}