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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Mailables;

/**
 * Get the recipient email address.
 */
class Address
{
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
     * @param  string|null  $name
     * 
     * @return void
     */
    public function __construct(string $address, string $name = null)
    {
        $this->address = $address;
        $this->name    = $name;        
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
     * Get the recipient's name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}