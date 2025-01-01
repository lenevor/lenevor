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

/**
 * A Simple MIME Header.
 */
final class FileHeader extends BaseHeader
{
    /**
     * Get the value.
     * 
     * @var string $value
     */
    protected string $value;
    
    /**
     * Constructor. Create a new FileHeader class instance.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return void
     */
    public function __construct(string $name, string $value)
    {
        parent::__construct($name);
        
        $this->setValue($value);
    }
    
    /**
     * Get the body.
     * 
     * @return string
     */
    public function getBody(): string
    {
        return $this->getValue();
    }

    /**
     * Set the body.
     * 
     * @param mixed $body
     * 
     * @return void
     */
    public function setBody(mixed $body): void
    {
        $this->setValue($body);
    }
    
    /**
     * Get the (unencoded) value of this header.
     *
     * @return string 
     */
    public function getValue(): string
    {
        return $this->value;
    }
    
    /**
     * Set the (unencoded) value of this header.
     * 
     * @param  string  $value
     * 
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
    
    /**
     * Get the value of this header prepared for rendering.
     * 
     * @return string 
     */
    public function getBodyAsString(): string
    {
        return ' <'.$this->getValue().'>';
    }
}