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
 * An ID MIME Header for something like Message-ID or Content-ID.
 */
class IdentificationMessageHeader extends BaseHeader
{
    /**
     * Get the ids.
     * 
     * @var array $ids
     */
    private array $ids = [];
    
    /**
     * Gets the ids addresses.
     * 
     * @var array $idsAddresses
     */
    private array $idsAddresses = [];
    
    /**
     * Constructor. Create a new IdentificationMessageHeader class instance.
     * 
     * @param  string  $name
     * @param  string|string[]  $ids
     * 
     * @return void
     */
    public function __construct(string $name, string|array $ids)
    {
        parent::__construct($name);
        
        $this->setId($ids);
    }
    
    /**
     * Set the body.
     * 
     * @param  string|string[]  $body  A string ID or an array of IDs
     * 
     * @return void
     */
    public function setBody(mixed $body): void
    {
        $this->setId($body);
    }
    
    /**
     * Get the body.
     * 
     * @return array
     */
    public function getBody(): array
    {
        return $this->getIds();
    }
    
    /**
     * Set the ID used in the value of this header.
     * 
     * @param  string|string[]  $id
     * 
     * @return void
     */
    public function setId(string|array $id): void
    {
        $this->setIds(is_array($id) ? $id : [$id]);
    }
    
    /**
     * Get the ID used in the value of this Header.
     * 
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->ids[0] ?? null;
    }
    
    /**
     * Set a collection of IDs to use in the value of this Header.
     * 
     * @param string[] $ids
     * 
     * @return void
     */
    public function setIds(array $ids): void
    {
        $this->ids = [];
        $this->idsAddresses = [];
        
        foreach ($ids as $id) {
            $this->idsAddresses[] = new Address($id);
            $this->ids[] = $id;
        }
    }
    
    /**
     * Get the list of IDs used in this Header.
     * 
     * @return string[]
     * 
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }
    
    /**
     * Get the body as string.
     * 
     * @return string
     */
    public function getBodyAsString(): string
    {
        $addrs = [];
        
        foreach ($this->idsAddresses as $address) {
            $addrs[] = '<'.$address->toString().'>';
        }
        return implode(' ', $addrs);
    }
}