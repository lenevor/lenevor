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

namespace Syscodes\Components\Mail\Headers;

use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Mail\Headers\BaseHeader;

/**
 * A Mailbox MIME Header for something like Sender.
 */
class MailboxHeader extends BaseHeader
{
    /**
     * Get the address.
     * 
     * @var Address $address
     */
    protected Address $address;
    
    /**
     * Constructor. Create a new MailboxHeader class instance.
     * 
     * @param  string  $name
     * @param  Address  $address
     * 
     * @return void
     */
    public function __construct(string $name, Address $address)
    {
        parent::__construct($name);
        
        $this->setAddress($address);
    }

    /**
     * Get the body.
     * 
     * @return Address
     */
    public function getBody(): Address
    {
        return $this->getAddress();
    }
    
    /**
     * Set the body.
     * 
     * @param  mixed  $body
     * 
     * @return void
     */
    public function setBody(mixed $body): void
    {
        $this->setAddress($body);
    }

    /**
     * Get the address.
     * 
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }
    
    /**
     * Set the address. 
     * 
     * @param  Address  $address
     * 
     * @return void
     */
    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }  

    /**
     * Get the body as string.
     * 
     * @return void
     */
    public function getBodyAsString(): string
    {
        $str = $this->address->getEncodedAddress();
        
        if ($name = $this->address->getName()) {
            $str = ' <'.$str.'>';
        }
        
        return $str;
    }
}