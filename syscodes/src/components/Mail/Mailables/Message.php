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

namespace Syscodes\Components\Mail\Mailables;

use Syscodes\Components\Mail\Headers\Headers;

/**
 * Sending of message
 */
class Message extends RawMessage
{
    /**
     * The body of a message.
     * 
     * @var string|null $body
     */
    protected string|null $body;

    /**
     * The headers for use in a message.
     * 
     * @var \Syscodes\Components\Mail\Headers\Headers $headers
     */
    protected Headers $headers;

    /**
     * Constructor. Create a new Message class instance.
     * 
     * @param  \Syscodes\Components\Mail\Headers  $headers
     * @param  string|null  $body
     * 
     * @return void
     */
    public function __construct(Headers $headers = null, string $body = null)
    {
        $this->headers = $headers ? clone $headers : new Headers;
        $this->body    = $body;
    }
    
    /**
     * Sets the body.
     * 
     * @param  mixed  $body
     * 
     * @return void
     */
    public function setBody(mixed $body): void
    {
        $this->body = $body;
    }
    
    /**
     * Gets the body.
     * 
     * @return mixed
     */    
    public function getBody(): mixed
    {
        return $this->body;
    }
    
    /**
     * Sets the headers.
     * 
     * @param  \Syscodes\Components\Mail\Headers\Headers  $headers
     * 
     * @return static
     */
    public function setHeaders(Headers $headers): static
    {
        $this->headers = $headers;
        
        return $this;
    }

    /**
     * Gets the headers.
     * 
     * @return \Syscodes\Components\Mail\Headers\Headers
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * Array representation of object.
     * 
     * @return array
     */
    public function __serialize(): array
    {
        return [$this->headers, $this->body];
    }

    /**
     * Constructs the object.
     * 
     * @param  string  $serialized
     * 
     * @return void
     */
    public function __unserialize(array $serialized): void
    {
        [$this->headers, $this->body] = $serialized;
    }
}