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

namespace Syscodes\Components\Mail\Headers;

use Syscodes\Components\Mail\Mailables\Address;

/**
 * A Mailbox list MIME Header for something like From, To, Cc, and Bcc.
 */
class MailboxListHeader extends BaseHeader
{
    /**
     * Get the addresses.
     * 
     * @var array $addresses
     */
    protected array $addresses = [];
    
    /**
     * Constructor. Create a new MailBoxListHeader class instance.
     * 
     * @param  string  $name
     * @param  Address[]  $addresses
     * 
     * @return void
     */
    public function __construct(string $name, array $addresses)
    {
        parent::__construct($name);
        
        $this->setAddresses($addresses);
    }

    /**
     * Get the body.
     * 
     * @return Address[]
     */
    public function getBody(): array
    {
        return $this->getAddresses();
    }
    
    /**
     * Set the body.
     * 
     * @param Address[] $body
     * 
     * @return void
     */
    public function setBody(mixed $body): void
    {
        $this->setAddresses($body);
    }
    
    /**
     * Get the addressess.
     * 
     * @return Address[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }
    
    /**
     * Sets a list of addresses to be shown in this Header.
     * 
     * @param  Address[]  $addresses
     *
     * @return void
     */
    public function setAddresses(array $addresses): void
    {
        $this->addresses = [];
        
        $this->addAddresses($addresses);
    }
    
    /**
     * Sets a list of addresses to be shown in this Header.
     * 
     * @param  Address[]  $addresses
     * 
     * @return void 
     */
    public function addAddresses(array $addresses): void
    {
        foreach ($addresses as $address) {
            $this->addAddress($address);
        }
    }
    
    /**
     * Set the address.
     * 
     * @param  Address  $address
     * 
     * @return void
     */
    public function addAddress(Address $address): void
    {
        $this->addresses[] = $address;
    }

    /**
     * Get the body as string.
     * 
     * @return string
     */
    public function getBodyAsString(): string
    {
        return implode(', ', $this->getAddressStrings());
    }
    
    /**
     * Gets the full mailbox list of this Header as an array of valid RFC 2822 strings.
     * 
     * @return string[]
     */
    public function getAddressStrings(): array
    {
        $strings = [];
        
        foreach ($this->addresses as $address) {
            $str = $address->getEncodedAddress();
            
            if ($name = $address->getName()) {
                $str = ' <'.$str.'>';
            }
            
            $strings[] = $str;
        }
        
        return $strings;
    }
}