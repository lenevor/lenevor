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

namespace Syscodes\Components\Mail;

use Syscodes\Components\Mail\Mailables\Email;
use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Support\Traits\ForwardsCalls;

/**
 * Allows the configuration to type of message for send at mail user.
 */
class Message
{
    use ForwardsCalls;

    /**
     * The Email instance.
     * 
     * @var \Syscodes\Components\Mail\Mailables\Email $message
     */
    protected $message;

    /**
     * Constructor. Create a new Message class instance.
     * 
     * @param  \Syscodes\Components\Mail\Mailables\Email  $message
     * 
     * @return void
     */
    public function __construct(Email $message)
    {
        $this->message = $message;
    }
    
    /**
     * Add a "from" address to the message.
     * 
     * @param  string|array  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function from($address, $name = null): static
    {
        is_array($address)
            ? $this->message->from(...$address)
            : $this->message->from(new Address($address, (string) $name));
        
        return $this;
    }
    
    /**
     * Set the "sender" of the message.
     * 
     * @param  string|array  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function sender($address, $name = null): static
    {
        is_array($address)
            ? $this->message->sender(...$address)
            : $this->message->sender(new Address($address, (string) $name));
            
        return $this;
    }
    
    /**
     * Set the "return path" of the message.
     * 
     * @param  string  $address
     * 
     * @return static
     */
    public function returnPath($address): static
    {
        $this->message->returnPath($address);
        
        return $this;
    }
    
    /**
     * Add a recipient to the message.
     * 
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * 
     * @return static
     */
    public function to($address, $name = null, $override = false): static
    {
        if ($override) {
            is_array($address)
                ? $this->message->to(...$address)
                : $this->message->to(new Address($address, (string) $name));
                
            return $this;
        }
        
        return $this->addAddresses($address, $name, 'To');
    }
    
    /**
     * Add a "reply to" address to the message.
     * 
     * @param  string|array  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function replyTo($address, $name = null): static
    {
        return $this->addAddresses($address, $name, 'ReplyTo');
    }
    
    /**
     * Add a recipient to the message.
     * 
     * @param  string|array  $address
     * @param  string  $name
     * @param  string  $type
     * 
     * @return static
     */
    protected function addAddresses($address, $name, $type): static
    {
        if (is_array($address)) {
            $type = lcfirst($type);
            
            $addresses = collect($address)->map(function ($address, $key) {
                if (is_string($key) && is_string($address)) {
                    return new Address($key, $address);
                }
                
                if (is_array($address)) {
                    return new Address($address['email'] ?? $address['address'], $address['name'] ?? null);
                }
                
                if (is_null($address)) {
                    return new Address($key);
                }
                
                return $address;
            })->all();
            
            $this->message->{"{$type}"}(...$addresses);
        } else {
            $this->message->{"add{$type}"}(new Address($address, (string) $name));
        }
        
        return $this;
    }
    
    /**
     * Add an address debug header for a list of recipients.
     * 
     * @param  string  $header
     * @param  \Syscodes\Components\Mail\Mailables\Address[]  $addresses
     * 
     * @return static
     */
    protected function addAddressDebugHeader(string $header, array $addresses): static
    {
        $this->message->getHeaders()->addTextHeader(
            $header,
            implode(', ', array_map(fn ($a) => $a->toString(), $addresses)),
        );
        
        return $this;
    }
    
    /**
     * Set the subject of the message.
     * 
     * @param  string  $subject
     * 
     * @return static
     */
    public function subject($subject): static
    {
        $this->message->subject($subject);
        
        return $this;
    }
    
    /**
     * Magic method.
     * 
     * Dynamically pass missing methods to the class instance.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardObjectCallTo($this->message, $method, $parameters);
    }
}