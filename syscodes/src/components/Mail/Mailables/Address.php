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

use InvalidArgumentException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Mail\Encoder\IdnAddressEncoder;

/**
 * Get the recipient email address.
 */
final class Address
{
    protected const STRING_PATTERN = '~(?<displayName>[^<]*)<(?<address>.*)>[^>]*~';

    /**
     * Get the idn address encoder.
     * 
     * @var IdnAddressEncoder $encoder
     */
    protected static IdnAddressEncoder $encoder;

    /**
     * The recipient's email address.
     * 
     * @var string $address
     */
    protected $address;

    /**
     * The recipient's name.
     * 
     * @var string|null $name
     */
    protected $name;

    /**
     * Constructor. Create a new Address class instance.
     * 
     * @param  string  $address
     * @param  string  $name
     * 
     * @return void
     */
    public function __construct(string $address, string $name = '')
    {
        $this->address = trim($address);
        $this->name    = trim(str_replace(["\n", "\r"], '', $name));      
    }

    /**
     * Get the recipient's email address.
     * 
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Get the encoded address.
     * 
     * @return string
     */
    public function getEncodedAddress(): string
    {
        self::$encoder ?? new IdnAddressEncoder();

        return self::$encoder->encodeString($this->address);
    }

    /**
     * Get the recipient's name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Gets this Header rendered as a compliant string.
     * 
     * @return string
     */
    public function toString(): string
    {
        return ($n = $this->getEncodedName()) ? $n.' <'.$this->getEncodedAddress().'>' : $this->getEncodedAddress();
    }
    
    /**
     * Get the encoded name.
     * 
     * @return string
     */
    public function getEncodedName(): string
    {
        if ('' === $this->getName()) {
            return '';
        }
        
        return sprintf('"%s"', preg_replace('/"/u', '\"', $this->getName()));
    }

    /**
     * Creates a parse for display name and address of a mailbox.
     * 
     * @param  self|string  $address
     * 
     * @return self
     */
    public static function create(self|string $address): self
    {
        if ($address instanceof self) {
            return $address;
        }
        
        if ( ! Str::contains($address, '<')) {
            return new self($address);
        }
        
        if ( ! preg_match(self::STRING_PATTERN, $address, $matches)) {
            throw new InvalidArgumentException(sprintf('Could not parse "%s" to a "%s" instance', $address, self::class));
        }
        
        return new self($matches['address'], trim($matches['displayName'], ' \'"'));
    }
    
    /**
     * Creates a parse for display name and address of a mailbox using an array.
     * 
     * @param  array<Address|string>  $addresses
     * 
     * @return Address[]
     */
    public static function createArray(array $addresses): array
    {
        $addrs = [];
        
        foreach ($addresses as $address) {
            $addrs[] = self::create($address);
        }
        
        return $addrs;
    }
}